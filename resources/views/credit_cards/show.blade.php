@extends('layouts.app')

@section('title', 'Fatura do Cartão - Gestor de Freelas')
@section('page_title', 'Fatura do Cartão')

@section('content')
@php
    $hasUnpaidCurrentMonth = $transactions->filter(fn($t) => $t->type === 'saida' && $t->status !== 'pago' && $t->due_date->month === \Carbon\Carbon::now()->month && $t->due_date->year === \Carbon\Carbon::now()->year)->isNotEmpty();
    $hasAnyTransactionsCurrentMonth = $transactions->filter(fn($t) => $t->due_date->month === \Carbon\Carbon::now()->month && $t->due_date->year === \Carbon\Carbon::now()->year)->isNotEmpty();
@endphp
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('bank-accounts.index', ['tab' => 'cards']) }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Carteira
        </a>
    </div>

    <!-- Header do Cartão de Crédito Premium -->
    <div class="w-full rounded-[10px] bg-gradient-to-br {{ $creditCard->brand_style['bg'] }} {{ $creditCard->brand_style['text'] }} p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-6 right-6 font-mono font-black text-2xl opacity-20 uppercase tracking-widest">{{ $creditCard->brand_style['icon'] }}</div>
        
        <div class="space-y-2 z-10">
            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-[4px] {{ $creditCard->brand_style['flag_badge'] }}">
                {{ $creditCard->brand_style['flag_label'] }}
            </span>
            <h2 class="text-xl font-extrabold tracking-tight">{{ $creditCard->card_name }}</h2>
            <p class="text-xs font-mono font-bold tracking-wider opacity-90">
                Banco: {{ $creditCard->bank_name }} • Fechamento: Dia {{ $creditCard->closing_day }} • Vencimento: Dia {{ $creditCard->due_day }}
            </p>
        </div>

        <div class="z-10 bg-white/10 backdrop-blur-xs border border-white/20 p-4 rounded-[5px] flex flex-col shrink-0">
            <span class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Limite Disponível</span>
            <span class="text-2xl font-black mt-0.5 tracking-tight text-white">
                R$ {{ number_format($availableLimit, 2, ',', '.') }}
            </span>
            <span class="text-[9px] opacity-75 font-semibold mt-1">Limite Total: R$ {{ number_format($creditCard->limit, 2, ',', '.') }}</span>
        </div>

        <!-- Stamp Carimbo Fatura Paga -->
        @if(!$hasUnpaidCurrentMonth && $hasAnyTransactionsCurrentMonth)
            <div class="absolute inset-0 bg-emerald-950/20 backdrop-blur-[0.5px] flex items-center justify-center pointer-events-none select-none z-10">
                <div class="border-4 border-emerald-400 text-emerald-400 font-black text-lg md:text-xl px-4 py-2 rounded-[6px] uppercase tracking-widest -rotate-12 transform shadow-lg bg-emerald-950/15 backdrop-blur-xs select-none">
                    ✓ FATURA PAGA
                </div>
            </div>
        @endif
    </div>

    <!-- Resumo dos Gastos -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Limite Geral</span>
            <h3 class="text-lg font-black text-slate-800 mt-2">
                R$ {{ number_format($creditCard->limit, 2, ',', '.') }}
            </h3>
        </div>
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Limite Utilizado (Mês Atual)</span>
            <h3 class="text-lg font-black text-rose-600 mt-2">
                R$ {{ number_format($usedLimit, 2, ',', '.') }}
            </h3>
        </div>
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Limite Disponível</span>
            <h3 class="text-lg font-black text-emerald-600 mt-2">
                R$ {{ number_format($availableLimit, 2, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- Lista de Compras do Cartão -->
    <div class="space-y-3">
        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
            <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider">Gastos Registrados no Cartão (Fatura)</h3>
            
            @if($hasUnpaidCurrentMonth)
                <form action="{{ route('finances.pay-invoice', $creditCard->id) }}" method="POST" class="inline no-print">
                    @csrf
                    <input type="hidden" name="month" value="{{ \Carbon\Carbon::now()->month }}">
                    <input type="hidden" name="year" value="{{ \Carbon\Carbon::now()->year }}">
                    <button type="submit" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-extrabold rounded-[5px] transition-colors uppercase tracking-wider flex items-center gap-1 shadow-sm">
                        <span>✓</span> Pagar Fatura do Mês
                    </button>
                </form>
            @elseif($hasAnyTransactionsCurrentMonth)
                <span class="px-2 py-1 bg-emerald-50 border border-emerald-200 text-emerald-700 text-[9px] font-black rounded-[5px] uppercase tracking-wider no-print">
                    Fatura Paga ✓
                </span>
            @endif
        </div>

        @if(count($transactions) > 0)
            <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden divide-y divide-slate-100">
                @foreach($transactions as $t)
                    <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors relative overflow-hidden">
                        
                        <!-- Stamp Carimbo Pago/Pendente no Extrato (Apenas Desktop) -->
                        @if($t->status === 'pago')
                            <div class="absolute right-28 top-1/2 -translate-y-1/2 rotate-[-12deg] pointer-events-none select-none z-10 opacity-30 transform scale-90 hidden sm:block">
                                <div class="border-2 border-emerald-600/75 text-emerald-600/75 font-black text-[9px] px-2 py-0.5 rounded uppercase tracking-widest flex items-center gap-0.5">
                                    <span>✓</span> <span>PAGO</span>
                                </div>
                            </div>
                        @else
                            <div class="absolute right-28 top-1/2 -translate-y-1/2 rotate-[-12deg] pointer-events-none select-none z-10 opacity-25 transform scale-90 hidden sm:block">
                                <div class="border-2 border-amber-600/75 text-amber-600/75 font-black text-[8px] px-1.5 py-0.5 rounded uppercase tracking-widest flex items-center gap-0.5">
                                    <span>⏳</span> <span>PENDENTE</span>
                                </div>
                            </div>
                        @endif

                        <div class="flex items-center gap-3 min-w-0 z-20">
                            <span class="w-10 h-10 rounded-[5px] bg-slate-50 border border-slate-100 flex items-center justify-center text-xl shrink-0">
                                {{ $t->category->icon ?? '💳' }}
                            </span>
                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-sm text-slate-800 truncate" title="{{ $t->description }}">{{ $t->description }}</h4>
                                <div class="flex flex-wrap items-center gap-1.5 text-[10px] text-slate-400 font-bold mt-0.5">
                                    <span>{{ $t->due_date->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span class="uppercase tracking-wider">{{ $t->category->name ?? 'Outros' }}</span>
                                    <span>•</span>
                                    <span class="uppercase tracking-wider px-1 bg-slate-100 rounded-sm">{{ $t->classification }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between sm:justify-end gap-6 shrink-0 w-full sm:w-auto border-t border-slate-100 sm:border-t-0 pt-2.5 sm:pt-0 z-20">
                            <!-- Valor e Status -->
                            <div class="text-left sm:text-right">
                                <span class="font-black text-sm block text-rose-600">
                                    － R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                                <!-- Status Badge (Sempre visível no mobile, oculto no desktop) -->
                                <span class="inline-block sm:hidden text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-[3px] mt-0.5 border
                                    {{ $t->status === 'pago' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' : 'bg-amber-50 text-amber-700 border-amber-100' }}">
                                    {{ $t->status === 'pago' ? 'Pago' : 'Pendente' }}
                                </span>
                            </div>

                            <!-- Ações Rápidas no extrato -->
                            <div class="flex items-center gap-1.5 no-print">
                                <form action="{{ route('finances.toggle-status', $t->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="p-1 border border-slate-200 hover:bg-slate-50 text-slate-400 hover:text-emerald-600 rounded-[5px] shadow-xs text-xs font-bold transition-all" title="Pagar/Pendente">
                                        ✓
                                    </button>
                                </form>
                                <a href="{{ route('finances.edit', $t->id) }}" class="p-1 border border-slate-200 hover:bg-slate-50 text-slate-400 hover:text-primary-600 rounded-[5px] shadow-xs transition-all" title="Editar">
                                    ✏️
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="border border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] text-sm font-medium bg-white shadow-sm">
                Nenhuma compra registrada nesta fatura ainda.
            </div>
        @endif
    </div>

</div>
@endsection
