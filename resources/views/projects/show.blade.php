@extends('layouts.app')

@section('title', 'Detalhes do Orçamento - Gestor de Freelas')
@section('page_title', 'Visualizar Orçamento')

@section('content')
<style>
    .wysiwyg-content ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .wysiwyg-content ol {
        list-style-type: decimal !important;
        padding-left: 1.5rem !important;
        margin-top: 0.5rem !important;
        margin-bottom: 0.5rem !important;
    }
    .wysiwyg-content a {
        color: #2563eb !important;
        text-decoration: underline !important;
    }
    .wysiwyg-content u {
        text-decoration: underline !important;
    }
</style>
<div class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('projects.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    @if($activeVersion)
        <div class="bg-amber-50 border border-amber-200 text-amber-900 rounded-[5px] p-4 flex flex-col sm:flex-row items-center justify-between gap-4 no-print shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-6 h-6 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div class="text-sm">
                    <span class="font-bold">Aviso:</span> Você está visualizando uma versão anterior deste orçamento gravada em <strong>{{ \Carbon\Carbon::parse($activeVersion->created_at)->format('d/m/Y \à\s H:i') }}</strong> (Ação: 
                    @if($activeVersion->action === 'criado')
                        Criado
                    @elseif($activeVersion->action === 'atualizado')
                        Atualizado
                    @elseif($activeVersion->action === 'aprovado')
                        Aprovado pelo Cliente
                    @elseif($activeVersion->action === 'rejeitado')
                        Rejeitado pelo Cliente
                    @endif).
                </div>
            </div>
            <a href="{{ route('projects.show', $project->id) }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm whitespace-nowrap text-center">
                Voltar para a Versão Atual
            </a>
        </div>
    @endif

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Cartão Financeiro (Esquerda - 1 Coluna) -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6">
            
            @php
                $totalPaid = $project->total_value - $project->remaining_balance;
                $percentPaid = $project->total_value > 0 ? min(100, max(0, ($totalPaid / $project->total_value) * 100)) : 0;
            @endphp
            <!-- Resumo Financeiro com Progresso -->
            <div class="space-y-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Faturamento Total</span>
                        <h3 class="text-2xl font-black text-slate-800 mt-1">R$ {{ number_format($project->total_value, 2, ',', '.') }}</h3>
                    </div>
                    
                    <!-- Status Badge Editável em Tempo Real -->
                    <div 
                        x-data="projectStatusEditor('{{ $project->status }}', '{{ route('projects.update-status', $project->id) }}', '{{ csrf_token() }}', {{ $project->payments->count() > 0 ? 'true' : 'false' }})" 
                        class="relative inline-flex items-center shrink-0"
                        @click.away="open = false"
                    >
                        <!-- Botão de Trigger (Looks exactly like the original tag) -->
                        <button 
                            type="button"
                            @click="open = !open"
                            :disabled="updating"
                            :class="{
                                'bg-slate-100 text-slate-700 border-slate-350': status === 'rascunho',
                                'bg-amber-100 text-amber-900 border-amber-300': status === 'analisando',
                                'bg-emerald-100 text-emerald-900 border-emerald-300': status === 'aprovado',
                                'bg-red-100 text-red-900 border-red-300': status === 'rejeitado',
                                'bg-purple-100 text-purple-900 border-purple-300': status === 'quitado',
                                'bg-blue-100 text-blue-900 border-blue-300': status === 'finalizado'
                            }" 
                            class="inline-flex items-center gap-1.5 text-xs font-black uppercase tracking-wider pl-2.5 pr-7 py-1 rounded-[5px] border mt-1 relative transition-colors duration-200 cursor-pointer focus:outline-none"
                        >
                            <!-- Status dot -->
                            <span :class="{
                                'bg-slate-400': status === 'rascunho',
                                'bg-amber-500': status === 'analisando',
                                'bg-emerald-500': status === 'aprovado',
                                'bg-red-500': status === 'rejeitado',
                                'bg-purple-500': status === 'quitado',
                                'bg-blue-500': status === 'finalizado'
                            }" class="w-2 h-2 rounded-full inline-block shrink-0"></span>

                            <span x-text="status"></span>
                            
                            <!-- Down Arrow SVG icon -->
                            <svg class="w-3 h-3 absolute right-2.5 top-1/2 -translate-y-1/2 pointer-events-none text-current opacity-70 transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                                    <!-- Floating Custom Dropdown List -->
                        <div 
                            x-show="open"
                            x-cloak
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute right-0 top-full mt-2 w-52 bg-white border border-slate-200 rounded-[5px] shadow-lg p-2.5 z-50 flex flex-col gap-1.5"
                        >
                            <!-- Option: Rascunho -->
                            <button 
                                type="button"
                                @click="selectStatus('rascunho')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200 hover:border-slate-400"
                                :class="{ 'ring-2 ring-slate-450/20 font-extrabold': status === 'rascunho' }"
                            >
                                <span class="w-2 h-2 rounded-full bg-slate-400 inline-block shrink-0"></span>
                                <span class="flex-1">Rascunho</span>
                                <svg x-show="status === 'rascunho'" class="w-3.5 h-3.5 ml-auto text-slate-500 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>

                            <!-- Option: Analisando -->
                            <button 
                                type="button"
                                @click="selectStatus('analisando')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none"
                                :class="{ 
                                    'opacity-50 cursor-not-allowed': hasPayments,
                                    'bg-amber-100 text-amber-900 border-amber-300 hover:bg-amber-200 hover:border-amber-450': !hasPayments,
                                    'bg-amber-100/50 text-amber-900/60 border-amber-300/50': hasPayments,
                                    'ring-2 ring-amber-500/20 font-extrabold': status === 'analisando'
                                }"
                            >
                                <span class="w-2 h-2 rounded-full bg-amber-500 inline-block shrink-0"></span>
                                <span class="flex-1">Analisando</span>
                                <template x-if="hasPayments">
                                    <span class="text-[9px] text-red-500 font-extrabold normal-case ml-auto" title="Não é possível voltar para analisando com pagamentos registrados">Bloqueado</span>
                                </template>
                                <template x-if="!hasPayments">
                                    <svg x-show="status === 'analisando'" class="w-3.5 h-3.5 ml-auto text-amber-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </template>
                            </button>

                            <!-- Option: Aprovado -->
                            <button 
                                type="button"
                                @click="selectStatus('aprovado')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none bg-emerald-100 text-emerald-900 border-emerald-300 hover:bg-emerald-200 hover:border-emerald-450"
                                :class="{ 'ring-2 ring-emerald-500/20 font-extrabold': status === 'aprovado' }"
                            >
                                <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                <span class="flex-1">Aprovado</span>
                                <svg x-show="status === 'aprovado'" class="w-3.5 h-3.5 ml-auto text-emerald-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>

                            <!-- Option: Rejeitado -->
                            <button 
                                type="button"
                                @click="selectStatus('rejeitado')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none bg-red-100 text-red-900 border-red-300 hover:bg-red-200 hover:border-red-450"
                                :class="{ 'ring-2 ring-red-500/20 font-extrabold': status === 'rejeitado' }"
                            >
                                <span class="w-2 h-2 rounded-full bg-red-500 inline-block shrink-0"></span>
                                <span class="flex-1">Rejeitado</span>
                                <svg x-show="status === 'rejeitado'" class="w-3.5 h-3.5 ml-auto text-red-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>

                            <!-- Option: Quitado -->
                            <button 
                                type="button"
                                @click="selectStatus('quitado')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none bg-purple-100 text-purple-900 border-purple-300 hover:bg-purple-200 hover:border-purple-450"
                                :class="{ 'ring-2 ring-purple-500/20 font-extrabold': status === 'quitado' }"
                            >
                                <span class="w-2 h-2 rounded-full bg-purple-500 inline-block shrink-0"></span>
                                <span class="flex-1">Quitado</span>
                                <svg x-show="status === 'quitado'" class="w-3.5 h-3.5 ml-auto text-purple-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>

                            <!-- Option: Finalizado -->
                            <button 
                                type="button"
                                @click="selectStatus('finalizado')"
                                class="w-full text-left px-3 py-1.5 text-xs font-black uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border transition-all duration-200 cursor-pointer focus:outline-none bg-blue-100 text-blue-900 border-blue-300 hover:bg-blue-200 hover:border-blue-450"
                                :class="{ 'ring-2 ring-blue-500/20 font-extrabold': status === 'finalizado' }"
                            >
                                <span class="w-2 h-2 rounded-full bg-blue-500 inline-block shrink-0"></span>
                                <span class="flex-1">Finalizado</span>
                                <svg x-show="status === 'finalizado'" class="w-3.5 h-3.5 ml-auto text-blue-900 font-bold" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3.5" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Spinner of status loading -->
                        <span x-show="updating" x-cloak class="absolute -right-6 top-1/2 -translate-y-1/2">
                            <svg class="animate-spin h-4.5 w-4.5 text-slate-555" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </div>
                </div>

                <!-- Slide visual de progresso dos pagamentos -->
                <div class="space-y-2 pt-1">
                    <div class="flex justify-between text-xs font-bold uppercase tracking-wider text-slate-400">
                        <span>Progresso Quitação</span>
                        <span class="text-slate-700">{{ number_format($percentPaid, 1) }}% Pago</span>
                    </div>
                    <!-- Barra de progresso visual estilosa -->
                    <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden border border-slate-200/50 shadow-inner relative">
                        <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ $percentPaid }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-2 mt-1.5 text-[11px]">
                        <div class="bg-emerald-50 border border-emerald-100 p-2 rounded-[5px] flex flex-col">
                            <span class="font-bold text-slate-400 uppercase tracking-wider">Pago</span>
                            <span class="font-black text-emerald-800 text-[13px] mt-0.5">R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                        </div>
                        <div class="bg-slate-50 border border-slate-150 p-2 rounded-[5px] flex flex-col">
                            <span class="font-bold text-slate-400 uppercase tracking-wider">Restante</span>
                            <span class="font-black text-slate-800 text-[13px] mt-0.5">R$ {{ number_format($project->remaining_balance, 2, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Divisor -->
            <div class="border-t border-slate-100"></div>

            <!-- Divisão de Valores -->
            <div class="space-y-3">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Divisão de Valores</span>
                
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                        <span class="text-slate-500 font-medium">Sinal ({{ $project->initial_payment_percent }}%):</span>
                        <span class="text-slate-800 font-extrabold">R$ {{ number_format($project->total_value * ($project->initial_payment_percent / 100), 2, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                        <span class="text-slate-500 font-medium">Restante ({{ 100 - $project->initial_payment_percent }}%):</span>
                        <span class="text-slate-800 font-extrabold">R$ {{ number_format($project->total_value * ((100 - $project->initial_payment_percent) / 100), 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Lista de Pagamentos Efetuados -->
            @if($project->payments->count() > 0)
                <!-- Divisor -->
                <div class="border-t border-slate-100"></div>

                <div class="space-y-3">
                    <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Histórico de Pagamentos</span>
                    <div class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                        @foreach($project->payments as $payment)
                            <div class="flex items-center justify-between text-xs bg-slate-50 border border-slate-100 p-2.5 rounded-[5px]">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-slate-800 text-sm">R$ {{ number_format($payment->amount, 2, ',', '.') }}</span>
                                    <span class="text-[11px] text-slate-500 font-medium">{{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') }} • {{ $payment->payment_method }}</span>
                                </div>
                                @if($payment->invoice_path)
                                    <a href="{{ route('payments.download', $payment->id) }}" class="inline-flex items-center justify-center py-1 px-2.5 bg-emerald-50 hover:bg-emerald-100 border border-emerald-150 text-emerald-700 text-[11px] font-bold rounded-[5px] transition-colors shadow-sm" title="Baixar Nota Fiscal">
                                        NF
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Divisor -->
            <div class="border-t border-slate-100"></div>

            <!-- Informações Básicas -->
            <div class="space-y-3 text-sm">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Prazo & Datas</span>
                <div class="space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Prazo de Entrega:</span>
                        <span class="text-slate-800 font-semibold">{{ $project->term }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Data do Orçamento:</span>
                        <span class="text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($project->budget_date)->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Data de Validade:</span>
                        <span class="text-slate-800 font-semibold">{{ \Carbon\Carbon::parse($project->expiration_date)->format('d/m/Y') }}</span>
                    </div>
                </div>
            </div>

            <!-- Divisor -->
            <div class="border-t border-slate-100"></div>

            <!-- Link de Compartilhamento -->
            <div class="space-y-3" x-data="{ copied: false, shareUrl: '{{ route('proposal.show', $proposal->hash) }}' }">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Link de Compartilhamento</span>
                
                <div class="relative">
                    <input type="text" readonly :value="shareUrl" class="w-full text-sm text-slate-600 bg-slate-50 border border-slate-200 rounded-[5px] py-2 px-3 pr-10 focus:outline-none focus:ring-0">
                    <button type="button" @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2000)" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" title="Copiar Link">
                        <svg x-show="!copied" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <svg x-show="copied" x-cloak class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <button type="button" @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2000)" class="flex items-center justify-center gap-1.5 py-2.5 px-3 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                        <span x-text="copied ? 'Copiado!' : 'Copiar Link'"></span>
                    </button>
                    <a :href="shareUrl" target="_blank" class="flex items-center justify-center gap-1.5 py-2.5 px-3 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                        Abrir Link
                    </a>
                </div>
            </div>

            <!-- Divisor -->
            <div class="border-t border-slate-100"></div>

            <!-- Ações Rápidas -->
            <div class="space-y-3">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Gerenciamento</span>
                
                @if($project->status !== 'rejeitado' && $project->status !== 'quitado' && $project->remaining_balance > 0.005)
                    <a href="{{ route('payments.create', ['project_id' => $project->id]) }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Registrar Pagamento
                    </a>
                @endif

                <a href="{{ route('projects.edit', $project->id) }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Editar Orçamento
                </a>

                <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Orçamento', message: 'Tem certeza de que deseja excluir o orçamento <strong class=\'text-slate-800\'>{{ addslashes($project->title) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Esta ação removerá permanentemente o orçamento e todas as transações vinculadas.</span>', action: '{{ route('projects.destroy', $project->id) }}', highSecurity: false })" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Excluir Orçamento
                </button>
            </div>

        </div>

        <!-- Conteúdo da Proposta e Dados Relacionados (Direita - 2 Colunas) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Cartão: Dados da Proposta -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Conteúdo da Proposta</h4>
                </div>
                
                <h2 class="text-xl font-bold text-slate-900">{{ $project->title }}</h2>

                <!-- Texto da Proposta -->
                <div class="bg-slate-50 border border-slate-100 p-5 rounded-[5px] text-sm text-slate-700 leading-relaxed font-normal text-justify wysiwyg-content break-words min-h-[150px]">
                    {!! $project->description !!}
                </div>
            </div>

            <!-- Cartão: Informações de Relacionamento (Cliente e Autores) -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-5">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Pessoas Envolvidas</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 text-sm">
                    
                    <!-- Bloco do Cliente -->
                    <div class="space-y-2 bg-slate-50 border border-slate-150 p-4 rounded-[5px] flex items-start gap-3">
                        <div class="w-10 h-10 rounded-full border border-slate-200 bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                            @if($project->client->avatar)
                                <img src="{{ asset('storage/' . $project->client->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-bold text-slate-400">
                                    {{ collect(explode(' ', $project->client->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block mb-0.5">Cliente Contratante</span>
                            <a href="{{ route('clients.show', $project->client->id) }}" class="font-bold text-slate-800 hover:text-primary-600 block truncate hover:underline" title="Visualizar Cliente">
                                {{ $project->client->name }}
                            </a>
                            <span class="text-sm text-slate-400 block truncate mt-0.5">{{ $project->client->email }}</span>
                            
                            @if(!$project->client->registration_completed)
                                <span class="bg-amber-100 text-amber-850 text-sm font-black px-1.5 py-0.5 rounded-[5px] border border-amber-200 inline-block mt-2">Cadastro Pendente</span>
                            @endif
                        </div>
                    </div>

                    <!-- Bloco dos Autores -->
                    <div class="space-y-3">
                        <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block mb-1">Equipe de Autores / Criadores</span>
                        
                        @if($project->authors->count() > 0)
                            <div class="space-y-3">
                                @foreach($project->authors as $author)
                                    <div class="bg-slate-50 border border-slate-150 p-3 rounded-[5px] flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full border border-slate-200 bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                                            @if($author->avatar)
                                                <img src="{{ asset('storage/' . $author->avatar) }}" class="w-full h-full object-cover">
                                            @else
                                                <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ route('authors.show', $author->id) }}" class="font-bold text-slate-800 hover:text-primary-600 block truncate hover:underline" title="Visualizar Autor">
                                                {{ $author->name }}
                                            </a>
                                            
                                            @if(!$author->registration_completed)
                                                <span class="bg-amber-100 text-amber-800 text-sm font-bold px-1.5 py-0.5 rounded-[5px] border border-amber-200 inline-block mt-0.5">Pendente</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="border border-dashed border-slate-200 p-4 text-center text-slate-400 rounded-[5px]">
                                Nenhum autor vinculado a este orçamento.
                            </div>
                        @endif
                    </div>

                </div>
            </div>

            <!-- Cartão 3: Observações Adicionais -->
            @if($project->additional_info)
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                    <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Informações Adicionais</h4>
                    <p class="text-sm text-slate-650 bg-slate-50 border border-slate-100 p-4 rounded-[5px] leading-relaxed whitespace-pre-wrap">
                        {{ $project->additional_info }}
                    </p>
                </div>
            @endif

            <!-- Cartão 3.5: Documentos e Anexos -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-5 no-print" x-data="attachmentUploader()">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Documentos e Anexos</h4>
                    <span class="text-sm text-slate-400 font-bold uppercase" x-text="attachments.length + (attachments.length === 1 ? ' Arquivo' : ' Arquivos')"></span>
                </div>

                <!-- Dropzone Area -->
                <div 
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    @click="triggerChoose"
                    :class="isDragging ? 'bg-primary-50/50 border-primary-400 shadow-inner' : 'bg-slate-50 border-slate-200 hover:bg-slate-100/50'"
                    class="border-2 border-dashed rounded-[5px] p-8 text-center cursor-pointer transition-all duration-200 space-y-2 relative"
                >
                    <input 
                        type="file" 
                        x-ref="fileInput" 
                        @change="handleFileSelect" 
                        class="hidden" 
                        multiple
                    />
                    
                    <div class="flex flex-col items-center justify-center space-y-2">
                        <!-- Icon -->
                        <div class="p-3 bg-white rounded-full border border-slate-200 shadow-sm text-slate-400">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        
                        <p class="text-sm font-bold text-slate-700">
                            Arraste arquivos aqui ou <span class="text-primary-600 hover:underline">clique para selecionar</span>
                        </p>
                        <p class="text-xs text-slate-400 font-medium">
                            Notas fiscais (XML/PDF), briefings, imagens ou ZIP de até 10MB por arquivo
                        </p>
                    </div>
                </div>

                <!-- Options / Actions while uploading or setting default -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 border border-slate-150 p-4 rounded-[5px] text-sm">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-slate-500 font-medium">Classificar uploads como:</span>
                        <select 
                            x-model="defaultClassification" 
                            class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-[5px] px-3 py-1.5 focus:outline-none focus:border-slate-350"
                        >
                            <option value="auto">Auto-detectar (Recomendado)</option>
                            <option value="nota_fiscal">Nota Fiscal</option>
                            <option value="material">Material do Projeto</option>
                            <option value="anexo">Anexo Geral</option>
                        </select>
                    </div>

                    <!-- Uploading Indicator -->
                    <div x-show="uploading" class="flex items-center gap-2 text-primary-600 font-semibold" x-cloak>
                        <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm">Enviando... (<span x-text="uploadProgress"></span>%)</span>
                    </div>
                </div>

                <!-- Attachments List -->
                <div class="space-y-3" x-show="attachments.length > 0">
                    <div class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Arquivos do Projeto</div>
                    
                    <div class="divide-y divide-slate-150 border border-slate-200 rounded-[5px] overflow-hidden bg-slate-50/30">
                        <template x-for="attachment in attachments" :key="attachment.id">
                            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 p-4 hover:bg-slate-50 transition-colors">
                                <!-- File Information -->
                                <div class="flex items-start gap-3 min-w-0 flex-1">
                                    <!-- Dynamic File Icon -->
                                    <div class="p-2.5 rounded-[5px] shrink-0" :class="{
                                        'bg-amber-100 text-amber-700': getFileIcon(attachment.name) === 'image',
                                        'bg-red-100 text-red-700': getFileIcon(attachment.name) === 'pdf',
                                        'bg-purple-100 text-purple-700': getFileIcon(attachment.name) === 'archive',
                                        'bg-emerald-100 text-emerald-700': getFileIcon(attachment.name) === 'spreadsheet',
                                        'bg-blue-100 text-blue-700': getFileIcon(attachment.name) === 'document',
                                        'bg-slate-200 text-slate-650': getFileIcon(attachment.name) === 'file'
                                    }">
                                        <!-- Image Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'image'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </template>
                                        <!-- PDF Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'pdf'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                            </svg>
                                        </template>
                                        <!-- Archive Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'archive'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                            </svg>
                                        </template>
                                        <!-- Spreadsheet Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'spreadsheet'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </template>
                                        <!-- Document Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'document'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </template>
                                        <!-- Generic File Icon -->
                                        <template x-if="getFileIcon(attachment.name) === 'file'">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </template>
                                    </div>
                                    
                                    <div class="min-w-0 flex-1 space-y-0.5">
                                        <p class="font-bold text-slate-800 truncate text-sm" :title="attachment.name" x-text="attachment.name"></p>
                                        <p class="text-xs text-slate-400 font-medium">
                                            <span x-text="attachment.file_size"></span> • Enviado em <span x-text="attachment.created_at"></span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Classification Selector and Actions -->
                                <div class="flex flex-wrap items-center gap-3 shrink-0">
                                    <!-- Classification Select Dropdown -->
                                    <div class="flex items-center gap-1.5 relative">
                                        <!-- Spinner for classification saving -->
                                        <div x-show="updatingId === attachment.id" class="absolute -left-6 top-1/2 -translate-y-1/2" x-cloak>
                                            <svg class="animate-spin h-5 w-5 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </div>
                                        <!-- Success Badge -->
                                        <div x-show="successId === attachment.id" class="absolute -left-6 top-1/2 -translate-y-1/2 text-emerald-500" x-cloak>
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                        
                                        <select 
                                            :value="attachment.classification" 
                                            @change="updateClassification(attachment, $event.target.value)"
                                            class="bg-white border border-slate-200 text-slate-700 text-sm font-semibold rounded-[5px] px-2.5 py-1.5 focus:outline-none focus:border-slate-350"
                                        >
                                            <option value="nota_fiscal">Nota Fiscal</option>
                                            <option value="material">Material do Projeto</option>
                                            <option value="anexo">Anexo Geral</option>
                                        </select>
                                    </div>

                                    <!-- Download and Delete Actions -->
                                    <div class="flex items-center gap-1.5">
                                        <!-- Download Button -->
                                        <a :href="attachment.download_url" class="p-2 bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 rounded-[5px] transition-colors shadow-sm" title="Baixar Arquivo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                            </svg>
                                        </a>

                                        <!-- Delete Button -->
                                        <button type="button" @click="deleteAttachment(attachment)" class="p-2 bg-red-50 hover:bg-red-100 border border-red-150 text-red-600 rounded-[5px] transition-colors shadow-sm" title="Excluir Arquivo">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Empty State -->
                <div x-show="attachments.length === 0" class="border border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] text-sm font-medium">
                    Nenhum documento ou anexo enviado para este orçamento.
                </div>
            </div>

            <script>
                function attachmentUploader() {
                    return {
                        isDragging: false,
                        uploading: false,
                        uploadProgress: 0,
                        defaultClassification: 'auto',
                        attachments: {!! json_encode($project->attachments->map(fn($a) => [
                            'id' => $a->id,
                            'name' => $a->name,
                            'file_size' => $a->file_size_formatted,
                            'classification' => $a->classification,
                            'download_url' => route('projects.attachments.download', $a->id),
                            'destroy_url' => route('projects.attachments.destroy', $a->id),
                            'created_at' => $a->created_at->format('d/m/Y H:i'),
                        ])->toArray()) !!},
                        updatingId: null,
                        successId: null,

                        triggerChoose() {
                            this.$refs.fileInput.click();
                        },

                        handleFileSelect(e) {
                            const files = e.target.files;
                            if (files.length > 0) {
                                this.uploadFiles(files);
                            }
                        },

                        handleDrop(e) {
                            this.isDragging = false;
                            const files = e.dataTransfer.files;
                            if (files.length > 0) {
                                this.uploadFiles(files);
                            }
                        },

                        async uploadFiles(files) {
                            this.uploading = true;
                            this.uploadProgress = 0;

                            const total = files.length;
                            let count = 0;

                            for (let i = 0; i < files.length; i++) {
                                const file = files[i];
                                
                                if (file.size > 10 * 1024 * 1024) {
                                    alert(`O arquivo "${file.name}" excede o limite de 10MB.`);
                                    continue;
                                }

                                const formData = new FormData();
                                formData.append('file', file);
                                formData.append('classification', this.defaultClassification);

                                try {
                                    const response = await fetch('{{ route('projects.attachments.store', $project->id) }}', {
                                        method: 'POST',
                                        body: formData,
                                        headers: {
                                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                            'Accept': 'application/json'
                                        }
                                    });

                                    const result = await response.json();
                                    if (result.success) {
                                        this.attachments.unshift(result.attachment);
                                    } else {
                                        alert(result.message || 'Erro ao enviar o arquivo.');
                                    }
                                } catch (error) {
                                    console.error(error);
                                    alert('Erro de conexão ao enviar o arquivo.');
                                }
                                
                                count++;
                                this.uploadProgress = Math.round((count / total) * 100);
                            }

                            this.uploading = false;
                            this.uploadProgress = 0;
                            this.$refs.fileInput.value = '';
                        },

                        async updateClassification(attachment, newClassification) {
                            this.updatingId = attachment.id;
                            try {
                                const response = await fetch(`/attachments/${attachment.id}/classification`, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ classification: newClassification })
                                });

                                const result = await response.json();
                                if (result.success) {
                                    attachment.classification = newClassification;
                                    this.successId = attachment.id;
                                    setTimeout(() => {
                                        if (this.successId === attachment.id) {
                                            this.successId = null;
                                        }
                                    }, 2000);
                                } else {
                                    alert(result.message || 'Erro ao atualizar.');
                                }
                            } catch (error) {
                                console.error(error);
                                alert('Erro de conexão ao atualizar classificação.');
                            } finally {
                                this.updatingId = null;
                            }
                        },

                        async deleteAttachment(attachment) {
                            if (!confirm(`Deseja excluir o anexo "${attachment.name}"?`)) {
                                return;
                            }

                            try {
                                const response = await fetch(attachment.destroy_url, {
                                    method: 'DELETE',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Accept': 'application/json'
                                    }
                                });

                                const result = await response.json();
                                if (result.success) {
                                    this.attachments = this.attachments.filter(a => a.id !== attachment.id);
                                } else {
                                    alert(result.message || 'Erro ao excluir.');
                                }
                            } catch (error) {
                                console.error(error);
                                alert('Erro de conexão ao excluir o anexo.');
                            }
                        },

                        getFileIcon(name) {
                            const ext = name.split('.').pop().toLowerCase();
                            if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'].includes(ext)) {
                                return 'image';
                            }
                            if (ext === 'pdf') {
                                return 'pdf';
                            }
                            if (['zip', 'rar', '7z', 'tar', 'gz'].includes(ext)) {
                                return 'archive';
                            }
                            if (['xls', 'xlsx', 'csv'].includes(ext)) {
                                return 'spreadsheet';
                            }
                            if (['doc', 'docx', 'txt', 'rtf'].includes(ext)) {
                                return 'document';
                            }
                            return 'file';
                        }
                    };
                }

                function projectStatusEditor(initialStatus, updateUrl, csrfToken, hasPayments) {
                    return {
                        status: initialStatus,
                        updating: false,
                        csrfToken: csrfToken,
                        updateUrl: updateUrl,
                        hasPayments: hasPayments,
                        open: false,

                        selectStatus(newStatus) {
                            if (newStatus === 'analisando' && this.hasPayments) {
                                alert('Não é possível voltar o status do orçamento para Analisando pois ele já possui pagamentos registrados.');
                                return;
                            }
                            this.open = false;
                            if (newStatus !== this.status) {
                                this.status = newStatus;
                                this.updateStatus();
                            }
                        },

                        async updateStatus() {
                            this.updating = true;
                            try {
                                const response = await fetch(this.updateUrl, {
                                    method: 'PATCH',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': this.csrfToken,
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ status: this.status })
                                });

                                const result = await response.json();
                                if (!response.ok || !result.success) {
                                    alert(result.message || 'Erro ao atualizar o status do projeto.');
                                    window.location.reload();
                                } else {
                                    this.status = result.status;
                                    // Se o status mudou para quitado ou rejeitado, recarrega para atualizar botões/layouts
                                    if (this.status === 'quitado' || this.status === 'rejeitado' || this.status === 'aprovado') {
                                        window.location.reload();
                                    }
                                }
                            } catch (error) {
                                console.error(error);
                                alert('Erro de conexão ao atualizar o status.');
                                window.location.reload();
                            } finally {
                                this.updating = false;
                            }
                        }
                    };
                }
            </script>

            <!-- Cartão 4: Histórico de Alterações -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-5 no-print">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Histórico de Alterações</h4>
                    <span class="text-sm text-slate-400 font-bold uppercase">{{ $histories->count() }} {{ $histories->count() == 1 ? 'Versão' : 'Versões' }}</span>
                </div>

                <div class="relative pl-6 border-l border-slate-200 space-y-6">
                    @foreach($histories as $history)
                        @php
                            $isActive = $activeVersion && $activeVersion->id === $history->id;
                            $isCurrent = !$activeVersion && $loop->first;
                        @endphp
                        <div class="relative">
                            <!-- Bullet Indicator -->
                            <span class="absolute -left-[30px] top-1 flex items-center justify-center w-3 h-3 rounded-full border bg-white
                                {{ $history->action === 'criado' ? 'border-blue-500 bg-blue-500' : '' }}
                                {{ $history->action === 'atualizado' ? 'border-amber-500 bg-amber-500' : '' }}
                                {{ $history->action === 'aprovado' ? 'border-emerald-500 bg-emerald-500' : '' }}
                                {{ $history->action === 'rejeitado' ? 'border-red-500 bg-red-500' : '' }}">
                            </span>

                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-[5px] border transition-all
                                {{ $isActive ? 'bg-amber-50/50 border-amber-300 shadow-sm' : ($isCurrent && !$activeVersion ? 'bg-primary-50/20 border-primary-200' : 'bg-slate-50 border-slate-100 hover:border-slate-150') }}">
                                
                                <div class="space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-bold text-sm text-slate-800">
                                            @if($history->action === 'criado')
                                                Orçamento Criado
                                            @elseif($history->action === 'atualizado')
                                                Orçamento Atualizado
                                            @elseif($history->action === 'aprovado')
                                                Aprovado pelo Cliente
                                            @elseif($history->action === 'rejeitado')
                                                Rejeitado pelo Cliente
                                            @endif
                                        </span>
                                        @if($isCurrent && !$activeVersion)
                                            <span class="bg-primary-100 text-primary-850 text-xs font-bold px-2 py-0.5 rounded-[5px] border border-primary-200">Versão Atual</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-slate-400">
                                        {{ \Carbon\Carbon::parse($history->created_at)->format('d/m/Y \à\s H:i') }}
                                        @if($history->user_id)
                                            por <span class="font-semibold text-slate-600">{{ DB::table('users')->where('id', $history->user_id)->value('name') ?? 'Usuário' }}</span>
                                        @else
                                            via link público
                                        @endif
                                    </p>
                                </div>

                                <div class="shrink-0 flex items-center gap-2">
                                    @if($isActive)
                                        <a href="{{ route('projects.show', $project->id) }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm text-center">
                                            Voltar ao Atual
                                        </a>
                                    @else
                                        <a href="{{ route('projects.show', [$project->id, 'version_id' => $history->id]) }}" class="px-3 py-1.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-700 text-sm font-semibold rounded-[5px] transition-colors shadow-sm text-center">
                                            Visualizar
                                        </a>
                                    @endif
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
