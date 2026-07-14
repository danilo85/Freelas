@extends('layouts.app')

@section('title', 'Controle Financeiro - Gestor de Freelas')
@section('page_title', 'Controle Financeiro')

@section('content')
<div id="pjax-container" class="space-y-6" x-data="financeManager()">

    <!-- Topo da página: Título e Link de Categorias -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Controle Financeiro</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Gerencie suas receitas e despesas pessoais e de sua empresa de forma unificada.</p>
        </div>
        <div class="flex items-center gap-2 no-print shrink-0">
            <a href="{{ route('finances.categories.index') }}" 
               title="Categorias" 
               class="inline-flex items-center justify-center p-2.5 border border-slate-200 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-[5px] transition-colors shadow-sm bg-white dark:bg-slate-900 focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path>
                </svg>
            </a>
            <a href="{{ route('finances.mei') }}" 
               title="Painel MEI / Faturamento" 
               class="inline-flex items-center justify-center p-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-[5px] transition-colors shadow-sm focus:outline-none">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </a>
            <button type="button" 
                    @click="openImportModal = true; importRawJson = ''; importPreviewData = null; importError = '';"
                    title="Importar JSON"
                    class="inline-flex items-center justify-center p-2.5 border border-slate-200 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 rounded-[5px] transition-colors shadow-sm bg-white dark:bg-slate-900 focus:outline-none cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                </svg>
            </button>
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

    <!-- Card 1: Filtros de Lançamentos -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm">
        <form method="GET" action="{{ route('finances.index') }}" class="flex flex-wrap items-center gap-3 w-full">
            <input type="hidden" name="month" value="{{ $month }}" />
            <input type="hidden" name="year" value="{{ $year }}" />

            <!-- Busca + Privacidade -->
            <div class="flex items-center gap-2 w-full sm:w-auto flex-1 sm:flex-initial min-w-[200px]">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}"
                    placeholder="Buscar descrição..." 
                    class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 focus:outline-none focus:border-slate-350 w-full flex-1"
                />
                
                <!-- Botão Modo Privacidade -->
                <button 
                    type="button"
                    @click="togglePrivacyMode()"
                    class="flex items-center justify-center p-2.5 rounded-[5px] border transition-all duration-200 focus:outline-none shrink-0"
                    :class="privacyMode ? 'bg-violet-50 border-violet-200 text-violet-750 shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                    :title="privacyMode ? 'Desativar Modo Privacidade' : 'Ativar Modo Privacidade'"
                >
                    <svg x-show="privacyMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 01-1.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                    <svg x-show="!privacyMode" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                </button>
            </div>

            <!-- Filtro Customizado: Classificação -->
            <div x-data="{ open: false }" class="relative w-full sm:w-auto sm:inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors w-full sm:min-w-[100px] text-left">
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
            <div x-data="{ open: false }" class="relative w-full sm:w-auto sm:inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors w-full sm:min-w-[120px] text-left">
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
            <div x-data="{ open: false }" class="relative w-full sm:w-auto sm:inline-block text-left">
                <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-3 py-2 hover:bg-slate-50 transition-colors w-full sm:min-w-[140px] text-left">
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

            <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-semibold rounded-[5px] transition-colors shadow-sm w-full sm:w-auto">
                Buscar
            </button>
        </form>
    </div>

    <!-- Card 2: Navegador de Meses -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-3 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-3 mt-3 no-print">
        <div class="flex items-center justify-between w-full sm:w-auto gap-2 sm:gap-3">
            <!-- Anterior -->
            <a href="{{ route('finances.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year, 'classification' => $classification, 'status' => $status, 'category_id' => $categoryId]) }}" 
               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2 border border-slate-200 hover:border-slate-350 text-slate-655 hover:text-slate-800 hover:bg-slate-50 text-xs font-bold rounded-[5px] transition-all shadow-sm shrink-0 uppercase tracking-wider"
               title="Mês Anterior">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden lg:inline">Anterior</span>
            </a>

            <!-- Mês / Ano Selecionado -->
            <div class="text-xs sm:text-sm font-extrabold text-slate-850 tracking-wider uppercase bg-slate-50 border border-slate-200 px-3 sm:px-6 py-2 rounded-[5px] shadow-inner text-center font-outfit min-w-[120px] sm:min-w-[210px] select-none flex-1 md:flex-none">
                {{ $months[$month] }} {{ $year }}
            </div>

            <!-- Próximo -->
            <a href="{{ route('finances.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year, 'classification' => $classification, 'status' => $status, 'category_id' => $categoryId]) }}" 
               class="inline-flex items-center justify-center gap-1.5 px-3 py-2 sm:px-3.5 sm:py-2 border border-slate-200 hover:border-slate-355 text-slate-655 hover:text-slate-800 hover:bg-slate-50 text-xs font-bold rounded-[5px] transition-all shadow-sm shrink-0 uppercase tracking-wider"
               title="Próximo Mês">
                <span class="hidden lg:inline">Próximo</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
        
        <!-- Botão Hoje -->
        <a href="{{ route('finances.index', ['month' => $today->month, 'year' => $today->year, 'classification' => $classification, 'status' => $status, 'category_id' => $categoryId]) }}" 
           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider w-full sm:w-auto text-center shrink-0">
            Hoje
        </a>
    </div>

    <!-- Cards de Resumo Financeiro (Previsto vs Realizado) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Receitas do Mês -->
        <div class="bg-emerald-600 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-shadow flex items-center justify-between">
            <div>
                <p class="text-[10px] font-semibold text-emerald-100 uppercase tracking-wider">Receitas do Mês</p>
                <h3 class="text-xl font-extrabold text-white mt-2">
                    <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $previstoIncomes }})">R$ {{ number_format($previstoIncomes, 2, ',', '.') }}</span>
                </h3>
                <span class="text-[10px] text-emerald-100 font-medium block mt-1">
                    Pago: <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $realizadoIncomes }})">R$ {{ number_format($realizadoIncomes, 2, ',', '.') }}</span>
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
                    <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $previstoExpenses }})">R$ {{ number_format($previstoExpenses, 2, ',', '.') }}</span>
                </h3>
                <span class="text-[10px] text-rose-100 font-medium block mt-1">
                    Pago: <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $realizadoExpenses }})">R$ {{ number_format($realizadoExpenses, 2, ',', '.') }}</span>
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
                    <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $realizadoBalance }})">R$ {{ number_format($realizadoBalance, 2, ',', '.') }}</span>
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
                    <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $previstoBalance }})">R$ {{ number_format($previstoBalance, 2, ',', '.') }}</span>
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
                        $hasPaid = collect($group['transactions'])->contains(fn($t) => $t->status === 'pago');
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
                                        <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $group['total_amount'] }})">R$ {{ number_format($group['total_amount'], 2, ',', '.') }}</span>
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
                                                id="modal-card-{{ $t->id }}"
                                                class="border p-2 rounded-[5px] space-y-1 text-xs relative flex flex-col justify-between cursor-pointer select-none transition-all duration-200 overflow-hidden {{ $t->status === 'pago' ? 'bg-emerald-50/30 border-emerald-500/20' : 'bg-slate-50 border-slate-150 hover:bg-slate-100/70 hover:border-slate-250' }}"
                                                :class="selectedItems.includes({{ $t->id }}) 
                                                    ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50/20 shadow-[0_0_15px_rgba(37,99,235,0.15)]' 
                                                    : ''"
                                                @dblclick="handleCardDblClick({{ $t->id }}, {{ $t->amount }}, $event)"
                                                @click="handleCardClick({{ $t->id }}, {{ $t->amount }}, $event)"
                                            >
                                                <!-- Stamp Carimbo Pago no Modal -->
                                                <div id="modal-status-stamp-{{ $t->id }}" class="absolute right-12 top-1.5 rotate-[-12deg] pointer-events-none select-none z-10 opacity-30 transform scale-90" style="display: {{ $t->status === 'pago' ? 'block' : 'none' }}">
                                                    <div class="border-2 border-emerald-600/75 text-emerald-600/75 font-black text-[9px] px-2 py-0.5 rounded uppercase tracking-widest flex items-center gap-0.5">
                                                        <span>✓</span> <span>PAGO</span>
                                                    </div>
                                                </div>

                                                <div class="flex justify-between items-start gap-2 z-20">
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-xs shrink-0">{{ $t->category->icon ?? '💳' }}</span>
                                                            <h4 class="font-bold text-slate-800 text-[11px] truncate" title="{{ $t->description }}">{{ $t->description }}</h4>
                                                        </div>
                                                        <p class="text-[9px] text-slate-400 font-bold mt-0.5">Vencimento: {{ $t->due_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div class="text-right shrink-0">
                                                        <span class="font-black text-rose-600 text-[11px] block" x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $t->amount }})">R$ {{ number_format($t->amount, 2, ',', '.') }}</span>
                                                    </div>
                                                </div>

                                                <!-- Ações do Cartão Expandido -->
                                                <div class="flex items-center justify-end gap-1 border-t border-slate-100 pt-1 mt-1 no-print">
                                                    <!-- Checkbox de Soma -->
                                                    <div class="mr-auto relative flex items-center justify-center">
                                                        <input type="checkbox" 
                                                               @change="toggleSelect({{ $t->id }}, {{ $t->amount }})" 
                                                               :checked="selectedItems.includes({{ $t->id }})" 
                                                               id="check-expanded-{{ $t->id }}"
                                                               class="sr-only cursor-pointer" />
                                                        <label for="check-expanded-{{ $t->id }}" 
                                                               class="w-4 h-4 flex items-center justify-center rounded-[4px] border transition-all cursor-pointer select-none"
                                                               :class="selectedItems.includes({{ $t->id }}) ? 'bg-primary-600 border-primary-600 text-white shadow-sm shadow-primary-600/30 scale-105' : 'border-slate-300 hover:border-slate-400 text-transparent bg-white'">
                                                            <svg class="w-2 h-2 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="4">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                                            </svg>
                                                        </label>
                                                    </div>

                                                    <!-- Toggle Status -->
                                                    <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline" @submit.prevent="toggleTransactionStatus({{ $t->id }}, '{{ route('finances.toggle-status', $t->id) }}')">
                                                        @csrf
                                                        <button 
                                                            id="modal-toggle-btn-{{ $t->id }}"
                                                            type="submit" 
                                                            class="w-6 h-6 flex items-center justify-center bg-transparent rounded-[5px] transition-all border-0 shadow-none cursor-pointer {{ $t->status === 'pago' ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-100' }}" 
                                                            title="Alternar status Pago/Pendente"
                                                        >
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
                                                    <!-- Editar -->
                                                    <a href="{{ route('finances.edit', $t->id) }}" class="w-6 h-6 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                        </svg>
                                                    </a>
                                                    <!-- Excluir -->
                                                    @if($t->group_code)
                                                        <button type="button" @click="confirmDelete('{{ $t->description }}', '{{ route('finances.destroy', $t->id) }}', true)" class="w-6 h-6 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    @else
                                                        <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Lançamento', message: 'Tem certeza que deseja excluir o lançamento?', action: '{{ route('finances.destroy', $t->id) }}', highSecurity: false })" class="w-6 h-6 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                                            <span class="text-rose-600 font-black text-sm" x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $group['total_amount'] }})">R$ {{ number_format($group['total_amount'], 2, ',', '.') }}</span>
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
                                            @endif
                                            @if($hasPaid)
                                                <form action="{{ route('finances.unpay-invoice', $card->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <input type="hidden" name="month" value="{{ $month }}">
                                                    <input type="hidden" name="year" value="{{ $year }}">
                                                    <button type="submit" class="px-3.5 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-[10px] font-extrabold rounded-[5px] transition-colors uppercase tracking-wider flex items-center gap-1">
                                                        <span>⏳</span> Voltar a Pendente
                                                    </button>
                                                </form>
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
    @php
        $commonTransactions = collect($commonTransactions)->sort(function($a, $b) {
            $statusA = $a->status === 'pago' ? 1 : 0;
            $statusB = $b->status === 'pago' ? 1 : 0;
            if ($statusA !== $statusB) {
                return $statusA <=> $statusB;
            }
            return strcmp($a->due_date->format('Y-m-d'), $b->due_date->format('Y-m-d'));
        });
    @endphp
    <div class="space-y-3">
        <div class="flex items-center justify-between gap-4">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Lançamentos Financeiros (Caixa Geral)</h3>
            
            <!-- Alternador de Visualização (Cards vs Lista) -->
            <div class="flex items-center border border-slate-200 rounded-[5px] p-0.5 bg-white shadow-sm select-none shrink-0 no-print">
                <button type="button" 
                        @click="setViewMode('cards')" 
                        class="p-1.5 px-3.5 rounded-[4px] text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                        :class="viewMode === 'cards' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                    </svg>
                    <span class="hidden xs:inline">Cards</span>
                </button>
                <button type="button" 
                        @click="setViewMode('list')" 
                        class="p-1.5 px-3.5 rounded-[4px] text-xs font-bold transition-all flex items-center gap-1 cursor-pointer"
                        :class="viewMode === 'list' ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-800'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    <span class="hidden xs:inline">Lista</span>
                </button>
            </div>
        </div>
        
        @if(count($commonTransactions) > 0)
            <!-- Modo Cards -->
            <div x-show="viewMode === 'cards'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                @foreach($commonTransactions as $t)
                    @php
                        $isIncome = $t->type === 'entrada';
                    @endphp
                    <!-- Card Lançamento -->
                    <div 
                        class="border rounded-[8px] p-5 shadow-sm hover:shadow-md transition-all duration-200 relative flex flex-col justify-between cursor-pointer select-none overflow-hidden"
                        :class="selectedItems.includes({{ $t->id }}) 
                            ? 'ring-2 ring-primary-500 border-primary-500 bg-primary-50/20 shadow-[0_0_15px_rgba(37,99,235,0.15)]' 
                            : '{{ $isIncome ? 'bg-emerald-50/50 border-emerald-350 hover:border-emerald-450 hover:bg-emerald-100/30' : 'bg-rose-50/50 border-rose-350 hover:border-rose-450 hover:bg-rose-100/30' }}'"
                        @dblclick="handleCardDblClick({{ $t->id }}, {{ $t->amount }}, $event)"
                        @click="handleCardClick({{ $t->id }}, {{ $t->amount }}, $event)"
                    >
                        <!-- Stamp Carimbo Pago/Pendente -->
                        <div id="status-stamp-{{ $t->id }}" class="absolute right-4 top-[50%] -translate-y-1/2 -rotate-12 pointer-events-none select-none z-10 opacity-25 transform scale-110" style="display: {{ $t->status === 'pago' ? 'block' : 'none' }}">
                            <div class="border-4 border-emerald-600/75 text-emerald-600/75 font-bold text-2xl px-4 py-2 rounded uppercase tracking-widest flex items-center gap-1 bg-white/90">
                                <span>✓</span> <span>PAGO</span>
                            </div>
                        </div>
                        
                        <!-- Header Card -->
                        <div class="flex items-center justify-between gap-4">
                            
                            <!-- Checkbox + Informações Principais -->
                            <div class="flex items-center gap-3 min-w-0 z-20 flex-1">
                                <div class="relative flex items-center justify-center shrink-0">
                                    <input 
                                        type="checkbox" 
                                        @change="toggleSelect({{ $t->id }}, {{ $t->amount }})"
                                        :checked="selectedItems.includes({{ $t->id }})"
                                        id="check-card-{{ $t->id }}"
                                        class="sr-only cursor-pointer"
                                    />
                                    <label for="check-card-{{ $t->id }}" 
                                           class="w-5 h-5 flex items-center justify-center rounded-[5px] border transition-all cursor-pointer select-none"
                                           :class="selectedItems.includes({{ $t->id }}) ? 'bg-primary-600 border-primary-600 text-white shadow-sm shadow-primary-600/30 scale-105' : 'border-slate-300 hover:border-slate-400 text-transparent bg-white'">
                                        <svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="3">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </label>
                                </div>
                                
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 flex-wrap">
                                        <span class="text-xl shrink-0" title="Categoria">{{ $t->category->icon ?? '💰' }}</span>
                                        <h4 class="font-bold text-sm text-slate-800 truncate leading-snug" title="{{ $t->description }}">
                                            {{ $t->description }}
                                        </h4>
                                        <span class="text-[9px] font-semibold px-2 py-0.5 rounded-[4px] uppercase tracking-wider
                                            {{ $t->classification === 'PJ' ? 'bg-blue-100/80 text-blue-800 border border-blue-200' : 'bg-slate-200 text-slate-700' }}">
                                            {{ $t->classification }}
                                        </span>
                                    </div>
                                    
                                    <p class="text-xs text-slate-500 font-medium block mt-1.5">
                                        Vencimento: <span class="text-slate-800 font-bold">{{ $t->due_date->format('d/m/Y') }}</span>
                                        @if($t->bankAccount)
                                            • Destino: <a href="{{ route('bank-accounts.show', $t->bankAccount->id) }}" class="text-primary-600 hover:underline font-bold">{{ $t->bankAccount->account_name }}</a>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Valor -->
                            <div class="text-right shrink-0 flex flex-col items-end z-20">
                                <span class="text-base sm:text-lg font-bold {{ $isIncome ? 'text-emerald-700' : 'text-rose-700' }}" x-text="privacyMode ? '{{ $isIncome ? '＋' : '－' }} R$ ••••' : '{{ $isIncome ? '＋' : '－' }} R$ ' + formatMoney({{ (float) $t->amount }})">
                                    {{ $isIncome ? '＋' : '－' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                            </div>

                        </div>

                        <!-- Footer Card: Anexo e Ações -->
                        <div class="flex items-center justify-between border-t border-slate-200/50 pt-2.5 mt-3 gap-2">
                            <!-- Anexo Link -->
                            <div>
                                @if($t->attachment_path)
                                    <a href="{{ route('finances.download-attachment', $t->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 hover:text-blue-800 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-[5px] transition-colors" title="Baixar Nota Fiscal / Comprovante">
                                        📎 Nota Fiscal
                                    </a>
                                @endif
                            </div>

                            <!-- Ações -->
                            <div class="flex items-center gap-1 shrink-0 no-print">
                                <!-- Marcar Pago / Pendente -->
                                <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline" @submit.prevent="toggleTransactionStatus({{ $t->id }}, '{{ route('finances.toggle-status', $t->id) }}')">
                                    @csrf
                                    <button type="submit" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none cursor-pointer" title="Alternar Pago/Pendente">
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

            <!-- Modo Tabela/Lista -->
            <div x-show="viewMode === 'list'" class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden overflow-x-auto" x-cloak>
                <table class="w-full text-left border-collapse min-w-[700px]">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-250 text-xs font-black text-slate-500 uppercase tracking-widest select-none">
                            <th class="py-3.5 px-4 w-12 text-center">Selec.</th>
                            <th class="py-3.5 px-4">Descrição</th>
                            <th class="py-3.5 px-4">Categoria</th>
                            <th class="py-3.5 px-4">Vencimento</th>
                            <th class="py-3.5 px-4">Conta Destino</th>
                            <th class="py-3.5 px-4 text-right">Valor</th>
                            <th class="py-3.5 px-4 text-center">Status</th>
                            <th class="py-3.5 px-4 text-right no-print">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @foreach($commonTransactions as $t)
                            @php
                                $isIncome = $t->type === 'entrada';
                            @endphp
                            <tr class="transition-colors cursor-pointer select-none text-sm border-b border-slate-200/60
                                {{ $isIncome ? 'bg-emerald-50/30 hover:bg-emerald-100/35 text-slate-800' : 'bg-rose-50/30 hover:bg-rose-100/35 text-slate-800' }}"
                                :class="selectedItems.includes({{ $t->id }}) ? '!bg-primary-50/15' : ''"
                                @click="handleCardClick({{ $t->id }}, {{ $t->amount }}, $event)">
                                <td class="py-4 px-4 text-center" @click.stop>
                                    <div class="relative flex items-center justify-center">
                                        <input 
                                            type="checkbox" 
                                            @change="toggleSelect({{ $t->id }}, {{ $t->amount }})"
                                            :checked="selectedItems.includes({{ $t->id }})"
                                            id="check-list-{{ $t->id }}"
                                            class="sr-only cursor-pointer"
                                        />
                                        <label for="check-list-{{ $t->id }}" 
                                               class="w-5 h-5 flex items-center justify-center rounded-[5px] border transition-all cursor-pointer select-none"
                                               :class="selectedItems.includes({{ $t->id }}) ? 'bg-primary-600 border-primary-600 text-white' : 'border-slate-350 bg-white text-transparent'">
                                            <svg class="w-3 h-3 stroke-current" fill="none" viewBox="0 0 24 24" stroke-width="4">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </label>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-black text-slate-900">
                                    <div class="flex items-center gap-2">
                                        <span class="truncate max-w-[200px]">{{ $t->description }}</span>
                                        <span class="text-[9px] font-black px-2 py-0.5 rounded bg-white text-slate-700 border border-slate-200 shadow-xs shrink-0">
                                            {{ $t->classification }}
                                        </span>
                                    </div>
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-600">
                                    <span class="text-xl mr-1">{{ $t->category->icon ?? '💰' }}</span>
                                    <span>{{ $t->category->name ?? 'Geral' }}</span>
                                </td>
                                <td class="py-4 px-4 font-mono font-black text-slate-700">
                                    {{ $t->due_date->format('d/m/Y') }}
                                </td>
                                <td class="py-4 px-4 font-bold text-slate-600">
                                    @if($t->bankAccount)
                                        <span class="text-primary-650 font-black">{{ $t->bankAccount->account_name }}</span>
                                    @else
                                        <span class="text-slate-400 font-normal">-</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right font-black text-base {{ $isIncome ? 'text-emerald-700' : 'text-rose-700' }}">
                                    <span x-text="privacyMode ? 'R$ ••••' : '{{ $isIncome ? '＋' : '－' }} R$ ' + formatMoney({{ (float) $t->amount }})">
                                        {{ $isIncome ? '＋' : '－' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-center">
                                    @if($t->status === 'pago')
                                        <span class="px-2.5 py-0.5 rounded-[4px] bg-emerald-100 border border-emerald-300 text-emerald-800 text-[10px] font-black uppercase tracking-wider">Pago</span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-[4px] bg-amber-100 border border-amber-300 text-amber-800 text-[10px] font-black uppercase tracking-wider">Pendente</span>
                                    @endif
                                </td>
                                <td class="py-4 px-4 text-right no-print" @click.stop>
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline" @submit.prevent="toggleTransactionStatus({{ $t->id }}, '{{ route('finances.toggle-status', $t->id) }}')">
                                            @csrf
                                            <button type="submit" class="p-1 text-slate-400 hover:text-emerald-600 rounded hover:bg-slate-100 cursor-pointer" title="Alternar Pago/Pendente">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('finances.duplicate', $t->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="p-1 text-slate-400 hover:text-primary-600 rounded hover:bg-slate-100" title="Duplicar">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                        <a href="{{ route('finances.edit', $t->id) }}" class="p-1 text-slate-400 hover:text-primary-600 rounded hover:bg-slate-100" title="Editar">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                        </a>
                                        @if($t->group_code)
                                            <button type="button" @click="confirmDelete('{{ $t->description }}', '{{ route('finances.destroy', $t->id) }}', true)" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100" title="Excluir">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @else
                                            <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Lançamento', message: 'Tem certeza de que deseja excluir o lançamento?', action: '{{ route('finances.destroy', $t->id) }}', highSecurity: false })" class="p-1 text-slate-400 hover:text-red-600 rounded hover:bg-slate-100" title="Excluir">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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
            <span class="text-base font-black text-emerald-400" x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney(selectedSum)"></span>
        </div>
        <div class="flex items-center gap-2">
            <button 
                type="button" 
                @click="deleteSelected()" 
                class="px-3 py-1.5 bg-rose-650 hover:bg-rose-700 text-white text-[10px] uppercase font-bold rounded-[5px] transition-colors"
            >
                Excluir
            </button>
            <button 
                type="button" 
                @click="clearSelection()" 
                class="px-3 py-1.5 bg-white/10 hover:bg-white/20 text-white text-[10px] uppercase font-bold rounded-[5px] transition-colors"
            >
                Desmarcar
            </button>
        </div>
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

    <!-- Modal Tem certeza de Exclusão em Lote -->
    <div x-show="showBatchDeleteModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs" @click="showBatchDeleteModal = false"></div>
        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md transform overflow-hidden rounded-[5px] bg-white p-6 shadow-xl transition-all border border-slate-200 space-y-4">
                <div class="text-center space-y-2">
                    <span class="text-rose-500 text-3xl block">⚠️</span>
                    <h3 class="text-base font-bold text-slate-800">Excluir Lançamentos Selecionados</h3>
                    <p class="text-xs text-slate-500 font-medium">Você está prestes a excluir <strong class="text-slate-850" x-text="selectedItems.length"></strong> lançamento(s) de forma definitiva. Essa ação não poderá ser desfeita.</p>
                </div>

                <div class="space-y-2 pt-2">
                    <button type="button" @click="executeBatchDelete()" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm block text-center">
                        Sim, excluir selecionados
                    </button>
                    
                    <button type="button" @click="showBatchDeleteModal = false" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-650 text-xs font-bold rounded-[5px] transition-all">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Importação JSON de Finanças -->
    <template x-teleport="body">
        <div x-show="openImportModal" 
             class="fixed inset-0 top-0 left-0 w-screen h-screen flex items-center justify-center bg-slate-900/50 backdrop-blur-xs"
             style="z-index: 9999999;"
             x-transition.opacity
             x-cloak>
            <div class="bg-white border border-slate-200 shadow-2xl rounded-lg max-w-2xl w-full p-6 space-y-4 text-left select-none max-h-[85vh] overflow-y-auto" @click.away="if(!isImporting) openImportModal = false">
                
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="font-bold text-slate-800 text-base uppercase tracking-tight flex items-center gap-2">
                        <span>📥</span> Importar Finanças (Giro JSON)
                    </h3>
                    <button type="button" @click="openImportModal = false" class="text-slate-400 hover:text-slate-655 cursor-pointer">
                        ✕
                    </button>
                </div>

                <!-- Error Banner -->
                <div x-show="importError" class="bg-rose-50 border border-rose-200 text-rose-800 p-3 rounded text-xs font-semibold leading-relaxed" x-cloak>
                    <span x-text="importError"></span>
                </div>

                <!-- Drag/Upload Area when no data is parsed -->
                <div x-show="!importPreviewData" class="space-y-4">
                    <div class="border-2 border-dashed border-slate-220 rounded-lg p-8 text-center bg-slate-50/50 hover:bg-slate-50 transition-colors relative">
                        <input type="file" @change="handleJsonFileSelect($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".json">
                        <span class="text-3xl block mb-2">📊</span>
                        <span class="text-sm font-bold text-slate-700 block">Clique para selecionar o arquivo JSON de Transações</span>
                        <span class="text-xs text-slate-400 block mt-1">Formatos suportados: Giro.transactions.v1</span>
                    </div>
                </div>

                <!-- Preview Data Area -->
                <div x-show="importPreviewData" class="space-y-4" x-cloak>
                    <div class="bg-slate-50 border border-slate-200 rounded p-4 space-y-3">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Resumo da Importação (<span x-text="importPreviewData ? importPreviewData.length : 0"></span> transação(ões) principal(is))</h4>
                        
                        <div class="divide-y divide-slate-200 max-h-[40vh] overflow-y-auto space-y-3 pr-2">
                            <template x-for="(item, idx) in importPreviewData" :key="idx">
                                <div class="pt-3 first:pt-0 space-y-2">
                                    <!-- Description & Value -->
                                    <div class="flex justify-between items-start gap-3">
                                        <div class="space-y-0.5">
                                            <span class="text-sm font-black text-slate-800" x-text="item.transaction.descricao"></span>
                                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded block w-max"
                                                  :class="item.transaction.tipo === 'despesa' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'"
                                                  x-text="item.transaction.tipo === 'despesa' ? 'Saída' : 'Entrada'"></span>
                                        </div>
                                        <span class="text-sm font-black shrink-0"
                                              :class="item.transaction.tipo === 'despesa' ? 'text-red-600' : 'text-emerald-600'">
                                            R$ <span x-text="formatMoney(item.transaction.valor)"></span>
                                        </span>
                                    </div>

                                    <!-- Bank & Category row -->
                                    <div class="grid grid-cols-3 gap-2 text-[10px] uppercase font-bold text-slate-550 bg-white p-2 border border-slate-200/50 rounded">
                                        <div>
                                            <span class="text-slate-400 block font-semibold">Conta / Banco</span>
                                            <span class="text-slate-800 truncate block" x-text="item.bank ? item.bank.nome : 'Nenhum'"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block font-semibold">Categoria</span>
                                            <span class="text-slate-800 truncate block" x-text="item.category ? item.category.nome : 'Geral'"></span>
                                        </div>
                                        <div>
                                            <span class="text-slate-400 block font-semibold">Recorrências</span>
                                            <span class="text-slate-800 block" x-text="item.related_installments ? '+' + item.related_installments.length + ' lançamentos' : 'Nenhuma'"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Warnings Banner about unsaved fields -->
                    <div class="bg-amber-50 border border-amber-200 text-amber-900 p-3 rounded text-xs space-y-1">
                        <span class="font-bold block uppercase tracking-wider text-[10px]">⚠️ Notas de conversão</span>
                        <p class="leading-relaxed font-semibold">IDs originais de bancos e categorias serão mapeados pelo nome correspondente. O sistema irá evitar duplicar transações idênticas (mesma descrição, valor e data de vencimento).</p>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 select-none">
                    <button type="button" 
                            :disabled="isImporting"
                            @click="openImportModal = false" 
                            class="px-4 py-2 border border-slate-200 text-xs font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-600 disabled:opacity-50">
                        Cancelar
                    </button>
                    <button type="button" 
                            x-show="importPreviewData"
                            :disabled="isImporting"
                            @click="submitImport()" 
                            class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-[5px] transition-colors flex items-center gap-1.5 disabled:opacity-50 cursor-pointer shadow-sm">
                        <span x-show="isImporting" class="w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        <span x-text="isImporting ? 'Importando...' : 'Confirmar Importação'"></span>
                    </button>
                </div>

            </div>
        </div>
    </template>

</div>

<script>
    function financeManager() {
        return {
            selectedItems: [],
            selectedSum: 0.00,
            privacyMode: localStorage.getItem('privacyMode') === 'true',
            viewMode: localStorage.getItem('finance_view_mode') || 'cards',
            
            init() {
                if (window.innerWidth < 768) {
                    this.viewMode = 'cards';
                }
                window.addEventListener('resize', () => {
                    if (window.innerWidth < 768 && this.viewMode !== 'cards') {
                        this.viewMode = 'cards';
                    }
                });
            },
            
            setViewMode(mode) {
                this.viewMode = mode;
                localStorage.setItem('finance_view_mode', mode);
            },
            
            // Import states
            openImportModal: false,
            importRawJson: '',
            importPreviewData: null,
            importError: '',
            isImporting: false,

            handleJsonFileSelect(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const rawData = e.target.result;
                        const parsed = JSON.parse(rawData);
                        
                        if (!parsed || !parsed.format || !parsed.format.startsWith('giro.transactions')) {
                            this.importError = "Formato de arquivo incompatível. Deve ser um export de transações do Giro v1.";
                            this.importPreviewData = null;
                            return;
                        }

                        this.importRawJson = rawData;
                        
                        let preview = [];
                        if (parsed.data && parsed.data.transaction) {
                            preview.push(parsed.data);
                        } else if (parsed.data && Array.isArray(parsed.data)) {
                            preview = parsed.data;
                        }
                        
                        this.importPreviewData = preview;
                        this.importError = "";
                    } catch(err) {
                        this.importError = "Erro ao processar JSON: " + err.message;
                        this.importPreviewData = null;
                    }
                };
                reader.readAsText(file);
            },

            async submitImport() {
                if (!this.importRawJson) return;
                this.isImporting = true;
                try {
                    const response = await fetch('{{ route("finances.import-json") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ json_data: this.importRawJson })
                    });
                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        this.importError = result.message || "Erro desconhecido na importação.";
                    }
                } catch (err) {
                    this.importError = "Erro ao enviar dados: " + err.message;
                } finally {
                    this.isImporting = false;
                }
            },
            
            togglePrivacyMode() {
                this.privacyMode = !this.privacyMode;
                localStorage.setItem('privacyMode', this.privacyMode ? 'true' : 'false');
            },
            
            formatMoney(value) {
                return parseFloat(value).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },
            
            // Delete modal for linked items
            showDeleteModal: false,
            showBatchDeleteModal: false,
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

            deleteSelected() {
                if (this.selectedItems.length === 0) return;
                this.showBatchDeleteModal = true;
            },

            async executeBatchDelete() {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch('{{ route("finances.batch-destroy") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ ids: this.selectedItems })
                    });
                    const result = await response.json();
                    if (result.success) {
                        window.location.reload();
                    } else {
                        alert(result.message || "Erro ao excluir itens.");
                    }
                } catch(e) {
                    alert("Erro ao enviar requisição de exclusão: " + e.message);
                } finally {
                    this.showBatchDeleteModal = false;
                }
            },

            confirmDelete(description, action, hasGroup) {
                this.deleteInfo = {
                    description: description,
                    action: action
                };
                this.showDeleteModal = true;
            },

            toggleTransactionStatus(id, url) {
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const stampPaid = document.getElementById('status-stamp-' + id);
                        const stampPending = document.getElementById('status-stamp-pending-' + id);
                        if (stampPaid) stampPaid.style.display = data.status === 'pago' ? 'block' : 'none';
                        if (stampPending) stampPending.style.display = data.status === 'pago' ? 'none' : 'block';

                        const modalStampPaid = document.getElementById('modal-status-stamp-' + id);
                        const modalStampPending = document.getElementById('modal-status-stamp-pending-' + id);
                        if (modalStampPaid) modalStampPaid.style.display = data.status === 'pago' ? 'block' : 'none';
                        if (modalStampPending) modalStampPending.style.display = data.status === 'pago' ? 'none' : 'block';

                        // Toggle classes for card element in list modal
                        const cardEl = document.getElementById('modal-card-' + id);
                        const toggleBtn = document.getElementById('modal-toggle-btn-' + id);
                        if (cardEl) {
                            if (data.status === 'pago') {
                                cardEl.classList.remove('bg-slate-50', 'border-slate-150');
                                cardEl.classList.add('bg-emerald-50/30', 'border-emerald-500/20');
                            } else {
                                cardEl.classList.remove('bg-emerald-50/30', 'border-emerald-500/20');
                                cardEl.classList.add('bg-slate-50', 'border-slate-150');
                            }
                        }
                        if (toggleBtn) {
                            if (data.status === 'pago') {
                                toggleBtn.classList.remove('text-slate-400', 'hover:bg-slate-100');
                                toggleBtn.classList.add('text-emerald-600', 'hover:bg-emerald-50');
                            } else {
                                toggleBtn.classList.remove('text-emerald-600', 'hover:bg-emerald-50');
                                toggleBtn.classList.add('text-slate-400', 'hover:bg-slate-100');
                            }
                        }
                    }
                })
                .catch(err => {
                    console.error('Erro ao alternar status da transação:', err);
                });
            }
        };
    }
</script>
@endsection
