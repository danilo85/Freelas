<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class MeiController extends Controller
{
    /**
     * Dashboard MEI com navegador anual, termômetro e consolidação mensal de notas.
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $user = auth()->user();
        
        $year = (int) $request->input('year', Carbon::now()->year);
        $meiLimit = (float) $user->mei_limit;

        // PJ Incomes (Faturamento PJ Pago) no ano selecionado
        $pjIncomes = Transaction::where('user_id', $userId)
            ->where('type', 'entrada')
            ->where('classification', 'PJ')
            ->where('status', 'pago')
            ->whereYear('due_date', $year)
            ->orderBy('due_date', 'asc')
            ->get();

        // PJ Expenses no ano selecionado
        $pjExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->where('classification', 'PJ')
            ->where('status', 'pago')
            ->whereYear('due_date', $year)
            ->orderBy('due_date', 'asc')
            ->get();

        // Soma anual acumulada do faturamento
        $annualFaturamento = (float) $pjIncomes->sum('amount');
        $annualExpenses = (float) $pjExpenses->sum('amount');

        // Percentual do termômetro
        $percent = $meiLimit > 0 ? min(100, ($annualFaturamento / $meiLimit) * 100) : 0;

        // Agrupamento por meses do ano selecionado (1 a 12)
        $monthsData = [];
        $monthsNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        for ($m = 1; $m <= 12; $m++) {
            $monthIncomes = $pjIncomes->filter(fn($t) => $t->due_date->month === $m);
            $monthExpenses = $pjExpenses->filter(fn($t) => $t->due_date->month === $m);

            $incomesSum = (float) $monthIncomes->sum('amount');
            $expensesSum = (float) $monthExpenses->sum('amount');

            // Consolidação de arquivos/anexos (Notas Fiscais / Recibos)
            $attachments = [];
            
            // Junta todas as transações com anexo no mês
            $allMonthTransactions = $monthIncomes->concat($monthExpenses);
            foreach ($allMonthTransactions as $t) {
                if ($t->attachment_path) {
                    $attachments[] = [
                        'transaction_id' => $t->id,
                        'description' => $t->description,
                        'amount' => $t->amount,
                        'type' => $t->type,
                        'date' => $t->due_date->format('d/m/Y'),
                        'filename' => basename($t->attachment_path),
                        'download_url' => route('finances.download-attachment', $t->id)
                    ];
                }
            }

            $monthsData[$m] = [
                'name' => $monthsNames[$m],
                'incomes_sum' => $incomesSum,
                'expenses_sum' => $expensesSum,
                'balance' => $incomesSum - $expensesSum,
                'incomes' => $monthIncomes,
                'expenses' => $monthExpenses,
                'attachments' => $attachments,
            ];
        }

        return view('finances.mei', compact(
            'year',
            'meiLimit',
            'annualFaturamento',
            'annualExpenses',
            'percent',
            'monthsData'
        ));
    }

    /**
     * Atualiza o limite de faturamento anual do MEI.
     */
    public function updateLimit(Request $request)
    {
        $validated = $request->validate([
            'mei_limit' => 'required|string',
        ]);

        $limitRaw = $validated['mei_limit'];
        $limitRaw = str_replace('R$', '', $limitRaw);
        $limitRaw = trim($limitRaw);
        $limitRaw = str_replace('.', '', $limitRaw);
        $limitRaw = str_replace(',', '.', $limitRaw);
        $limit = (float) $limitRaw;

        auth()->user()->update([
            'mei_limit' => $limit,
        ]);

        return redirect()->route('finances.mei')->with('success', 'Limite MEI atualizado com sucesso!');
    }
}
