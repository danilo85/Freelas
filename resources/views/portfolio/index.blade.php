@extends('layouts.app')

@section('title', 'Portfólio - Gestor de Freelas')
@section('page_title', 'Administração de Portfólio')

@section('content')
@php
    if (!function_exists('getCategoryColorStyle')) {
        function getCategoryColorStyle($categoryName) {
            $colors = [
                'design' => ['bg' => 'bg-indigo-50 text-indigo-750 border-indigo-200', 'badge' => 'bg-indigo-500', 'icon_color' => 'text-indigo-500'],
                'web' => ['bg' => 'bg-blue-50 text-blue-750 border-blue-200', 'badge' => 'bg-blue-500', 'icon_color' => 'text-blue-500'],
                'branding' => ['bg' => 'bg-purple-50 text-purple-750 border-purple-200', 'badge' => 'bg-purple-500', 'icon_color' => 'text-purple-500'],
                'social' => ['bg' => 'bg-rose-50 text-rose-750 border-rose-200', 'badge' => 'bg-rose-500', 'icon_color' => 'text-rose-500'],
                'video' => ['bg' => 'bg-red-50 text-red-750 border-red-200', 'badge' => 'bg-red-500', 'icon_color' => 'text-red-500'],
                'foto' => ['bg' => 'bg-emerald-50 text-emerald-750 border-emerald-200', 'badge' => 'bg-emerald-500', 'icon_color' => 'text-emerald-500'],
                'marketing' => ['bg' => 'bg-amber-50 text-amber-750 border-amber-200', 'badge' => 'bg-amber-500', 'icon_color' => 'text-amber-500'],
                'default' => ['bg' => 'bg-teal-50 text-teal-750 border-teal-200', 'badge' => 'bg-teal-500', 'icon_color' => 'text-teal-500'],
            ];

            $lower = mb_strtolower($categoryName);
            foreach ($colors as $keyword => $style) {
                if (str_contains($lower, $keyword)) {
                    return $style;
                }
            }
            
            // Deterministic selection based on string hash
            $availableKeys = ['design', 'web', 'branding', 'social', 'video', 'foto', 'marketing', 'default'];
            $hash = crc32($lower);
            $key = $availableKeys[abs($hash) % count($availableKeys)];
            return $colors[$key];
        }
    }
@endphp
<div x-data="portfolioList()" class="space-y-8">
    
    <!-- Top Cards (Métricas) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-5 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-xs font-bold text-blue-100 uppercase tracking-wider">Total de Trabalhos</p>
                <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ $totalCount }}</h3>
                <span class="text-[11px] text-blue-100/90 font-medium block mt-1">Trabalhos cadastrados</span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Publicados (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-5 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Publicados</p>
                <h3 class="text-2xl font-extrabold text-white mt-1.5" x-text="publishedCount"></h3>
                <span class="text-[11px] text-emerald-100/90 font-medium block mt-1">Exibindo no site público</span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
            </div>
        </div>

        <!-- Rascunhos (Card Laranja) -->
        <div class="bg-amber-600 rounded-[5px] p-5 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-xs font-bold text-amber-100 uppercase tracking-wider">Rascunhos</p>
                <h3 class="text-2xl font-extrabold text-white mt-1.5" x-text="draftsCount"></h3>
                <span class="text-[11px] text-amber-100/90 font-medium block mt-1">Aguardando revisão</span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                </svg>
            </div>
        </div>

        <!-- Destaques (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-5 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-xs font-bold text-purple-100 uppercase tracking-wider">Destacados</p>
                <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ $featuredCount }}</h3>
                <span class="text-[11px] text-purple-100/90 font-medium block mt-1">Exibição de destaque</span>
            </div>
            <div class="w-10 h-10 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                </svg>
            </div>
    </div>
