@extends('layouts.app')

@section('title', 'Extrato da Conta - Gestor de Freelas')
@section('page_title', 'Extrato da Conta')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('bank-accounts.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Carteira
        </a>
    </div>

    <!-- Header do Banco Premium -->
    <div class="w-full rounded-[10px] bg-gradient-to-br {{ $bankAccount->brand_style['bg'] }} {{ $bankAccount->brand_style['text'] }} p-6 shadow-md flex flex-col md:flex-row md:items-center md:justify-between gap-6 relative overflow-hidden">
        <div class="absolute top-6 right-6 font-mono font-black text-2xl opacity-20 uppercase tracking-widest">{{ $bankAccount->brand_style['icon'] }}</div>
        
        <div class="space-y-2 z-10">
            <span class="text-[10px] font-extrabold uppercase px-2.5 py-0.5 rounded-[4px] {{ $bankAccount->brand_style['badge'] }}">
                @switch($bankAccount->account_type)
                    @case('corrente') Corrente @break
                    @case('poupanca') Poupança @break
                    @case('digital') Digital @break
                    @case('investimento') Investimento @break
                    @default Outros
                @endswitch
                ({{ $bankAccount->person_type }})
            </span>
            <h2 class="text-xl font-extrabold tracking-tight">{{ $bankAccount->account_name }}</h2>
            <p class="text-xs font-mono font-bold tracking-wider opacity-90">
                Banco: {{ $bankAccount->bank_name }} • Ag: {{ $bankAccount->agency ?? '---' }} • Cc: {{ $bankAccount->account_number ?? '---' }}
            </p>
        </div>

        <div class="z-10 bg-white/10 backdrop-blur-xs border border-white/20 p-4 rounded-[5px] flex flex-col shrink-0">
            <span class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Saldo Atual</span>
            <span class="text-2xl font-black mt-0.5 tracking-tight {{ $bankAccount->brand_style['accent'] }}">
                R$ {{ number_format($currentBalance, 2, ',', '.') }}
            </span>
            <span class="text-[9px] opacity-75 font-semibold mt-1">Apenas transações pagas</span>
        </div>
    </div>

    <!-- Ajuste Manual de Saldo -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4 no-print" x-data="{ open: false, balanceVal: '{{ number_format($currentBalance, 2, ',', '.') }}' }">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-xs font-black text-slate-800 uppercase tracking-wider">Ajuste de Saldo Rápido</h3>
                <p class="text-xs text-slate-400 font-bold mt-0.5">Corrige o saldo total alterando o saldo inicial da conta.</p>
            </div>
            <button type="button" @click="open = !open" class="px-3 py-1.5 border border-slate-200 hover:bg-slate-50 text-[10px] text-slate-500 font-bold rounded-[5px] uppercase tracking-wider">
                Ajustar Saldo
            </button>
        </div>

        <div x-show="open" class="border-t border-slate-100 pt-4" x-cloak>
            <form action="{{ route('bank-accounts.update-balance', $bankAccount->id) }}" method="POST" class="flex flex-col sm:flex-row items-end gap-3 max-w-md">
                @csrf
                @method('PUT')
                <div class="space-y-1 flex-1">
                    <label for="initial_balance" class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Qual o saldo real desta conta hoje?</label>
                    <input 
                        type="text" 
                        name="initial_balance" 
                        id="initial_balance" 
                        required 
                        x-model="balanceVal"
                        @input="balanceVal = formatMoney($event.target.value)"
                        class="w-full px-3 py-2 rounded-[5px] border border-slate-200 text-sm font-bold text-slate-800 focus:outline-none"
                    />
                </div>
                <button type="submit" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm focus:ring-4 focus:ring-primary-500/20 shrink-0">
                    Ajustar Saldo
                </button>
            </form>
        </div>
    </div>

    <!-- Extrato Cronológico -->
    <div class="space-y-3">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Linha do Tempo de Lançamentos (Extrato)</h3>

        @if(count($transactions) > 0)
            <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden divide-y divide-slate-100">
                @foreach($transactions as $t)
                    @php
                        $isIncome = $t->type === 'entrada';
                    @endphp
                    <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="w-10 h-10 rounded-[5px] bg-slate-50 border border-slate-100 flex items-center justify-center text-xl shrink-0">
                                {{ $t->category->icon ?? '💰' }}
                            </span>
                            <div class="min-w-0">
                                <h4 class="font-bold text-sm text-slate-800 truncate">{{ $t->description }}</h4>
                                <div class="flex items-center gap-1.5 text-[10px] text-slate-400 font-bold mt-0.5">
                                    <span>{{ $t->due_date->format('d/m/Y') }}</span>
                                    <span>•</span>
                                    <span class="uppercase tracking-wider">{{ $t->category->name ?? 'Outros' }}</span>
                                    <span>•</span>
                                    <span class="uppercase tracking-wider px-1 bg-slate-100 rounded-sm">{{ $t->classification }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 shrink-0">
                            <!-- Valor e Status -->
                            <div class="text-right">
                                <span class="font-black text-sm block {{ $isIncome ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isIncome ? '＋' : '－' }} R$ {{ number_format($t->amount, 2, ',', '.') }}
                                </span>
                                <span class="inline-block text-[8px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-[3px] mt-0.5 border
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
                Nenhum lançamento registrado nesta conta ainda.
            </div>
        @endif
    </div>

</div>

<script>
    function formatMoney(value) {
        if (!value) return 'R$ 0,00';
        let clean = value.replace(/\D/g, '');
        let number = (parseFloat(clean) / 100).toFixed(2);
        let parts = number.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        return 'R$ ' + parts.join(',');
    }
</script>
@endsection
