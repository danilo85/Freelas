<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = auth()->id();

        // 1. Faturamento do mês atual (entradas pagas no mês vigente de projetos de clientes do usuário logado)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        
        $currentMonthRevenue = Transaction::whereHas('project.client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('type', 'entrada')
        ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
        ->sum('amount');

        // 2. Valor total dos projetos (pertencentes a clientes do usuário logado) que possuem proposta pendente
        $pendingProposalsValue = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereHas('proposals', function ($query) {
            $query->where('status', 'pendente');
        })
        ->sum('total_value');

        // 3. Quantidade de projetos com status "em andamento" (pertencentes a clientes do usuário logado)
        $activeProjectsCount = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where('status', 'em andamento')
        ->count();

        // 4. Todos os projetos com relacionamento do cliente do usuário logado para o Kanban
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->with('client')
        ->get();

        // 5. Contas a Vencer nos Próximos 7 Dias (Pendente)
        $upcomingBills = Transaction::where('user_id', $userId)
            ->where('status', 'pendente')
            ->whereBetween('due_date', [Carbon::now()->toDateString(), Carbon::now()->addDays(7)->toDateString()])
            ->with(['category'])
            ->orderBy('due_date', 'asc')
            ->get();

        // 6. Gráfico por Categoria (Gastos Realizados + Previstos no Mês)
        $categoryExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'saida')
            ->whereBetween('due_date', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->selectRaw('category_id, SUM(amount) as total')
            ->groupBy('category_id')
            ->with('category')
            ->get();

        $categoryChartLabels = [];
        $categoryChartData = [];
        $categoryChartColors = [];

        foreach ($categoryExpenses as $exp) {
            $categoryChartLabels[] = $exp->category ? $exp->category->name : 'Sem Categoria';
            $categoryChartData[] = (float) $exp->total;
            $categoryChartColors[] = $exp->category ? ($exp->category->color ?: '#94a3b8') : '#94a3b8';
        }

        return view('dashboard', compact(
            'currentMonthRevenue',
            'pendingProposalsValue',
            'activeProjectsCount',
            'projects',
            'upcomingBills',
            'categoryChartLabels',
            'categoryChartData',
            'categoryChartColors'
        ));
    }
}
