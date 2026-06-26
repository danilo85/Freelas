@extends('layouts.app')

@section('title', 'Painel MEI & Faturamento - Gestor de Freelas')
@section('page_title', 'Painel MEI')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="meiManager()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('finances.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para o Controle Financeiro
        </a>
    </div>

    <!-- Título Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Faturamento Anual MEI</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Acompanhe seu teto de faturamento e console suas notas fiscais de forma simplificada.</p>
        </div>
        
        <!-- Navegador Anual -->
        <div x-data="{ open: false }" class="relative inline-block text-left no-print">
            <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-[5px] px-3.5 py-2 hover:bg-slate-50 transition-colors min-w-[110px]">
                <span>Ano {{ $year }}</span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 rounded-[5px] bg-white border border-slate-200 shadow-md z-30 py-1 text-sm max-h-60 overflow-y-auto" x-cloak>
                @for($y = date('Y') - 4; $y <= date('Y') + 4; $y++)
                    <a href="{{ route('finances.mei', ['year' => $y]) }}" class="block px-4 py-2 text-slate-750 hover:bg-slate-50 hover:text-slate-900 font-semibold {{ $year == $y ? 'bg-slate-50 font-black text-primary-600' : '' }}">Ano {{ $y }}</a>
                @endfor
            </div>
        </div>
    </div>

    <!-- Termômetro de Faturamento (ProgressBar) -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Termômetro de Limite Anual</h3>
                <p class="text-xs text-slate-400 font-bold mt-0.5">Ano base: {{ $year }}</p>
            </div>
            
            <!-- Limite MEI Ajustável -->
            <div class="flex items-center gap-2 no-print" x-data="{ editing: false, limitVal: '{{ number_format($meiLimit, 2, ',', '.') }}' }">
                <span class="text-xs text-slate-400 font-bold">Limite MEI:</span>
                <template x-if="!editing">
                    <div class="flex items-center gap-1.5">
                        <strong class="text-slate-800 text-sm">R$ {{ number_format($meiLimit, 2, ',', '.') }}</strong>
                        <button type="button" @click="editing = true" class="text-primary-600 hover:text-primary-700 font-bold text-xs bg-transparent border-0 p-0 shadow-none cursor-pointer">✏️</button>
                    </div>
                </template>
                <template x-if="editing">
                    <form action="{{ route('finances.mei.limit') }}" method="POST" class="flex items-center gap-1">
                        @csrf
                        <input 
                            type="text" 
                            name="mei_limit" 
                            x-model="limitVal"
                            @input="limitVal = formatMoney($event.target.value)"
                            class="w-28 px-2 py-1 rounded-[5px] border border-slate-200 text-xs font-bold text-slate-700 focus:outline-none"
                        />
                        <button type="submit" class="px-2 py-1 bg-emerald-600 text-white font-bold text-xs rounded-[5px]">Salvar</button>
                        <button type="button" @click="editing = false" class="px-2 py-1 bg-slate-200 text-slate-700 font-bold text-xs rounded-[5px]">X</button>
                    </form>
                </template>
            </div>
        </div>

        <!-- Barra do Termômetro -->
        <div class="space-y-2">
            <div class="w-full bg-slate-100 rounded-full h-5 relative overflow-hidden border border-slate-200 shadow-inner">
                <div 
                    class="h-full rounded-full transition-all duration-500 bg-gradient-to-r"
                    :class="percent > 90 ? 'from-rose-500 to-red-600' : (percent > 75 ? 'from-amber-400 to-orange-500' : 'from-emerald-500 to-emerald-600')"
                    style="width: {{ $percent }}%"
                >
                    @if($percent > 10)
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-[10px] font-black text-white uppercase tracking-wider drop-shadow-md">
                            {{ number_format($percent, 1, ',', '.') }}%
                        </span>
                    @endif
                </div>
            </div>
            
            <div class="flex items-center justify-between text-xs font-mono font-bold text-slate-450 mt-1">
                <span>R$ 0,00</span>
                <span class="text-slate-700">Faturado: R$ {{ number_format($annualFaturamento, 2, ',', '.') }}</span>
                <span>R$ {{ number_format($meiLimit, 2, ',', '.') }}</span>
            </div>
        </div>

        @if($annualFaturamento > $meiLimit)
            <div class="p-3 bg-red-50 border border-red-200 text-red-800 text-xs font-bold rounded-[5px] mt-2 leading-relaxed">
                ⚠️ Atenção: Seu faturamento ultrapassou o limite anual configurado do MEI. Providencie junto ao seu contador o desenquadramento ou analise as receitas do período.
            </div>
        @endif
    </div>

    <!-- Cards de Resumo Consolidado Anual PJ -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Faturamento PJ -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Faturamento PJ Acumulado</span>
            <h3 class="text-xl font-extrabold text-emerald-600 mt-2">
                R$ {{ number_format($annualFaturamento, 2, ',', '.') }}
            </h3>
        </div>
        <!-- Despesas PJ -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Despesas PJ Acumuladas</span>
            <h3 class="text-xl font-extrabold text-rose-600 mt-2">
                R$ {{ number_format($annualExpenses, 2, ',', '.') }}
            </h3>
        </div>
        <!-- Saldo Líquido PJ -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm">
            <span class="text-[10px] font-black text-slate-400 uppercase tracking-wider block">Lucro Líquido PJ Anual</span>
            <h3 class="text-xl font-extrabold mt-2 {{ ($annualFaturamento - $annualExpenses) >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                R$ {{ number_format($annualFaturamento - $annualExpenses, 2, ',', '.') }}
            </h3>
        </div>
    </div>

    <!-- Acordeão Mensal (Arquivos & Notas) -->
    <div class="space-y-4">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-wider border-b border-slate-100 pb-2">Consolidação Fiscal por Mês</h3>
        
        <div class="space-y-2">
            @foreach($monthsData as $mNum => $m)
                <div 
                    x-data="{ open: false }" 
                    class="bg-white border border-slate-200 rounded-[5px] overflow-hidden shadow-sm"
                >
                    <!-- Header do Mês -->
                    <button 
                        type="button" 
                        @click="open = !open" 
                        class="w-full px-5 py-4 flex items-center justify-between text-left hover:bg-slate-50 transition-colors"
                    >
                        <div class="flex items-center gap-4 flex-1">
                            <!-- Month Column (Fixed Width to align vertically across rows) -->
                            <div class="w-20 sm:w-28 shrink-0">
                                <span class="font-extrabold text-sm text-slate-700 block">{{ $m['name'] }}</span>
                            </div>
                            
                            <!-- Values Column (Aligned and formatted as tags) -->
                            <div class="flex items-center gap-2 sm:gap-4 flex-wrap">
                                <!-- Receitas Tag -->
                                <span class="inline-flex items-center justify-center bg-emerald-100/60 text-emerald-800 text-[10px] sm:text-xs font-black px-3 py-1.5 rounded-full tracking-wide min-w-[95px] sm:min-w-[120px] text-center">
                                    R$ {{ number_format($m['incomes_sum'], 2, ',', '.') }}
                                </span>
                                <!-- Despesas Tag -->
                                <span class="inline-flex items-center justify-center bg-rose-100/65 text-rose-800 text-[10px] sm:text-xs font-black px-3 py-1.5 rounded-full tracking-wide min-w-[95px] sm:min-w-[120px] text-center">
                                    R$ {{ number_format($m['expenses_sum'], 2, ',', '.') }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0">
                            @if(count($m['attachments']) > 0)
                                <span class="bg-blue-50 text-blue-800 text-[10px] font-bold px-2 py-0.5 rounded-[5px] border border-blue-100">
                                    📎 {{ count($m['attachments']) }} Documentos
                                </span>
                            @endif
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <!-- Conteúdo Expandido -->
                    <div x-show="open" class="border-t border-slate-150 p-5 bg-slate-50/50 space-y-4" x-collapse x-cloak>
                        
                        <!-- Lista de Arquivos Notas Fiscais -->
                        <div class="space-y-2">
                            <h4 class="text-[11px] font-black text-slate-400 uppercase tracking-wider block">Notas Fiscais & Recibos Consolidados</h4>
                            
                            @if(count($m['attachments']) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($m['attachments'] as $doc)
                                        <div class="bg-white border border-slate-200 p-3 rounded-[5px] flex items-center justify-between gap-3 shadow-xs">
                                            <div class="min-w-0">
                                                <h5 class="font-bold text-xs text-slate-800 truncate" title="{{ $doc['description'] }}">{{ $doc['description'] }}</h5>
                                                <p class="text-[10px] text-slate-400 font-bold block mt-0.5">
                                                    {{ $doc['date'] }} • 
                                                    <span class="{{ $doc['type'] === 'entrada' ? 'text-emerald-600' : 'text-rose-600' }}">
                                                        R$ {{ number_format($doc['amount'], 2, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>
                                            <a 
                                                href="{{ $doc['download_url'] }}" 
                                                class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-[5px] transition-colors shadow-sm shrink-0 flex items-center gap-1 uppercase tracking-wider"
                                            >
                                                Baixar
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-450 border border-dashed border-slate-200 p-4 rounded-[5px] text-center bg-white">
                                    Nenhum comprovante ou nota fiscal anexada neste mês.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

<script>
    function meiManager() {
        return {
            percent: {{ $percent }},
            
            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
            }
        };
    }
</script>
@endsection
