<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class PaymentController extends Controller
{
    /**
     * Exibe o calendário de pagamentos e a listagem mensal.
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

        // Busca pagamentos pertencentes ao usuário logado no período
        $payments = Payment::whereHas('project.client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereBetween('paid_at', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
        ->with(['project.client', 'relatedProjects'])
        ->orderBy('paid_at', 'desc')
        ->get();

        // Estatísticas Resumo
        $totalMonth = $payments->sum('amount');
        $paymentsCount = $payments->count();
        $daysWithPaymentCount = $payments->pluck('paid_at')->map(fn($d) => $d->toDateString())->unique()->count();

        // Gerar calendário
        $startOfWeekday = $startOfMonth->dayOfWeek; // 0 = Domingo, 6 = Sábado
        $calendarDays = [];

        // Padding do mês anterior
        $prevMonth = $startOfMonth->copy()->subMonth();
        $prevMonthDaysCount = $prevMonth->daysInMonth;
        for ($i = $startOfWeekday - 1; $i >= 0; $i--) {
            $dayNum = $prevMonthDaysCount - $i;
            $dateStr = $prevMonth->copy()->day($dayNum)->toDateString();
            $calendarDays[] = [
                'day' => $dayNum,
                'date' => $dateStr,
                'is_current_month' => false,
                'payments_sum' => 0,
                'payments_count' => 0,
            ];
        }

        // Dias do mês corrente
        $daysInMonth = $startOfMonth->daysInMonth;
        $paymentsByDay = $payments->groupBy(fn($p) => $p->paid_at->toDateString());

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $dateStr = $selectedDate->copy()->day($day)->toDateString();
            $dayPayments = $paymentsByDay->get($dateStr, collect());
            
            $calendarDays[] = [
                'day' => $day,
                'date' => $dateStr,
                'is_current_month' => true,
                'payments_sum' => (float) $dayPayments->sum('amount'),
                'payments_count' => $dayPayments->count(),
            ];
        }

        // Padding do mês posterior para fechar grid de 7 colunas
        $totalCells = ceil(count($calendarDays) / 7) * 7;
        $nextMonth = $startOfMonth->copy()->addMonth();
        $nextDayNum = 1;
        while (count($calendarDays) < $totalCells) {
            $dateStr = $nextMonth->copy()->day($nextDayNum)->toDateString();
            $calendarDays[] = [
                'day' => $nextDayNum,
                'date' => $dateStr,
                'is_current_month' => false,
                'payments_sum' => 0,
                'payments_count' => 0,
            ];
            $nextDayNum++;
        }

        // Agrupamento para os cards empilhados
        $groupedPayments = $payments->groupBy('project_id');
        $projectPayments = [];
        
        foreach ($groupedPayments as $projectId => $projectGroup) {
            $firstPayment = $projectGroup->first();
            $project = $firstPayment->project;

            $projectPayments[] = [
                'project' => $project,
                'client' => $project->client,
                'payments' => $projectGroup->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'paid_at' => $p->paid_at->format('d/m/Y'),
                    'payment_method' => $p->payment_method,
                    'bank_account' => $p->bank_account ?? '-',
                    'observations' => $p->observations,
                    'download_invoice_url' => $p->invoice_path ? route('payments.download-invoice', $p->id) : null,
                    'related_projects' => $p->relatedProjects->pluck('title')->toArray(),
                    'edit_url' => route('payments.edit', $p->id),
                    'destroy_url' => route('payments.destroy', $p->id)
                ])->toArray()
            ];
        }

        return view('payments.index', compact(
            'calendarDays',
            'projectPayments',
            'month',
            'year',
            'totalMonth',
            'paymentsCount',
            'daysWithPaymentCount'
        ));
    }

    /**
     * Exibe o formulário de cadastro de pagamento.
     */
    public function create(Request $request)
    {
        $userId = auth()->id();

        // Projetos que não estejam rejeitados nem quitados, e tenham saldo devedor restante
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', '!=', 'rejeitado')
        ->where('status', '!=', 'quitado')
        ->orderBy('title')
        ->get()
        ->filter(function ($proj) {
            return $proj->remaining_balance > 0.005;
        });

        // Todos os projetos ativos para o select de vinculação secundária de Notas Fiscais (somente aprovados, quitados e finalizados)
        $allProjects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereIn('status', ['aprovado', 'quitado', 'finalizado'])
        ->orderBy('title')
        ->get();

        // Pré-seleção e cálculo de saldo restante se project_id vier na URL
        $selectedProjectId = $request->input('project_id');
        $selectedProject = null;
        $remainingBalance = 0.00;

        if ($selectedProjectId) {
            $selectedProject = Project::whereHas('client', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->findOrFail($selectedProjectId);

            if ($selectedProject->status === 'rejeitado') {
                return redirect()->route('payments.index')->with('error', 'Não é possível registrar pagamentos para um orçamento com status de rejeitado.');
            }
            if ($selectedProject->status === 'quitado') {
                return redirect()->route('payments.index')->with('error', 'Este orçamento já está totalmente pago (quitado).');
            }
            if ($selectedProject->remaining_balance <= 0.005) {
                return redirect()->route('payments.index')->with('error', 'Este orçamento não possui saldo restante para novos pagamentos.');
            }

            $remainingBalance = $selectedProject->remaining_balance;
        }

        // Criar um array mapeado com os saldos de todos os projetos para o Alpine.js usar reativamente
        $projectBalances = [];
        foreach ($projects as $proj) {
            $projectBalances[$proj->id] = (float) $proj->remaining_balance;
        }

        $today = Carbon::now()->toDateString();

        // Contas bancárias cadastradas do usuário logado
        $bankAccounts = \App\Models\BankAccount::where('user_id', $userId)->orderBy('account_name')->get();

        return view('payments.create', compact(
            'projects',
            'allProjects',
            'selectedProjectId',
            'selectedProject',
            'remainingBalance',
            'projectBalances',
            'today',
            'bankAccounts'
        ));
    }

    /**
     * Armazena um pagamento no banco.
     */
    public function store(Request $request)
    {
        $userId = auth()->id();

        $validated = $request->validate([
            'project_id' => 'required|integer',
            'amount' => 'required|string',
            'paid_at' => 'required|date',
            'payment_method' => 'required|in:pix,dinheiro,deposito,outros',
            'bank_account' => 'nullable|string|max:255',
            'bank_account_id' => 'nullable|integer',
            'observations' => 'nullable|string',
            'invoice' => 'nullable|file|max:10240', // 10MB
            'related_project_ids' => 'nullable|array',
            'related_project_ids.*' => 'integer',
        ]);

        // Valida tenancy do projeto principal
        $project = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($validated['project_id']);

        // Valida tenancy da conta bancária se fornecida
        $bankAccountId = $validated['bank_account_id'] ?? null;
        $bankAccountStr = $validated['bank_account'] ?? null;

        if ($bankAccountId) {
            $bankAccount = \App\Models\BankAccount::where('user_id', $userId)->findOrFail($bankAccountId);
            $bankAccountStr = $bankAccount->bank_name . ' (' . $bankAccount->account_name . ')';
        }

        // Trava 5: se tiver o status de rejeitado nao pode registrar pagamentos
        if ($project->status === 'rejeitado') {
            return back()->withErrors(['amount' => 'Não é possível registrar pagamentos para um orçamento com status de rejeitado.'])->withInput();
        }

        // Trava 3: se o valor total de pagamentos for completa nao deixa mais registrar pagamentos
        if ($project->remaining_balance <= 0.005) {
            return back()->withErrors(['amount' => 'Este orçamento já está totalmente pago (quitado). Não é possível registrar novos pagamentos.'])->withInput();
        }

        // Limpeza de valor formatado brasileiro
        $cleanValue = $validated['amount'];
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $amount = (float) $cleanValue;

        // Trava 3 (excesso): O valor do pagamento não pode ser maior do que o saldo restante
        if ($amount > $project->remaining_balance + 0.005) {
            return back()->withErrors(['amount' => 'O valor do pagamento não pode ser maior do que o saldo restante de R$ ' . number_format($project->remaining_balance, 2, ',', '.') . '.'])->withInput();
        }

        // Upload da nota fiscal
        $invoicePath = null;
        if ($request->hasFile('invoice')) {
            $invoicePath = $request->file('invoice')->store('invoices', 'local');
        }

        // Cria o Pagamento
        $payment = Payment::create([
            'project_id' => $project->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'],
            'payment_method' => $validated['payment_method'],
            'bank_account' => $bankAccountStr,
            'bank_account_id' => $bankAccountId,
            'observations' => $validated['observations'] ?? null,
            'invoice_path' => $invoicePath,
        ]);

        // Trava 2: se nao tiver nenhum pagamento e ele registrar um pagamento muda automaticamente pra aprovado
        // Contamos os pagamentos (incluindo o que acabamos de registrar). Se for 1, significa que era o primeiro pagamento do projeto
        if ($project->payments()->count() === 1) {
            if (!in_array($project->status, ['aprovado', 'quitado', 'finalizado'])) {
                $project->update(['status' => 'aprovado']);
            }
        }

        // Trava 3: se o valor total de pagamentos for completa muda o status para quitado
        if ($project->fresh()->remaining_balance <= 0.005) {
            $project->update(['status' => 'quitado']);
        }

        // Vincula orçamentos adicionais contemplados
        if (!empty($validated['related_project_ids'])) {
            $validRelatedIds = Project::whereHas('client', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereIn('id', $validated['related_project_ids'])
            ->where('id', '!=', $project->id) // Evita auto-vinculação
            ->pluck('id')
            ->toArray();

            $payment->relatedProjects()->sync($validRelatedIds);
        }

        return redirect()->route('payments.index')->with('success', 'Pagamento registrado com sucesso!');
    }

    /**
     * Faz o download seguro do anexo da Nota Fiscal vinculada.
     */
    public function downloadInvoice(Payment $payment)
    {
        // Tenancy Check
        abort_if($payment->project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $path = $payment->invoice_path;
        if (!$path) {
            abort(404, 'Nenhum caminho de nota fiscal cadastrado.');
        }

        $path = str_replace('\\', '/', $path);
        $cleanPath = ltrim($path, '/');
        $disks = ['local', 'public'];
        $pathVariations = [
            $path,
            $cleanPath,
            str_replace('public/', '', $cleanPath),
            str_replace('storage/', '', $cleanPath),
            'invoices/' . basename($cleanPath),
            basename($cleanPath)
        ];

        foreach ($disks as $disk) {
            foreach ($pathVariations as $var) {
                if (Storage::disk($disk)->exists($var)) {
                    return Storage::disk($disk)->download($var, basename($var));
                }
            }
        }

        abort(404, 'Nota fiscal não encontrada no servidor (Discos local/public).');
    }

    /**
     * Remove um pagamento.
     */
    public function destroy(Payment $payment)
    {
        // Tenancy Check
        abort_if($payment->project->client->user_id !== auth()->id(), 403, 'Ação não autorizada.');

        $paidAtDate = Carbon::parse($payment->paid_at);

        // Remove arquivo físico de forma ultra tolerante
        if ($payment->invoice_path) {
            try {
                $path = $payment->invoice_path;
                $cleanPath = ltrim($path, '/');
                $pathVariations = [
                    $path,
                    $cleanPath,
                    str_replace('public/', '', $cleanPath),
                    str_replace('storage/', '', $cleanPath),
                    'invoices/' . basename($cleanPath),
                    basename($cleanPath)
                ];
                foreach (['local', 'public'] as $disk) {
                    foreach ($pathVariations as $var) {
                        if (Storage::disk($disk)->exists($var)) {
                            Storage::disk($disk)->delete($var);
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao deletar anexo do pagamento no destroy: ' . $e->getMessage());
            }
        }

        $project = $payment->project;

        // Deleta o registro (deleta a Transaction associada via CASCADE do BD)
        $payment->delete();

        // Se o orçamento estava quitado e agora possui saldo restante, muda para aprovado
        if ($project->status === 'quitado' && $project->fresh()->remaining_balance > 0.005) {
            $project->update(['status' => 'aprovado']);
        }

        return redirect()->route('payments.index', [
            'month' => $paidAtDate->month,
            'year' => $paidAtDate->year,
        ])->with('success', 'Pagamento excluído com sucesso!');
    }

    /**
     * Exibe o formulário de edição de um pagamento.
     */
    public function edit(Payment $payment)
    {
        $userId = auth()->id();
        // Tenancy Check
        abort_if($payment->project->client->user_id !== $userId, 403, 'Ação não autorizada.');

        // Projetos ativos para o select principal
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', '!=', 'rejeitado')
        ->orderBy('title')
        ->get();

        // Todos os projetos ativos para o select de vinculação secundária (somente aprovados, quitados e finalizados)
        $allProjects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereIn('status', ['aprovado', 'quitado', 'finalizado'])
        ->orderBy('title')
        ->get();

        $projectBalances = [];
        foreach ($projects as $proj) {
            $balance = (float) $proj->remaining_balance;
            if ($proj->id === $payment->project_id) {
                $balance += (float) $payment->amount;
            }
            $projectBalances[$proj->id] = $balance;
        }

        $bankAccounts = \App\Models\BankAccount::where('user_id', $userId)->orderBy('account_name')->get();
        $relatedProjectIds = $payment->relatedProjects->pluck('id')->toArray();

        return view('payments.edit', compact(
            'payment',
            'projects',
            'allProjects',
            'projectBalances',
            'bankAccounts',
            'relatedProjectIds'
        ));
    }

    /**
     * Atualiza os dados de um pagamento.
     */
    public function update(Request $request, Payment $payment)
    {
        $userId = auth()->id();
        // Tenancy Check
        abort_if($payment->project->client->user_id !== $userId, 403, 'Ação não autorizada.');

        $validated = $request->validate([
            'project_id' => 'required|integer',
            'amount' => 'required|string',
            'paid_at' => 'required|date',
            'payment_method' => 'required|in:pix,dinheiro,deposito,outros',
            'bank_account_id' => 'nullable|integer',
            'observations' => 'nullable|string',
            'invoice' => 'nullable|file|max:10240', // 10MB
            'related_project_ids' => 'nullable|array',
            'related_project_ids.*' => 'integer',
        ]);

        // Valida tenancy do novo projeto principal
        $newProject = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->findOrFail($validated['project_id']);

        // Trava: se tiver o status de rejeitado nao pode registrar pagamentos
        if ($newProject->status === 'rejeitado') {
            return back()->withErrors(['amount' => 'Não é possível registrar pagamentos para um orçamento com status de rejeitado.'])->withInput();
        }

        // Limpeza de valor formatado brasileiro
        $cleanValue = $validated['amount'];
        $cleanValue = str_replace('R$', '', $cleanValue);
        $cleanValue = trim($cleanValue);
        $cleanValue = str_replace('.', '', $cleanValue);
        $cleanValue = str_replace(',', '.', $cleanValue);
        $amount = (float) $cleanValue;

        // Calcula o limite do valor de pagamento
        $availableBalance = $newProject->remaining_balance;
        if ($payment->project_id === $newProject->id) {
            $availableBalance += $payment->amount;
        }

        if ($amount > $availableBalance + 0.005) {
            return back()->withErrors(['amount' => 'O valor do pagamento não pode ser maior do que o saldo restante de R$ ' . number_format($availableBalance, 2, ',', '.') . '.'])->withInput();
        }

        // Upload de nova nota fiscal se enviada
        $invoicePath = $payment->invoice_path;
        if ($request->hasFile('invoice')) {
            if ($payment->invoice_path && Storage::disk('local')->exists($payment->invoice_path)) {
                Storage::disk('local')->delete($payment->invoice_path);
            }
            $invoicePath = $request->file('invoice')->store('invoices', 'local');
        }

        // Valida tenancy da conta bancária se fornecida
        $bankAccountId = $validated['bank_account_id'] ?? null;
        $bankAccountStr = null;

        if ($bankAccountId) {
            $bankAccount = \App\Models\BankAccount::where('user_id', $userId)->findOrFail($bankAccountId);
            $bankAccountStr = $bankAccount->bank_name . ' (' . $bankAccount->account_name . ')';
        }

        $oldProject = $payment->project;

        // Atualiza o Pagamento
        $payment->update([
            'project_id' => $newProject->id,
            'amount' => $amount,
            'paid_at' => $validated['paid_at'],
            'payment_method' => $validated['payment_method'],
            'bank_account' => $bankAccountStr,
            'bank_account_id' => $bankAccountId,
            'observations' => $validated['observations'] ?? null,
            'invoice_path' => $invoicePath,
        ]);

        // Vincula orçamentos adicionais
        if (isset($validated['related_project_ids'])) {
            $validRelatedIds = Project::whereHas('client', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
            ->whereIn('id', $validated['related_project_ids'])
            ->where('id', '!=', $newProject->id)
            ->pluck('id')
            ->toArray();

            $payment->relatedProjects()->sync($validRelatedIds);
        } else {
            $payment->relatedProjects()->detach();
        }

        // Atualização de status
        if ($oldProject->id !== $newProject->id) {
            if ($oldProject->status === 'quitado' && $oldProject->fresh()->remaining_balance > 0.005) {
                $oldProject->update(['status' => 'aprovado']);
            }
        }

        if ($newProject->fresh()->remaining_balance <= 0.005) {
            $newProject->update(['status' => 'quitado']);
        } else {
            if ($newProject->status === 'quitado' && $newProject->fresh()->remaining_balance > 0.005) {
                $newProject->update(['status' => 'aprovado']);
            }
        }

        $paidAtDate = Carbon::parse($payment->paid_at);

        return redirect()->route('payments.index', [
            'month' => $paidAtDate->month,
            'year' => $paidAtDate->year,
        ])->with('success', 'Pagamento atualizado com sucesso!');
    }
}
