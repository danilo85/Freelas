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
        $month = (int) $request->input('month', Carbon::now()->month);
        $meiLimit = (float) $user->mei_limit;

        // Todas as transações pagas/recebidas no ano selecionado
        $transactions = Transaction::where('user_id', $userId)
            ->where('status', 'pago')
            ->where(function($q) use ($year) {
                $q->whereYear('paid_at', $year)
                  ->orWhere(function($sub) use ($year) {
                      $sub->whereNull('paid_at')->whereYear('due_date', $year);
                  });
            })
            ->with('category')
            ->get();

        // Filtra transferências de lucros e transferências internas para não poluírem receitas e despesas
        $transactions = $transactions->filter(function($t) {
            if ($t->category) {
                $catName = mb_strtolower($t->category->name);
                return !in_array($catName, [
                    'transferência de lucros', 
                    'transferencia de lucros', 
                    'transferência', 
                    'transferencia', 
                    'transferência interna', 
                    'transferencia interna'
                ]);
            }
            return true;
        });

        // Totais Anuais Separados
        $annualPjFaturamento = (float) $transactions->filter(fn($t) => $t->type === 'entrada' && $t->classification === 'PJ')->sum('amount');
        $annualPfFaturamento = (float) $transactions->filter(fn($t) => $t->type === 'entrada' && $t->classification !== 'PJ')->sum('amount');
        $annualPjExpenses = (float) $transactions->filter(fn($t) => $t->type === 'saida' && $t->classification === 'PJ')->sum('amount');
        $annualPfExpenses = (float) $transactions->filter(fn($t) => $t->type === 'saida' && $t->classification !== 'PJ')->sum('amount');

        // Percentual do termômetro baseia-se apenas no faturamento PJ do MEI
        $percent = $meiLimit > 0 ? min(100, ($annualPjFaturamento / $meiLimit) * 100) : 0;

        // Agrupamento por meses do ano selecionado (1 a 12)
        $monthsData = [];
        $monthsNames = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];

        $pjIncomesChart = [];
        $pfIncomesChart = [];
        $expensesChart = [];

        for ($m = 1; $m <= 12; $m++) {
            // Filtra transações do mês respectivo (pelo paid_at, senão due_date)
            $monthTransactions = $transactions->filter(function($t) use ($m) {
                $date = $t->paid_at ?: $t->due_date;
                return $date->month === $m;
            });

            $pjIncomesSum = (float) $monthTransactions->filter(fn($t) => $t->type === 'entrada' && $t->classification === 'PJ')->sum('amount');
            $pfIncomesSum = (float) $monthTransactions->filter(fn($t) => $t->type === 'entrada' && $t->classification !== 'PJ')->sum('amount');
            
            $pjExpensesSum = (float) $monthTransactions->filter(fn($t) => $t->type === 'saida' && $t->classification === 'PJ')->sum('amount');
            $pfExpensesSum = (float) $monthTransactions->filter(fn($t) => $t->type === 'saida' && $t->classification !== 'PJ')->sum('amount');

            $pjIncomesChart[] = $pjIncomesSum;
            $pfIncomesChart[] = $pfIncomesSum;
            $expensesChart[] = $pjExpensesSum + $pfExpensesSum;

            // Consolidação de arquivos/anexos (Notas Fiscais / Recibos)
            $attachments = [];
            foreach ($monthTransactions as $t) {
                if ($t->attachment_path) {
                    $attachments[] = [
                        'transaction_id' => $t->id,
                        'description' => $t->description,
                        'amount' => $t->amount,
                        'type' => $t->type,
                        'classification' => $t->classification === 'PJ' ? 'PJ' : 'PF',
                        'date' => ($t->paid_at ?: $t->due_date)->format('d/m/Y'),
                        'filename' => basename($t->attachment_path),
                        'attachment_path' => $t->attachment_path,
                        'download_url' => route('finances.download-attachment', $t->id)
                    ];
                }
            }

            $monthsData[$m] = [
                'name' => $monthsNames[$m],
                'pj_incomes_sum' => $pjIncomesSum,
                'pf_incomes_sum' => $pfIncomesSum,
                'pj_expenses_sum' => $pjExpensesSum,
                'pf_expenses_sum' => $pfExpensesSum,
                'balance' => ($pjIncomesSum + $pfIncomesSum) - ($pjExpensesSum + $pfExpensesSum),
                'attachments' => $attachments,
            ];
        }

        return view('finances.mei', compact(
            'year',
            'month',
            'meiLimit',
            'annualPjFaturamento',
            'annualPfFaturamento',
            'annualPjExpenses',
            'annualPfExpenses',
            'percent',
            'monthsData',
            'pjIncomesChart',
            'pfIncomesChart',
            'expensesChart'
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

    /**
     * Exporta transações consolidadas do MEI para o mês/ano selecionado em formato CSV.
     */
    public function exportCsv(Request $request)
    {
        $userId = auth()->id();
        $month = (int) $request->input('month', Carbon::now()->month);
        $year = (int) $request->input('year', Carbon::now()->year);

        $transactions = Transaction::where('user_id', $userId)
            ->where(function($q) use ($year, $month) {
                $q->where(function($sub) use ($year, $month) {
                    $sub->whereMonth('paid_at', $month)->whereYear('paid_at', $year);
                })->orWhere(function($sub) use ($year, $month) {
                    $sub->whereNull('paid_at')->whereMonth('due_date', $month)->whereYear('due_date', $year);
                });
            })
            ->with('category')
            ->orderBy('due_date', 'asc')
            ->get();

        // Filtra transferências de lucros e transferências internas
        $transactions = $transactions->filter(function($t) {
            if ($t->category) {
                $catName = mb_strtolower($t->category->name);
                return !in_array($catName, [
                    'transferência de lucros', 
                    'transferencia de lucros', 
                    'transferência', 
                    'transferencia', 
                    'transferência interna', 
                    'transferencia interna'
                ]);
            }
            return true;
        });

        $filename = "faturamento_mei_{$year}_{$month}.csv";

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['Data', 'Descrição', 'Tipo', 'Classificação', 'Valor (R$)', 'Categoria', 'Status'];

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($transactions as $t) {
                $row['Data'] = ($t->paid_at ?: $t->due_date)->format('d/m/Y');
                $row['Descrição'] = $t->description;
                $row['Tipo'] = $t->type === 'entrada' ? 'Receita' : 'Despesa';
                $row['Classificação'] = $t->classification === 'PJ' ? 'PJ' : 'PF';
                $row['Valor (R$)'] = number_format($t->amount, 2, ',', '');
                $row['Categoria'] = $t->category ? $t->category->name : 'Sem Categoria';
                $row['Status'] = $t->status === 'pago' ? 'Pago' : 'Pendente';

                fputcsv($file, array_values($row), ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
