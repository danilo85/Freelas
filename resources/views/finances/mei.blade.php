@extends('layouts.app')

@section('title', 'Controle de Faturamento Mensal & Impostos - Gestor de Freelas')
@section('page_title', 'Faturamento & Impostos')

@section('content')
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
<div class="max-w-5xl mx-auto space-y-6" x-data="meiManager()" id="mei-content-wrapper">

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

    <!-- Deletion Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999; margin: 0 !important;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-sm w-full p-6 text-center space-y-4 select-none relative"
             @click.away="showDeleteModal = false">
            <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-950 text-red-600 dark:text-red-400 flex items-center justify-center text-xl mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight">Excluir Nota / Recibo?</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block" x-text="deleteFileName"></p>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Tem certeza de que deseja deletar e desvincular este anexo de todos os trabalhos relacionados? Esta ação é definitiva.
            </p>
            <div class="flex justify-center gap-2 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 border border-slate-200 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold rounded-[5px] uppercase tracking-wider cursor-pointer">
                    Cancelar
                </button>
                <form action="{{ route('finances.mei.delete-invoice') }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="transaction_id" :value="deleteTransId">
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-[5px] shadow-sm uppercase tracking-wider cursor-pointer">
                        Sim, Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Replace Invoice Modal -->
    <div x-show="showReplaceModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999; margin: 0 !important;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-md w-full p-6 space-y-4 select-none relative"
             @click.away="showReplaceModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <div>
                    <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight">Substituir Nota / Recibo</h3>
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5" x-text="'Substituindo: ' + replaceFileName"></p>
                </div>
                <button type="button" @click="showReplaceModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-black text-sm p-1 cursor-pointer">✕</button>
            </div>
            
            <form action="{{ route('finances.mei.replace-invoice') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="transaction_id" :value="replaceTransId">
                
                <p class="text-xs text-slate-500 leading-relaxed">
                    O novo arquivo enviado substituirá o arquivo atual em <strong>todos os trabalhos contemplados</strong> por este mesmo comprovante de faturamento.
                </p>
                
                <div class="space-y-1.5">
                    <label class="text-[10px] font-bold text-slate-455 uppercase tracking-wider block font-outfit">Selecione o Novo Arquivo:</label>
                    <input type="file" name="invoice" required class="w-full text-xs text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] px-3 py-2.5 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="showReplaceModal = false" class="px-4 py-2 border border-slate-200 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold rounded-[5px] uppercase tracking-wider cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-[5px] shadow-sm uppercase tracking-wider cursor-pointer">
                        Salvar e Substituir
                    </button>
                </div>
            </form>
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
            <canvas id="monthlyEvolutionChart" data-pj="{{ json_encode($pjIncomesChart) }}" data-pf="{{ json_encode($pfIncomesChart) }}" data-expenses="{{ json_encode($expensesChart) }}"></canvas>
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

    <!-- Card 3: Consolidação Mensal (Calendário e Documentos) -->
    <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-850 pb-2">
                    <h3 class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Consolidação Mensal</h3>
                    <a 
                        href="{{ route('finances.mei.export-csv', ['month' => $month, 'year' => $year]) }}" 
                        class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 dark:hover:bg-slate-655 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider w-full sm:w-auto"
                        title="Exportar lançamentos do mês em formato CSV"
                    >
                        📥 Exportar CSV
                    </a>
                </div>

                <!-- Month Navigator identical to finances index page -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-3 shadow-sm flex flex-col md:flex-row items-center justify-between gap-3 no-print">
                    <div class="flex items-center justify-between w-full md:w-auto gap-2 sm:gap-3">
                        <!-- Anterior -->
                        <button type="button" @click="navigateTo({{ $prevMonth->month }}, {{ $prevMonth->year }})" 
                           class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 border border-slate-200 dark:border-slate-800 hover:border-slate-350 dark:hover:border-slate-700 text-slate-650 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50 text-xs font-bold rounded-[5px] transition-all shadow-sm shrink-0 uppercase tracking-wider"
                           title="Mês Anterior">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            <span class="hidden sm:inline">Anterior</span>
                        </button>

                        <!-- Mês / Ano Selecionado -->
                        <div class="relative flex items-center justify-center gap-2 text-xs sm:text-sm font-extrabold text-slate-850 dark:text-slate-200 tracking-wider uppercase bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 px-3 sm:px-6 py-2 rounded-[5px] shadow-inner text-center font-outfit min-w-[120px] sm:min-w-[210px] select-none flex-1 md:flex-none"
                             x-data="{ showPicker: false, pickerMonth: {{ $month }}, pickerYear: {{ $year }} }">
                            <span>{{ $months[$month] }} {{ $year }}</span>
                            
                            <!-- Ícone para ir direto -->
                            <button type="button" @click="showPicker = !showPicker" class="text-slate-450 hover:text-slate-750 dark:hover:text-slate-200 transition-colors p-1" title="Ir para Mês/Ano Específico">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </button>

                            <!-- Painel do Seletor -->
                            <div x-show="showPicker" @click.away="showPicker = false" class="absolute top-full left-1/2 -translate-x-1/2 mt-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl rounded-[5px] p-3 z-50 text-slate-800 dark:text-slate-200 w-64 space-y-3" x-cloak x-transition>
                                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block text-left">Escolha o Período</span>
                                <div class="grid grid-cols-2 gap-2">
                                    <select x-model="pickerMonth" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold px-2 py-1.5 rounded-[5px] focus:outline-none focus:ring-1 focus:ring-primary-500 text-slate-850 dark:text-slate-200">
                                        @foreach($months as $mNum => $mName)
                                            <option value="{{ $mNum }}" {{ $month == $mNum ? 'selected' : '' }}>{{ $mName }}</option>
                                        @endforeach
                                    </select>
                                    <select x-model="pickerYear" class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-semibold px-2 py-1.5 rounded-[5px] focus:outline-none focus:ring-1 focus:ring-primary-500 text-slate-850 dark:text-slate-200">
                                        @for($y = date('Y') - 4; $y <= date('Y') + 4; $y++)
                                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="button" @click="navigateTo(pickerMonth, pickerYear); showPicker = false;" class="w-full py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider">
                                    Ir para o Período
                                </button>
                            </div>
                        </div>

                        <!-- Próximo -->
                        <button type="button" @click="navigateTo({{ $nextMonth->month }}, {{ $nextMonth->year }})" 
                           class="inline-flex items-center justify-center gap-1.5 px-2.5 sm:px-3.5 py-2 border border-slate-200 dark:border-slate-800 hover:border-slate-355 dark:hover:border-slate-700 text-slate-650 dark:text-slate-300 hover:text-slate-800 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800/50 text-xs font-bold rounded-[5px] transition-all shadow-sm shrink-0 uppercase tracking-wider"
                           title="Próximo Mês">
                            <span class="hidden sm:inline">Próximo</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </button>
                    </div>
                    
                    <!-- Botão Hoje -->
                    <button type="button" @click="navigateTo({{ $today->month }}, {{ $today->year }})" 
                       class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider w-full md:w-auto text-center">
                        Hoje
                    </button>
                </div>

                <!-- Details Card for the Selected Month -->
                @php
                    $m = $monthsData[$month];
                @endphp
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] shadow-sm p-5 space-y-4">
                    
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
                                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-855 p-3.5 rounded-[5px] flex flex-col justify-between gap-3 shadow-xs">
                                        <!-- Top Row: Badges, Title, Date and Action Buttons -->
                                        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-3 w-full border-b border-slate-100 dark:border-slate-800/60 pb-3">
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-[9px] font-black uppercase px-1.5 py-0.5 rounded {{ $doc['classification'] === 'PJ' ? 'bg-primary-50 text-primary-700 dark:bg-primary-950/40 dark:text-primary-300' : 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-300' }}">
                                                        {{ $doc['classification'] }}
                                                    </span>
                                                    <h5 class="font-extrabold text-xs text-slate-850 dark:text-slate-200 truncate max-w-[280px] sm:max-w-xs" title="{{ $doc['filename'] }}">{{ $doc['filename'] }}</h5>
                                                </div>
                                                <p class="text-[10px] text-slate-400 font-bold block mt-1">
                                                    {{ $doc['date'] }} • Total: 
                                                    <span class="{{ $doc['type'] === 'entrada' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-455' }}">
                                                        R$ {{ number_format($doc['amount'], 2, ',', '.') }}
                                                    </span>
                                                </p>
                                            </div>

                                            <div class="flex items-center gap-1.5 shrink-0 flex-wrap lg:flex-nowrap">
                                                <button 
                                                    type="button"
                                                    @click="openPreview({{ json_encode($doc) }})"
                                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] rounded-[5px] transition-colors shadow-xs shrink-0 uppercase tracking-wider flex items-center gap-1 cursor-pointer"
                                                    title="Visualizar anexo"
                                                >
                                                    👁️ Ver
                                                </button>
                                                <a 
                                                    href="{{ $doc['download_url'] }}" 
                                                    class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-[10px] rounded-[5px] transition-colors shadow-xs shrink-0 flex items-center gap-1 uppercase tracking-wider"
                                                    title="Baixar anexo"
                                                >
                                                    Baixar
                                                </a>
                                                <button 
                                                    type="button"
                                                    @click="openReplace({{ $doc['transaction_id'] }}, '{{ addslashes($doc['filename']) }}')"
                                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-[10px] rounded-[5px] transition-colors border border-slate-200 dark:border-slate-800 shadow-xs shrink-0 uppercase tracking-wider cursor-pointer"
                                                    title="Substituir nota/comprovante"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                                    </svg>
                                                </button>
                                                <button 
                                                    type="button"
                                                    @click="confirmDelete({{ $doc['transaction_id'] }}, '{{ addslashes($doc['filename']) }}')"
                                                    class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-650 hover:text-red-750 font-bold text-[10px] rounded-[5px] transition-colors border border-red-200 shadow-xs shrink-0 uppercase tracking-wider cursor-pointer"
                                                    title="Remover anexo de todos os trabalhos"
                                                >
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Lista de Trabalhos Vinculados -->
                                        <div class="w-full bg-slate-50/50 dark:bg-slate-950/20 border border-slate-100 dark:border-slate-850 p-2 rounded-[3px] space-y-1 text-[9px] font-bold text-slate-500 dark:text-slate-400">
                                            <span class="text-[8px] font-black uppercase text-slate-400 block tracking-wider mb-1">Trabalhos Contemplados:</span>
                                            @foreach($doc['projects'] as $relProj)
                                                <div class="flex items-center justify-between gap-2 border-t border-slate-100/50 dark:border-slate-850/50 pt-1 first:border-t-0 first:pt-0">
                                                    <span class="truncate pr-1 text-slate-700 dark:text-slate-300" title="{{ $relProj['description'] }}">• {{ str_replace('Recebimento: ', '', $relProj['description']) }}</span>
                                                    @if($relProj['amount'] > 0)
                                                        <span class="shrink-0 text-slate-500 font-mono">R$ {{ number_format($relProj['amount'], 2, ',', '.') }}</span>
                                                    @else
                                                        <span class="shrink-0 text-blue-500 dark:text-blue-400 font-extrabold uppercase text-[8px] tracking-wider">(NF Compartilhada)</span>
                                                    @endif
                                                </div>
                                            @endforeach
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

                    <!-- Divisor -->
                    <div class="border-t border-slate-100 dark:border-slate-850 my-4"></div>

                    <!-- Enviar Nota Fiscal / Recibo -->
                    <div class="space-y-3 bg-slate-50 dark:bg-slate-950/20 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px]">
                        <h4 class="text-[11px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider block">Anexar Nota Fiscal / Recibo a um Trabalho</h4>
                        <p class="text-xs text-slate-400 font-medium">Esta função permite associar PDFs, XMLs ou Imagens de notas fiscais diretamente a parcelas de pagamentos de qualquer mês.</p>
                        
                        <form action="{{ route('finances.mei.upload-invoice') }}" method="POST" enctype="multipart/form-data" class="space-y-3" x-data="{ paySearch: '' }">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <label class="text-[10px] font-bold text-slate-455 uppercase tracking-wider block font-outfit">Selecione os Trabalhos / Parcelas:</label>
                                    
                                    <!-- Busca rápida de parcelas -->
                                    <input 
                                        type="text" 
                                        x-model="paySearch" 
                                        placeholder="Filtrar parcelas..." 
                                        class="w-full text-xs bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] px-3 py-1.5 focus:outline-none focus:ring-1 focus:ring-primary-500 font-semibold text-slate-700 dark:text-slate-200"
                                    >

                                    <!-- Lista de Checkboxes de Pagamentos -->
                                    <div class="border border-slate-200 dark:border-slate-800 rounded-[5px] p-2 bg-white dark:bg-slate-950 max-h-40 overflow-y-auto space-y-1.5">
                                        @foreach($allPayments as $p)
                                            <label 
                                                x-show="'{{ strtolower(addslashes($p->project->title)) }}'.includes(paySearch.toLowerCase()) || '{{ strtolower(addslashes(number_format($p->amount, 2, ',', '.'))) }}'.includes(paySearch.toLowerCase())"
                                                class="flex items-start gap-2.5 p-2 bg-slate-50 dark:bg-slate-900 border border-slate-150 dark:border-slate-850 rounded-[5px] cursor-pointer hover:bg-slate-100 dark:hover:bg-slate-800/80 transition-all text-xs font-semibold text-slate-750 dark:text-slate-200"
                                            >
                                                <input 
                                                    type="checkbox" 
                                                    name="payment_ids[]" 
                                                    value="{{ $p->id }}"
                                                    class="rounded border-slate-350 text-primary-600 focus:ring-primary-500/20 w-4 h-4 mt-0.5"
                                                />
                                                <span class="leading-tight">
                                                    <strong>{{ $p->project->title }}</strong><br>
                                                    <span class="text-[10px] text-slate-400 font-medium">R$ {{ number_format($p->amount, 2, ',', '.') }} • Pago em: {{ $p->paid_at->format('d/m/Y') }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="space-y-1.5">
                                    <label class="text-[10px] font-bold text-slate-455 uppercase tracking-wider block font-outfit">Arquivo da Nota / Recibo:</label>
                                    <input type="file" name="invoice" required class="w-full text-xs text-slate-500 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] px-3 py-2.5 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                                </div>
                            </div>
                            <div class="flex justify-end pt-1">
                                <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-all shadow-sm uppercase tracking-wider font-outfit">
                                    Anexar Comprovante
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

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
    window.initMonthlyChart = function() {
        const canvas = document.getElementById('monthlyEvolutionChart');
        if (!canvas) return;

        if (window.monthlyChart) {
            window.monthlyChart.destroy();
        }

        const ctx = canvas.getContext('2d');
        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? '#1e293b' : '#f1f5f9';
        const labelColor = isDark ? '#94a3b8' : '#64748b';

        const pjData = JSON.parse(canvas.getAttribute('data-pj') || '[]');
        const pfData = JSON.parse(canvas.getAttribute('data-pf') || '[]');
        const expensesData = JSON.parse(canvas.getAttribute('data-expenses') || '[]');

        window.monthlyChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'],
                datasets: [
                    {
                        label: 'Faturamento PJ',
                        data: pjData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Receitas PF',
                        data: pfData,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.08)',
                        borderWidth: 2.5,
                        tension: 0.35,
                        fill: true
                    },
                    {
                        label: 'Despesas Gerais',
                        data: expensesData,
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
    };

    document.addEventListener('DOMContentLoaded', window.initMonthlyChart);

    function meiManager() {
        return {
            percent: {{ $percent }},
            showPreviewModal: false,
            previewDocUrl: '',
            previewDocName: '',
            previewDocType: '',
            previewDownloadUrl: '',

            // Deletion Modal
            showDeleteModal: false,
            deleteTransId: null,
            deleteFileName: '',

            // Replace Modal
            showReplaceModal: false,
            replaceTransId: null,
            replaceFileName: '',

            confirmDelete(transactionId, filename) {
                this.deleteTransId = transactionId;
                this.deleteFileName = filename;
                this.showDeleteModal = true;
            },

            openReplace(transactionId, filename) {
                this.replaceTransId = transactionId;
                this.replaceFileName = filename;
                this.showReplaceModal = true;
            },
            
            async navigateTo(month, year) {
                const url = new URL(window.location.href);
                url.searchParams.set('month', month);
                url.searchParams.set('year', year);
                window.history.pushState({ month, year }, '', url.toString());

                try {
                    const response = await fetch(url.toString());
                    const html = await response.text();
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');

                    const target = document.getElementById('mei-content-wrapper');
                    const source = doc.getElementById('mei-content-wrapper');
                    if (target && source) {
                        target.innerHTML = source.innerHTML;
                    }

                    // Re-init chart and scroll smoothly
                    window.initMonthlyChart();
                } catch (e) {
                    console.error('Erro na navegação:', e);
                }
            },

            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
            },

            openPreview(doc) {
                this.previewDocUrl = doc.preview_url;
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
