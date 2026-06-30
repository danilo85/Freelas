@extends('layouts.app')

@section('title', 'Revisão de Trabalhos - Gestor de Freelas')
@section('page_title', 'Revisão de Trabalhos')

@section('content')
<div x-data="revisionsManager()" class="space-y-8">
    
    <!-- Top Cards (Métricas) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
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
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
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
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
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
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Filtros e Busca -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        <form action="{{ route('revisoes.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3 w-full">
            <!-- Input Pesquisa -->
            <div class="relative flex-1">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" 
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Buscar por título, subtítulo ou autor..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
            </div>

            <!-- Filtro Status -->
            <select name="status" class="py-2.5 px-4 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white min-w-[150px]">
                <option value="">Todos os Status</option>
                <option value="ativo" {{ request('status') === 'ativo' ? 'selected' : '' }}>Ativos</option>
                <option value="arquivado" {{ request('status') === 'arquivado' ? 'selected' : '' }}>Arquivados</option>
            </select>

            <button type="submit" class="bg-slate-900 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-[5px] hover:bg-slate-800 transition-all shadow-sm">
                Filtrar
            </button>
            
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('revisoes.index') }}" class="border border-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center px-4 rounded-[5px] text-xs font-bold uppercase tracking-wider transition-all">
                    Limpar
                </a>
            @endif
        </form>
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
                @endphp
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between relative overflow-hidden">
                    
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

                        <!-- Trabalho Vinculado -->
                        <div class="text-xs text-slate-500">
                            <span class="font-semibold text-slate-400 block text-[9px] uppercase tracking-wider">Trabalho Vinculado</span>
                            <p class="text-slate-700 font-medium truncate mt-0.5">
                                @if($rev->project)
                                    📁 {{ $rev->project->title }}
                                @else
                                    📄 Projeto Avulso
                                @endif
                            </p>
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
                    <div class="flex items-center gap-2 mt-6 pt-4 border-t border-slate-100">
                        <a href="{{ route('revisoes.show', $rev->id) }}" class="flex-1 text-center bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded-[5px] transition-all shadow-sm shadow-blue-500/10">
                            Ver Linha do Tempo
                        </a>
                        
                        <form action="{{ route('revisoes.destroy', $rev->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este projeto de revisão de arquivos e todas as suas rodadas?')" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="border border-slate-200 text-rose-600 hover:bg-rose-50 p-2.5 rounded-[5px] transition-all" title="Excluir Projeto de Revisão">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                </div>
            @endforeach
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
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak>
         
         <!-- Caixa do Modal -->
         <div class="bg-white rounded-[5px] border border-slate-200 w-full max-w-lg shadow-2xl overflow-hidden"
              @click.away="showCreateModal = false">
              
              <!-- Topo do Modal -->
              <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                  <h4 class="font-outfit font-black text-slate-800 text-lg">Nova Revisão de Trabalho</h4>
                  <button @click="showCreateModal = false" class="text-slate-400 hover:text-slate-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                      </svg>
                  </button>
              </div>

              <!-- Form -->
              <form action="{{ route('revisoes.store') }}" method="POST" class="p-6 space-y-4">
                  @csrf

                  <!-- Autocomplete/Select Autor -->
                  <div class="space-y-1">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Autor Creditado</label>
                      <select name="author_id" 
                              x-model="selectedAuthor" 
                              @change="loadAuthorProjects()"
                              required
                              class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white">
                          <option value="">Selecione o Autor...</option>
                          @foreach($authors as $author)
                              <option value="{{ $author->id }}">{{ $author->name }}</option>
                          @endforeach
                      </select>
                  </div>

                  <!-- Dropdown Trabalhos do Autor -->
                  <div class="space-y-1" x-show="selectedAuthor">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Trabalho/Projeto Vinculado</label>
                      <div class="relative">
                          <select name="project_id" 
                                  class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white"
                                  :disabled="loadingProjects">
                              <option value="">Projeto Avulso (Sem vinculo)</option>
                              <template x-for="project in projects" :key="project.id">
                                  <option :value="project.id" x-text="project.title + ' (' + project.status + ')'"></option>
                              </template>
                          </select>
                          <div x-show="loadingProjects" class="absolute inset-y-0 right-8 flex items-center">
                              <span class="w-4 h-4 border-2 border-blue-500 border-t-transparent rounded-full animate-spin"></span>
                          </div>
                      </div>
                  </div>

                  <!-- Título da Revisão -->
                  <div class="space-y-1">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Título da Revisão</label>
                      <input type="text" 
                             name="title" 
                             placeholder="Ex: Diagramação Final - Livro Infantil" 
                             required
                             class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                  </div>

                  <!-- Subtítulo -->
                  <div class="space-y-1">
                      <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Subtítulo (Opcional)</label>
                      <input type="text" 
                             name="subtitle" 
                             placeholder="Ex: Segunda versão enviada para revisão de texto" 
                             class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
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
            projects: [],
            loadingProjects: false,

            loadAuthorProjects() {
                if (!this.selectedAuthor) {
                    this.projects = [];
                    return;
                }

                this.loadingProjects = true;
                const url = `/utilidades/api/projetos-autor/${this.selectedAuthor}`;

                fetch(url)
                    .then(response => response.json())
                    .then(data => {
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
@endsection
