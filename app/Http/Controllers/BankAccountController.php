<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use Illuminate\Http\Request;

class BankAccountController extends Controller
{
    /**
     * Exibe a listagem de contas bancárias do usuário autenticado com estatísticas.
     */
    public function index()
    {
        $userId = auth()->id();

        // Carrega as contas bancárias do usuário
        $bankAccounts = BankAccount::where('user_id', $userId)
            ->orderBy('account_name')
            ->get();

        // Calcula saldo consolidado para cada conta e totais gerais
        $totalCombinedBalance = 0.00;
        $totalReceivedInAccounts = 0.00;
        $totalAccountsCount = $bankAccounts->count();

        foreach ($bankAccounts as $account) {
            // Soma receitas pagas da conta
            $incomesSum = (float) \App\Models\Transaction::where('bank_account_id', $account->id)
                ->where('type', 'entrada')
                ->where('status', 'pago')
                ->sum('amount');
            
            // Soma despesas pagas da conta
            $expensesSum = (float) \App\Models\Transaction::where('bank_account_id', $account->id)
                ->where('type', 'saida')
                ->where('status', 'pago')
                ->sum('amount');

            $account->current_balance = (float) $account->initial_balance + $incomesSum - $expensesSum;
            
            $totalCombinedBalance += $account->current_balance;
            $totalReceivedInAccounts += $incomesSum;
        }

        // Carrega os cartões de crédito do usuário
        $creditCards = \App\Models\CreditCard::where('user_id', $userId)
            ->orderBy('card_name')
            ->get();

        $totalCreditLimit = (float) $creditCards->sum('limit');
        $totalUsedLimit = 0.00;

        foreach ($creditCards as $card) {
            // Despesas de cartão no mês atual
            $cardUsed = (float) \App\Models\Transaction::where('credit_card_id', $card->id)
                ->where('type', 'saida')
                ->whereMonth('due_date', \Carbon\Carbon::now()->month)
                ->whereYear('due_date', \Carbon\Carbon::now()->year)
                ->sum('amount');

            $card->used_limit = $cardUsed;
            $card->available_limit = (float) $card->limit - $cardUsed;
            $totalUsedLimit += $cardUsed;
        }

        $totalAvailableLimit = $totalCreditLimit - $totalUsedLimit;
        $totalCardsCount = $creditCards->count();

        return view('bank_accounts.index', compact(
            'bankAccounts',
            'totalCombinedBalance',
            'totalReceivedInAccounts',
            'totalAccountsCount',
            'creditCards',
            'totalCreditLimit',
            'totalUsedLimit',
            'totalAvailableLimit',
            'totalCardsCount'
        ));
    }

    /**
     * Exibe o formulário de cadastro.
     */
    public function create()
    {
        return view('bank_accounts.create');
    }

