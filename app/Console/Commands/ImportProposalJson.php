<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Client;
use App\Models\Author;
use App\Models\Project;
use App\Models\Proposal;
use App\Models\Payment;
use App\Models\ProjectHistory;

class ImportProposalJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:proposal {file : The path to the exported JSON file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import proposal information from a JSON file matching Giro schema';

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

        if (!$decoded || !isset($decoded['format']) || !str_starts_with($decoded['format'], 'giro.orcamentos')) {
            $this->error("Invalid format or unsupported Giro JSON schema.");
            return Command::FAILURE;
        }

        $items = [];
        // Support both single budget data and array of budgets
        if (isset($decoded['data']['orcamento'])) {
            $items[] = $decoded['data'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            $items = $decoded['data'];
        } else {
            $this->error("Could not find budget data in JSON structure.");
            return Command::FAILURE;
        }

        $this->info("Found " . count($items) . " proposal(s) to import. Starting transaction...");
        
        $user = User::where('role', 'master')->first() ?? User::first();
        if (!$user) {
            $this->error("No master or system user found in database to associate imports.");
            return Command::FAILURE;
        }

        $importedCount = 0;

        DB::beginTransaction();

        try {
            foreach ($items as $item) {
                if (!isset($item['orcamento'])) {
                    continue;
                }

                $orcamento = $item['orcamento'];
                $clientData = $item['cliente'] ?? null;
                $authorsData = $item['autores'] ?? [];
                $paymentsData = $item['pagamentos'] ?? [];
                $historyData = $item['historico'] ?? [];

                // 1. Process client
                $clientId = null;
                if ($clientData) {
                    $clientEmail = (!empty($clientData['email'])) ? $clientData['email'] : 'cliente-sem-email-' . uniqid() . '@freela.com';
                    $client = Client::where('email', $clientEmail)->first();
                    if (!$client) {
                        $client = Client::create([
                            'user_id' => $user->id,
                            'name' => $clientData['nome'],
                            'email' => $clientEmail,
                            'avatar' => null,
                            'phone' => $clientData['telefone'] ?? $clientData['whatsapp'] ?? null,
                            'registration_completed' => true
                        ]);
                    }
                    $clientId = $client->id;
                }

                // 2. Create Project
                $project = Project::create([
                    'title' => $orcamento['titulo'],
                    'description' => $orcamento['descricao'],
                    'client_id' => $clientId,
                    'status' => $orcamento['status'] ?? 'analisando',
                    'total_value' => $orcamento['valor_total'],
                    'term' => $orcamento['prazo_dias'],
                    'budget_date' => $orcamento['data_orcamento'],
                    'expiration_date' => $orcamento['data_validade'],
                    'additional_info' => $orcamento['observacoes'],
                ]);

                // 3. Create Proposal Token Hash
                Proposal::create([
                    'project_id' => $project->id,
                    'hash' => $orcamento['token_publico'] ?? bin2hex(random_bytes(16)),
                    'status' => $project->status
                ]);

                // 4. Authors relationship
                if (!empty($authorsData)) {
                    $authorIds = [];
                    foreach ($authorsData as $authorData) {
                        $authorEmail = (!empty($authorData['email'])) ? $authorData['email'] : 'autor-importado-' . uniqid() . '@pendente.com';
                        $author = Author::where('email', $authorEmail)
                            ->orWhere('name', $authorData['nome'])
                            ->first();

                        if (!$author) {
                            $author = Author::create([
                                'user_id' => $user->id,
                                'name' => $authorData['nome'],
                                'email' => $authorEmail,
                                'avatar' => null,
                                'phone' => $authorData['telefone'] ?? $authorData['whatsapp'] ?? null,
                                'registration_completed' => true
                            ]);
                        }
                        $authorIds[] = $author->id;
                    }
                    $project->authors()->sync($authorIds);
                }

                // 5. Payments
                if (!empty($paymentsData)) {
                    foreach ($paymentsData as $paymentData) {
                        $bankAccountId = null;
                        if (!empty($paymentData['bank'])) {
                            $bankData = $paymentData['bank'];
                            $bankAccount = \App\Models\BankAccount::where('user_id', $user->id)
                                ->where(function($query) use ($bankData) {
                                    $query->where('account_name', $bankData['nome'] ?? '')
                                          ->orWhere('bank_name', $bankData['banco'] ?? '');
                                })
                                ->first();
                            
                            if (!$bankAccount) {
                                $bankAccount = \App\Models\BankAccount::create([
                                    'user_id' => $user->id,
                                    'bank_name' => $bankData['banco'] ?? 'Outro',
                                    'account_name' => $bankData['nome'] ?? 'Conta Importada',
                                    'account_type' => $bankData['tipo_conta'] ?? 'Conta Corrente',
                                    'person_type' => $bankData['titular_tipo'] ?? 'pf',
                                    'agency' => $bankData['agencia'] ?? null,
                                    'account_number' => $bankData['numero_conta'] ?? null,
                                    'initial_balance' => 0.00
                                ]);
                            }
                            $bankAccountId = $bankAccount->id;
                        }

                        Payment::create([
                            'project_id' => $project->id,
                            'amount' => $paymentData['valor'],
                            'paid_at' => $paymentData['data_pagamento'],
                            'payment_method' => $paymentData['metodo'] ?? $paymentData['forma_pagamento'] ?? 'transferência',
                            'bank_account_id' => $bankAccountId,
                            'observations' => $paymentData['observacoes'] ?? null
                        ]);
                    }
                }

                // 6. Histories
                if (!empty($historyData)) {
                    // Clear default created history generated by booted hook to avoid duplicate details
                    $project->histories()->delete();

                    foreach ($historyData as $hist) {
                        ProjectHistory::create([
                            'project_id' => $project->id,
                            'user_id' => $user->id,
                            'action' => $hist['acao'],
                            'title' => $project->title,
                            'description' => $hist['descricao'],
                            'total_value' => $project->total_value,
                            'term' => $project->term,
                            'budget_date' => $project->budget_date,
                            'expiration_date' => $project->expiration_date,
                            'status' => $project->status,
                            'created_at' => $hist['created_at']
                        ]);
                    }
                }

                $importedCount++;
            }

            DB::commit();
            $this->info("Import completed successfully! {$importedCount} proposal(s) imported.");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Failed to import proposals: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
