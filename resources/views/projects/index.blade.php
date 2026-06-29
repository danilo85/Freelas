@extends('layouts.app')

@section('title', 'Projetos e Orçamentos - Gestor de Freelas')
@section('page_title', 'Projetos e Orçamentos')

@section('content')
<style>
    .wysiwyg-content ul {
        list-style-type: disc !important;
        padding-left: 1.5rem !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }
    .wysiwyg-content ol {
        list-style-type: decimal !important;
        padding-left: 1.5rem !important;
        margin-top: 0.25rem !important;
        margin-bottom: 0.25rem !important;
    }
    .wysiwyg-content a {
        color: #2563eb !important;
        text-decoration: underline !important;
    }
    .wysiwyg-content u {
        text-decoration: underline !important;
    }
</style>
<div x-data="projectList()" @selection-toggled.window="handleSelectionToggled($event)" class="space-y-8">
    
    <!-- Top Cards (Métricas com Cores Sólidas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Total de Orçamentos (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-semibold text-blue-100 uppercase tracking-wider">Total de Orçamentos</p>
                <h3 class="text-3xl font-bold text-white mt-2">
                    {{ $totalProjectsCount }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Projetos e orçamentos registrados
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Valor Total Aprovado / Soma Selecionada (Card Verde/Roxo Dinâmico) -->
        <div 
            :class="selectionActive ? 'bg-violet-700 shadow-[0_0_20px_rgba(109,40,217,0.4)] border border-violet-500' : 'bg-emerald-600 border border-emerald-500'"
            class="summary-card rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-all duration-300 relative overflow-hidden"
        >
            <div class="flex-1 min-w-0">
                <p 
                    x-text="selectionActive ? 'Soma Selecionada (Saldo a Receber)' : 'Faturamento Aprovado'"
                    class="text-sm font-semibold uppercase tracking-wider transition-all duration-300"
                    :class="selectionActive ? 'text-violet-100' : 'text-emerald-100'"
                ></p>
                <h3 class="text-2xl font-bold text-white mt-2 transition-all duration-300">
                    <span x-text="selectionActive ? (privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney(selectionSum)) : (privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ (float) $approvedRemainingBalance }}))"></span>
                </h3>
                <span 
                    x-text="selectionActive ? 'Soma das parcelas a receber dos itens selecionados' : 'Total restante a receber'"
                    class="text-sm font-medium block mt-1.5 transition-all duration-300"
                    :class="selectionActive ? 'text-violet-100/90' : 'text-emerald-100/90'"
                ></span>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <!-- Botão de Limpar Seleção -->
                <button 
                    x-show="selectionActive" 
                    @click.stop="clearSelection()"
                    x-transition
                    type="button" 
                    class="px-3 py-1.5 bg-white/20 hover:bg-white/35 text-white text-xs font-semibold uppercase tracking-wider rounded-[5px] border border-white/30 transition-colors shadow-sm focus:outline-none"
                >
                    Limpar
                </button>

                <div 
                    class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm transition-all duration-300"
                >
                    <svg x-show="!selectionActive" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <svg x-show="selectionActive" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Propostas em Análise (Card Amarelo/Laranja) -->
        <div class="bg-amber-500 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-sm font-semibold text-amber-50 uppercase tracking-wider">Em Negociação</p>
                <h3 class="text-3xl font-bold text-white mt-2">
                    {{ $analyzingCount }}
                </h3>
                <span class="text-sm text-amber-50/90 font-medium block mt-1.5 flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-white inline-block animate-pulse"></span>
                    Orçamentos aguardando resposta
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Filtro e Ações -->
    <div class="space-y-4 bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        <div class="flex items-center gap-3 w-full">
            <!-- Pesquisa Moderna -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" 
                       x-model="searchQuery" 
                       placeholder="Pesquise por título, cliente ou descrição..." 
                       class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                <!-- Botão de Limpar Filtro -->
                <button x-show="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                        x-cloak>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Botão Modo Privacidade -->
            <button 
                type="button"
                @click="togglePrivacyMode()"
                class="flex items-center justify-center p-2.5 rounded-[5px] border transition-all duration-200 focus:outline-none shrink-0"
                :class="privacyMode ? 'bg-violet-50 border-violet-200 text-violet-750 shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:text-slate-700 hover:bg-slate-50'"
                :title="privacyMode ? 'Desativar Modo Privacidade' : 'Ativar Modo Privacidade'"
            >
                <svg x-show="privacyMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                </svg>
                <svg x-show="!privacyMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </button>
        </div>

        <!-- Filtro por Status via Tags -->
        <div class="flex flex-wrap gap-2 items-center pt-3 border-t border-slate-100">
            <span class="text-[11px] font-semibold text-slate-400 mr-2 uppercase tracking-wider hidden sm:inline">Filtrar por:</span>
            
            <button type="button" @click="statusFilter = ''" 
                :class="statusFilter === '' ? 'bg-slate-800 text-white border-slate-800' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-3.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Todos">
                <svg class="w-3.5 h-3.5 sm:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
                <span class="hidden sm:inline">Todos</span>
            </button>
            
            <button type="button" @click="statusFilter = 'rascunho'" 
                :class="statusFilter === 'rascunho' ? 'bg-slate-300 text-slate-900 border-slate-400 ring-2 ring-slate-200' : 'bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Rascunho">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full bg-slate-400"></span>
                <span class="hidden sm:inline">Rascunho</span>
            </button>
            
            <button type="button" @click="statusFilter = 'analisando'" 
                :class="statusFilter === 'analisando' ? 'bg-amber-600 text-white border-amber-600 ring-2 ring-amber-300' : 'bg-amber-100 text-amber-900 border-amber-300 hover:bg-amber-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Analisando">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full" :class="statusFilter === 'analisando' ? 'bg-white' : 'bg-amber-500'"></span>
                <span class="hidden sm:inline">Analisando</span>
            </button>
            
            <button type="button" @click="statusFilter = 'aprovado'" 
                :class="statusFilter === 'aprovado' ? 'bg-emerald-600 text-white border-emerald-600 ring-2 ring-emerald-300' : 'bg-emerald-100 text-emerald-900 border-emerald-300 hover:bg-emerald-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Aprovado">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full" :class="statusFilter === 'aprovado' ? 'bg-white' : 'bg-emerald-500'"></span>
                <span class="hidden sm:inline">Aprovado</span>
            </button>
            
            <button type="button" @click="statusFilter = 'rejeitado'" 
                :class="statusFilter === 'rejeitado' ? 'bg-red-600 text-white border-red-600 ring-2 ring-red-300' : 'bg-red-100 text-red-900 border-red-300 hover:bg-red-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Rejeitado">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full" :class="statusFilter === 'rejeitado' ? 'bg-white' : 'bg-red-500'"></span>
                <span class="hidden sm:inline">Rejeitado</span>
            </button>
            
            <button type="button" @click="statusFilter = 'quitado'" 
                :class="statusFilter === 'quitado' ? 'bg-purple-600 text-white border-purple-600 ring-2 ring-purple-300' : 'bg-purple-100 text-purple-900 border-purple-300 hover:bg-purple-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Quitado">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full" :class="statusFilter === 'quitado' ? 'bg-white' : 'bg-purple-500'"></span>
                <span class="hidden sm:inline">Quitado</span>
            </button>
            
            <button type="button" @click="statusFilter = 'finalizado'" 
                :class="statusFilter === 'finalizado' ? 'bg-blue-600 text-white border-blue-600 ring-2 ring-blue-300' : 'bg-blue-100 text-blue-900 border-blue-300 hover:bg-blue-200'" 
                class="w-8 h-8 sm:w-auto sm:h-auto p-0 sm:px-2.5 sm:py-1 rounded-full sm:rounded-[5px] text-[10px] font-semibold uppercase tracking-wider border transition-all duration-150 flex items-center justify-center gap-1.5"
                title="Finalizado">
                <span class="w-2.5 h-2.5 sm:w-1.5 sm:h-1.5 rounded-full" :class="statusFilter === 'finalizado' ? 'bg-white' : 'bg-blue-500'"></span>
                <span class="hidden sm:inline">Finalizado</span>
            </button>
        </div>
    </div>

    <!-- Grid de Projetos -->
    <div class="space-y-4">
        
        <!-- Grid de Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            @forelse($projects as $project)
                <div 
                    x-show="shouldShow('{{ addslashes($project->title) }} {{ addslashes($project->client->name) }} {{ addslashes($project->description) }}', status)"
                    x-transition
                    class="w-full flex"
                >
                    <div 
                        x-data="projectCard('{{ $project->id }}', '{{ $project->status }}', {{ (float) $project->total_value }}, {{ (float) $project->remaining_balance }}, {{ $project->payments->count() > 0 ? 'true' : 'false' }})"
                        @dblclick="handleCardDblClick($event)"
                        @click="handleCardClick($event)"
                        :class="getCardClass()"
                        class="project-card relative p-5 rounded-[5px] border shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all duration-200 cursor-pointer select-none w-full"
                    >
                        <!-- Checkmark de Seleção -->
                        <div x-show="isSelected" x-cloak class="absolute top-3 right-3 w-6 h-6 rounded-full bg-violet-600 text-white flex items-center justify-center shadow-sm z-10 border border-violet-500">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>

                        <!-- Topo do Card (Avatar à esquerda, Nome/Status/ID distribuídos) -->
                        <div class="flex items-start gap-3">
                            <!-- Logo redondo do cliente -->
                            <div class="w-12 h-12 rounded-full border border-slate-200 bg-white overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                                @if($project->client->avatar)
                                    <img src="{{ asset('storage/' . $project->client->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    <span class="text-sm font-bold text-slate-400">
                                        {{ collect(explode(' ', $project->client->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                                    </span>
                                @endif
                            </div>
                            
                            <!-- Informações do Cliente, ID e Status -->
                            <div class="min-w-0 flex-1 space-y-1">
                                <div class="flex flex-wrap items-center justify-between gap-1.5 w-full">
                                    <span class="bg-blue-50 text-blue-700 border border-blue-150 text-xs font-bold px-2 py-0.5 rounded-[5px] shrink-0">
                                        #{{ $project->id }}
                                    </span>

                                    <!-- Dropdown customizado de status -->
                                    <div class="relative inline-flex items-center shrink-0 status-dropdown" @click.away="openStatus = false">
                                        <button 
                                            type="button"
                                            @click.stop="openStatus = !openStatus"
                                            :disabled="updating"
                                            :class="{
                                                'bg-slate-100 text-slate-700 border-slate-350': status === 'rascunho',
                                                'bg-amber-100 text-amber-900 border-amber-300': status === 'analisando',
                                                'bg-emerald-100 text-emerald-900 border-emerald-300': status === 'aprovado',
                                                'bg-red-100 text-red-900 border-red-300': status === 'rejeitado',
                                                'bg-purple-100 text-purple-900 border-purple-300': status === 'quitado',
                                                'bg-blue-100 text-blue-900 border-blue-300': status === 'finalizado'
                                            }" 
                                            class="inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-wider pl-2.5 pr-7 py-1 rounded-[5px] border relative transition-colors duration-200 cursor-pointer focus:outline-none"
                                        >
                                            <span :class="{
                                                'bg-slate-400': status === 'rascunho',
                                                'bg-amber-500': status === 'analisando',
                                                'bg-emerald-500': status === 'aprovado',
                                                'bg-red-500': status === 'rejeitado',
                                                'bg-purple-500': status === 'quitado',
                                                'bg-blue-500': status === 'finalizado'
                                            }" class="w-1.5 h-1.5 rounded-full inline-block shrink-0"></span>

                                            <span x-text="status"></span>
                                            
                                            <svg class="w-2.5 h-2.5 absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none text-current opacity-70 transition-transform duration-200" :class="{ 'rotate-180': openStatus }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>

                                        <!-- Floating Dropdown List -->
                                        <div 
                                            x-show="openStatus"
                                            x-cloak
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute left-1/2 -translate-x-1/2 top-full mt-1.5 w-48 bg-white border border-slate-200 rounded-[5px] shadow-lg p-2.5 z-50 flex flex-col gap-1.5"
                                        >
                                            <!-- Rascunho -->
                                            <button type="button" @click.stop="selectStatus('rascunho')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border bg-slate-100 text-slate-700 border-slate-300 hover:bg-slate-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block shrink-0"></span>
                                                <span class="flex-1">Rascunho</span>
                                            </button>
                                            <!-- Analisando -->
                                            <button type="button" @click.stop="selectStatus('analisando')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border" :class="{ 'opacity-50 cursor-not-allowed bg-amber-100/50 text-amber-900/60 border-amber-300/50': hasPayments, 'bg-amber-100 text-amber-900 border-amber-300 hover:bg-amber-200': !hasPayments }">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block shrink-0"></span>
                                                <span class="flex-1">Analisando</span>
                                            </button>
                                            <!-- Aprovado -->
                                            <button type="button" @click.stop="selectStatus('aprovado')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border bg-emerald-100 text-emerald-900 border-emerald-300 hover:bg-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block shrink-0"></span>
                                                <span class="flex-1">Aprovado</span>
                                            </button>
                                            <!-- Rejeitado -->
                                            <button type="button" @click.stop="selectStatus('rejeitado')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border bg-red-100 text-red-900 border-red-300 hover:bg-red-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                                <span class="flex-1">Rejeitado</span>
                                            </button>
                                            <!-- Quitado -->
                                            <button type="button" @click.stop="selectStatus('quitado')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border bg-purple-100 text-purple-900 border-purple-300 hover:bg-purple-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 inline-block shrink-0"></span>
                                                <span class="flex-1">Quitado</span>
                                            </button>
                                            <!-- Finalizado -->
                                            <button type="button" @click.stop="selectStatus('finalizado')" class="w-full text-left px-2.5 py-1.5 text-[10px] font-semibold uppercase tracking-wider flex items-center gap-1.5 rounded-[5px] border bg-blue-100 text-blue-900 border-blue-300 hover:bg-blue-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block shrink-0"></span>
                                                <span class="flex-1">Finalizado</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Nome do cliente -->
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-400 mt-1 truncate" title="{{ $project->client->name }}">
                                    {{ $project->client->name }}
                                </p>
                            </div>
                        </div>

                        <!-- Título do projeto e proposta -->
                        <div class="space-y-1.5">
                            <h4 class="font-bold text-base leading-snug line-clamp-2" :class="getTitleClass()" title="{{ $project->title }}">
                                {{ $project->title }}
                            </h4>
                            <div class="text-sm line-clamp-2 leading-relaxed wysiwyg-content text-slate-500" :class="getDescriptionClass()" title="{{ strip_tags($project->description) }}">
                                {!! $project->description !!}
                            </div>
                        </div>

                        <!-- Autores como mini cards estruturados -->
                        @if($project->authors->count() > 0)
                            <div class="space-y-1.5 pt-0.5">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block">Equipe / Autores</span>
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($project->authors as $author)
                                        <span class="bg-slate-50 border border-slate-200/50 text-slate-650 px-2 py-0.5 rounded-[5px] text-[10px] font-semibold uppercase tracking-wider inline-block">
                                            {{ $author->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Box de Resumo Financeiro Minimalista e Elegante -->
                        @php
                            $totalPaid = $project->total_value - $project->remaining_balance;
                            $percentPaid = $project->total_value > 0 ? min(100, max(0, ($totalPaid / $project->total_value) * 100)) : 0;
                        @endphp
                        <div :class="getBoxClass()" class="border p-3.5 rounded-[5px] space-y-2.5 transition-colors duration-200">
                            <!-- Valores Alinhados -->
                            <div class="flex justify-between items-baseline">
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Valor do Projeto</span>
                                <span class="text-base font-bold text-slate-800" :class="isSelected ? 'text-violet-950' : ''">
                                    <span x-text="privacyMode ? 'R$ ••••' : 'R$ ' + formatMoney({{ $project->total_value }})"></span>
                                </span>
                            </div>

                            <!-- Barra de Progresso Minimalista -->
                            <div class="space-y-1">
                                <div class="w-full bg-slate-150 border border-slate-200/40 rounded-full h-1.5 overflow-hidden">
                                    <div class="bg-emerald-600 h-full rounded-full transition-all duration-500" style="width: {{ $percentPaid }}%"></div>
                                </div>
                                <div class="flex justify-between text-[9px] font-semibold text-slate-400 uppercase tracking-wide">
                                    <span>Pago: <span class="font-bold text-emerald-600" x-text="privacyMode ? '••••' : 'R$ ' + formatMoney({{ $totalPaid }})"></span></span>
                                    <span>Restante: <span class="font-bold text-amber-600" x-text="privacyMode ? '••••' : 'R$ ' + formatMoney({{ $project->remaining_balance }})"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- Data como Badge/Tag -->
                        <div class="text-center">
                            <span class="bg-slate-100 text-slate-500 border border-slate-200/50 px-2 py-0.5 rounded-[5px] text-[10px] font-semibold uppercase tracking-wider inline-block">
                                Criado em {{ \Carbon\Carbon::parse($project->created_at)->format('d/m/Y \à\s H:i') }}
                            </span>
                        </div>

                        <!-- Divisor -->
                        <div class="border-t my-0.5" :class="getDividerClass()"></div>

                        <!-- Rodapé com botões de ação compactos e alinhados à direita -->
                        <div class="flex flex-wrap items-center justify-end pt-1 gap-1">
                            <!-- Visualizar -->
                            <a href="{{ route('projects.show', $project->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Visualizar Orçamento">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                             </a>

                             <!-- Editar -->
                             <a href="{{ route('projects.edit', $project->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar Orçamento">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                 </svg>
                             </a>

                             <!-- Registrar Pagamento -->
                             @if($project->status !== 'rejeitado' && $project->status !== 'quitado' && $project->remaining_balance > 0.005)
                                 <a href="{{ route('payments.create', ['project_id' => $project->id]) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Registrar Pagamento">
                                     <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                                     </svg>
                                 </a>
                             @endif

                             <!-- Deletar -->
                             <button type="button" @click.stop="$dispatch('trigger-global-delete', { title: 'Excluir Orçamento', message: 'Tem certeza de que deseja excluir o orçamento <strong class=\'text-slate-850\'>{{ addslashes($project->title) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Esta ação removerá permanentemente o orçamento e todas as transações vinculadas.</span>', action: '{{ route('projects.destroy', $project->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none animate-pulse-once" title="Excluir Orçamento">
                                 <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                 </svg>
                             </button>
                         </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]">
                    Nenhum projeto ou orçamento cadastrado ainda.
                </div>
            @endforelse
        </div>
        
        <!-- Mensagem de Nenhum Resultado da Busca (Filtragem Alpine) -->
        <div x-show="(searchQuery !== '' || statusFilter !== '') && countVisibleCards() === 0" 
             class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]"
             x-cloak>
            Nenhum orçamento atende aos critérios da sua pesquisa.
        </div>

    </div>

    <!-- Botão Flutuante Redondo (FAB) -->
    <a href="{{ route('projects.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" title="Novo Orçamento">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function projectList() {
        return {
            searchQuery: '',
            statusFilter: '',
            selectedProjects: [],
            privacyMode: localStorage.getItem('privacyMode') === 'true',
            
            init() {
                // Escuta cliques no documento para limpar seleções fora dos componentes
                window.addEventListener('click', (e) => {
                    this.handleOutsideClick(e);
                });
            },

            togglePrivacyMode() {
                this.privacyMode = !this.privacyMode;
                localStorage.setItem('privacyMode', this.privacyMode ? 'true' : 'false');
            },

            clearSelection() {
                this.selectedProjects = [];
                // Dispara o evento global para desmarcar todos os cards
                window.dispatchEvent(new CustomEvent('clear-selections'));
            },

            get selectionActive() {
                return this.selectedProjects.length > 0;
            },

            get selectionSum() {
                return this.selectedProjects.reduce((acc, p) => acc + p.remainingBalance, 0);
            },

            handleSelectionToggled(e) {
                const detail = e.detail;
                if (detail.isSelected) {
                    if (!this.selectedProjects.some(p => p.id === detail.projectId)) {
                        this.selectedProjects.push({
                            id: detail.projectId,
                            remainingBalance: parseFloat(detail.remainingBalance)
                        });
                    }
                } else {
                    this.selectedProjects = this.selectedProjects.filter(p => p.id !== detail.projectId);
                }
            },

            handleOutsideClick(e) {
                if (this.selectionActive) {
                    if (!e.target.closest('.project-card') && !e.target.closest('.summary-card') && !e.target.closest('.status-dropdown') && !e.target.closest('button') && !e.target.closest('a')) {
                        this.clearSelection();
                    }
                }
            },

            shouldShow(searchText, cardStatus) {
                if (this.statusFilter && cardStatus !== this.statusFilter) {
                    return false;
                }
                if (this.searchQuery) {
                    const query = this.searchQuery.toLowerCase().trim();
                    return searchText.toLowerCase().includes(query) || cardStatus.toLowerCase().includes(query);
                }
                return true;
            },

            countVisibleCards() {
                let count = 0;
                const cards = document.querySelectorAll('.project-card-wrapper');
                cards.forEach(card => {
                    if (card.style.display !== 'none') {
                        count++;
                    }
                });
                return count;
            },

            formatMoney(value) {
                return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            }
        }
    }

    function projectCard(projectId, initialStatus, totalValue, remainingBalance, hasPayments) {
        return {
            projectId: projectId,
            status: initialStatus,
            totalValue: parseFloat(totalValue),
            remainingBalance: parseFloat(remainingBalance),
            hasPayments: hasPayments,
            isSelected: false,
            openStatus: false,
            updating: false,

            init() {
                window.addEventListener('clear-selections', () => {
                    this.isSelected = false;
                });
            },

            toggleSelection(e) {
                this.isSelected = !this.isSelected;
                this.$dispatch('selection-toggled', {
                    projectId: this.projectId,
                    isSelected: this.isSelected,
                    remainingBalance: this.remainingBalance
                });
            },

            handleCardClick(e) {
                if (this.selectionActive) {
                    if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('.status-dropdown')) {
                        this.toggleSelection(e);
                    }
                }
            },

            handleCardDblClick(e) {
                if (!e.target.closest('button') && !e.target.closest('a') && !e.target.closest('.status-dropdown')) {
                    this.toggleSelection(e);
                }
            },

            getCardClass() {
                if (this.isSelected) {
                    return 'bg-violet-50/30 border-violet-500 shadow-[0_0_20px_rgba(109,40,217,0.25)] ring-2 ring-violet-400/20';
                }
                switch (this.status) {
                    case 'rascunho':
                        return 'bg-slate-100/80 border-slate-300 hover:border-slate-400 hover:bg-slate-200/50';
                    case 'analisando':
                        return 'bg-amber-50/80 border-amber-300 hover:border-amber-400 hover:bg-amber-100/40';
                    case 'aprovado':
                        return 'bg-emerald-50/80 border-emerald-300 hover:border-emerald-400 hover:bg-emerald-100/40';
                    case 'rejeitado':
                        return 'bg-red-50/80 border-red-300 hover:border-red-400 hover:bg-red-100/40';
                    case 'quitado':
                        return 'bg-purple-50/80 border-purple-300 hover:border-purple-400 hover:bg-purple-100/40';
                    case 'finalizado':
                        return 'bg-blue-50/80 border-blue-300 hover:border-blue-400 hover:bg-blue-100/40';
                    default:
                        return 'bg-white border-slate-200 hover:border-slate-350';
                }
            },

            getTitleClass() {
                switch (this.status) {
                    case 'rascunho': return 'text-slate-900';
                    case 'analisando': return 'text-amber-900';
                    case 'aprovado': return 'text-emerald-950';
                    case 'rejeitado': return 'text-red-950';
                    case 'quitado': return 'text-purple-950';
                    case 'finalizado': return 'text-blue-950';
                    default: return 'text-slate-900';
                }
            },

            getBoxClass() {
                if (this.isSelected) {
                    return 'bg-violet-100/20 border-violet-200/30';
                }
                switch (this.status) {
                    case 'rascunho': return 'bg-slate-100/50 border-slate-200/50';
                    case 'analisando': return 'bg-amber-100/30 border-amber-200/40';
                    case 'aprovado': return 'bg-emerald-100/30 border-emerald-200/40';
                    case 'rejeitado': return 'bg-red-100/30 border-red-200/40';
                    case 'quitado': return 'bg-purple-100/30 border-purple-200/40';
                    case 'finalizado': return 'bg-blue-100/30 border-blue-200/40';
                    default: return 'bg-slate-50 border-slate-100';
                }
            },

            getDividerClass() {
                if (this.isSelected) {
                    return 'border-violet-200/60';
                }
                switch (this.status) {
                    case 'rascunho': return 'border-slate-200/60';
                    case 'analisando': return 'border-amber-200/60';
                    case 'aprovado': return 'border-emerald-200/60';
                    case 'rejeitado': return 'border-red-200/60';
                    case 'quitado': return 'border-purple-200/60';
                    case 'finalizado': return 'border-blue-200/60';
                    default: return 'border-slate-100';
                }
            },

            formatMoney(value) {
                return new Intl.NumberFormat('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(value);
            },

            selectStatus(newStatus) {
                if (newStatus === 'analisando' && this.hasPayments) {
                    alert('Não é possível voltar o status do orçamento para Analisando pois ele já possui pagamentos registrados.');
                    return;
                }
                this.openStatus = false;
                if (newStatus !== this.status) {
                    this.status = newStatus;
                    this.updateStatus();
                }
            },

            async updateStatus() {
                this.updating = true;
                try {
                    const response = await fetch(`/api/projects/${this.projectId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
                        this.hasPayments = result.has_payments || this.hasPayments;
                    }
                } catch (error) {
                    console.error(error);
                    alert('Erro de conexão ao atualizar o status.');
                    window.location.reload();
                } finally {
                    this.updating = false;
                }
            }
        }
    }
</script>
@endsection
