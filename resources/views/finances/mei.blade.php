@extends('layouts.app')

@section('title', 'Controle de Faturamento Mensal & Impostos - Gestor de Freelas')
@section('page_title', 'Faturamento & Impostos')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="meiManager()">

    <!-- Floating Preview Modal -->
    <div x-show="showPreviewModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-3xl w-full mx-4 overflow-hidden flex flex-col h-[85vh] relative"
             @click.away="showPreviewModal = false">
            
            <!-- Modal Header -->
            <div class="p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50 dark:bg-slate-900/50">
                <div class="min-w-0">
                    <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight truncate" x-text="previewDocName"></h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Visualização de Comprovante</p>
                </div>
                <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-black text-sm p-1.5 shrink-0 cursor-pointer">✕</button>
            </div>

            <!-- Modal Body (Preview container) -->
            <div class="flex-1 overflow-auto bg-slate-100 dark:bg-slate-950 p-4 flex items-center justify-center min-h-0">
                <!-- If Image -->
                <template x-if="previewDocType === 'image'">
                    <img :src="previewDocUrl" class="max-w-full max-h-full object-contain rounded border border-slate-200 dark:border-slate-800 shadow-sm" />
                </template>
                
                <!-- If PDF -->
                <template x-if="previewDocType === 'pdf'">
                    <iframe :src="previewDocUrl" class="w-full h-full rounded border border-slate-200 dark:border-slate-800" frameborder="0"></iframe>
                </template>

                <!-- If Other -->
                <template x-if="previewDocType === 'other'">
                    <div class="text-center space-y-3">
                        <span class="text-5xl block">📎</span>
                        <h4 class="text-slate-800 dark:text-slate-200 font-bold text-sm">Este arquivo não suporta preview direto</h4>
                        <p class="text-xs text-slate-400 max-w-xs">Formatos não visuais podem ser baixados diretamente para o seu dispositivo.</p>
                        <a :href="previewDownloadUrl" class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[5px] shadow transition-colors uppercase tracking-wider">
                            Baixar Arquivo
                        </a>
                    </div>
                </template>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-2 shrink-0 bg-slate-50 dark:bg-slate-900/50">
                <button type="button" @click="showPreviewModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-350 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-[5px] uppercase tracking-wider">Fechar</button>
                <a :href="previewDownloadUrl" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[5px] shadow transition-colors uppercase tracking-wider flex items-center gap-1">
                    Baixar
                </a>
            </div>
        </div>
    </div>

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('finances.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-300 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para o Controle Financeiro
        </a>
    </div>

    <!-- Título Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 font-outfit">Faturamento Mensal & Impostos</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium mt-1">Monitore suas receitas e despesas por pessoa física e jurídica e organize comprovantes para o Imposto de Renda.</p>
        </div>
        
        <!-- Navegador Anual -->
        <div x-data="{ open: false }" class="relative inline-block text-left no-print">
            <button @click="open = !open" type="button" class="inline-flex items-center justify-between gap-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 text-sm font-semibold rounded-[5px] px-3.5 py-2 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors min-w-[110px]">
                <span>Ano {{ $year }}</span>
                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 rounded-[5px] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-md z-30 py-1 text-sm max-h-60 overflow-y-auto" x-cloak>
                @for($y = date('Y') - 4; $y <= date('Y') + 4; $y++)
                    <a href="{{ route('finances.mei', ['year' => $y]) }}" class="block px-4 py-2 text-slate-750 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold {{ $year == $y ? 'bg-slate-50 dark:bg-slate-800 font-black text-primary-600 dark:text-primary-400' : '' }}">Ano {{ $y }}</a>
                @endfor
            </div>
        </div>
    </div>

    <!-- Gráfico de Evolução Mensal -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm">
        <h3 class="text-xs font-black text-slate-850 dark:text-slate-100 uppercase tracking-wider mb-4">Evolução Mensal de Faturamento & Despesas</h3>
        <div class="relative w-full h-[280px]">
            <canvas id="monthlyEvolutionChart"></canvas>
        </div>
    </div>

    <!-- Termômetro de Faturamento MEI (Apenas PJ) -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-wider">Termômetro de Faturamento MEI (Pessoa Jurídica)</h3>
                <p class="text-xs text-slate-400 font-bold mt-0.5">Controlador de teto anual base: {{ $year }}</p>
            </div>
            
            <!-- Limite MEI Ajustável -->
            <div class="flex items-center gap-2 no-print" x-data="{ editing: false, limitVal: '{{ number_format($meiLimit, 2, ',', '.') }}' }">
                <span class="text-xs text-slate-450 dark:text-slate-400 font-bold">Limite MEI:</span>
                <template x-if="!editing">
                    <div class="flex items-center gap-1.5">
                        <strong class="text-slate-800 dark:text-slate-200 text-sm">R$ {{ number_format($meiLimit, 2, ',', '.') }}</strong>
                        <button type="button" @click="editing = true" class="text-primary-600 dark:text-primary-400 hover:text-primary-700 font-bold text-xs bg-transparent border-0 p-0 shadow-none cursor-pointer">✏️</button>
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
                            class="w-28 px-2 py-1 rounded-[5px] border border-slate-200 dark:border-slate-850 text-xs font-bold text-slate-700 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none"
                        />
                        <button type="submit" class="px-2 py-1 bg-emerald-600 text-white font-bold text-xs rounded-[5px]">Salvar</button>
                        <button type="button" @click="editing = false" class="px-2 py-1 bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs rounded-[5px]">X</button>
                    </form>
                </template>
            </div>
        </div>

        <!-- Barra do Termômetro -->
        <div class="space-y-2">
            <div class="w-full bg-slate-100 dark:bg-slate-950 rounded-full h-5 relative overflow-hidden border border-slate-200 dark:border-slate-800 shadow-inner">
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
            
            <div class="flex items-center justify-between text-xs font-mono font-bold text-slate-455 dark:text-slate-400 mt-1">
                <span>R$ 0,00</span>
                <span class="text-slate-700 dark:text-slate-300">Faturado PJ: R$ {{ number_format($annualPjFaturamento, 2, ',', '.') }}</span>
                <span>R$ {{ number_format($meiLimit, 2, ',', '.') }}</span>
            </div>
        </div>

        @if($annualPjFaturamento > $meiLimit)
            <div class="p-3 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 text-red-800 dark:text-red-300 text-xs font-bold rounded-[5px] mt-2 leading-relaxed">
                ⚠️ Atenção: Seu faturamento PJ ultrapassou o limite anual configurado do MEI. Providencie junto ao seu contador o desenquadramento ou analise as receitas do período.
            </div>
        @endif
    </div>

    <!-- Cards de Resumo Consolidado Anual PJ vs PF -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Bloco Pessoa Jurídica (PJ) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
                <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">🏢 Pessoa Jurídica (PJ)</h4>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Faturamento PJ:</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-450">R$ {{ number_format($annualPjFaturamento, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Despesas PJ:</span>
                    <span class="font-extrabold text-rose-600 dark:text-rose-455">R$ {{ number_format($annualPjExpenses, 2, ',', '.') }}</span>
                </div>
                <hr class="border-slate-100 dark:border-slate-850" />
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-700 dark:text-slate-300 font-extrabold">Lucro Líquido PJ:</span>
                    <span class="font-black {{ ($annualPjFaturamento - $annualPjExpenses) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        R$ {{ number_format($annualPjFaturamento - $annualPjExpenses, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bloco Pessoa Física (PF) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
                <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">👤 Pessoa Física (PF)</h4>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Receitas PF:</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-450">R$ {{ number_format($annualPfFaturamento, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Despesas PF:</span>
                    <span class="font-extrabold text-rose-600 dark:text-rose-455">R$ {{ number_format($annualPfExpenses, 2, ',', '.') }}</span>
                </div>
                <hr class="border-slate-100 dark:border-slate-850" />
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-700 dark:text-slate-300 font-extrabold">Lucro Líquido PF:</span>
                    <span class="font-black {{ ($annualPfFaturamento - $annualPfExpenses) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        R$ {{ number_format($annualPfFaturamento - $annualPfExpenses, 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Bloco Consolidado -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm space-y-4">
            <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
                <h4 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">📊 Total Consolidado (Geral)</h4>
            </div>
            <div class="space-y-2">
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Total Recebido:</span>
                    <span class="font-extrabold text-emerald-600 dark:text-emerald-450">R$ {{ number_format($annualPjFaturamento + $annualPfFaturamento, 2, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center text-xs">
                    <span class="text-slate-500 dark:text-slate-450 font-bold">Total Despesas:</span>
                    <span class="font-extrabold text-rose-600 dark:text-rose-455">R$ {{ number_format($annualPjExpenses + $annualPfExpenses, 2, ',', '.') }}</span>
                </div>
                <hr class="border-slate-100 dark:border-slate-850" />
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-700 dark:text-slate-300 font-extrabold">Resultado Geral:</span>
                    <span class="font-black {{ (($annualPjFaturamento + $annualPfFaturamento) - ($annualPjExpenses + $annualPfExpenses)) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        R$ {{ number_format(($annualPjFaturamento + $annualPfFaturamento) - ($annualPjExpenses + $annualPfExpenses), 2, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Acordeão Mensal (Arquivos & Notas) -->
    <div class="space-y-4">
        <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider border-b border-slate-100 dark:border-slate-850 pb-2">Consolidação Mensal</h3>
        
        <div class="space-y-2">
            @foreach($monthsData as $mNum => $m)
                <div 
                    x-data="{ open: false }" 
                    class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] overflow-hidden shadow-sm"
                >
                    <!-- Header do Mês -->
                    <button 
                        type="button" 
                        @click="open = !open" 
                        class="w-full px-5 py-4 flex flex-col md:flex-row md:items-center justify-between text-left hover:bg-slate-50 dark:hover:bg-slate-800/50 transition-colors gap-3"
                    >
                        <div class="flex items-center gap-4 flex-1">
                            <!-- Nome do Mês -->
                            <div class="w-24 shrink-0">
                                <span class="font-extrabold text-sm text-slate-700 dark:text-slate-200 block">{{ $m['name'] }}</span>
                            </div>
                            
                            <!-- Valores Resumo PJ / PF -->
                            <div class="grid grid-cols-2 gap-2 sm:gap-4">
                                <!-- Coluna PJ -->
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">🏢 PJ:</span>
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($m['pj_incomes_sum'], 2, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-300">/</span>
                                    <span class="text-[11px] font-bold text-rose-600 dark:text-rose-455">R$ {{ number_format($m['pj_expenses_sum'], 2, ',', '.') }}</span>
                                </div>
                                <!-- Coluna PF -->
                                <div class="flex items-center gap-1">
                                    <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase">👤 PF:</span>
                                    <span class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($m['pf_incomes_sum'], 2, ',', '.') }}</span>
                                    <span class="text-[10px] text-slate-300">/</span>
                                    <span class="text-[11px] font-bold text-rose-600 dark:text-rose-455">R$ {{ number_format($m['pf_expenses_sum'], 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 justify-between md:justify-end shrink-0 w-full md:w-auto">
                            @if(count($m['attachments']) > 0)
                                <span class="bg-blue-50 dark:bg-blue-950/40 text-blue-800 dark:text-blue-300 text-[10px] font-bold px-2 py-0.5 rounded-[5px] border border-blue-100 dark:border-blue-900/60">
                                    📎 {{ count($m['attachments']) }} Documentos
                                </span>
                            @endif
                            <svg class="w-4 h-4 text-slate-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </button>

                    <!-- Conteúdo Expandido -->
                    <div x-show="open" class="border-t border-slate-150 dark:border-slate-800 p-5 bg-slate-50/50 dark:bg-slate-950/20 space-y-4" x-collapse x-cloak>
                        
                        <!-- Resumo Mensal Detalhado -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 border-b border-slate-100 dark:border-slate-850 pb-4">
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Faturamento PJ Mensal</span>
                                <strong class="text-sm font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($m['pj_incomes_sum'], 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Despesas PJ Mensais</span>
                                <strong class="text-sm font-bold text-rose-600 dark:text-rose-455">R$ {{ number_format($m['pj_expenses_sum'], 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Resultado PJ do Mês</span>
                                <strong class="text-sm font-bold {{ ($m['pj_incomes_sum'] - $m['pj_expenses_sum']) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">R$ {{ number_format($m['pj_incomes_sum'] - $m['pj_expenses_sum'], 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Receitas PF Mensais</span>
                                <strong class="text-sm font-bold text-emerald-600 dark:text-emerald-400">R$ {{ number_format($m['pf_incomes_sum'], 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Despesas PF Mensais</span>
                                <strong class="text-sm font-bold text-rose-600 dark:text-rose-455">R$ {{ number_format($m['pf_expenses_sum'], 2, ',', '.') }}</strong>
                            </div>
                            <div>
                                <span class="text-[9px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider block">Resultado PF do Mês</span>
                                <strong class="text-sm font-bold {{ ($m['pf_incomes_sum'] - $m['pf_expenses_sum']) >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">R$ {{ number_format($m['pf_incomes_sum'] - $m['pf_expenses_sum'], 2, ',', '.') }}</strong>
                            </div>
                        </div>

                        <!-- Lista de Arquivos Notas Fiscais -->
                        <div class="space-y-2">
                            <h4 class="text-[11px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider block">Notas Fiscais & Recibos do Mês</h4>
                            
                            @if(count($m['attachments']) > 0)
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    @foreach($m['attachments'] as $doc)
                                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-850 p-3 rounded-[5px] flex items-center justify-between gap-3 shadow-xs">
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded {{ $doc['classification'] === 'PJ' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                        {{ $doc['classification'] }}
                                                    </span>
                                                    <h5 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate" title="{{ $doc['description'] }}">{{ $doc['description'] }}</h5>
                                                </div>
                                                <p class="text-[10px] text-slate-400 font-bold block mt-1">
                                                    {{ $doc['date'] }} • 
                                                    <span class="{{ $doc['type'] === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-455' }}">
                                                        R$ {{ number_format($doc['amount'], 2, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>
                                            
                                            <div class="flex items-center gap-1.5 shrink-0">
                                                <button 
                                                    type="button"
                                                    @click="openPreview({{ json_encode($doc) }})"
                                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] rounded-[5px] transition-colors shadow-sm shrink-0 uppercase tracking-wider flex items-center gap-1 cursor-pointer"
                                                >
                                                    👁️ Ver
                                                </button>
                                                <a 
                                                    href="{{ $doc['download_url'] }}" 
                                                    class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-[5px] transition-colors shadow-sm shrink-0 flex items-center gap-1 uppercase tracking-wider"
                                                >
                                                    Baixar
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-xs text-slate-455 border border-dashed border-slate-200 dark:border-slate-800 p-4 rounded-[5px] text-center bg-white dark:bg-slate-900 text-slate-400">
                                    Nenhum comprovante ou nota fiscal anexada neste mês.
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Declaração Anual MEI & Carne Leão -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4">
        <div class="border-b border-slate-100 dark:border-slate-800 pb-2">
            <h3 class="text-sm font-black text-slate-800 dark:text-slate-100 uppercase tracking-wider">📋 Assistente de Declaração de Impostos (Ano Base: {{ $year }})</h3>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-xs leading-relaxed">
            <!-- MEI - DASN-SIMEI -->
            <div class="space-y-3 bg-slate-50 dark:bg-slate-950/40 p-4 rounded-[5px] border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-1.5 font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">
                    <span>🏢 Declaração Anual MEI (DASN-SIMEI)</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400">Na declaração anual do MEI, você deve declarar a receita bruta de serviços recebidos da sua empresa:</p>
                <div class="p-3 bg-white dark:bg-slate-900 rounded border border-slate-100 dark:border-slate-850 space-y-1">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Receita de Serviços (PJ):</span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-bold">R$ {{ number_format($annualPjFaturamento, 2, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Receita de Comércio:</span>
                        <strong class="text-slate-600 dark:text-slate-300 font-bold">R$ 0,00</strong>
                    </div>
                </div>
                <div class="text-[10px] text-slate-405">
                    💡 <em>Nota: Esse valor deve ser copiado diretamente para o campo "Prestação de Serviços" no portal do Simples Nacional.</em>
                </div>
            </div>

            <!-- PF - Carne-Leão e IRPF -->
            <div class="space-y-3 bg-slate-50 dark:bg-slate-950/40 p-4 rounded-[5px] border border-slate-100 dark:border-slate-800">
                <div class="flex items-center gap-1.5 font-extrabold text-slate-800 dark:text-slate-200 uppercase tracking-wider text-[11px]">
                    <span>👤 Imposto de Renda Pessoa Física (IRPF)</span>
                </div>
                <p class="text-slate-500 dark:text-slate-400">Valores recebidos diretamente de pessoas físicas (sem CNPJ), que devem ser reportados no Carnê-Leão / Declaração IRPF:</p>
                <div class="p-3 bg-white dark:bg-slate-900 rounded border border-slate-100 dark:border-slate-850 space-y-1">
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Receitas PF:</span>
                        <strong class="text-emerald-600 dark:text-emerald-400 font-bold">R$ {{ number_format($annualPfFaturamento, 2, ',', '.') }}</strong>
                    </div>
                    <div class="flex justify-between font-mono">
                        <span class="text-slate-500 dark:text-slate-400">Despesas PF (Dedutíveis):</span>
                        <strong class="text-rose-600 dark:text-rose-455 font-bold">R$ {{ number_format($annualPfExpenses, 2, ',', '.') }}</strong>
                    </div>
                </div>
                <div class="text-[10px] text-slate-405">
                    💡 <em>Nota: Use o livro-caixa no portal do e-CAC para lançar as despesas dedutíveis (como aluguel do escritório, internet, etc) e abater o cálculo do IRPF.</em>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('monthlyEvolutionChart').getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#1e293b' : '#f1f5f9';
        const labelColor = isDark ? '#94a3b8' : '#64748b';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [
                    {
                        label: 'Faturamento PJ',
                        data: @json($pjIncomesChart),
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Receitas PF',
                        data: @json($pfIncomesChart),
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Despesas Gerais',
                        data: @json($expensesChart),
                        borderColor: '#ef4444',
                        backgroundColor: 'rgba(239, 68, 68, 0.05)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            color: labelColor,
                            font: { weight: 'bold', size: 10 }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor, font: { weight: 'bold', size: 10 } }
                    },
                    y: {
                        grid: { color: gridColor },
                        ticks: { color: labelColor, font: { weight: 'bold', size: 10 } }
                    }
                }
            }
        });
    });

    function meiManager() {
        return {
            percent: {{ $percent }},
            showPreviewModal: false,
            previewDocUrl: '',
            previewDocName: '',
            previewDocType: '',
            previewDownloadUrl: '',
            
            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
            },

            openPreview(doc) {
                this.previewDocUrl = '/storage/' + doc.attachment_path;
                this.previewDownloadUrl = doc.download_url;
                this.previewDocName = doc.description;
                const ext = doc.filename.split('.').pop().toLowerCase();
                if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
                    this.previewDocType = 'image';
                } else if (ext === 'pdf') {
                    this.previewDocType = 'pdf';
                } else {
                    this.previewDocType = 'other';
                }
                this.showPreviewModal = true;
            }
        };
    }
</script>
@endsection
