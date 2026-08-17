@extends('layouts.app')

@section('title', 'Painel Geral - Gestor de Freelas')
@section('page_title', 'Painel Geral')

@section('content')
<div class="space-y-8">
    
    <!-- Seção de Métricas (Cards Coloridos Individuais) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Faturamento do Mês -->
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 dark:from-emerald-600 dark:to-teal-800 text-white rounded-xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-emerald-100/90 uppercase tracking-widest">Faturamento do Mês</p>
                <h3 class="text-3xl font-black mt-2 font-outfit tracking-tight drop-shadow-sm truncate">
                    R$ {{ number_format($currentMonthRevenue, 2, ',', '.') }}
                </h3>
                <span class="text-xs font-bold flex items-center gap-1 mt-2 text-emerald-100/90">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Recebidos (entradas)
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/15 text-white flex items-center justify-center shadow-inner shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Orçamentos Pendentes -->
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 dark:from-amber-600 dark:to-orange-800 text-white rounded-xl p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-black text-amber-100/90 uppercase tracking-widest">Orçamentos Pendentes</p>
                <h3 class="text-3xl font-black mt-2 font-outfit tracking-tight drop-shadow-sm truncate">
                    R$ {{ number_format($pendingProposalsValue, 2, ',', '.') }}
                </h3>
                <span class="text-xs font-bold flex items-center gap-1.5 mt-2 text-amber-100/95">
                    <svg class="w-3.5 h-3.5 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    Aguardando retorno do cliente
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/15 text-white flex items-center justify-center shadow-inner shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Projetos Ativos -->
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 dark:from-blue-700 dark:to-indigo-800 text-white rounded-xl p-6 shadow-sm flex items-center justify-between sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-[10px] font-black text-blue-100/90 uppercase tracking-widest">Projetos Ativos</p>
                <h3 class="text-3xl font-black mt-2 font-outfit tracking-tight drop-shadow-sm truncate">
                    {{ $activeProjectsCount }}
                </h3>
                <span class="text-xs font-bold flex items-center gap-1.5 mt-2 text-blue-100/90">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 inline-block animate-pulse shadow-md"></span>
                    Em andamento no sistema
                </span>
            </div>
            <div class="w-12 h-12 rounded-xl bg-white/15 text-white flex items-center justify-center shadow-inner shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>
        </div>
        
    </div>

    <!-- Widgets Financeiros Unificados -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        <!-- Widget: Distribuição de Despesas (Doughnut Chart) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col justify-between min-h-[260px]">
            <div>
                <h3 class="text-sm font-black text-slate-850 dark:text-slate-100 uppercase tracking-wider">📊 Distribuição de Despesas</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Visão do mês corrente</p>
            </div>
            
            <div class="relative w-full h-[180px] mt-4 flex items-center justify-center">
                @if(count($categoryChartData) > 0)
                    <canvas id="dashboardCategoryChart"></canvas>
                @else
                    <div class="text-xs text-slate-400 dark:text-slate-500 text-center py-10 border border-dashed border-slate-200 dark:border-slate-800 rounded-[5px] w-full bg-slate-50/30">
                        Nenhuma despesa registrada neste mês.
                    </div>
                @endif
            </div>
        </div>

        <!-- Widget: Contas a Vencer (Lembrete) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm hover:shadow-md transition-shadow flex flex-col min-h-[260px]">
            <div>
                <h3 class="text-sm font-black text-slate-855 dark:text-slate-100 uppercase tracking-wider">⏳ Contas a Vencer (Próximos 7 dias)</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Lembretes automáticos</p>
            </div>
            
            <div class="flex-1 mt-4 overflow-y-auto max-h-[180px] space-y-2.5 pr-1">
                @forelse($upcomingBills as $bill)
                    <div class="flex items-center justify-between p-3 bg-amber-50/40 dark:bg-amber-950/10 border border-amber-100 dark:border-amber-900/30 rounded-[5px] text-xs hover:bg-amber-50/60 dark:hover:bg-amber-950/20 transition-colors">
                        <div class="min-w-0 flex-1 pr-3">
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs shrink-0">{{ $bill->category->icon ?? '💸' }}</span>
                                <span class="font-extrabold text-slate-800 dark:text-slate-200 truncate block" title="{{ $bill->description }}">
                                    {{ $bill->description }}
                                </span>
                            </div>
                            <span class="text-[9px] text-slate-400 font-bold block mt-1 uppercase tracking-wider">Vence em: {{ $bill->due_date->format('d/m/Y') }}</span>
                        </div>
                        <span class="font-bold text-rose-600 dark:text-rose-455 shrink-0 font-mono text-sm">R$ {{ number_format($bill->amount, 2, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center flex-1 text-center py-10 border border-dashed border-slate-200 dark:border-slate-800 rounded-[5px] bg-slate-50/30">
                        <span class="text-2xl mb-1">🎉</span>
                        <h4 class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Tudo em dia!</h4>
                        <p class="text-[10px] text-slate-400 max-w-[200px] mt-0.5">Nenhum vencimento pendente para os próximos 7 dias.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Kanban Interativo (Alpine.js) -->
    <div x-data="kanbanBoard()" x-init="init()" class="space-y-6">
        
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="min-w-0 flex-1">
                <h2 class="text-xl font-bold text-slate-900 dark:text-slate-100">Projetos & Fluxo de Trabalho</h2>
                <p class="text-sm text-slate-400 dark:text-slate-500 mt-0.5">Visualize, movimente e configure colunas personalizadas para os seus projetos.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-2 sm:gap-3 no-print w-full lg:w-auto shrink-0">
                <!-- Botão Travar/Destravar -->
                <button 
                    type="button" 
                    @click="toggleLock()" 
                    class="px-3 py-2 sm:px-3.5 border rounded-[5px] text-[10px] sm:text-xs font-bold uppercase tracking-wider transition-all flex items-center gap-1.5 cursor-pointer shadow-sm focus:outline-none flex-1 sm:flex-initial justify-center"
                    :class="locked ? 'bg-rose-50 border-rose-200 text-rose-700 dark:bg-rose-950/20 dark:border-rose-900/30' : 'bg-emerald-50 border-emerald-200 text-emerald-700 dark:bg-emerald-950/20 dark:border-emerald-900/30'"
                >
                    <span class="flex items-center gap-1.5">
                        <template x-if="locked">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </template>
                        <template x-if="!locked">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                        </template>
                        <span x-text="locked ? 'Painel Travado' : 'Mover Livre'"></span>
                    </span>
                </button>
 
                <!-- Navegador de Colunas (Scroll) -->
                <div class="flex items-center gap-1 no-print">
                    <button 
                        type="button" 
                        @click="document.getElementById('kanbanBoardContainer').scrollBy({ left: -340, behavior: 'smooth' })"
                        class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-black text-xs rounded-[5px] shadow-sm transition-colors cursor-pointer flex items-center justify-center focus:outline-none"
                        title="Rolar para Esquerda"
                    >
                        ◀
                    </button>
                    <button 
                        type="button" 
                        @click="document.getElementById('kanbanBoardContainer').scrollBy({ left: 340, behavior: 'smooth' })"
                        class="px-3 py-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 font-black text-xs rounded-[5px] shadow-sm transition-colors cursor-pointer flex items-center justify-center focus:outline-none"
                        title="Rolar para Direita"
                    >
                        ▶
                    </button>
                </div>
 
                <!-- Botão Adicionar Coluna -->
                <button 
                    type="button" 
                    @click="openAddColumnModal = true"
                    class="px-3 py-2 sm:px-3.5 bg-blue-600 hover:bg-blue-700 text-white text-[10px] sm:text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors shadow-sm cursor-pointer flex items-center gap-1 focus:outline-none flex-1 sm:flex-initial justify-center"
                >
                    <span>+</span> Nova Coluna
                </button>
            </div>
        </div>

        <!-- Kanban Board Horizontal Scroll Container -->
        <div id="kanbanBoardContainer" class="w-full overflow-x-auto pb-4" @dragover="dragOverBoard($event)">
            <div class="flex gap-6 min-w-max">
                
                <!-- Colunas Dinâmicas -->
                <template x-for="(col, colIndex) in columns" :key="col.id">
                    <div 
                        class="w-[320px] rounded-[5px] p-4 flex flex-col min-h-[380px] bg-slate-50 dark:bg-slate-900/40 border border-slate-200/60 dark:border-slate-800 transition-colors"
                        @dragover.prevent="dragOverColumn($event, col.id)"
                        @dragleave="dragLeaveColumn($event, col.id)"
                        @drop="dropCard($event, col.id)"
                        :class="draggedOverColumnId === col.id ? 'bg-blue-50/40 dark:bg-blue-950/10 border-blue-300' : ''"
                    >
                        <!-- Header da Coluna -->
                        <div class="flex items-center justify-between mb-4 px-1 pb-2 border-b border-slate-100 dark:border-slate-800">
                            <div class="flex items-center gap-2">
                                <!-- Ponto Colorido -->
                                <span class="w-3 h-3 rounded-full shrink-0" :style="'background-color: ' + col.color"></span>
                                <h4 class="font-extrabold text-slate-800 dark:text-slate-200 text-sm tracking-wide" x-text="col.name"></h4>
                            </div>
                            
                            <!-- Contador e Ações da Coluna -->
                            <div class="flex items-center gap-1.5 font-bold">
                                <span class="text-[10px] font-black px-2 py-0.5 rounded-[5px] shadow-sm uppercase" 
                                      :style="'background-color: ' + col.color + '15; color: ' + col.color"
                                      x-text="countProjectsInColumn(col.id)">0</span>
                                
                                <!-- Arrows para reordenar -->
                                <button type="button" @click="moveColumn(col.id, 'left')" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold focus:outline-none cursor-pointer" title="Mover para Esquerda">‹</button>
                                <button type="button" @click="moveColumn(col.id, 'right')" class="p-1 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-bold focus:outline-none cursor-pointer" title="Mover para Direita">›</button>
                                
                                <!-- Editar Coluna -->
                                <button type="button" @click="editColumn(col)" class="p-1 hover:bg-slate-105 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 focus:outline-none cursor-pointer flex items-center justify-center rounded" title="Configurar Coluna">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Area para Cards com altura fixa scrollable (Post-it style columns) -->
                        <div class="space-y-2 flex-1 overflow-y-auto h-[320px] pr-1.5 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-800">
                            <template x-for="project in getProjectsByColumn(col.id)" :key="project.id">
                                <div 
                                    class="rounded-[3px] p-3 border shadow-xs hover:shadow-md transition-all duration-200 relative select-none cursor-pointer"
                                    :style="'background-color: ' + col.color + '12; border-color: ' + col.color + '30;'"
                                    @click="expandedCardId = (expandedCardId === project.id ? null : project.id)"
                                    :draggable="!locked"
                                    @dragstart="dragStartCard($event, project.id)"
                                    @dragend="dragEndCard($event)"
                                >
                                    <!-- Overlay de Salvando -->
                                    <div x-show="updatingId === project.id" class="absolute inset-0 bg-white/70 dark:bg-slate-900/70 rounded-[3px] flex items-center justify-center z-10">
                                        <svg class="animate-spin h-5 w-5 text-slate-700 dark:text-slate-350" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- Cabeçalho (Sempre Visível) -->
                                    <div class="flex items-start justify-between gap-2">
                                        <h5 class="font-extrabold text-slate-800 dark:text-slate-100 text-xs leading-snug tracking-tight" x-text="project.title"></h5>
                                        <span class="text-[9px] text-slate-400 font-bold shrink-0" x-text="expandedCardId === project.id ? '▲' : '▼'"></span>
                                    </div>

                                    <!-- Conteúdo Expansível (Accordion / Stacked style) -->
                                    <div x-show="expandedCardId === project.id" x-collapse class="mt-2.5 space-y-2 border-t border-dashed pt-2.5" :style="'border-color: ' + col.color + '40'">
                                        <p class="text-[9px] text-slate-400 font-bold block uppercase tracking-wider" x-text="project.client ? project.client.name : 'Sem Cliente'"></p>
                                        
                                        @if(auth()->user()->role === 'master')
                                            <div class="flex justify-between text-[10px] font-bold">
                                                <span class="text-slate-500 font-bold">Valor:</span>
                                                <span class="text-emerald-655 dark:text-emerald-450 font-extrabold" x-text="formatCurrency(project.total_value)"></span>
                                            </div>
                                        @endif

                                        <div class="flex justify-between text-[9px] text-slate-400 font-bold">
                                            <span>Movido em:</span>
                                            <span class="font-semibold text-slate-650 dark:text-slate-350" x-text="formatDateTime(project.updated_at)"></span>
                                        </div>

                                        <div class="flex items-center justify-between pt-1">
                                            <!-- Status Badge -->
                                            <span class="text-[9px] font-black px-2 py-0.5 rounded-[3px] uppercase tracking-wider font-mono"
                                                  :class="getStatusBadgeClass(project.status)"
                                                  x-text="project.status">
                                            </span>

                                            <!-- Ver Detalhes Link -->
                                            <a :href="'/freelas/projects/' + project.id" class="w-6 h-6 flex items-center justify-center bg-white dark:bg-slate-800 border hover:bg-slate-50 rounded-[3px] text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 transition-colors" :style="'border-color: ' + col.color + '40'" title="Visualizar Detalhes" @click.stop>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </template>



                            <div x-show="countProjectsInColumn(col.id) === 0" class="border-2 border-dashed border-slate-200/60 dark:border-slate-800 rounded-[5px] py-12 px-4 text-center text-slate-405 dark:text-slate-500 text-xs font-semibold">
                                Arraste projetos para cá
                            </div>
                        </div>

                    </div>
                </template>

                <!-- Coluna de Espaço no Final para adicionar Coluna -->
                <div class="w-[300px] border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-[5px] p-6 flex flex-col items-center justify-center text-center cursor-pointer hover:bg-slate-50/20 dark:hover:bg-slate-900/10 transition-colors min-h-[480px]"
                     @click="openAddColumnModal = true">
                    <span class="text-3xl text-slate-400 font-light">+</span>
                    <h4 class="font-extrabold text-slate-505 dark:text-slate-400 uppercase tracking-wider text-xs block mt-2">Criar Nova Coluna</h4>
                    <p class="text-[10px] text-slate-400 max-w-[200px] mt-1">Configure uma etapa personalizada de acordo com a sua rotina.</p>
                </div>

            </div>
        </div>

        <!-- MODAL: Criar / Editar Coluna -->
        <div x-show="openAddColumnModal || openEditColumnModal" 
             class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
             style="z-index: 99999;"
             x-transition.opacity
             x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-md w-full mx-4 overflow-hidden flex flex-col relative"
                 @click.away="closeColumnModals()">
                
                <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50 dark:bg-slate-900/50">
                    <div>
                        <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight"
                            x-text="openEditColumnModal ? 'Editar Coluna Kanban' : 'Nova Coluna Kanban'"></h3>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Defina a etapa e a cor indicadora</p>
                    </div>
                    <button type="button" @click="closeColumnModals()" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-black text-sm p-1.5 shrink-0 cursor-pointer">✕</button>
                </div>

                <div class="p-5 space-y-4">
                    <!-- Nome da Coluna -->
                    <div class="space-y-1">
                        <label for="column_name" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Nome da Etapa</label>
                        <input type="text" x-model="columnForm.name" placeholder="Ex: Revisão, Em Testes, Faturamento" class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none" />
                    </div>

                    <!-- Cor -->
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Cor Indicadora</label>
                        <div class="flex items-center gap-3">
                            <input type="color" x-model="columnForm.color" class="w-10 h-10 border border-slate-205 dark:border-slate-800 rounded cursor-pointer shrink-0" />
                            <div class="flex flex-wrap gap-2">
                                <template x-for="pColor in colorPalette">
                                    <button 
                                        type="button" 
                                        @click="columnForm.color = pColor"
                                        class="w-6 h-6 rounded-full border border-slate-200 dark:border-slate-700 shadow-xs focus:outline-none"
                                        :style="'background-color: ' + pColor"
                                    ></button>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/50 flex justify-between gap-2 shrink-0">
                    <div>
                        <!-- Botão Deletar se for edição -->
                        <button 
                            type="button" 
                            x-show="openEditColumnModal"
                            @click="deleteColumnAction()"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-[5px] transition-colors uppercase tracking-wider cursor-pointer"
                        >
                            Excluir Coluna
                        </button>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" @click="closeColumnModals()" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-350 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-[5px] uppercase tracking-wider cursor-pointer">Cancelar</button>
                        <button 
                            type="button" 
                            @click="saveColumnAction()"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[5px] shadow transition-colors uppercase tracking-wider cursor-pointer"
                            x-text="openEditColumnModal ? 'Atualizar' : 'Salvar'"
                        ></button>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- Script do Alpine.js para Controle de Estado do Kanban -->
<script>
    function kanbanBoard() {
        return {
            projects: @js($projects),
            columns: @js($columns),
            locked: false,
            loading: false,
            updatingId: null,
            draggedCardId: null,
            draggedOverColumnId: null,
            expandedCardId: null,
            
            // Modals
            openAddColumnModal: false,
            openEditColumnModal: false,
            editingColumnId: null,
            columnForm: {
                name: '',
                color: '#3B82F6'
            },
            colorPalette: ['#EC4899', '#3B82F6', '#F59E0B', '#9D174D', '#EAB308', '#10B981', '#EF4444', '#8B5CF6', '#06B6D4', '#64748b'],

            init() {
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            },

            countProjectsInColumn(columnId) {
                return this.projects.filter(p => p.kanban_column_id === columnId).length;
            },

            getProjectsByColumn(columnId) {
                return this.projects.filter(p => p.kanban_column_id === columnId);
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
            },

            formatDateTime(value) {
                if (!value) return '';
                const date = new Date(value);
                return date.toLocaleDateString('pt-BR') + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            },

            getStatusBadgeClass(status) {
                switch(status) {
                    case 'quitado':
                    case 'finalizado':
                        return 'bg-green-50 text-green-700 dark:bg-green-950/20 dark:text-green-400 border border-green-200 dark:border-green-900/30';
                    case 'aprovado':
                        return 'bg-blue-50 text-blue-700 dark:bg-blue-950/20 dark:text-blue-400 border border-blue-200 dark:border-blue-900/30';
                    case 'analisando':
                        return 'bg-amber-50 text-amber-700 dark:bg-amber-950/20 dark:text-amber-400 border border-amber-200 dark:border-amber-900/30';
                    case 'rejeitado':
                        return 'bg-rose-50 text-rose-700 dark:bg-rose-950/20 dark:text-rose-400 border border-rose-200 dark:border-rose-900/30';
                    case 'rascunho':
                    default:
                        return 'bg-slate-50 text-slate-600 dark:bg-slate-900/40 dark:text-slate-405 border border-slate-200 dark:border-slate-800';
                }
            },

            getPostItBgClass(status) {
                switch(status) {
                    case 'quitado':
                    case 'finalizado':
                        return 'bg-[#e6fcf5] border-[#c3fae8] text-[#099268] dark:bg-emerald-950/20 dark:border-emerald-900/30 dark:text-emerald-300';
                    case 'aprovado':
                        return 'bg-[#e7f5ff] border-[#a5d8ff] text-[#1c7ed6] dark:bg-blue-950/20 dark:border-blue-900/30 dark:text-blue-300';
                    case 'analisando':
                        return 'bg-[#fff9db] border-[#ffe066] text-[#f08c00] dark:bg-amber-955/10 dark:border-amber-900/30 dark:text-amber-300';
                    case 'rejeitado':
                        return 'bg-[#fff5f5] border-[#ffc9c9] text-[#e03131] dark:bg-rose-950/20 dark:border-rose-900/30 dark:text-rose-300';
                    case 'rascunho':
                    default:
                        return 'bg-[#f1f3f5] border-[#e9ecef] text-[#495057] dark:bg-slate-900/40 dark:border-slate-800 dark:text-slate-300';
                }
            },

            getColumnColor(columnId) {
                const col = this.columns.find(c => c.id === columnId);
                return col ? col.color : '#3B82F6';
            },

            dragOverBoard(event) {
                if (this.locked) return;
                const board = document.getElementById('kanbanBoardContainer');
                if (!board) return;
                
                const rect = board.getBoundingClientRect();
                const mouseX = event.clientX;
                
                if (mouseX > rect.right - 80) {
                    board.scrollLeft += 15;
                } else if (mouseX < rect.left + 80) {
                    board.scrollLeft -= 15;
                }
            },

            toggleLock() {
                this.locked = !this.locked;
            },

            // Drag and Drop
            dragStartCard(event, id) {
                if (this.locked) return;
                this.draggedCardId = id;
                event.dataTransfer.setData('text/plain', id);
                event.dataTransfer.effectAllowed = 'move';
            },

            dragEndCard(event) {
                this.draggedCardId = null;
                this.draggedOverColumnId = null;
            },

            dragOverColumn(event, colId) {
                if (this.locked) return;
                this.draggedOverColumnId = colId;
            },

            dragLeaveColumn(event, colId) {
                if (this.draggedOverColumnId === colId) {
                    this.draggedOverColumnId = null;
                }
            },

            async dropCard(event, colId) {
                if (this.locked) return;
                const cardId = parseInt(event.dataTransfer.getData('text/plain') || this.draggedCardId);
                if (!cardId) return;

                // Optimistic UI update
                const pIndex = this.projects.findIndex(p => p.id === cardId);
                if (pIndex === -1) return;
                const originalColumnId = this.projects[pIndex].kanban_column_id;
                this.projects[pIndex].kanban_column_id = colId;
                this.projects[pIndex].updated_at = new Date().toISOString();

                this.updatingId = cardId;
                
                try {
                    const response = await fetch(`/api/projects/${cardId}/kanban-move`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ kanban_column_id: colId })
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        // Rollback on error
                        this.projects[pIndex].kanban_column_id = originalColumnId;
                        alert(data.message || 'Erro ao mover projeto.');
                    }
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    this.projects[pIndex].kanban_column_id = originalColumnId;
                    alert('Erro de conexão ao mover o projeto.');
                } finally {
                    this.updatingId = null;
                    this.draggedOverColumnId = null;
                }
            },

            // Column CRUD Action Helpers
            closeColumnModals() {
                this.openAddColumnModal = false;
                this.openEditColumnModal = false;
                this.editingColumnId = null;
                this.columnForm = { name: '', color: '#3B82F6' };
            },

            editColumn(col) {
                this.editingColumnId = col.id;
                this.columnForm = { name: col.name, color: col.color };
                this.openEditColumnModal = true;
            },

            async saveColumnAction() {
                if (!this.columnForm.name.trim()) {
                    alert('Por favor, informe o nome da etapa.');
                    return;
                }

                this.loading = true;

                try {
                    const isEdit = !!this.editingColumnId;
                    const url = isEdit ? `/api/kanban/columns/${this.editingColumnId}` : '/api/kanban/columns';
                    const method = isEdit ? 'PUT' : 'POST';

                    const response = await fetch(url, {
                        method: method,
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(this.columnForm)
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        if (isEdit) {
                            const index = this.columns.findIndex(c => c.id === this.editingColumnId);
                            if (index !== -1) {
                                this.columns[index] = data.column;
                            }
                        } else {
                            this.columns.push(data.column);
                        }
                        this.closeColumnModals();
                    } else {
                        alert(data.message || 'Erro ao salvar coluna.');
                    }
                } catch (err) {
                    console.error('Erro ao salvar coluna:', err);
                    alert('Erro de conexão.');
                } finally {
                    this.loading = false;
                }
            },

            async deleteColumnAction() {
                if (!confirm('Tem certeza de que deseja excluir esta coluna? Os projetos nesta coluna serão migrados para a primeira coluna disponível.')) {
                    return;
                }

                this.loading = true;

                try {
                    const response = await fetch(`/api/kanban/columns/${this.editingColumnId}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Remove column from local state
                        const colId = this.editingColumnId;
                        this.columns = this.columns.filter(c => c.id !== colId);
                        
                        // Fallback column assignment (locally)
                        const fallbackCol = this.columns[0];
                        if (fallbackCol) {
                            this.projects.forEach(p => {
                                if (p.kanban_column_id === colId) {
                                    p.kanban_column_id = fallbackCol.id;
                                }
                            });
                        }
                        this.closeColumnModals();
                    } else {
                        alert(data.message || 'Erro ao excluir coluna.');
                    }
                } catch (err) {
                    console.error('Erro ao excluir coluna:', err);
                    alert('Erro de conexão.');
                } finally {
                    this.loading = false;
                }
            },

            async moveColumn(columnId, direction) {
                this.loading = true;

                try {
                    const response = await fetch('/api/kanban/columns/reorder', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ column_id: columnId, direction: direction })
                    });

                    const data = await response.json();
                    if (response.ok && data.success) {
                        // Reload or dynamically swap locally
                        window.location.reload();
                    }
                } catch (err) {
                    console.error('Erro ao mover coluna:', err);
                    alert('Erro de conexão.');
                } finally {
                    this.loading = false;
                }
            }
        };
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const catCanvas = document.getElementById('dashboardCategoryChart');
        if (catCanvas) {
            const ctx = catCanvas.getContext('2d');
            const isDark = document.documentElement.classList.contains('dark');
            const labelColor = isDark ? '#94a3b8' : '#475569';
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: @json($categoryChartLabels),
                    datasets: [{
                        data: @json($categoryChartData),
                        backgroundColor: @json($categoryChartColors),
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                color: labelColor,
                                boxWidth: 8,
                                font: { size: 9, weight: 'bold' }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    });
</script>
@endsection
