@extends('layouts.app')

@section('title', 'Controle Financeiro - Gestor de Freelas')
@section('page_title', 'Controle Financeiro')

@section('content')
<div class="space-y-6" x-data="financeManager()">

    <!-- Topo da página: Título e Link de Categorias -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Controle Financeiro</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Gerencie suas receitas e despesas pessoais e de sua empresa de forma unificada.</p>
        </div>
        <div class="flex flex-wrap gap-2 no-print">
            <a href="{{ route('finances.categories.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-sm font-semibold rounded-[5px] transition-colors shadow-sm bg-white">
                📂 Categorias
            </a>
            <a href="{{ route('finances.mei') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                🏢 Painel MEI / Faturamento
            </a>
        </div>
    </div>

    <!-- Navegação por Mês / Ano -->
    @php
        $months = [
            1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
            5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
            9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
        ];
        $currentDate = \Carbon\Carbon::createFromDate($year, $month, 1);
        $prevMonth = $currentDate->copy()->subMonth();
        $nextMonth = $currentDate->copy()->addMonth();
        $today = \Carbon\Carbon::now();

        $classLabels = ['all' => 'PF e PJ', 'PJ' => 'Apenas PJ', 'PF' => 'Apenas PF'];
        $selectedClassLabel = $classLabels[$classification] ?? 'PF e PJ';

        $statusLabels = ['all' => 'Todos Status', 'pago' => 'Pago / Recebido', 'pendente' => 'Pendente'];
        $selectedStatusLabel = $statusLabels[$status] ?? 'Todos Status';

        $selectedCategoryName = 'Todas Categorias';
        if ($categoryId && $categoryId !== 'all') {
            $selectedCatObj = $categories->firstWhere('id', $categoryId);
            if ($selectedCatObj) {
                $selectedCategoryName = $selectedCatObj->icon . ' ' . $selectedCatObj->name;
            }
        }
    @endphp

    <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Botões de Navegação Rápida -->
        <div class="flex items-center gap-2 w-full md:w-auto justify-between md:justify-start">
            <a href="{{ route('finances.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'classification' => $classification, 'status' => $status, 'category_id' => $categoryId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
                Anterior
            </a>
            <div class="text-lg font-black text-slate-800 tracking-tight">
                {{ $months[$month] }} {{ $year }}
            </div>
            <a href="{{ route('finances.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'classification' => $classification, 'status' => $status, 'category_id' => $categoryId]) }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                Próximo
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Filtros e Busca Rápida -->
        <form method="GET" action="{{ route('finances.index') }}" class="flex flex-wrap items-center gap-2 w-full md:w-auto justify-end">
            <input type="hidden" name="month" value="{{ $month }}" />
            <input type="hidden" name="year" value="{{ $year }}" />
            <input type="hidden" name="classification" value="{{ $classification }}" />
            <input type="hidden" name="status" value="{{ $status }}" />
            <input type="hidden" name="category_id" value="{{ $categoryId }}" />
            
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}"
                placeholder="Buscar descrição..." 
                class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 focus:outline-none focus:border-slate-350 max-w-[150px]"
            />

            <!-- Filtro Customizado: Classificação -->
            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors min-w-[100px] text-left">
                    <span>{{ $selectedClassLabel }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-40 rounded-[5px] bg-white border border-slate-200 shadow-md z-30 py-1 text-xs" x-cloak>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['classification' => 'all'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $classification === 'all' || !$classification ? 'bg-slate-50 font-black text-primary-600' : '' }}">PF e PJ</a>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['classification' => 'PJ'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $classification === 'PJ' ? 'bg-slate-50 font-black text-primary-600' : '' }}">Apenas PJ</a>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['classification' => 'PF'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $classification === 'PF' ? 'bg-slate-50 font-black text-primary-600' : '' }}">Apenas PF</a>
                </div>
            </div>

            <!-- Filtro Customizado: Status -->
            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors min-w-[120px] text-left">
                    <span>{{ $selectedStatusLabel }}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-44 rounded-[5px] bg-white border border-slate-200 shadow-md z-30 py-1 text-xs" x-cloak>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['status' => 'all'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $status === 'all' || !$status ? 'bg-slate-50 font-black text-primary-600' : '' }}">Todos Status</a>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['status' => 'pago'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $status === 'pago' ? 'bg-slate-50 font-black text-primary-600' : '' }}">Pago / Recebido</a>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['status' => 'pendente'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $status === 'pendente' ? 'bg-slate-50 font-black text-primary-600' : '' }}">Pendente</a>
                </div>
            </div>

            <!-- Filtro Customizado: Categoria -->
            <div x-data="{ open: false }" class="relative inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors min-w-[140px] text-left">
                    <span>{!! $selectedCategoryName !!}</span>
                    <svg class="w-3.5 h-3.5 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-48 max-h-60 overflow-y-auto rounded-[5px] bg-white border border-slate-200 shadow-md z-30 py-1 text-xs" x-cloak>
                    <a href="{{ route('finances.index', array_merge(request()->query(), ['category_id' => 'all'])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $categoryId === 'all' || !$categoryId ? 'bg-slate-50 font-black text-primary-600' : '' }}">Todas Categorias</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('finances.index', array_merge(request()->query(), ['category_id' => $cat->id])) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $categoryId == $cat->id ? 'bg-slate-50 font-black text-primary-600' : '' }}">
                            {{ $cat->icon }} {{ $cat->name }}
                        </a>
                    @endforeach
                </div>
            </div>

            <a href="{{ route('finances.index', ['month' => $today->month, 'year' => $today->year]) }}" class="inline-flex items-center gap-1 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-[5px] transition-colors shadow-sm">
                Hoje
            </a>
        </form>

    </div>

    <!-- Cards de Resumo Financeiro (Previsto vs Realizado) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Receitas do Mês -->
        <div class="bg-emerald-600 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-emerald-100 uppercase tracking-wider">Receitas do Mês</p>
                <h3 class="text-xl font-extrabold text-white mt-2">
                    R$ {{ number_format($previstoIncomes, 2, ',', '.') }}
                </h3>
                <span class="text-[10px] text-emerald-100 font-medium block mt-1">
                    Pago: R$ {{ number_format($realizadoIncomes, 2, ',', '.') }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                ＋
            </div>
        </div>

        <!-- Card 2: Despesas do Mês -->
        <div class="bg-rose-600 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-rose-100 uppercase tracking-wider">Despesas do Mês</p>
                <h3 class="text-xl font-extrabold text-white mt-2">
                    R$ {{ number_format($previstoExpenses, 2, ',', '.') }}
                </h3>
                <span class="text-[10px] text-rose-100 font-medium block mt-1">
                    Pago: R$ {{ number_format($realizadoExpenses, 2, ',', '.') }}
                </span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                －
            </div>
        </div>

        <!-- Card 3: Saldo Realizado (Faturado) -->
        <div class="{{ $realizadoBalance >= 0 ? 'bg-sky-700' : 'bg-rose-800' }} rounded-[5px] p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Saldo Realizado (Caixa)</p>
                <h3 class="text-xl font-extrabold text-white mt-2">
                    R$ {{ number_format($realizadoBalance, 2, ',', '.') }}
                </h3>
                <span class="text-[10px] text-white/80 font-medium block mt-1">
                    Apenas lançamentos pagos
                </span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                $
            </div>
        </div>

        <!-- Card 4: Saldo Previsto (Projeção) -->
        <div class="{{ $previstoBalance >= 0 ? 'bg-violet-700' : 'bg-rose-900' }} rounded-[5px] p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-white/80 uppercase tracking-wider">Saldo Projetado</p>
                <h3 class="text-xl font-extrabold text-white mt-2">
                    R$ {{ number_format($previstoBalance, 2, ',', '.') }}
                </h3>
                <span class="text-[10px] text-white/80 font-medium block mt-1">
                    Considerando pendências
                </span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                🔮
            </div>
        </div>

    </div>

    <!-- Seção de Cartões de Crédito Agrupados -->
    @if(count($cardGroups) > 0)
        <div class="space-y-3">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Compras no Cartão (Agrupado por Fatura)</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($cardGroups as $group)
                    @php
                        $card = $group['card'];
                        $hasUnpaid = collect($group['transactions'])->contains(fn($t) => $t->status !== 'pago');
                    @endphp
                    <div 
                        x-data="{ expanded: false }" 
                        class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm space-y-4 hover:shadow-md transition-all flex flex-col"
                    >
                        <!-- Card Visual Mockup -->
                        <div class="w-full rounded-[10px] bg-gradient-to-br {{ $card->brand_style['bg'] }} {{ $card->brand_style['text'] }} p-5 shadow-sm flex flex-col justify-between min-h-[160px] relative overflow-hidden">
                            <div class="absolute top-4 right-4 font-mono font-black text-base opacity-40 uppercase tracking-widest">{{ $card->brand_style['icon'] }}</div>
                            <div>
                                <span class="text-[9px] font-extrabold uppercase px-2 py-0.5 rounded-[4px] {{ $card->brand_style['flag_badge'] }}">
                                    {{ $card->brand_style['flag_label'] }}
                                </span>
                                <h4 class="font-bold text-sm truncate uppercase tracking-wider mt-2">{{ $card->card_name }}</h4>
                                <p class="text-[10px] opacity-75 font-semibold mt-0.5 tracking-wider">{{ $card->bank_name }}</p>
                            </div>
                            <div class="flex justify-between items-end border-t border-white/20 pt-3">
                                <div>
                                    <p class="text-[8px] uppercase tracking-wider opacity-75 font-bold">Fatura Atual (Gasto)</p>
                                    <p class="text-lg font-black mt-0.5 tracking-tight text-white">
                                        R$ {{ number_format($group['total_amount'], 2, ',', '.') }}
                                    </p>
                                </div>
                                <span class="text-xs font-mono tracking-widest font-black opacity-80">•••• {{ $card->last_four_digits ?? '••••' }}</span>
                            </div>

                            <!-- Stamp Carimbo Fatura Paga -->
                            @if(!$hasUnpaid && count($group['transactions']) > 0)
                                <div class="absolute inset-0 bg-emerald-950/20 backdrop-blur-[0.5px] flex items-center justify-center pointer-events-none select-none z-10">
                                    <div class="border-4 border-emerald-400 text-emerald-400 font-black text-lg md:text-xl px-3 py-1.5 rounded-[6px] uppercase tracking-widest -rotate-12 transform shadow-lg bg-emerald-950/15 backdrop-blur-xs select-none">
                                        ✓ FATURA PAGA
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Botão de Fatura (Modal) -->
                        <button 
                            type="button"
                            @click="expanded = true" 
                            class="w-full py-1.5 border border-slate-100 hover:bg-slate-50 text-[10px] text-slate-500 font-bold rounded-[5px] uppercase tracking-wider flex items-center justify-center gap-1.5"
                        >
                            <span>Visualizar Fatura ({{ count($group['transactions']) }})</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                        </button>

                        <!-- Modal Overlay para Compras do Cartão -->
                        <div 
                            x-show="expanded" 
                            class="fixed inset-0 z-50 overflow-y-auto" 
                            x-cloak
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100"
                            x-transition:leave-end="opacity-0"
                        >
                            <!-- Backdrop Overlay -->
                            <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="expanded = false"></div>
                            
                            <!-- Container Centrado -->
                            <div class="flex min-h-full items-center justify-center p-4">
                                <div 
                                    class="relative w-full max-w-lg transform overflow-hidden rounded-[8px] bg-white p-6 shadow-xl transition-all border border-slate-200 flex flex-col max-h-[80vh] sm:max-h-[85vh] text-left"
                                    @click.away="expanded = false"
                                >
                                    
                                    <!-- Cabeçalho -->
                                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                                        <div class="min-w-0">
                                            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider truncate">Fatura Detalhada</h3>
                                            <p class="text-[10px] text-slate-500 font-bold mt-0.5 uppercase tracking-widest">{{ $card->card_name }} • {{ $card->bank_name }} (•••• {{ $card->last_four_digits ?? '••••' }})</p>
                                        </div>
                                        <button type="button" @click="expanded = false" class="w-8 h-8 flex items-center justify-center bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 rounded-full transition-colors font-bold text-xs" title="Fechar">
                                            ✕
                                        </button>
                                    </div>

                                    <!-- Lista Scrollable de Compras -->
                                    <div class="flex-1 overflow-y-auto py-4 space-y-2 pr-1.5 -mr-1.5 min-h-[180px] max-h-[50vh]">
                                        @foreach($group['transactions'] as $t)
                                            <div 
                                                class="border p-2.5 rounded-[5px] space-y-2 text-xs relative flex flex-col justify-between cursor-pointer select-none transition-all duration-200 overflow-hidden"
                                                :class="selectedItems.includes({{ $t->id }}) 
                                                    ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50/20 shadow-[0_0_15px_rgba(37,99,235,0.15)]' 
                                                    : 'bg-slate-50 border-slate-150 hover:bg-slate-100/70 hover:border-slate-250'"
                                                @dblclick="handleCardDblClick({{ $t->id }}, {{ $t->amount }}, $event)"
                                                @click="handleCardClick({{ $t->id }}, {{ $t->amount }}, $event)"
                                            >
                                                <!-- Stamp Carimbo Pago/Pendente no Modal -->
                                                @if($t->status === 'pago')
                                                    <div class="absolute right-12 top-1.5 rotate-[-12deg] pointer-events-none select-none z-10 opacity-30 transform scale-90">
                                                        <div class="border-2 border-emerald-600/75 text-emerald-600/75 font-black text-[9px] px-2 py-0.5 rounded uppercase tracking-widest flex items-center gap-0.5">
                                                            <span>✓</span> <span>PAGO</span>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="absolute right-12 top-1.5 rotate-[-12deg] pointer-events-none select-none z-10 opacity-25 transform scale-90">
                                                        <div class="border-2 border-amber-600/75 text-amber-600/75 font-black text-[8px] px-1.5 py-0.5 rounded uppercase tracking-widest flex items-center gap-0.5">
                                                            <span>⏳</span> <span>PENDENTE</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="flex justify-between items-start gap-2 z-20">
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1.5">
                                                            <span class="text-sm shrink-0">{{ $t->category->icon ?? '💳' }}</span>
                                                            <h4 class="font-bold text-slate-800 truncate" title="{{ $t->description }}">{{ $t->description }}</h4>
                                                        </div>
                                                        <p class="text-[10px] text-slate-400 font-bold mt-0.5">Vencimento: {{ $t->due_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-black text-rose-600 block">R$ {{ number_format($t->amount, 2, ',', '.') }}</span>
                                                    </div>
                                                </div>

                                                <!-- Ações do Cartão Expandido -->
                                                <div class="flex items-center justify-end gap-1 border-t border-slate-100 pt-2 mt-1.5 no-print">
                                                    <!-- Checkbox de Soma -->
                                                    <label class="mr-auto flex items-center cursor-pointer">
                                                        <input type="checkbox" @change="toggleSelect({{ $t->id }}, {{ $t->amount }})" :checked="selectedItems.includes({{ $t->id }})" class="rounded text-primary-600 border-slate-350 focus:ring-primary-500/20 w-3.5 h-3.5" />
                                                    </label>

                                                    <!-- Toggle Status -->
                                                    <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline">
                                                        @csrf
                                                        <button type="submit" class="w-7 h-7 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Alternar status Pago/Pendente">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <!-- Editar -->
                                                    <a href="{{ route('finances.edit', $t->id) }}" class="w-7 h-7 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                        </svg>
                                                    </a>
                                                    <!-- Excluir -->
                                                    @if($t->group_code)
                                                        <button type="button" @click="confirmDelete('{{ $t->description }}', '{{ route('finances.destroy', $t->id) }}', true)" class="w-7 h-7 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Lançamento', message: 'Tem certeza que deseja excluir o lançamento?', action: '{{ route('finances.destroy', $t->id) }}', highSecurity: false })" class="w-7 h-7 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Rodapé -->
                                    <div class="border-t border-slate-100 pt-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 shrink-0">
                                        <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider flex items-center gap-1">
                                            <span>Valor Total:</span>
                                            <span class="text-rose-600 font-black text-sm">R$ {{ number_format($group['total_amount'], 2, ',', '.') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            @if($hasUnpaid)
                                                <form action="{{ route('finances.pay-invoice', $card->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="month" value="{{ $month }}">
                                                    <input type="hidden" name="year" value="{{ $year }}">
                                                    <button type="submit" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-extrabold rounded-[5px] transition-colors uppercase tracking-wider flex items-center gap-1">
                                                        <span>✓</span> Pagar Fatura
                                                    </button>
                                                </form>
                                            @else
                                                <span class="px-2.5 py-1.5 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black rounded-[5px] uppercase tracking-wider">
                                                    Fatura Paga ✓
                                                </span>
                                            @endif
                                            <button type="button" @click="expanded = false" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors uppercase tracking-wider">
                                                Fechar
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Lista de Lançamentos do Caixa Geral -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Lançamentos Financeiros (Caixa Geral)</h3>
        
        @if(count($commonTransactions) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($commonTransactions as $t)
                    @php
                        $isIncome = $t->type === 'entrada';
                    @endphp
                    <!-- Card Lançamento -->
                    <div 
                        class="border rounded-[5px] p-4 shadow-sm hover:shadow-md transition-all duration-200 relative flex flex-col justify-between min-h-[140px] cursor-pointer select-none overflow-hidden"
                        :class="selectedItems.includes({{ $t->id }}) 
                            ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50/20 shadow-[0_0_15px_rgba(37,99,235,0.15)]' 
                            : '{{ $isIncome ? 'bg-emerald-50/40 border-emerald-200 hover:border-emerald-300 hover:bg-emerald-50/60' : 'bg-rose-50/45 border-rose-200 hover:border-rose-300 hover:bg-rose-50/65' }}'"
                        @dblclick="handleCardDblClick({{ $t->id }}, {{ $t->amount }}, $event)"
                        @click="handleCardClick({{ $t->id }}, {{ $t->amount }}, $event)"
                    >
                        <!-- Stamp Carimbo Pago/Pendente -->
                        @if($t->status === 'pago')
                            <div class="absolute right-4 top-[40%] -translate-y-1/2 -rotate-12 pointer-events-none select-none z-10 opacity-30 transform scale-110">
                                <div class="border-4 border-emerald-600/75 text-emerald-600/75 font-black text-xl px-3.5 py-1.5 rounded uppercase tracking-widest flex items-center gap-1">
                                    <span>✓</span> <span>PAGO</span>
                                </div>
                            </div>
                        @else
                            <div class="absolute right-4 top-[40%] -translate-y-1/2 -rotate-12 pointer-events-none select-none z-10 opacity-25 transform scale-110">
                                <div class="border-4 border-amber-600/75 text-amber-600/75 font-black text-base px-3 py-1.5 rounded uppercase tracking-widest flex items-center gap-1">
                                    <span>⏳</span> <span>PENDENTE</span>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Header Card -->
                        <div class="flex items-start justify-between gap-4">
                            
                            <!-- Checkbox + Informações Principais -->
                            <div class="flex items-start gap-3 min-w-0 z-20">
                                <input 
                                    type="checkbox" 
                                    @change="toggleSelect({{ $t->id }}, {{ $t->amount }})"
                                    :checked="selectedItems.includes({{ $t->id }})"
                                    class="rounded text-primary-600 border-slate-350 focus:ring-primary-500/20 w-4 h-4 mt-0.5 shrink-0 cursor-pointer"
                                />
                                
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-lg shrink-0" title="Categoria">{{ $t->category->icon ?? '💰' }}</span>
                                        <h4 class="font-bold text-sm text-slate-800 truncate" title="{{ $t->description }}">
                                            {{ $t->description }}
                                        </h4>
                                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded-[4px] uppercase tracking-wider
                                            {{ $t->classification === 'PJ' ? 'bg-blue-50 text-blue-700 border border-blue-100' : 'bg-slate-100/80 text-slate-650' }}">
                                            {{ $t->classification }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-[10px] text-slate-400 font-bold block mt-1">
                                        Vencimento: <span class="text-slate-650 font-black">{{ $t->due_date->format('d/m/Y') }}</span>
                                        @if($t->bankAccount)
                                            • Destino: <a href="{{ route('bank-accounts.show', $t->bankAccount->id) }}" class="text-primary-600 hover:underline font-extrabold">{{ $t->bankAccount->account_name }}</a>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Valor -->
                            <div class="text-right shrink-0 flex flex-col items-end z-20">
                                <span class="text-base font-black {{ $isIncome ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isIncome ? '＋' : '－' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                            </div>

                        </div>

                        <!-- Footer Card: Anexo e Ações -->
                        <div class="flex items-center justify-between border-t border-slate-100 pt-3 mt-4 gap-2">
                            <!-- Anexo Link -->
                            <div>
                                @if($t->attachment_path)
                                    <a href="{{ route('finances.download-attachment', $t->id) }}" class="inline-flex items-center gap-1 text-[10px] font-bold text-blue-600 hover:text-blue-700 bg-blue-50 border border-blue-100 px-2 py-1 rounded-[5px] transition-colors" title="Baixar Nota Fiscal / Comprovante">
                                        📎 Nota Fiscal
                                    </a>
                                @endif
                            </div>

                            <!-- Ações -->
                            <div class="flex items-center gap-1 shrink-0 no-print">
                                <!-- Marcar Pago / Pendente -->
                                <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Alternar Pago/Pendente">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </form>
                                <!-- Duplicar -->
                                <form action="{{ route('finances.duplicate', $t->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Duplicar Lançamento">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                        </svg>
                                    </button>
                                </form>
                                <!-- Editar -->
                                <a href="{{ route('finances.edit', $t->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                                <!-- Excluir -->
                                @if($t->group_code)
                                    <button type="button" @click="confirmDelete('{{ $t->description }}', '{{ route('finances.destroy', $t->id) }}', true)" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @else
                                    <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Lançamento', message: 'Tem certeza de que deseja excluir o lançamento?', action: '{{ route('finances.destroy', $t->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                @endif
                            </div>

                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <div class="border border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] text-sm font-medium bg-white shadow-sm">
                Nenhum lançamento financeiro neste mês.
            </div>
        @endif
    </div>

    <!-- Barra Flutuante de Soma de Itens Selecionados -->
    <div 
        x-show="selectedItems.length > 0" 
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 bg-slate-900 text-white rounded-[8px] px-5 py-3 shadow-xl flex items-center justify-between gap-6 w-full max-w-md no-print"
        x-cloak
    >
        <div class="flex flex-col">
            <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold">Selecionados (<span x-text="selectedItems.length"></span>)</span>
            <span class="text-base font-black text-emerald-400" x-text="'R$ ' + parseFloat(selectedSum).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, '.')"></span>
        </div>
        <button 
            type="button" 
            @click="clearSelection()" 
            class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-[10px] uppercase font-bold rounded-[5px] transition-colors"
        >
            Desmarcar Todos
        </button>
    </div>

    <!-- Botão Flutuante (FAB) para Criar Lançamento -->
    <a href="{{ route('finances.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30 no-print" title="Novo Lançamento">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

    <!-- Modal Tem certeza de Exclusão (Lote de Parcelas / Recorrências) -->
    <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs" @click="showDeleteModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md transform overflow-hidden rounded-[5px] bg-white p-6 shadow-xl transition-all border border-slate-200 space-y-4">
                <div class="text-center space-y-2">
                    <span class="text-rose-500 text-3xl block">⚠️</span>
                    <h3 class="text-base font-bold text-slate-800">Excluir Lançamento Parcelado / Recorrente</h3>
                    <p class="text-xs text-slate-500 font-medium">Você está prestes a excluir o lançamento: <strong class="text-slate-850" x-text="deleteInfo.description"></strong>.</p>
                </div>

                <div class="space-y-2">
                    <form :action="deleteInfo.action" method="POST">
                        @csrf
                        @method('DELETE')
                        
                        <button type="submit" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm block text-center mb-2">
                            Excluir apenas este lançamento
                        </button>
                        
                        <button type="submit" name="delete_all" value="1" class="w-full py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm block text-center">
                            Excluir todas as ocorrências do grupo
                        </button>
                    </form>
                </div>

                <button type="button" @click="showDeleteModal = false" class="w-full py-2 bg-slate-100 hover:bg-slate-200 text-slate-650 text-xs font-bold rounded-[5px] transition-all">
                    Cancelar
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function financeManager() {
        return {
            selectedItems: [],
            selectedSum: 0.00,
            
            // Delete modal for linked items
            showDeleteModal: false,
            deleteInfo: {
                description: '',
                action: ''
            },

            init() {
                window.addEventListener('click', (e) => {
                    this.handleOutsideClick(e);
                });
            },

            handleOutsideClick(e) {
                if (this.selectedItems.length > 0) {
                    if (!e.target.closest('.cursor-pointer') && !e.target.closest('.fixed') && !e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input')) {
                        this.clearSelection();
                    }
                }
            },

            handleCardClick(id, amount, e) {
                if (this.selectedItems.length > 0) {
                    if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input')) {
                        this.toggleSelect(id, amount);
                    }
                }
            },

            handleCardDblClick(id, amount, e) {
                if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('input')) {
                    this.toggleSelect(id, amount);
                }
            },

            toggleSelect(id, amount) {
                const index = this.selectedItems.indexOf(id);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                    this.selectedSum = (parseFloat(this.selectedSum) - parseFloat(amount)).toFixed(2);
                } else {
                    this.selectedItems.push(id);
                    this.selectedSum = (parseFloat(this.selectedSum) + parseFloat(amount)).toFixed(2);
                }
            },

            clearSelection() {
                this.selectedItems = [];
                this.selectedSum = 0.00;
            },

            confirmDelete(description, action, hasGroup) {
                this.deleteInfo = {
                    description: description,
                    action: action
                };
                this.showDeleteModal = true;
            }
        };
    }
</script>
@endsection
