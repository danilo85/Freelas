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

        // 2. Valor total dos projetos (pertencentes a clientes do usuário logado) que possuem proposta em análise ou pendente
        $pendingProposalsValue = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->where(function($query) {
            $query->where('status', 'analisando')
                  ->orWhereHas('proposals', function ($q) {
                      $q->whereIn('status', ['pendente', 'analisando']);
                  });
        })
        ->sum('total_value');

        // 3. Quantidade de projetos ativos (aprovados ou quitados) do usuário logado
        $activeProjectsCount = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereIn('status', ['aprovado', 'quitado'])
        ->count();

        // Kanban Columns
        $columns = \App\Models\KanbanColumn::where('user_id', $userId)
            ->orderBy('position', 'asc')
            ->get();
        
        if ($columns->isEmpty()) {
            $defaults = [
                ['name' => 'Revisão de texto', 'color' => '#EC4899', 'position' => 1],
                ['name' => 'Em Andamento', 'color' => '#3B82F6', 'position' => 2],
                ['name' => 'Revisão Autor(a)', 'color' => '#F59E0B', 'position' => 3],
                ['name' => 'ISBN e F. catalográfica', 'color' => '#9D174D', 'position' => 4],
                ['name' => 'Prova física', 'color' => '#EAB308', 'position' => 5],
                ['name' => 'Concluído', 'color' => '#10B981', 'position' => 6],
            ];
            foreach ($defaults as $d) {
                \App\Models\KanbanColumn::create([
                    'user_id' => $userId,
                    'name' => $d['name'],
                    'color' => $d['color'],
                    'position' => $d['position'],
                ]);
            }
            $columns = \App\Models\KanbanColumn::where('user_id', $userId)
                ->orderBy('position', 'asc')
                ->get();
        }

        // Todos os projetos com relacionamento do cliente do usuário logado para o Kanban (somente aprovados e quitados)
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereIn('status', ['aprovado', 'quitado'])
        ->with('client')
        ->get();

        // Garante que projetos sem coluna sejam associados
        foreach ($projects as $project) {
            if (!$project->kanban_column_id) {
                $matchedCol = $columns->first(function($c) use ($project) {
                    return strtolower(trim($c->name)) === strtolower(trim($project->status));
                });
                if (!$matchedCol) {
                    if ($project->status === 'em andamento') {
                        $matchedCol = $columns->where('name', 'Em Andamento')->first();
                    } elseif ($project->status === 'concluido' || $project->status === 'finalizado') {
                        $matchedCol = $columns->where('name', 'Concluído')->first();
                    } else {
                        $matchedCol = $columns->first();
                    }
                }
                if ($matchedCol) {
                    $project->kanban_column_id = $matchedCol->id;
                    $project->save();
                }
            }
        }

        // Recarrega os projetos com a relação do cliente e da coluna do Kanban
        $projects = Project::whereHas('client', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })
        ->whereIn('status', ['aprovado', 'quitado'])
        ->with(['client', 'kanbanColumn'])
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

        $vibrantPalette = [
            '#3B82F6', // Blue
            '#10B981', // Emerald
            '#F59E0B', // Amber
            '#EF4444', // Red
            '#8B5CF6', // Purple
            '#EC4899', // Pink
            '#06B6D4', // Cyan
            '#F97316', // Orange
            '#14B8A6', // Teal
            '#6366F1', // Indigo
        ];

        foreach ($categoryExpenses as $index => $exp) {
            $categoryChartLabels[] = $exp->category ? $exp->category->name : 'Sem Categoria';
            $categoryChartData[] = (float) $exp->total;
            
            // Se a categoria tiver cor definida e for diferente do cinza padrão, usa ela. Caso contrário, usa a paleta premium.
            $catColor = $exp->category ? $exp->category->color : null;
            if ($catColor && !in_array(strtolower($catColor), ['#94a3b8', '#94a3b8ff', 'grey', 'gray'])) {
                $categoryChartColors[] = $catColor;
            } else {
                $categoryChartColors[] = $vibrantPalette[$index % count($vibrantPalette)];
            }
        }

        return view('dashboard', compact(
            'currentMonthRevenue',
            'pendingProposalsValue',
            'activeProjectsCount',
            'projects',
            'columns',
            'upcomingBills',
            'categoryChartLabels',
            'categoryChartData',
            'categoryChartColors'
        ));
    }

    public function storeColumn(Request $request)
    {
        $userId = auth()->id();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        $maxPosition = \App\Models\KanbanColumn::where('user_id', $userId)->max('position') ?: 0;

        $column = \App\Models\KanbanColumn::create([
            'user_id' => $userId,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'position' => $maxPosition + 1,
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
        ]);
    }

    public function updateColumn(Request $request, \App\Models\KanbanColumn $column)
    {
        abort_if($column->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'color' => 'required|string|max:7',
        ]);

        $column->update([
            'name' => $validated['name'],
            'color' => $validated['color'],
        ]);

        return response()->json([
            'success' => true,
            'column' => $column,
        ]);
    }

    public function deleteColumn(\App\Models\KanbanColumn $column)
    {
        abort_if($column->user_id !== auth()->id(), 403);

        $userId = auth()->id();
        
        // Encontra coluna alternativa para migrar os projetos
        $fallbackColumn = \App\Models\KanbanColumn::where('user_id', $userId)
            ->where('id', '!=', $column->id)
            ->orderBy('position', 'asc')
            ->first();

        if ($fallbackColumn) {
            Project::where('kanban_column_id', $column->id)->update([
                'kanban_column_id' => $fallbackColumn->id,
            ]);
        }

        $column->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    public function moveProject(Request $request, Project $project)
    {
        // Tenancy check
        abort_if($project->client->user_id !== auth()->id(), 403);

        $validated = $request->validate([
            'kanban_column_id' => 'required|exists:kanban_columns,id',
        ]);

        $column = \App\Models\KanbanColumn::where('user_id', auth()->id())->findOrFail($validated['kanban_column_id']);

        $project->update([
            'kanban_column_id' => $column->id,
        ]);

        return response()->json([
            'success' => true,
            'project' => $project->load(['client', 'kanbanColumn']),
        ]);
    }

    public function moveColumnPosition(Request $request)
    {
        $validated = $request->validate([
            'column_id' => 'required|exists:kanban_columns,id',
            'direction' => 'required|in:left,right',
        ]);

        $userId = auth()->id();
        $column = \App\Models\KanbanColumn::where('user_id', $userId)->findOrFail($validated['column_id']);
        
        $currentPos = $column->position;

        if ($validated['direction'] === 'left') {
            $swapColumn = \App\Models\KanbanColumn::where('user_id', $userId)
                ->where('position', '<', $currentPos)
                ->orderBy('position', 'desc')
                ->first();
        } else {
            $swapColumn = \App\Models\KanbanColumn::where('user_id', $userId)
                ->where('position', '>', $currentPos)
                ->orderBy('position', 'asc')
                ->first();
        }

        if ($swapColumn) {
            $column->update(['position' => $swapColumn->position]);
            $swapColumn->update(['position' => $currentPos]);
        }

        return response()->json(['success' => true]);
    }
}
