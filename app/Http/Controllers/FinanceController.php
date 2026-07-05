<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\BankAccount;
use App\Models\CreditCard;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FinanceController extends Controller
{
    /**
     * Listagem principal e Dashboard de Controle Financeiro.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();

        // Parâmetros de ano/mês (padrão: mês e ano atual)
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $selectedDate = Carbon::createFromDate($year, $month, 1);
        $startOfMonth = $selectedDate->copy()->startOfMonth();
        $endOfMonth = $selectedDate->copy()->endOfMonth();

        // Filtros
        $search = $request->input('search');
        $classification = $request->input('classification'); // 'PF', 'PJ' ou 'all'
        $status = $request->input('status'); // 'pago', 'pendente' ou 'all'
        $categoryId = $request->input('category_id');

        // Query Base de Transações do usuário no mês
        $query = Transaction::where('user_id', $userId)
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->with(['category', 'bankAccount', 'creditCard']);

        if ($search) {
            $query->where('description', 'like', "%{$search}%");
        }
        if ($classification && $classification !== 'all') {
            $query->where('classification', $classification);
        }
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }
        if ($categoryId && $categoryId !== 'all') {
            $query->where('category_id', $categoryId);
        }

        $allTransactions = $query->orderBy('due_date', 'desc')->get();

        // Separa as transações comuns (Dinheiro, Conta Bancária) das de Cartão de Crédito
        $commonTransactions = $allTransactions->filter(fn($t) => is_null($t->credit_card_id));
        $cardTransactionsRaw = $allTransactions->filter(fn($t) => !is_null($t->credit_card_id));

        // Agrupa as transações de cartão de crédito por cartão
        $cardGroups = [];
        $groupedByCard = $cardTransactionsRaw->groupBy('credit_card_id');
        foreach ($groupedByCard as $cardId => $transactions) {
            $card = CreditCard::find($cardId);
            if ($card) {
                $cardGroups[] = [
                    'card' => $card,
                    'total_amount' => $transactions->sum('amount'),
                    'transactions' => $transactions,
                ];
            }
        }

        // Estatísticas Financeiras do Mês (Somente transações com due_date neste mês)
        $totalIncomes = Transaction::where('user_id', $userId)
            ->where('type', 'entrada')
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);
            
        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()]);

        // Previsto (independe de status)
        $previstoIncomes = (float) $totalIncomes->sum('amount');
        $previstoExpenses = (float) $totalExpenses->sum('amount');
        $previstoBalance = $previstoIncomes - $previstoExpenses;

        // Realizado (somente pagas/recebidas)
        $realizadoIncomes = (float) Transaction::where('user_id', $userId)
            ->where('type', 'entrada')
            ->where('status', 'pago')
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('amount');
            
        $realizadoExpenses = (float) Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->where('status', 'pago')
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('amount');
        $realizadoBalance = $realizadoIncomes - $realizadoExpenses;

        // Categorias para o filtro
        $categories = TransactionCategory::forUser($userId)->get();

        return view('finances.index', compact(
            'commonTransactions',
            'cardGroups',
            'month',
            'year',
            'previstoIncomes',
            'previstoExpenses',
            'previstoBalance',
            'realizadoIncomes',
            'realizadoExpenses',
            'realizadoBalance',
            'categories',
            'search',
            'classification',
            'status',
            'categoryId'
        ));
    }

    /**
     * Formulário de criação.
     */
    public function create()
    {
        $userId = auth()->id();

        $categories = TransactionCategory::forUser($userId)->get();
        $bankAccounts = BankAccount::where('user_id', $userId)->orderBy('account_name')->get();
        $creditCards = CreditCard::where('user_id', $userId)->orderBy('card_name')->get();
        
        // Projetos ativos para vincular receitas
        $projects = Project::whereHas('client', fn($q) => $q->where('user_id', $userId))
            ->where('status', '!=', 'rejeitado')
            ->orderBy('title')
            ->get();

        return view('finances.create', compact('categories', 'bankAccounts', 'creditCards', 'projects'));
    }

    /**
     * Armazena a transação no banco (suporta parcelamento e recorrência).
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'type' => 'required|in:entrada,saida',
            'description' => 'required|string|max:255',
            'amount' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pago,pendente',
            'classification' => 'required|in:PF,PJ',
            'category_id' => 'required|integer',
            'bank_account_id' => 'nullable|integer',
            'credit_card_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'repeat_type' => 'required|in:single,installments,recurring',
            'installment_mode' => 'nullable|required_if:repeat_type,installments|in:total,installment',
            'installments_count' => 'nullable|required_if:repeat_type,installments|integer|min:1',
            'recurrence_period' => 'nullable|required_if:repeat_type,recurring|in:diaria,semanal,mensal,anual',
            'attachment' => 'nullable|file|max:10240', // 10MB
        ]);

        // Limpa valor formatado brasileiro
        $amountRaw = $validated['amount'];
        $amountRaw = str_replace('R$', '', $amountRaw);
        $amountRaw = trim($amountRaw);
        $amountRaw = str_replace('.', '', $amountRaw);
        $amountRaw = str_replace(',', '.', $amountRaw);
        $amount = (float) $amountRaw;

        // Trata anexo
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('financial_attachments', 'local');
        }

        $bankAccountId = $validated['bank_account_id'] ?? null;
        $creditCardId = $validated['credit_card_id'] ?? null;
        $projectId = $validated['project_id'] ?? null;

        // Valida bank account / credit card tenancy
        if ($bankAccountId) {
            BankAccount::where('user_id', $userId)->findOrFail($bankAccountId);
        }
        if ($creditCardId) {
            CreditCard::where('user_id', $userId)->findOrFail($creditCardId);
        }

        $baseDate = Carbon::parse($validated['due_date']);
        $groupCode = ($validated['repeat_type'] !== 'single') ? Str::uuid()->toString() : null;

        if ($validated['repeat_type'] === 'single') {
            // Transação única
            Transaction::create([
                'user_id' => $userId,
                'project_id' => $projectId,
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'bank_account_id' => $bankAccountId,
                'credit_card_id' => $creditCardId,
                'type' => $validated['type'],
                'amount' => $amount,
                'due_date' => $validated['due_date'],
                'paid_at' => $validated['status'] === 'pago' ? $validated['due_date'] : null,
                'status' => $validated['status'],
                'attachment_path' => $attachmentPath,
                'classification' => $validated['classification'],
            ]);
        } elseif ($validated['repeat_type'] === 'installments') {
            // Transação parcelada
            $installments = (int) $validated['installments_count'];
            $mode = $validated['installment_mode'];

            $installmentAmount = $amount;
            if ($mode === 'total') {
                $installmentAmount = round($amount / $installments, 2);
            }

            for ($i = 1; $i <= $installments; $i++) {
                $dueDate = $baseDate->copy()->addMonths($i - 1);
                
                // Tratamento de arredondamento na última parcela se for divisão do total
                if ($mode === 'total' && $i === $installments) {
                    $installmentAmount = $amount - ($installmentAmount * ($installments - 1));
                }

                Transaction::create([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'description' => $validated['description'] . " ({$i}/{$installments})",
                    'category_id' => $validated['category_id'],
                    'bank_account_id' => $bankAccountId,
                    'credit_card_id' => $creditCardId,
                    'type' => $validated['type'],
                    'amount' => $installmentAmount,
                    'due_date' => $dueDate->toDateString(),
                    // A primeira parcela herda o status informado. As parcelas futuras são geradas como pendentes
                    'paid_at' => ($i === 1 && $validated['status'] === 'pago') ? $dueDate->toDateString() : null,
                    'status' => ($i === 1) ? $validated['status'] : 'pendente',
                    'attachment_path' => $attachmentPath,
                    'classification' => $validated['classification'],
                    'group_code' => $groupCode,
                    'installment_number' => $i,
                    'total_installments' => $installments,
                ]);
            }
        } elseif ($validated['repeat_type'] === 'recurring') {
            // Transação recorrente (pré-popula 12 ocorrências)
            $period = $validated['recurrence_period'];

            for ($i = 1; $i <= 12; $i++) {
                $dueDate = $baseDate->copy();
                if ($period === 'diaria') {
                    $dueDate->addDays($i - 1);
                } elseif ($period === 'semanal') {
                    $dueDate->addWeeks($i - 1);
                } elseif ($period === 'mensal') {
                    $dueDate->addMonths($i - 1);
                } elseif ($period === 'anual') {
                    $dueDate->addYears($i - 1);
                }

                Transaction::create([
                    'user_id' => $userId,
                    'project_id' => $projectId,
                    'description' => $validated['description'] . " (Recorrente)",
                    'category_id' => $validated['category_id'],
                    'bank_account_id' => $bankAccountId,
                    'credit_card_id' => $creditCardId,
                    'type' => $validated['type'],
                    'amount' => $amount,
                    'due_date' => $dueDate->toDateString(),
                    // A primeira ocorrência herda o status informado. As futuras são geradas como pendentes
                    'paid_at' => ($i === 1 && $validated['status'] === 'pago') ? $dueDate->toDateString() : null,
                    'status' => ($i === 1) ? $validated['status'] : 'pendente',
                    'attachment_path' => $attachmentPath,
                    'classification' => $validated['classification'],
                    'group_code' => $groupCode,
                    'recurrence' => $period,
                ]);
            }
        }

        return redirect()->route('finances.index')->with('success', 'Movimentação registrada com sucesso!');
    }

    /**
     * Formulário de edição.
     */
    public function edit(Transaction $finance)
    {
        $userId = auth()->id();
        abort_if($finance->user_id !== $userId, 403, 'Ação não autorizada.');

        $categories = TransactionCategory::forUser($userId)->get();
        $bankAccounts = BankAccount::where('user_id', $userId)->orderBy('account_name')->get();
        $creditCards = CreditCard::where('user_id', $userId)->orderBy('card_name')->get();
        
        $projects = Project::whereHas('client', fn($q) => $q->where('user_id', $userId))
            ->where('status', '!=', 'rejeitado')
            ->orderBy('title')
            ->get();

        return view('finances.edit', compact('finance', 'categories', 'bankAccounts', 'creditCards', 'projects'));
    }

    /**
     * Atualiza os dados da transação.
     */
    public function update(Request $request, Transaction $finance)
    {
        $userId = auth()->id();
        abort_if($finance->user_id !== $userId, 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|string',
            'due_date' => 'required|date',
            'status' => 'required|in:pago,pendente',
            'classification' => 'required|in:PF,PJ',
            'category_id' => 'required|integer',
            'bank_account_id' => 'nullable|integer',
            'credit_card_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'attachment' => 'nullable|file|max:10240',
            'update_all_future' => 'nullable|boolean', // editar parcelas futuras vinculadas
        ]);

        $amountRaw = $validated['amount'];
        $amountRaw = str_replace('R$', '', $amountRaw);
        $amountRaw = trim($amountRaw);
        $amountRaw = str_replace('.', '', $amountRaw);
        $amountRaw = str_replace(',', '.', $amountRaw);
        $amount = (float) $amountRaw;

        // Trata anexo
        $attachmentPath = $finance->attachment_path;
        if ($request->hasFile('attachment')) {
            if ($finance->attachment_path && Storage::disk('local')->exists($finance->attachment_path)) {
                Storage::disk('local')->delete($finance->attachment_path);
            }
            $attachmentPath = $request->file('attachment')->store('financial_attachments', 'local');
        }

        $bankAccountId = $validated['bank_account_id'] ?? null;
        $creditCardId = $validated['credit_card_id'] ?? null;
        $projectId = $validated['project_id'] ?? null;

        if ($bankAccountId) {
            BankAccount::where('user_id', $userId)->findOrFail($bankAccountId);
        }
        if ($creditCardId) {
            CreditCard::where('user_id', $userId)->findOrFail($creditCardId);
        }

        // Lógica de Atualização (Única vs lote futuro)
        if ($request->input('update_all_future') && $finance->group_code) {
            // Atualiza a transação atual e todas as outras transações pendentes/futuras do mesmo grupo
            $futureTransactions = Transaction::where('user_id', $userId)
                ->where('group_code', $finance->group_code)
                ->where('due_date', '>=', $finance->due_date->toDateString())
                ->get();

            foreach ($futureTransactions as $t) {
                $t->update([
                    'description' => $validated['description'] . ($t->installment_number ? " ({$t->installment_number}/{$t->total_installments})" : ($t->recurrence ? " (Recorrente)" : "")),
                    'amount' => $amount,
                    'category_id' => $validated['category_id'],
                    'bank_account_id' => $bankAccountId,
                    'credit_card_id' => $creditCardId,
                    'classification' => $validated['classification'],
                    'attachment_path' => $attachmentPath,
                ]);
            }
        } else {
            // Atualiza apenas esta transação
            $finance->update([
                'description' => $validated['description'],
                'amount' => $amount,
                'due_date' => $validated['due_date'],
                'status' => $validated['status'],
                'paid_at' => $validated['status'] === 'pago' ? $validated['due_date'] : null,
                'category_id' => $validated['category_id'],
                'bank_account_id' => $bankAccountId,
                'credit_card_id' => $creditCardId,
                'project_id' => $projectId,
                'classification' => $validated['classification'],
                'attachment_path' => $attachmentPath,
            ]);
        }

        return redirect()->route('finances.index')->with('success', 'Movimentação atualizada com sucesso!');
    }

    /**
     * Clona/Duplica a transação e redireciona para a tela de cadastro pré-preenchida.
     */
    public function duplicate(Transaction $transaction)
    {
        $userId = auth()->id();
        abort_if($transaction->user_id !== $userId, 403, 'Ação não autorizada.');

        // Clona a transação e salva como nova transação única pendente na data de hoje
        $newTransaction = $transaction->replicate();
        $newTransaction->due_date = Carbon::now()->toDateString();
        $newTransaction->paid_at = null;
        $newTransaction->status = 'pendente';
        $newTransaction->group_code = null;
        $newTransaction->installment_number = null;
        $newTransaction->total_installments = null;
        $newTransaction->recurrence = null;
        $newTransaction->save();

        return redirect()->route('finances.edit', $newTransaction->id)->with('success', 'Transação duplicada! Ajuste os dados conforme necessário.');
    }

    /**
     * Alterna rapidamente o status de Pago <-> Pendente.
     */
    public function toggleStatus(Transaction $transaction)
    {
        $userId = auth()->id();
        abort_if($transaction->user_id !== $userId, 403, 'Ação não autorizada.');

        $newStatus = $transaction->status === 'pago' ? 'pendente' : 'pago';
        
        $transaction->update([
            'status' => $newStatus,
            'paid_at' => $newStatus === 'pago' ? Carbon::now()->toDateString() : null,
        ]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'paid_at' => $transaction->paid_at ? Carbon::parse($transaction->paid_at)->format('d/m/Y') : '-'
            ]);
        }

        return back()->with('success', 'Status da transação atualizado com sucesso!');
    }

    /**
     * Remove a transação.
     */
    public function destroy(Request $request, Transaction $finance)
    {
        $userId = auth()->id();
        abort_if($finance->user_id !== $userId, 403, 'Ação não autorizada.');

        // Se o usuário optou por excluir todo o grupo de parcelas/recorrências
        if ($request->input('delete_all') && $finance->group_code) {
            Transaction::where('user_id', $userId)
                ->where('group_code', $finance->group_code)
                ->get()
                ->each(function ($t) {
                    if ($t->attachment_path && Storage::disk('local')->exists($t->attachment_path)) {
                        Storage::disk('local')->delete($t->attachment_path);
                    }
                    $t->delete();
                });

            return redirect()->route('finances.index')->with('success', 'Todas as parcelas/recorrências vinculadas foram excluídas.');
        }

        if ($finance->attachment_path && Storage::disk('local')->exists($finance->attachment_path)) {
            Storage::disk('local')->delete($finance->attachment_path);
        }
        $finance->delete();

        return redirect()->route('finances.index')->with('success', 'Movimentação excluída com sucesso!');
    }

    /**
     * Faz o download seguro do anexo da transação.
     */
    public function downloadAttachment(Transaction $transaction)
    {
        abort_if($transaction->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        if (!$transaction->attachment_path || !Storage::disk('local')->exists($transaction->attachment_path)) {
            abort(404, 'Anexo não encontrado.');
        }

        return Storage::disk('local')->download($transaction->attachment_path, basename($transaction->attachment_path));
    }

    /**
     * Marca todas as despesas pendentes da fatura de um cartão de crédito no mês/ano como pagas.
     */
    public function payInvoice(Request $request, \App\Models\CreditCard $creditCard)
    {
        $userId = auth()->id();
        abort_if($creditCard->user_id !== $userId, 403, 'Ação não autorizada.');

        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer',
        ]);

        $month = (int) $request->input('month');
        $year = (int) $request->input('year');

        Transaction::where('user_id', $userId)
            ->where('credit_card_id', $creditCard->id)
            ->where('type', 'saida')
            ->where('status', 'pendente')
            ->whereMonth('due_date', $month)
            ->whereYear('due_date', $year)
            ->update([
                'status' => 'pago',
                'paid_at' => Carbon::now()->toDateString(),
            ]);

        return back()->with('success', 'Fatura do cartão de crédito marcada como paga!');
    }

    /**
     * Importa transações financeiras a partir do JSON Giro.
     */
    public function importJson(Request $request)
    {
        $request->validate([
            'json_data' => 'required|string',
        ]);

        $decoded = json_decode($request->json_data, true);

        if (!$decoded || !isset($decoded['format']) || !str_starts_with($decoded['format'], 'giro.transactions')) {
            return response()->json([
                'success' => false,
                'message' => 'O formato do JSON não é compatível ou não é um export de transações do Giro válido.'
            ], 422);
        }

        $items = [];
        if (isset($decoded['data']['transaction'])) {
            $items[] = $decoded['data'];
        } elseif (isset($decoded['data']) && is_array($decoded['data'])) {
            $items = $decoded['data'];
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Não foi possível encontrar a estrutura de transações no JSON.'
            ], 422);
        }

        $user = auth()->user();
        $importedCount = 0;

        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Track imported unique signatures to avoid duplicates
            // Signature format: "description|amount|due_date"
            $existingSignatures = \App\Models\Transaction::where('user_id', $user->id)
                ->get()
                ->map(function($t) {
                    $dueDateStr = $t->due_date ? \Carbon\Carbon::parse($t->due_date)->toDateString() : '';
                    return "{$t->description}|{$t->amount}|{$dueDateStr}";
                })
                ->toArray();

            foreach ($items as $item) {
                // Collect main transaction and related installments if they exist
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
                    
                    // Map type: 'despesa' -> 'saida', 'receita' -> 'entrada'
                    $type = ($tx['tipo'] === 'despesa') ? 'saida' : 'entrada';
                    $amount = (float) $tx['valor'];
                    $dueDate = $tx['data'];
                    
                    $signature = "{$tx['descricao']}|{$amount}|{$dueDate}";
                    
                    if (in_array($signature, $existingSignatures)) {
                        continue; // Skip duplicate
                    }

                    // 1. Process Bank Account
                    $bankAccountId = null;
                    if ($txData['bank'] && isset($txData['bank']['nome'])) {
                        $bankData = $txData['bank'];
                        $bankAcc = \App\Models\BankAccount::where('user_id', $user->id)
                            ->where('account_name', $bankData['nome'])
                            ->first();
                        
                        if (!$bankAcc) {
                            $bankAcc = \App\Models\BankAccount::create([
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

                    // 2. Process Credit Card
                    $creditCardId = null;
                    if ($txData['card'] && (isset($txData['card']['nome_cartao']) || isset($txData['card']['nome']))) {
                        $cardData = $txData['card'];
                        $cardName = $cardData['nome_cartao'] ?? $cardData['nome'];
                        $cardObj = \App\Models\CreditCard::where('user_id', $user->id)
                            ->where('card_name', $cardName)
                            ->first();

                        if (!$cardObj) {
                            $cardObj = \App\Models\CreditCard::create([
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

                    // 3. Process Category
                    $categoryId = null;
                    if ($txData['category'] && isset($txData['category']['nome'])) {
                        $catData = $txData['category'];
                        $category = \App\Models\TransactionCategory::where('user_id', $user->id)
                            ->where('name', $catData['nome'])
                            ->where('type', $type)
                            ->first();

                        if (!$category) {
                            $category = \App\Models\TransactionCategory::create([
                                'user_id' => $user->id,
                                'name' => $catData['nome'],
                                'type' => $type,
                                'color' => $catData['cor'] ?? '#6B7280',
                                'icon' => 'tag',
                            ]);
                        }
                        $categoryId = $category->id;
                    }

                    // 4. Create Transaction
                    \App\Models\Transaction::create([
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

            \Illuminate\Support\Facades\DB::commit();

            session()->flash('success', "Importação de finanças realizada com sucesso! {$importedCount} transações importadas.");

            return response()->json([
                'success' => true,
                'message' => "Importação concluída! {$importedCount} transações importadas."
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erro durante a importação de transações: ' . $e->getMessage()
            ], 500);
        }
    }
}
