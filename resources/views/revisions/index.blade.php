@extends('layouts.app')

@section('title', 'Revisão de Trabalhos - Gestor de Freelas')
@section('page_title', 'Revisão de Trabalhos')

@section('content')
<style>
    @keyframes pulse-glow-rose {
        0%, 100% { box-shadow: 0 0 5px rgba(244, 63, 94, 0.15), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(244, 63, 94, 0.35); }
        50% { box-shadow: 0 0 15px rgba(244, 63, 94, 0.5), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(244, 63, 94, 0.65); }
    }
    .pulse-glow-rose {
        animation: pulse-glow-rose 2s infinite ease-in-out;
    }
</style>
<div x-data="revisionsManager()" class="space-y-8">
    
    <!-- Top Cards (Métricas) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total de Projetos de Revisão (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Trabalhos em Revisão</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $totalProjects }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Provas ativas na base de dados
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        </div>

        <!-- Rodadas Ativas (Card Laranja/Amarelo) -->
        <div class="bg-amber-500 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-amber-100 uppercase tracking-wider">Rodadas Ativas</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $activeRoundsCount }}
                </h3>
                <span class="text-sm text-amber-100/90 font-medium block mt-1.5">
                    Processos aguardando retorno
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H18.5"></path>
                </svg>
            </div>
        </div>

        <!-- Ajustes Solicitados (Card Vermelho) -->
        <div class="bg-rose-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-rose-100 uppercase tracking-wider">Ajustes Pendentes</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $pendingAdjustmentsCount }}
                </h3>
                <span class="text-sm text-rose-100/90 font-medium block mt-1.5">
                    Correções abertas marcadas por clientes
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
            </div>
        </div>

        <!-- Espaço de Armazenamento Ocupado (Card Cinza/Slate) -->
        <div class="bg-slate-700 rounded-[5px] p-6 shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow duration-200 text-white min-h-[140px]">
            <div class="flex items-center justify-between w-full">
                <div>
                    <p class="text-sm font-bold text-slate-200 uppercase tracking-wider">Espaço em Disco</p>
                    <h3 class="text-2xl font-extrabold text-white mt-2 leading-none">
                        {{ $formattedStorageSize }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3zm0 5h16M4 15h16"></path>
                    </svg>
                </div>
            </div>
            
            <div class="mt-4 space-y-1 w-full">
                <div class="w-full bg-slate-800 rounded-full h-1.5 overflow-hidden relative">
                    <div class="bg-blue-400 h-full rounded-full" style="width: {{ $storagePercent }}%"></div>
                </div>
                <div class="flex items-center justify-between text-[9px] text-slate-300 font-bold uppercase tracking-wider">
                    <span>Limite: {{ $storageLimitFormatted }}</span>
                    <span>{{ number_format($storagePercent, 1) }}% usado</span>
                </div>
            </div>
        </div>

    </div>

    <!-- Busca e Filtros -->
    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm select-none space-y-4">
        <!-- Campo de Busca -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery"
                   placeholder="Buscar por título, subtítulo ou autor..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <button type="button" 
                    x-show="searchQuery" 
                    @click="searchQuery = ''" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-655 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Filtros Rápidos (Status) -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Status:</span>
            
            <!-- Todos -->
            <button type="button" 
                    @click="filterStatus = ''" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="!filterStatus ? 'bg-slate-900 dark:bg-slate-100 border-slate-900 dark:border-slate-100 text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-750 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700/50'">
                Todos
            </button>

            <!-- Ativos -->
            <button type="button" 
                    @click="filterStatus = 'ativo'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="filterStatus === 'ativo' ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/10' : 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/30'">
                Ativos
            </button>

            <!-- Arquivados -->
            <button type="button" 
                    @click="filterStatus = 'arquivado'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="filterStatus === 'arquivado' ? 'bg-amber-600 border-amber-600 text-white shadow-md shadow-amber-600/10' : 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/30'">
                Arquivados
            </button>
        </div>
    </div>

    <!-- Grid de Projetos de Revisão -->
    @if($revisions->isEmpty())
        <div class="border-2 border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] text-sm bg-white">
            Nenhum projeto de revisão de arquivos criado ainda.
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($revisions as $rev)
                @php
                    $pendingCount = 0;
                    $resolvedCount = 0;
                    foreach ($rev->rounds as $round) {
                        foreach ($round->files as $file) {
                            $pendingCount += $file->annotations->where('status', 'aberto')->count();
                            $resolvedCount += $file->annotations->where('status', 'resolvido')->count();
                        }
                    }
                    $latestRound = $rev->rounds->first();

                    $isAllOk = ($resolvedCount > 0 && $pendingCount === 0);
                    $isApproved = ($latestRound && $latestRound->status === 'aprovado');
                    $hasAdjustments = ($pendingCount > 0);

                    $cardClass = 'bg-white border-slate-200';
                    if ($isAllOk) {
                        $cardClass = 'bg-white border-slate-200';
                    } elseif ($hasAdjustments) {
                        $cardClass = 'bg-rose-50/25 border-rose-200 pulse-glow-rose';
                    } elseif ($isApproved) {
                        $cardClass = 'bg-emerald-50/25 border-emerald-200';
                    }
                @endphp
                <div x-show="shouldShowRevision('{{ addslashes($rev->title) }}', '{{ addslashes($rev->subtitle) }}', '{{ addslashes($rev->author ? $rev->author->name : '') }}', '{{ $rev->status }}')"
                     class="{{ $cardClass }} rounded-[5px] p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden"
                     x-transition>
                    
                    <!-- Stamp Carimbo Tudo Ok -->
                    @if($isAllOk)
                        <div class="absolute right-4 top-[35%] -translate-y-1/2 -rotate-12 pointer-events-none select-none z-10 opacity-30 transform scale-110">
                            <div class="border-4 border-emerald-600/75 text-emerald-600/75 font-black text-lg px-3 py-1 rounded uppercase tracking-widest flex items-center gap-1">
                                <span>✓</span> <span>TUDO OK</span>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Badge Status -->
                    <span class="absolute top-4 right-4 text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-full tracking-wide 
                        {{ $rev->status === 'ativo' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 'bg-slate-100 text-slate-600 border border-slate-200' }}">
                        {{ $rev->status }}
                    </span>

                    <div class="space-y-4">
                        <div>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto de Revisão</span>
                            <h4 class="font-outfit font-black text-slate-800 text-lg leading-snug mt-1 truncate" title="{{ $rev->title }}">
                                {{ $rev->title }}
                            </h4>
                            @if($rev->subtitle)
                                <p class="text-xs text-slate-400 truncate mt-0.5">{{ $rev->subtitle }}</p>
                            @endif
                        </div>

                        <!-- Info do Autor -->
                        <div class="flex items-center gap-3 bg-slate-50 p-3 rounded-[5px] border border-slate-100">
                            <div class="w-8 h-8 rounded-full bg-slate-200 border border-slate-300 text-slate-600 font-bold flex items-center justify-center text-xs overflow-hidden shrink-0">
                                @if($rev->author->avatar)
                                    <img src="{{ asset('storage/' . $rev->author->avatar) }}" class="w-full h-full object-cover">
                                @else
                                    {{ collect(explode(' ', $rev->author->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                                @endif
                            </div>
                            <div class="min-w-0">
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">Autor Creditado</span>
                                <p class="text-xs font-bold text-slate-700 truncate leading-tight mt-0.5">{{ $rev->author->name }}</p>
                            </div>
                        </div>

                        <!-- Trabalho Vinculado e Valor -->
                        <div class="text-xs text-slate-500 space-y-2">
                            <div class="flex items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="font-semibold text-slate-400 block text-[9px] uppercase tracking-wider">Trabalho Vinculado</span>
                                    <p class="text-slate-700 font-semibold truncate mt-0.5">
                                        @if($rev->project)
                                            📁 {{ $rev->project->title }}
                                        @else
                                            📄 Projeto Avulso
                                        @endif
                                    </p>
                                </div>
                                @if($rev->project)
                                    <span class="shrink-0 inline-flex items-center px-2 py-0.5 rounded-[5px] text-[10px] font-extrabold bg-emerald-50 text-emerald-600 border border-emerald-250/60 shadow-sm">
                                        R$ {{ number_format($rev->project->total_value, 2, ',', '.') }}
                                    </span>
                                @endif
                            </div>

                            <!-- Tags Rápidas -->
                            <div class="flex flex-wrap gap-1.5 pt-1">
                                @if($rev->project)
                                    <a href="{{ route('projects.show', $rev->project_id) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[3px] text-[9px] font-extrabold uppercase tracking-wide bg-slate-100 text-slate-650 border border-slate-200 hover:bg-slate-200 hover:text-slate-800 transition-all" title="Ver orçamento do projeto">
                                        📄 Ver Orçamento
                                    </a>
                                @endif
                                
                                @if($latestRound)
                                    <a href="{{ route('public.revisao.show', $rev->share_token) }}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[3px] text-[9px] font-extrabold uppercase tracking-wide bg-blue-50 text-blue-600 border border-blue-150 hover:bg-blue-100 transition-all" title="Abrir página de revisão pública da última rodada">
                                        🔗 Revisão Pública
                                    </a>
                                @endif
                            </div>
                        </div>

                        <!-- Stats de Ajustes -->
                        <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100">
                            <div class="bg-rose-50/50 border border-rose-100 p-2 rounded-[5px] text-center">
                                <span class="text-[9px] font-semibold text-rose-500 uppercase tracking-wide block">Ajustes Abertos</span>
                                <span class="text-sm font-extrabold text-rose-600 block mt-0.5">{{ $pendingCount }}</span>
                            </div>
                            <div class="bg-emerald-50/50 border border-emerald-100 p-2 rounded-[5px] text-center">
                                <span class="text-[9px] font-semibold text-emerald-500 uppercase tracking-wide block">Resolvidos</span>
                                <span class="text-sm font-extrabold text-emerald-600 block mt-0.5">{{ $resolvedCount }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rodapé do Card: Ações -->
                    <div class="flex items-center gap-2 mt-6 pt-4 border-t border-slate-100"
                         x-data="{ 
                             copied: false,
                             shareMessage: 'Olá! Segue o link para a revisão de arquivos do projeto *{{ $rev->title }}*:\n\n{{ route('public.revisao.show', $rev->share_token) }}'
                         }">
                        <a href="{{ route('revisoes.show', $rev->id) }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded-[5px] transition-all shadow-sm shadow-blue-500/10">
                            Ver Linha do Tempo
                        </a>

                        <!-- Copiar Link de Revisão -->
                        <button type="button" 
                                @click="navigator.clipboard.writeText(shareMessage); copied = true; setTimeout(() => copied = false, 2000)"
                                class="w-8 h-8 flex items-center justify-center rounded-[5px] transition-all border-0 shadow-none bg-transparent cursor-pointer"
                                :class="copied ? 'text-emerald-600 bg-emerald-50' : 'text-slate-500 hover:bg-slate-50'"
                                :title="copied ? 'Copiado!' : 'Copiar Link de Revisão'">
                            <span x-show="!copied">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            </span>
                            <span x-show="copied" x-cloak class="font-extrabold text-emerald-600">✓</span>
                        </button>
                        
                        <button type="button" 
                                @click="$dispatch('trigger-global-delete', { title: 'Excluir Projeto de Revisão', message: 'Deseja realmente excluir este projeto de revisão de arquivos e todas as suas rodadas?', action: '{{ route('revisoes.destroy', $rev->id) }}', backupUrl: '{{ route('revisoes.backup', $rev->id) }}', highSecurity: false })"
                                class="w-8 h-8 flex items-center justify-center bg-transparent border-0 shadow-none text-rose-600 hover:bg-rose-50 rounded-[5px] transition-all cursor-pointer" 
                                title="Excluir Projeto de Revisão">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                </div>
            @endforeach
        </div>

        <!-- Estado de Filtro Vazio (Client-side) -->
        <div x-show="revisionsList.filter(r => shouldShowRevision(r.title, r.subtitle, r.author ? r.author.name : '', r.status)).length === 0 && revisionsList.length > 0" 
             class="text-center py-12 bg-white border border-slate-200 rounded-[5px] shadow-sm select-none" 
             x-cloak>
            <span class="text-5xl block">🔍</span>
            <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight mt-4">Nenhum projeto de revisão corresponde à busca</h3>
            <p class="text-xs text-slate-400 mt-1">Experimente limpar a sua busca ou trocar as tags selecionadas.</p>
        </div>
    @endif

    <!-- Botão Flutuante Estilizado para Abrir Modal -->
    <button @click="showCreateModal = true" class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-blue-600/30 hover:scale-105 hover:rotate-90 transition-all duration-300 z-40 focus:outline-none">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>

    <!-- Modal de Criação (Alpine.js) -->
    <div x-show="showCreateModal" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         style="position: fixed !important; top: 0 !important; left: 0 !important; right: 0 !important; bottom: 0 !important; width: 100vw !important; height: 100vh !important; margin: 0 !important; z-index: 99999;"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
          
         <!-- Caixa do Modal -->
         <div class="bg-white dark:bg-slate-900 rounded-[5px] border border-slate-200 dark:border-slate-800 w-full max-w-lg shadow-2xl overflow-visible"
              @click.away="showCreateModal = false">
              
              <!-- Topo do Modal -->
              <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                  <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-lg">Nova Revisão de Trabalho</h4>
                  <button type="button" @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                  </button>
              </div>

              <!-- Form -->
              <form action="{{ route('revisoes.store') }}" method="POST" class="p-6 space-y-4">
                  @csrf

                  <!-- Autocomplete Autor -->
                  <div class="space-y-1 relative" @click.away="showAuthorDropdown = false">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Autor Creditado</label>
                      <input type="hidden" name="author_id" x-model="selectedAuthor" required>
                      
                      <div class="relative">
                          <input type="text" 
                                 x-model="authorSearch" 
                                 @focus="showAuthorDropdown = true; highlightedAuthorIndex = -1;"
                                 @input="showAuthorDropdown = true; selectedAuthor = ''; projects = []; selectedProject = ''; projectSearch = ''; highlightedAuthorIndex = -1;"
                                 @keydown.arrow-down.prevent="highlightNextAuthor()"
                                 @keydown.arrow-up.prevent="highlightPrevAuthor()"
                                 @keydown.enter.prevent="selectHighlightedAuthor()"
                                 @keydown.escape="showAuthorDropdown = false"
                                 placeholder="Digite o nome do autor para buscar..."
                                 required
                                 class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 dark:text-slate-100">
                          
                          <div x-show="selectedAuthor" class="absolute inset-y-0 right-3 flex items-center text-emerald-600">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                          </div>
                      </div>

                      <!-- Lista suspensa de Autores -->
                      <div x-show="showAuthorDropdown" 
                           class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] shadow-lg max-h-48 overflow-y-auto"
                           x-cloak>
                          <template x-for="(author, index) in filteredAuthors()" :key="author.id">
                              <div @click="selectAuthor(author); showAuthorDropdown = false;" 
                                   class="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-sm text-slate-700 dark:text-slate-200" 
                                   :class="{'bg-slate-100 dark:bg-slate-800': highlightedAuthorIndex === index}"
                                   x-text="author.name"></div>
                          </template>
                          <div x-show="filteredAuthors().length === 0" class="px-4 py-2 text-sm text-slate-400 dark:text-slate-500">Nenhum autor encontrado</div>
                      </div>
                  </div>

                  <!-- Dropdown Trabalhos do Autor -->
                  <div class="space-y-1 relative" x-show="selectedAuthor" @click.away="showProjectDropdown = false">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Trabalho/Projeto Vinculado</label>
                      <input type="hidden" name="project_id" x-model="selectedProject">
                      
                      <div class="relative">
                          <input type="text" 
                                 x-model="projectSearch" 
                                 @focus="showProjectDropdown = true; highlightedProjectIndex = -1;"
                                 @input="showProjectDropdown = true; selectedProject = ''; highlightedProjectIndex = -1;"
                                 @keydown.arrow-down.prevent="highlightNextProject()"
                                 @keydown.arrow-up.prevent="highlightPrevProject()"
                                 @keydown.enter.prevent="selectHighlightedProject()"
                                 @keydown.escape="showProjectDropdown = false"
                                 placeholder="Selecione ou busque o trabalho/projeto..."
                                 class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 dark:text-slate-100"
                                 :disabled="loadingProjects">
                          
                          <div x-show="loadingProjects" class="absolute inset-y-0 right-3 flex items-center">
                              <span class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
                          </div>
                          <div x-show="selectedProject && !loadingProjects" class="absolute inset-y-0 right-3 flex items-center text-emerald-600">
                              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                              </svg>
                          </div>
                      </div>

                      <!-- Lista suspensa de Projetos -->
                      <div x-show="showProjectDropdown" 
                           class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] shadow-lg max-h-48 overflow-y-auto"
                           x-cloak>
                          <!-- Opção padrão -->
                          <div @click="selectProject(null); showProjectDropdown = false;" 
                               class="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-sm text-slate-500 font-semibold"
                               :class="{'bg-slate-100 dark:bg-slate-800': highlightedProjectIndex === 0}">
                               Projeto Avulso (Sem vinculo)
                          </div>
                          
                          <template x-for="(p, index) in filteredProjects()" :key="p.id">
                              <div @click="selectProject(p); showProjectDropdown = false;" 
                                   class="px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer text-sm text-slate-700 dark:text-slate-200" 
                                   :class="{'bg-slate-100 dark:bg-slate-800': highlightedProjectIndex === (index + 1)}"
                                   x-text="p.title + ' (' + p.status + ')'"></div>
                          </template>
                          <div x-show="filteredProjects().length === 0" class="px-4 py-2 text-sm text-slate-400 dark:text-slate-500">Nenhum projeto encontrado</div>
                      </div>
                  </div>

                  <!-- Título da Revisão (Visível apenas se for avulso/sem projeto) -->
                  <div class="space-y-1" x-show="!selectedProject">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Título da Revisão</label>
                      <input type="text" 
                             name="title" 
                             x-model="revisionTitle"
                             placeholder="Ex: Diagramação Final - Livro Infantil" 
                             :required="!selectedProject"
                             class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 dark:text-slate-100">
                  </div>

                  <!-- Subtítulo -->
                  <div class="space-y-1">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Subtítulo (Opcional)</label>
                      <input type="text" 
                             name="subtitle" 
                             placeholder="Ex: Segunda versão enviada para revisão de texto" 
                             class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 dark:text-slate-100">
                  </div>

                  <!-- Ações -->
                  <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                      <button type="button" @click="showCreateModal = false" class="border border-slate-200 hover:bg-slate-50 text-slate-600 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] transition-all">
                          Cancelar
                      </button>
                      <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] transition-all shadow-sm">
                          Criar Projeto e Iniciar Prova
                      </button>
                  </div>
              </form>
         </div>
    </div>

</div>

<script>
    function revisionsManager() {
        return {
            showCreateModal: false,
            selectedAuthor: '',
            authorSearch: '',
            showAuthorDropdown: false,
            selectedProject: '',
            revisionTitle: '',
            projects: [],
            loadingProjects: false,
            authors: {!! json_encode($authors) !!},
            showProjectDropdown: false,
            projectSearch: '',
            highlightedAuthorIndex: -1,
            highlightedProjectIndex: -1,

            filteredAuthors() {
                if (!this.authorSearch) {
                    return this.authors;
                }
                const q = this.authorSearch.toLowerCase();
                return this.authors.filter(a => a.name.toLowerCase().includes(q));
            },

            selectAuthor(author) {
                this.selectedAuthor = author.id;
                this.authorSearch = author.name;
                this.selectedProject = '';
                this.projectSearch = '';
                this.loadAuthorProjects();
            },

            onProjectChange() {
                if (this.selectedProject) {
                    const p = this.projects.find(proj => proj.id == this.selectedProject);
                    if (p) {
                        this.revisionTitle = p.title;
                    }
                } else {
                    this.revisionTitle = '';
                }
            },

            filteredProjects() {
                if (!this.projects) return [];
                if (!this.projectSearch) {
                    return this.projects;
                }
                const q = this.projectSearch.toLowerCase();
                return this.projects.filter(p => p.title.toLowerCase().includes(q) || p.status.toLowerCase().includes(q));
            },

            selectProject(project) {
                if (project) {
                    this.selectedProject = project.id;
                    this.projectSearch = `${project.title} (${project.status})`;
                } else {
                    this.selectedProject = '';
                    this.projectSearch = 'Projeto Avulso (Sem vinculo)';
                }
                this.onProjectChange();
            },

            highlightNextAuthor() {
                const count = this.filteredAuthors().length;
                if (count > 0) {
                    this.highlightedAuthorIndex = (this.highlightedAuthorIndex + 1) % count;
                }
            },

            highlightPrevAuthor() {
                const count = this.filteredAuthors().length;
                if (count > 0) {
                    this.highlightedAuthorIndex = (this.highlightedAuthorIndex - 1 + count) % count;
                }
            },

            selectHighlightedAuthor() {
                const list = this.filteredAuthors();
                if (this.highlightedAuthorIndex >= 0 && this.highlightedAuthorIndex < list.length) {
                    this.selectAuthor(list[this.highlightedAuthorIndex]);
                    this.showAuthorDropdown = false;
                }
            },

            highlightNextProject() {
                const count = this.filteredProjects().length + 1;
                if (count > 0) {
                    this.highlightedProjectIndex = (this.highlightedProjectIndex + 1) % count;
                }
            },

            highlightPrevProject() {
                const count = this.filteredProjects().length + 1;
                if (count > 0) {
                    this.highlightedProjectIndex = (this.highlightedProjectIndex - 1 + count) % count;
                }
            },

            selectHighlightedProject() {
                if (this.highlightedProjectIndex === 0) {
                    this.selectProject(null);
                    this.showProjectDropdown = false;
                } else {
                    const list = this.filteredProjects();
                    const index = this.highlightedProjectIndex - 1;
                    if (index >= 0 && index < list.length) {
                        this.selectProject(list[index]);
                        this.showProjectDropdown = false;
                    }
                }
            },

            init() {
                console.log('[DEBUG] init() do revisionsManager foi carregado!');
            },

            // Filter states
            searchQuery: '{{ request('search', '') }}',
            filterStatus: '{{ request('status', '') }}',
            revisionsList: {!! json_encode($revisions->map(function($r) {
                return [
                    'id' => $r->id,
                    'title' => $r->title,
                    'subtitle' => $r->subtitle,
                    'status' => $r->status,
                    'author' => $r->author ? ['name' => $r->author->name] : null
                ];
            })) !!},

            shouldShowRevision(title, subtitle, authorName, status) {
                if (this.filterStatus && status !== this.filterStatus) return false;
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const t = (title || '').toLowerCase();
                    const sub = (subtitle || '').toLowerCase();
                    const auth = (authorName || '').toLowerCase();
                    return t.includes(q) || sub.includes(q) || auth.includes(q);
                }
                return true;
            },

            loadAuthorProjects() {
                if (!this.selectedAuthor) {
                    this.projects = [];
                    return;
                }

                this.loadingProjects = true;
                const baseUrl = '{{ route('revisoes.api.projects', ['author' => '_AUTHOR_ID_']) }}';
                const url = baseUrl.replace('_AUTHOR_ID_', this.selectedAuthor);

                console.log('[DEBUG] Buscando projetos para o autor ID:', this.selectedAuthor, 'URL:', url);

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
                        console.log('[DEBUG] Projetos carregados da API:', data);
                        this.projects = data;
                        this.loadingProjects = false;
                    })
                    .catch(err => {
                        console.error('Erro ao buscar projetos do autor:', err);
                        this.loadingProjects = false;
                    });
            }
        }
    }
</script>

@if(session('download_backup_url'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = "{{ session('download_backup_url') }}";
        document.body.appendChild(iframe);
    });
</script>
@endif
@endsection