</div>

    <!-- Busca e Filtros -->
    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm select-none space-y-4">
        
        <!-- Campo de Busca -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery"
                   @input="currentPage = 1"
                   placeholder="Buscar por título, descrição ou tecnologia..." 
                   class="w-full pl-9 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <button type="button" 
                    x-show="searchQuery" 
                    @click="searchQuery = ''; currentPage = 1;" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-655 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Filtros Rápidos (Categorias e Status) -->
        <div class="space-y-3">
            <!-- Categorias -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Categoria:</span>
                
                <!-- Todas Categorias -->
                <button type="button" 
                        @click="setCategory('')" 
                        class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="!filterCategoryId ? 'background-color: #0f172a; border-color: #0f172a; color: #ffffff;' : 'background-color: #f1f5f9; border-color: #e2e8f0; color: #475569;'">
                    Todas
                </button>

                @foreach($categories as $cat)
                    <!-- Category specific button -->
                    <button type="button" 
                            @click="setCategory('{{ $cat->id }}')" 
                            class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                            :style="filterCategoryId == '{{ $cat->id }}' ? 'background-color: #2563eb; border-color: #2563eb; color: #ffffff;' : 'background-color: #eff6ff; border-color: #dbeafe; color: #1d4ed8;'">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Status -->
            <div class="flex flex-wrap items-center gap-2 text-xs">
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Status:</span>
                
                <!-- Todos Status -->
                <button type="button" 
                        @click="setStatus('')" 
                        class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="!filterStatus ? 'background-color: #0f172a; border-color: #0f172a; color: #ffffff;' : 'background-color: #f1f5f9; border-color: #e2e8f0; color: #475569;'">
                    Todos
                </button>

                <!-- Publicados -->
                <button type="button" 
                        @click="setStatus('publicado')" 
                        class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterStatus === 'publicado' ? 'background-color: #10b981; border-color: #10b981; color: #ffffff;' : 'background-color: #ecfdf5; border-color: #d1fae5; color: #047857;'">
                    Publicados
                </button>

                <!-- Rascunhos -->
                <button type="button" 
                        @click="setStatus('rascunho')" 
                        class="px-3 py-1 rounded-full text-[11px] font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterStatus === 'rascunho' ? 'background-color: #f59e0b; border-color: #f59e0b; color: #ffffff;' : 'background-color: #fffbeb; border-color: #fef3c7; color: #b45309;'">
                    Rascunhos
                </button>
            </div>
        </div>

    </div>

    <!-- Grid de Trabalhos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
            <div x-show="shouldShowItem('{{ addslashes($item->title) }}', '{{ addslashes(strip_tags($item->description)) }}', '{{ addslashes($item->technologies) }}', '{{ $item->category_id }}', '{{ $item->status }}') && isItemOnCurrentPage({{ $item->id }})"
                 class="bg-white border border-slate-200 rounded-[5px] overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow duration-200 relative group"
                 x-transition>
                
                <!-- Thumb / Imagem Principal -->
                <div class="relative aspect-video bg-slate-100 overflow-hidden shrink-0">
                    <!-- Placeholder skeleton que desaparece após carregar a imagem -->
                    <div class="absolute inset-0 bg-slate-200 animate-pulse skeleton-loader"></div>
                    
                    @if($item->thumb_path)
                        <img src="{{ asset('storage/' . $item->thumb_path) }}" 
                             loading="lazy"
                             class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                             onload="this.previousElementSibling.remove()">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-350" onload="this.previousElementSibling.remove()">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif

                    <!-- Badges flutuantes -->
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5 z-20">
                        <div x-data="{ currentStatus: '{{ $item->status }}', isLoading: false }">
                            <select @change="updateStatus('{{ $item->id }}', $event.target.value, $data)"
                                    :disabled="isLoading"
                                    :class="{
                                        'bg-emerald-600 text-white border-emerald-500 hover:bg-emerald-700': currentStatus === 'publicado',
                                        'bg-amber-500 text-white border-amber-400 hover:bg-amber-600': currentStatus === 'rascunho',
                                        'opacity-60 cursor-wait': isLoading
                                    }"
                                    class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border shadow-sm cursor-pointer focus:outline-none transition-all appearance-none pr-4 relative">
                                <option value="publicado" class="bg-slate-900 text-emerald-400 font-bold" :selected="currentStatus === 'publicado'">● Publicado</option>
                                <option value="rascunho" class="bg-slate-900 text-amber-400 font-bold" :selected="currentStatus === 'rascunho'">● Rascunho</option>
                            </select>
                        </div>
                        @if($item->is_featured)
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border bg-purple-100 text-purple-800 border-purple-200 flex items-center gap-1.5 shadow-sm">
                                <span>★ Destaque</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Detalhes do Conteúdo -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2.5">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            @php
                                $style = getCategoryColorStyle($item->category->name);
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-[5px] text-[9px] font-bold uppercase tracking-wider border {{ $style['bg'] }}">
                                <span class="w-1 h-1 rounded-full {{ $style['badge'] }}"></span>
                                {{ $item->category->name }}
                            </span>
                            @if($item->client)
                                <span class="text-xs text-slate-500 font-medium truncate max-w-[120px]">
                                    Cliente: <strong>{{ $item->client->name }}</strong>
                                </span>
                            @endif
                        </div>
                        <h4 class="font-extrabold text-slate-900 text-base line-clamp-1" title="{{ $item->title }}">
                            {{ $item->title }}
                        </h4>
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed font-normal">
                            {{ strip_tags($item->description) }}
                        </p>
                    </div>

                    <!-- Tecnologias, Autores & Data -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        <div class="flex flex-wrap items-center justify-between gap-2 text-[10px] text-slate-400 font-semibold">
                            <span>Criado em: <strong class="text-slate-650">{{ $item->created_at->format('d/m/Y') }}</strong></span>
                        </div>

                        @if($item->technologies)
                            <div class="flex flex-wrap gap-1 items-center">
                                @foreach(explode(',', $item->technologies) as $tech)
                                    <span class="bg-slate-100 text-slate-650 text-[10px] font-bold px-2 py-0.5 rounded-[4px] border border-slate-200">
                                        {{ trim($tech) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if($item->authors->count() > 0)
                            <div class="space-y-1">
                                <span class="text-[9px] font-semibold text-slate-400 uppercase tracking-wider block">Autores</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($item->authors as $author)
                                        <span class="bg-slate-50 border border-slate-200/50 text-slate-650 px-2 py-0.5 rounded-[4px] text-[9px] font-bold uppercase tracking-wider inline-block">
                                            {{ $author->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Botões de Ações -->
                        <div class="flex flex-wrap items-center justify-between gap-2 pt-3 border-t border-slate-100 mt-2">
                            <!-- Visualizações & Likes (Pre-implementado) -->
                            <div class="flex items-center gap-3 text-slate-400 text-[11px] font-bold shrink-0 select-none">
                                <div class="flex items-center gap-1.5" title="{{ $item->views }} visualizações">
                                    <svg class="w-4 h-4 text-slate-450" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span>{{ $item->views }}</span>
                                </div>
                                <div class="flex items-center gap-1.5" title="{{ $item->likes }} curtidas">
                                    <svg class="w-3.5 h-3.5 text-rose-500 fill-rose-500" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span class="text-slate-500">{{ $item->likes }}</span>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <!-- Visualizar -->
                                <a href="{{ route('portfolio.show', $item->id) }}" 
                                   class="w-8 h-8 flex items-center justify-center text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-colors"
                                   title="Visualizar Trabalho">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </a>

                                <!-- Editar -->
                                <a href="{{ route('portfolio.edit', $item->id) }}" 
                                   class="w-8 h-8 flex items-center justify-center text-primary-650 hover:bg-primary-50 rounded-[5px] transition-colors"
                                   title="Editar Trabalho">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>

                                <!-- Excluir -->
                                <button type="button" 
                                        @click="$dispatch('trigger-global-delete', { 
                                            title: 'Excluir Trabalho do Portfólio', 
                                            message: 'Tem certeza de que deseja excluir o trabalho <strong class=\'text-slate-800\'>{{ addslashes($item->title) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1 block\'>Aviso: Esta ação excluirá permanentemente o registro e todas as fotos da galeria.</span>', 
                                            action: '{{ route('portfolio.destroy', $item->id) }}', 
                                            highSecurity: false 
                                        })" 
                                        class="w-8 h-8 flex items-center justify-center text-red-600 hover:bg-red-50 rounded-[5px] transition-colors"
                                        title="Excluir Trabalho">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>                 </div>
                    </div>
                </div>

            @empty
            <div class="col-span-full border-2 border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] text-sm">
                Nenhum trabalho de portfólio cadastrado ainda.
            </div>
        @endforelse
    </div>

    <!-- Estado de Filtro Vazio (Client-side) -->
    <div x-show="totalFilteredCount === 0 && itemsList.length > 0" 
         class="text-center py-12 bg-white border border-slate-200 rounded-[5px] shadow-sm select-none" 
         x-cloak>
        <span class="text-5xl block">🔍</span>
        <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight mt-4">Nenhum trabalho corresponde à busca</h3>
        <p class="text-xs text-slate-400 mt-1">Experimente limpar a sua busca ou trocar as tags selecionadas.</p>
    </div>

    <!-- Painel de Paginação Dinâmica (Sem Reload) -->
    <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-6 mt-4" x-cloak>
        <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
            Mostrando <span class="text-slate-800 dark:text-slate-200" x-text="Math.min((currentPage - 1) * perPage + 1, totalFilteredCount)"></span> a 
            <span class="text-slate-800 dark:text-slate-200" x-text="Math.min(currentPage * perPage, totalFilteredCount)"></span> de 
            <span class="text-slate-800 dark:text-slate-200" x-text="totalFilteredCount"></span> trabalhos
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

    <!-- Botão Flutuante Redondo (FAB) -->
    <a href="{{ route('portfolio.create') }}" 
       class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" 
       title="Novo Trabalho">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function portfolioList() {
        return {
            itemsList: @json($items),
            publishedCount: {{ $publishedCount }},
            draftsCount: {{ $draftsCount }},
            searchQuery: '{{ request('search', '') }}',
            filterCategoryId: '{{ request('category_id', '') }}',
            filterStatus: '{{ request('status', '') }}',
            currentPage: 1,
            perPage: 12,

            async updateStatus(itemId, newStatus, cardData) {
                cardData.isLoading = true;
                try {
                    const response = await fetch(`/freelas/portfolio/${itemId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });

                    const data = await response.json();
                    if (data.success) {
                        cardData.currentStatus = newStatus;
                        
                        // Atualiza status no array de items local para refletir nos filtros
                        const item = this.itemsList.find(i => i.id == itemId);
                        if (item) {
                            item.status = newStatus;
                        }

                        // Atualiza as métricas dos cards topo
                        if (data.publishedCount !== undefined) this.publishedCount = data.publishedCount;
                        if (data.draftsCount !== undefined) this.draftsCount = data.draftsCount;

                        // Toast simples se Alpine/Notify estiver global
                        if (window.Alpine && window.Alpine.store('toast')) {
                            window.Alpine.store('toast').show(data.message, 'success');
                        }
                    } else {
                        alert(data.message || 'Erro ao atualizar status.');
                    }
                } catch (e) {
                    console.error(e);
                    alert('Erro de conexão ao alterar status.');
                } finally {
                    cardData.isLoading = false;
                }
            },

            setCategory(id) {
                this.filterCategoryId = id;
                this.currentPage = 1;
            },

            setStatus(status) {
                this.filterStatus = status;
                this.currentPage = 1;
            },

            get totalFilteredCount() {
                return this.filteredItems.length;
            },

            get filteredItems() {
                return this.itemsList.filter(item => {
                    return this.shouldShowItem(item.title, item.description, item.technologies, item.portfolio_category_id, item.status);
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

            isItemOnCurrentPage(itemId) {
                const index = this.filteredItems.findIndex(i => i.id === itemId);
                if (index === -1) return false;
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return index >= start && index < end;
            },

            shouldShowItem(title, description, technologies, categoryId, status) {
                if (this.filterCategoryId && categoryId != this.filterCategoryId) return false;
                if (this.filterStatus && status !== this.filterStatus) return false;
                
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const t = (title || '').toLowerCase();
                    const d = (description || '').toLowerCase();
                    const tech = (technologies || '').toLowerCase();
                    return t.includes(q) || d.includes(q) || tech.includes(q);
                }
                return true;
            }
        }
    }
</script>
@endsection
