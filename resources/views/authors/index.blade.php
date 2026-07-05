@extends('layouts.app')

@section('title', 'Autores - Gestor de Freelas')
@section('page_title', 'Gerenciamento de Autores')

@section('content')
@php
    $mappedAuthors = $authors->map(fn($a) => [
        'id' => $a->id,
        'searchable' => strtolower($a->name . ' ' . $a->email . ' ' . $a->phone . ' ' . strip_tags($a->bio))
    ]);
@endphp

<div x-data="authorList()" class="space-y-8">
    
    <!-- Top Cards (Métricas com Cores Diferentes) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Total de Autores (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total de Autores</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $totalAuthorsCount }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Autores cadastrados na base
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
            </div>
        </div>

        <!-- Autores com Biografia (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Com Biografia</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $authorsWithBioCount }}
                </h3>
                <span class="text-sm text-emerald-100/90 font-medium block mt-1.5 flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-white inline-block animate-pulse"></span>
                    Com perfil biográfico preenchido
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
            </div>
        </div>

        <!-- Novos Autores (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-purple-100 uppercase tracking-wider">Novos Autores</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $newAuthorsCount }}
                </h3>
                <span class="text-sm text-purple-100/90 font-medium block mt-1.5">
                    Registrados nos últimos 30 dias
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Filtro e Ações -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        
        <!-- Pesquisa Moderna -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery" 
                   @input="currentPage = 1"
                   placeholder="Pesquise por nome, email, documento, telefone ou biografia..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <!-- Botão de Limpar Filtro -->
            <button x-show="searchQuery" 
                    @click="searchQuery = ''; currentPage = 1;" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                    x-cloak>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

    </div>

    <!-- Grid de Autores -->
    <div class="space-y-4">
           <!-- Grid de Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            @forelse($authors as $author)
                <div x-show="isAuthorOnCurrentPage({{ $author->id }})" 
                     x-transition
                     class="w-full flex"
                >
                    <div class="{{ in_array($author->id, $topAuthorIds) ? 'bg-gradient-to-br from-amber-50/40 to-yellow-50/10 dark:from-amber-950/25 dark:to-slate-900/40 border-amber-350 dark:border-amber-800/40 ring-1 ring-amber-300/30 dark:ring-amber-950/20 shadow-md' : (!$author->registration_completed ? 'bg-amber-50/25 border-amber-200' : 'bg-white border-slate-200') }} border p-5 rounded-[5px] shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all duration-200 w-full relative overflow-hidden">
                    
                    @if(in_array($author->id, $topAuthorIds))
                        <!-- Destaque Ouro -->
                        <div class="absolute top-0 right-0 bg-amber-500 text-amber-950 text-[9px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-bl-[5px] flex items-center gap-1 shadow-sm">
                            <span>★</span> <span>Principal</span>
                        </div>
                    @endif

                    <!-- Topo do Card (Foto + Identificação) -->
                    <div class="flex items-start gap-3">
                        <!-- Fallback com silhueta de perfil se estiver vazio -->
                        <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                            @if($author->avatar)
                                <img src="{{ asset('storage/' . $author->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 truncate" title="{{ $author->name }}">{{ $author->name }}</h4>
                                @if(!$author->registration_completed)
                                    <span class="bg-amber-100 text-amber-850 text-sm font-bold px-1.5 py-0.5 rounded-[5px] border border-amber-200 shrink-0">Pendente</span>
                                @endif
                            </div>
                            <p class="text-sm text-primary-600 truncate font-medium" title="{{ $author->email }}">{{ $author->email }}</p>
                        </div>
                    </div>

                    <!-- Dados de Contato e Biografia -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-sm text-slate-500">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-medium">WhatsApp / Telefone:</span>
                            <span class="text-slate-800 font-semibold truncate max-w-[150px]">{{ $author->phone ?? 'Não informado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-medium">CPF / CNPJ:</span>
                            <span class="text-slate-850 font-mono font-semibold truncate max-w-[150px]">{{ $author->document ?? 'Não informado' }}</span>
                        </div>

                        <!-- Bloco de Estatísticas de Orçamentos -->
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 border border-slate-100 p-2.5 rounded-[5px] text-xs my-2">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Valor Gerado</span>
                                <span class="text-slate-800 font-black text-sm block mt-0.5">R$ {{ number_format($author->total_value, 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Orçamentos</span>
                                <span class="text-slate-700 font-medium block mt-0.5">
                                    <strong class="text-slate-850 font-black text-sm">{{ $author->projects_count }}</strong> total
                                    <span class="text-[11px] text-slate-400 block font-normal mt-0.5">
                                        Aceitos: <strong class="text-emerald-600 font-bold">{{ $author->approved_count }}</strong> | Rej: <strong class="text-red-500 font-bold">{{ $author->rejected_count }}</strong>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <!-- Principais Parcerias -->
                        @if(!empty($author->top_partners))
                            <div class="pt-1 flex flex-wrap gap-1 items-center">
                                <span class="text-[11px] text-slate-400 font-semibold mr-1">Parcerias:</span>
                                @foreach($author->top_partners as $partner)
                                    <span class="bg-slate-100 text-slate-650 text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] border border-slate-150">{{ $partner }}</span>
                                @endforeach
                            </div>
                        @endif
                        
                        <!-- Biografia -->
                        <div class="pt-2">
                            <span class="text-slate-400 font-medium block mb-1">Biografia:</span>
                            <p class="text-slate-700 bg-slate-50/50 p-2 rounded-[5px] border border-slate-100 font-normal leading-relaxed line-clamp-2 text-justify min-h-[48px]" title="{{ $author->bio }}">
                                {{ $author->bio ?? 'Nenhuma biografia informada.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center justify-end gap-1 pt-3 border-t border-slate-100">
                        <!-- Botão Visualizar -->
                        <a href="{{ route('authors.show', $author->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Visualizar Autor">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>

                        <!-- Botão Editar -->
                        <a href="{{ route('authors.edit', $author->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar Autor">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </a>
                        
                        <!-- Botão Excluir -->
                        <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Autor', message: 'Tem certeza de que deseja excluir o autor <strong class=\'text-slate-880\'>{{ addslashes($author->name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Esta ação excluirá permanentemente o autor do sistema.</span>', action: '{{ route('authors.destroy', $author->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir Autor">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]">
                    Nenhum autor cadastrado ainda.
                </div>
            @endforelse
        </div>
        
        <!-- Mensagem de Nenhum Resultado da Busca (Filtragem Alpine) -->
        <div x-show="searchQuery !== '' && totalFilteredCount === 0" 
             class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]"
             x-cloak>
            Nenhum autor atende aos critérios da sua pesquisa.
        </div>

        <!-- Painel de Paginação Dinâmica (Sem Reload) -->
        <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-6 mt-4" x-cloak>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                Mostrando <span class="text-slate-800 dark:text-slate-200" x-text="Math.min((currentPage - 1) * perPage + 1, totalFilteredCount)"></span> a 
                <span class="text-slate-800 dark:text-slate-200" x-text="Math.min(currentPage * perPage, totalFilteredCount)"></span> de 
                <span class="text-slate-800 dark:text-slate-200" x-text="totalFilteredCount"></span> autores
            </div>
            
            <div class="flex items-center gap-1.5">
                <button type="button" @click="prevPage()" :disabled="currentPage === 1" 
                    class="h-8 px-3 rounded-[5px] border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    Anterior
                </button>
                
                <template x-for="p in totalPagesArray" :key="p">
                    <button type="button" 
                        @click="p !== '...' ? currentPage = p : null" 
                        :disabled="p === '...'"
                        :class="{
                            'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/20': currentPage === p,
                            'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-850 hover:bg-slate-50 dark:hover:bg-slate-800': currentPage !== p && p !== '...',
                            'text-slate-400 dark:text-slate-650 border-transparent bg-transparent cursor-default select-none': p === '...'
                        }" 
                        class="w-8 h-8 rounded-[5px] border text-xs font-bold transition-all"
                        x-text="p">
                    </button>
                </template>
                
                <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" 
                    class="h-8 px-3 rounded-[5px] border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    Próxima
                </button>
            </div>
        </div>

    </div>

    <!-- Botão Flutuante Redondo (FAB) -->
    <a href="{{ route('authors.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" title="Novo Autor">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function authorList() {
        return {
            searchQuery: '',
            currentPage: 1,
            perPage: 12,
            authors: @json($mappedAuthors),

            get totalFilteredCount() {
                return this.filteredAuthors.length;
            },

            get filteredAuthors() {
                return this.authors.filter(a => {
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase().trim();
                        return a.searchable.includes(query);
                    }
                    return true;
                });
            },

            get totalPages() {
                return Math.ceil(this.totalFilteredCount / this.perPage) || 1;
            },

            get totalPagesArray() {
                const total = this.totalPages;
                const current = this.currentPage;
                const delta = 1;
                const range = [];
                for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                    range.push(i);
                }
                if (current - delta > 2) range.unshift('...');
                range.unshift(1);
                if (current + delta < total - 1) range.push('...');
                if (total > 1) range.push(total);
                return range;
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.scrollToTop();
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.scrollToTop();
                }
            },

            scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            isAuthorOnCurrentPage(authorId) {
                const index = this.filteredAuthors.findIndex(a => a.id === authorId);
                if (index === -1) return false;
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return index >= start && index < end;
            }
        }
    }
</script>
@endsection
