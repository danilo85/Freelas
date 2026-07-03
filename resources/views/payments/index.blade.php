@extends('layouts.app')

@section('title', 'Calendário de Pagamentos - Gestor de Freelas')
@section('page_title', 'Calendário de Pagamentos')

@section('content')
<div id="pjax-container" class="space-y-6">

    <!-- Topo da página: Título e Link de Retorno -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Controle de Faturamento</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Acompanhe todos os recebimentos e parcelas dos seus orçamentos aprovados.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm self-start sm:self-auto no-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Ir para Orçamentos
        </a>
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
    @endphp

    <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        
        <!-- Botões de Navegação Rápida -->
        <div class="flex items-center justify-between md:justify-start gap-3 w-full md:w-auto">
            <a href="{{ route('payments.index', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-2 sm:px-3.5 sm:py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-xs sm:text-sm font-semibold rounded-[5px] transition-colors shadow-sm shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                </svg>
                <span class="hidden sm:inline">Anterior</span>
            </a>
            <div class="text-base sm:text-lg font-black text-slate-800 tracking-tight text-center flex-1 md:flex-none">
                {{ $months[$month] }} {{ $year }}
            </div>
            <a href="{{ route('payments.index', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="inline-flex items-center justify-center gap-1.5 px-2.5 py-2 sm:px-3.5 sm:py-2 border border-slate-200 text-slate-650 hover:bg-slate-50 text-xs sm:text-sm font-semibold rounded-[5px] transition-colors shadow-sm shrink-0">
                <span class="hidden sm:inline">Próximo</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>

        <!-- Dropdowns de Seleção e Hoje -->
        <form method="GET" action="{{ route('payments.index') }}" class="grid grid-cols-3 gap-2 w-full md:flex md:w-auto justify-end">
            <select name="month" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-2 py-2 focus:outline-none focus:border-slate-350 w-full md:w-auto">
                @foreach($months as $num => $name)
                    <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" class="bg-white border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] px-2 py-2 focus:outline-none focus:border-slate-350 w-full md:w-auto">
                @for($y = $today->year - 5; $y <= $today->year + 5; $y++)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <a href="{{ route('payments.index', ['month' => $today->month, 'year' => $today->year]) }}" class="inline-flex items-center justify-center gap-1 px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-[5px] transition-colors shadow-sm w-full md:w-auto">
                Hoje
            </a>
        </form>

    </div>

    <!-- Cards de Resumo Estatístico -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Card 1: Total do Mês (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total do Mês</p>
                <h3 class="text-2xl font-extrabold text-white mt-2">
                    R$ {{ number_format($totalMonth, 2, ',', '.') }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Faturamento consolidado
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Card 2: Quantidade de Pagamentos (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Pagamentos</p>
                <h3 class="text-2xl font-extrabold text-white mt-2">
                    {{ $paymentsCount }}
                </h3>
                <span class="text-sm text-emerald-100/90 font-medium block mt-1.5">
                    Transações registradas
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 112-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>

        <!-- Card 3: Dias com Pagamento (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-sm font-bold text-purple-100 uppercase tracking-wider">Dias com Pagamento</p>
                <h3 class="text-2xl font-extrabold text-white mt-2">
                    {{ $daysWithPaymentCount }}
                </h3>
                <span class="text-sm text-purple-100/90 font-medium block mt-1.5">
                    Dias ativos de recebimento
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Calendário Mensal -->
    <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm p-4">
        
        <div class="border border-slate-200 rounded-[5px] overflow-hidden">
            <!-- Dias da Semana Header -->
            <div class="grid grid-cols-7 bg-slate-50 border-b border-slate-200 text-center py-3 text-xs font-bold text-slate-500 uppercase tracking-wider">
                <div>Dom</div>
                <div>Seg</div>
                <div>Ter</div>
                <div>Qua</div>
                <div>Qui</div>
                <div>Sex</div>
                <div>Sáb</div>
            </div>

            <!-- Grid de Células dos Dias -->
            <div class="grid grid-cols-7 divide-x divide-y divide-slate-150">
                @foreach($calendarDays as $cell)
                    @php
                        $isToday = $cell['is_current_month'] && $cell['date'] === $today->toDateString();
                    @endphp
                    <div class="min-h-[110px] p-2.5 flex flex-col justify-between transition-all hover:bg-slate-50/50 relative
                        {{ $cell['payments_sum'] > 0 ? 'bg-emerald-50/40 border border-emerald-200/80 shadow-[inset_0_0_10px_rgba(16,185,129,0.06),_0_0_8px_rgba(16,185,129,0.15)] z-10' : ($cell['is_current_month'] ? 'bg-white' : 'bg-slate-50/30 text-slate-400') }}
                        {{ $isToday ? 'ring-2 ring-primary-500 ring-inset' : '' }}">
                        
                        <!-- Dia número -->
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold {{ $cell['is_current_month'] ? ($isToday ? 'text-primary-600' : 'text-slate-700') : 'text-slate-400' }}">
                                {{ $cell['day'] }}
                            </span>
                            @if($isToday)
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500" title="Hoje"></span>
                            @endif
                        </div>

                        <!-- Dados do pagamento -->
                        @if($cell['payments_sum'] > 0)
                            <div class="space-y-1 mt-auto">
                                <!-- Badge Valor Total -->
                                <div class="bg-emerald-50 border border-emerald-150 text-emerald-800 text-[11px] md:text-xs font-black px-1.5 py-0.5 rounded-[5px] truncate text-center" title="Faturamento do dia">
                                    R$ {{ number_format($cell['payments_sum'], 2, ',', '.') }}
                                </div>
                                <!-- Contagem -->
                                <div class="text-[9px] md:text-[10px] text-slate-400 font-bold uppercase tracking-wider text-center">
                                    {{ $cell['payments_count'] }} {{ $cell['payments_count'] == 1 ? 'pag.' : 'pag.' }}
                                </div>
                            </div>
                        @endif

                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Cards de Pagamentos e Slider Alpine.js -->
    <div class="space-y-4">
        <h2 class="text-lg font-black text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
            Pagamentos por Orçamento no Mês
        </h2>

        @if(count($projectPayments) > 0)
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                @foreach($projectPayments as $group)
                    @php
                        $paymentCount = count($group['payments']);
                    @endphp
                    
                    <div x-data="{ currentSlide: 0, totalSlides: {{ $paymentCount }} }" class="relative">
                        
                        <!-- Stack visual effect layers if there are multiple payments -->
                        @if($paymentCount > 1)
                            <!-- Under layers for stack effect -->
                            <div class="absolute inset-0 bg-white border border-slate-200 rounded-[5px] translate-x-2 translate-y-2 shadow-sm z-0"></div>
                            <div class="absolute inset-0 bg-white border border-slate-200 rounded-[5px] translate-x-1 translate-y-1 shadow-sm z-1"></div>
                        @endif

                        <!-- Main Payment Card -->
                        <div class="relative bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4 z-10 hover:shadow-md transition-shadow">
                            
                            <!-- Header: Project and Client info -->
                            <div class="flex items-start justify-between border-b border-slate-100 pb-3 gap-3">
                                <div>
                                    <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                                        Cliente: {{ $group['client']->name }}
                                    </span>
                                    <h3 class="text-base font-bold text-slate-800 hover:text-primary-600 transition-colors mt-0.5">
                                        <a href="{{ route('projects.show', $group['project']->id) }}" class="hover:underline">
                                            {{ $group['project']->title }}
                                        </a>
                                    </h3>
                                </div>
                                <span class="bg-slate-100 text-slate-650 text-xs font-bold px-2 py-0.5 rounded-[5px] border border-slate-200 shrink-0">
                                    {{ $paymentCount }} {{ $paymentCount == 1 ? 'Pagamento' : 'Pagamentos' }}
                                </span>
                            </div>

                            <!-- Carousel Container -->
                            <div class="relative overflow-hidden min-h-[190px]">
                                @foreach($group['payments'] as $idx => $p)
                                    <!-- Slide Component -->
                                    <div 
                                        x-show="currentSlide === {{ $idx }}" 
                                        x-transition:enter="transition ease-out duration-300 transform"
                                        x-transition:enter-start="opacity-0 translate-x-12"
                                        x-transition:enter-end="opacity-100 translate-x-0"
                                        class="space-y-4"
                                        x-cloak
                                    >
                                        <!-- Amount and Date -->
                                        <div class="flex items-baseline justify-between">
                                            <span class="text-xl font-black text-emerald-600">
                                                R$ {{ number_format($p['amount'], 2, ',', '.') }}
                                            </span>
                                            <span class="text-sm font-semibold text-slate-500">
                                                Pago em {{ $p['paid_at'] }}
                                            </span>
                                        </div>

                                        <!-- Details Grid -->
                                        <div class="grid grid-cols-2 gap-4 text-sm bg-slate-50 border border-slate-100 p-3 rounded-[5px]">
                                            <div>
                                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Forma de Pagamento</span>
                                                <span class="font-bold text-slate-700 uppercase">{{ $p['payment_method'] }}</span>
                                            </div>
                                            <div>
                                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Conta Bancária</span>
                                                <span class="font-semibold text-slate-700">{{ $p['bank_account'] }}</span>
                                            </div>
                                        </div>

                                        <!-- Observations -->
                                        @if($p['observations'])
                                            <div class="text-sm">
                                                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Observações</span>
                                                <p class="text-slate-650 italic mt-0.5 leading-relaxed">{{ $p['observations'] }}</p>
                                            </div>
                                        @endif

                                        <!-- Invoice file links -->
                                        @if($p['download_invoice_url'])
                                            <div class="flex items-center justify-between gap-3 bg-blue-50/50 border border-blue-100 p-2.5 rounded-[5px] text-sm">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    <span class="font-semibold text-blue-800 truncate text-xs">Nota Fiscal Anexada</span>
                                                </div>
                                                <a href="{{ $p['download_invoice_url'] }}" class="px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm">
                                                    Baixar NF
                                                </a>
                                            </div>
                                        @endif

                                        <!-- Additional projects sharing -->
                                        @if(!empty($p['related_projects']))
                                            <div class="text-xs space-y-1">
                                                <span class="font-bold text-slate-400 uppercase tracking-wider block">NF compartilhada com:</span>
                                                <div class="flex flex-wrap gap-1">
                                                    @foreach($p['related_projects'] as $relTitle)
                                                        <span class="bg-slate-100 border border-slate-200 text-slate-600 font-bold px-1.5 py-0.5 rounded-[5px] truncate max-w-[180px]">
                                                            {{ $relTitle }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Action buttons -->
                                        <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-3">
                                            <!-- Editar Pagamento -->
                                            <a 
                                                href="{{ $p['edit_url'] }}"
                                                class="flex items-center gap-1.5 py-1.5 px-3 bg-primary-50 hover:bg-primary-100 border border-primary-200 text-primary-700 text-xs font-bold rounded-[5px] transition-colors shadow-sm"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                                </svg>
                                                Editar
                                            </a>
                                            
                                            <!-- Excluir Pagamento -->
                                            <button 
                                                type="button" 
                                                @click="$dispatch('trigger-global-delete', { title: 'Excluir Pagamento', message: 'Tem certeza de que deseja excluir o pagamento de <strong class=\'text-slate-800\'>R$ {{ number_format($p['amount'], 2, ',', '.') }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Esta ação removerá permanentemente este registro de pagamento do caixa e a transação correspondente no dashboard.</span>', action: '{{ $p['destroy_url'] }}', highSecurity: false })"
                                                class="flex items-center gap-1.5 py-1.5 px-3 bg-red-50 hover:bg-red-100 border border-red-150 text-red-600 text-xs font-bold rounded-[5px] transition-colors shadow-sm"
                                            >
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                                Excluir Pagamento
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Footer Slider controls (Only if multiple slides exist) -->
                            @if($paymentCount > 1)
                                <div class="flex items-center justify-between border-t border-slate-150 pt-4 mt-2 gap-4">
                                    
                                    <!-- Indicator -->
                                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                                        Pagamento <span x-text="currentSlide + 1" class="text-slate-700"></span> de <span x-text="totalSlides" class="text-slate-700"></span>
                                    </div>

                                    <!-- Range Slider -->
                                    <div class="flex-1 max-w-[120px] sm:max-w-[160px] flex items-center">
                                        <input 
                                            type="range" 
                                            min="0" 
                                            :max="totalSlides - 1" 
                                            x-model.number="currentSlide" 
                                            class="w-full h-1 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-primary-600 focus:outline-none focus:ring-0"
                                        />
                                    </div>

                                    <!-- Arrow controls -->
                                    <div class="flex items-center gap-1">
                                        <button 
                                            type="button" 
                                            @click="currentSlide = (currentSlide - 1 + totalSlides) % totalSlides" 
                                            class="p-1 border border-slate-200 hover:bg-slate-50 text-slate-650 rounded-[5px] shadow-sm transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                                            </svg>
                                        </button>
                                        <button 
                                            type="button" 
                                            @click="currentSlide = (currentSlide + 1) % totalSlides" 
                                            class="p-1 border border-slate-200 hover:bg-slate-50 text-slate-650 rounded-[5px] shadow-sm transition-colors"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                            </svg>
                                        </button>
                                    </div>

                                </div>
                            @endif

                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] text-sm font-medium bg-white shadow-sm">
                Nenhum pagamento registrado neste mês.
            </div>
        @endif
    </div>

    <!-- Botão Flutuante (FAB) para Registrar Pagamento -->
    <a href="{{ route('payments.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-emerald-600 hover:bg-emerald-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-emerald-500/30 no-print" title="Registrar Pagamento">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>
@endsection
