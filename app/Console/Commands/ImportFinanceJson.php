<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\BankAccount;
use App\Models\CreditCard;
use App\Models\TransactionCategory;
use App\Models\Transaction;

class ImportFinanceJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:finance {file : The path to the exported transactions JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import financial transactions from a JSON file matching Giro transactions schema';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->argument('file');

        if (!file_exists($filePath)) {
            $this->error("File not found at: {$filePath}");
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($filePath);
        $decoded = json_decode($jsonContent, true);

        if (!$decoded || !isset($decoded['format']) || !str_starts_with($decoded['format'], 'giro.transactions')) {
            $this->error("Invalid format or unsupported Giro transactions JSON schema.");
            return Command::FAILURE;
        }

        $items = [];
        if (isset($decoded['data']['transaction'])) {
            $items[] = $decoded['data'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            $items = $decoded['data'];
        } else {
            $this->error("Could not find transactions data in JSON structure.");
            return Command::FAILURE;
        }

        $this->info("Found " . count($items) . " main transaction(s) to process. Starting transaction...");
        
        $user = User::where('role', 'master')->first() ?? User::first();
        if (!$user) {
            $this->error("No system user found in database to associate imports.");
            return Command::FAILURE;
        }

        $importedCount = 0;

        DB::beginTransaction();

        try {
            $existingSignatures = Transaction::where('user_id', $user->id)
                ->get()
                ->map(function($t) {
                    $dueDateStr = $t->due_date ? \Carbon\Carbon::parse($t->due_date)->toDateString() : '';
                    return "{$t->description}|{$t->amount}|{$dueDateStr}";
                })
                ->toArray();

            foreach ($items as $item) {
                $transactionsToProcess = [];
                if (isset($item['transaction'])) {
                    $transactionsToProcess[] = [
                        'tx' => $item['transaction'],
                        'bank' => $item['bank'] ?? null,
                        'card' => $item['credit_card'] ?? null,
                        'category' => $item['category'] ?? null
                    ];
                }

                if (!empty($item['related_installments'])) {
                    foreach ($item['related_installments'] as $inst) {
                        $transactionsToProcess[] = [
                            'tx' => $inst,
                            'bank' => $item['bank'] ?? null,
                            'card' => $item['credit_card'] ?? null,
                            'category' => $item['category'] ?? null
                        ];
                    }
                }

                foreach ($transactionsToProcess as $txData) {
                    $tx = $txData['tx'];
                    
                    $type = ($tx['tipo'] === 'despesa') ? 'saida' : 'entrada';
                    $amount = (float) $tx['valor'];
                    $dueDate = $tx['data'];
                    
                    $signature = "{$tx['descricao']}|{$amount}|{$dueDate}";
                    
                    if (in_array($signature, $existingSignatures)) {
                        continue;
                    }

                    // 1. Bank Account
                    $bankAccountId = null;
                    if ($txData['bank'] && isset($txData['bank']['nome'])) {
                        $bankData = $txData['bank'];
                        $bankAcc = BankAccount::where('user_id', $user->id)
                            ->where('account_name', $bankData['nome'])
                            ->first();
                        
                        if (!$bankAcc) {
                            $bankAcc = BankAccount::create([
                                'user_id' => $user->id,
                                'bank_name' => $bankData['banco'] ?? $bankData['nome'],
                                'account_name' => $bankData['nome'],
                                'account_type' => $bankData['tipo_conta'] ?? 'Corrente',
                                'person_type' => $bankData['titular_tipo'] ?? 'pf',
                                'agency' => $bankData['agencia'] ?? null,
                                'account_number' => $bankData['numero_conta'] ?? $bankData['conta'] ?? null,
                                'initial_balance' => $bankData['saldo_inicial'] ?? 0.00,
                            ]);
                        }
                        $bankAccountId = $bankAcc->id;
                    }

                    // 2. Credit Card
                    $creditCardId = null;
                    if ($txData['card'] && (isset($txData['card']['nome_cartao']) || isset($txData['card']['nome']))) {
                        $cardData = $txData['card'];
                        $cardName = $cardData['nome_cartao'] ?? $cardData['nome'];
                        $cardObj = CreditCard::where('user_id', $user->id)
                            ->where('card_name', $cardName)
                            ->first();

                        if (!$cardObj) {
                            $cardObj = CreditCard::create([
                                'user_id' => $user->id,
                                'card_name' => $cardName,
                                'bank_name' => $cardData['banco'] ?? $cardName ?? 'Nubank',
                                'flag' => $cardData['bandeira'] ?? 'mastercard',
                                'limit' => $cardData['limite_total'] ?? $cardData['limite'] ?? $cardData['limit'] ?? 0.00,
                                'closing_day' => $cardData['data_fechamento'] ?? 10,
                                'due_day' => $cardData['data_vencimento'] ?? 15,
                            ]);
                        }
                        $creditCardId = $cardObj->id;
                    }

                    // 3. Category
                    $categoryId = null;
                    if ($txData['category'] && isset($txData['category']['nome'])) {
                        $catData = $txData['category'];
                        $mappedCategoryType = ($type === 'saida') ? 'despesa' : 'receita';
                        $category = TransactionCategory::where('user_id', $user->id)
                            ->where('name', $catData['nome'])
                            ->where('type', $mappedCategoryType)
                            ->first();

                        if (!$category) {
                            $category = TransactionCategory::create([
                                'user_id' => $user->id,
                                'name' => $catData['nome'],
                                'type' => $mappedCategoryType,
                                'color' => $catData['cor'] ?? '#6B7280',
                                'icon' => 'tag',
                            ]);
                        }
                        $categoryId = $category->id;
                    }

                    // 4. Create Transaction
                    Transaction::create([
                        'user_id' => $user->id,
                        'description' => $tx['descricao'],
                        'category_id' => $categoryId,
                        'bank_account_id' => $bankAccountId,
                        'credit_card_id' => $creditCardId,
                        'type' => $type,
                        'amount' => $amount,
                        'due_date' => $dueDate,
                        'paid_at' => $tx['data_pagamento'] ?? null,
                        'status' => $tx['status'] ?? 'pendente',
                        'classification' => $tx['classification'] ?? 'PF',
                    ]);

                    $existingSignatures[] = $signature;
                    $importedCount++;
                }
            }

            DB::commit();
            $this->info("Import completed successfully! {$importedCount} transaction(s) imported.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to import transactions: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