    /**
     * Armazena uma nova conta bancária para o usuário logado.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'custom_bank_name' => 'nullable|required_if:bank_name,Outro|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:corrente,poupanca,digital,investimento,outros',
            'person_type' => 'required|in:PF,PJ',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'initial_balance' => 'required|string',
            'observations' => 'nullable|string',
        ]);

        // Processa o nome do banco se for customizado
        $bankName = $validated['bank_name'];
        if ($bankName === 'Outro') {
            $bankName = $validated['custom_bank_name'];
        }

        // Limpeza de valor formatado em reais
        $cleanValue = $validated['initial_balance'];
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $initialBalance = (float) $cleanValue;

        $account = BankAccount::create([
            'user_id' => auth()->id(),
            'bank_name' => $bankName,
            'account_name' => $validated['account_name'],
            'account_type' => $validated['account_type'],
            'person_type' => $validated['person_type'],
            'agency' => $validated['agency'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'initial_balance' => $initialBalance,
            'observations' => $validated['observations'] ?? null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Conta bancária cadastrada com sucesso!',
                'bank_account' => $account
            ]);
        }

        return redirect()->route('bank-accounts.index')->with('success', 'Conta bancária cadastrada com sucesso!');
    }

    /**
     * Exibe o formulário de edição de uma conta bancária.
     */
    public function edit(BankAccount $bankAccount)
    {
        // Tenancy Check
        abort_if($bankAccount->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        return view('bank_accounts.edit', compact('bankAccount'));
    }

    /**
     * Atualiza os dados de uma conta bancária do usuário logado.
     */
    public function update(Request $request, BankAccount $bankAccount)
    {
        // Tenancy Check
        abort_if($bankAccount->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'bank_name' => 'required|string|max:255',
            'custom_bank_name' => 'nullable|required_if:bank_name,Outro|string|max:255',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:corrente,poupanca,digital,investimento,outros',
            'person_type' => 'required|in:PF,PJ',
            'agency' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'initial_balance' => 'required|string',
            'observations' => 'nullable|string',
        ]);

        // Processa o nome do banco se for customizado
        $bankName = $validated['bank_name'];
        if ($bankName === 'Outro') {
            $bankName = $validated['custom_bank_name'];
        }

        // Limpeza de valor formatado em reais
        $cleanValue = $validated['initial_balance'];
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $initialBalance = (float) $cleanValue;

        $bankAccount->update([
            'bank_name' => $bankName,
            'account_name' => $validated['account_name'],
            'account_type' => $validated['account_type'],
            'person_type' => $validated['person_type'],
            'agency' => $validated['agency'] ?? null,
            'account_number' => $validated['account_number'] ?? null,
            'initial_balance' => $initialBalance,
            'observations' => $validated['observations'] ?? null,
        ]);

        return redirect()->route('bank-accounts.index')->with('success', 'Conta bancária atualizada com sucesso!');
    }

    /**
     * Exclui uma conta bancária do usuário logado.
     */
    public function destroy(BankAccount $bankAccount)
    {
        // Tenancy Check
        abort_if($bankAccount->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $bankAccount->delete();

        return redirect()->route('bank-accounts.index')->with('success', 'Conta bancária excluída com sucesso!');
    }

    /**
     * Exibe o extrato detalhado de uma conta bancária.
     */
    public function show(BankAccount $bankAccount)
    {
        $userId = auth()->id();
        abort_if($bankAccount->user_id !== $userId, 403, 'Ação não autorizada.');

        // Busca todas as transações vinculadas a esta conta ordenadas por data
        $transactions = \App\Models\Transaction::where('bank_account_id', $bankAccount->id)
            ->with('category')
            ->orderBy('due_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        // Calcula saldo atual dinâmico
        $incomesSum = (float) \App\Models\Transaction::where('bank_account_id', $bankAccount->id)
            ->where('type', 'entrada')
            ->where('status', 'pago')
            ->sum('amount');
        
        $expensesSum = (float) \App\Models\Transaction::where('bank_account_id', $bankAccount->id)
            ->where('type', 'saida')
            ->where('status', 'pago')
            ->sum('amount');

        $currentBalance = (float) $bankAccount->initial_balance + $incomesSum - $expensesSum;

        return view('bank_accounts.show', compact('bankAccount', 'transactions', 'currentBalance'));
    }

    /**
     * Ajuste manual de saldo (atualiza o initial_balance).
     */
    public function updateBalance(Request $request, BankAccount $bankAccount)
    {
        $userId = auth()->id();
        abort_if($bankAccount->user_id !== $userId, 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'initial_balance' => 'required|string',
        ]);

        $cleanValue = $validated['initial_balance'];
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $initialBalance = (float) $cleanValue;

        // Para ajustar o saldo de forma que bate com o desejado,
        // ajustamos o initial_balance subtraindo/somando a diferença das transações pagas.
        $incomesSum = (float) \App\Models\Transaction::where('bank_account_id', $bankAccount->id)
            ->where('type', 'entrada')
            ->where('status', 'pago')
            ->sum('amount');
        
        $expensesSum = (float) \App\Models\Transaction::where('bank_account_id', $bankAccount->id)
            ->where('type', 'saida')
            ->where('status', 'pago')
            ->sum('amount');

        // Novo initial_balance = saldo_desejado - receitas_pagas + despesas_pagas
        $newInitialBalance = $initialBalance - $incomesSum + $expensesSum;

        $bankAccount->update([
            'initial_balance' => $newInitialBalance,
        ]);

        return redirect()->route('bank-accounts.show', $bankAccount->id)->with('success', 'Saldo ajustado com sucesso!');
    }
}
