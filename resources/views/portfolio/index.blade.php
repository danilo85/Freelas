@extends('layouts.app')

@section('title', 'Portfólio - Gestor de Freelas')
@section('page_title', 'Administração de Portfólio')

@section('content')
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
                <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ $publishedCount }}</h3>
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
                <h3 class="text-2xl font-extrabold text-white mt-1.5">{{ $draftsCount }}</h3>
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

    <!-- Filtros e Busca -->
    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        <form action="{{ route('portfolio.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-4 items-center">
            <!-- Campo de Busca -->
            <div class="relative sm:col-span-6 w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Buscar por título, descrição ou tecnologia..." 
                       class="w-full pl-9 pr-4 py-2 rounded-[5px] border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            </div>

            <!-- Categoria Select -->
            <div class="sm:col-span-3 w-full">
                <select name="category_id" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 rounded-[5px] border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white text-slate-700">
                    <option value="">Todas as Categorias</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Select -->
            <div class="sm:col-span-2 w-full">
                <select name="status" 
                        onchange="this.form.submit()"
                        class="w-full px-3 py-2 rounded-[5px] border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white text-slate-700">
                    <option value="">Todos os Status</option>
                    <option value="publicado" {{ request('status') === 'publicado' ? 'selected' : '' }}>Publicado</option>
                    <option value="rascunho" {{ request('status') === 'rascunho' ? 'selected' : '' }}>Rascunho</option>
                </select>
            </div>

            <!-- Limpar Filtros -->
            <div class="sm:col-span-1 w-full text-right">
                <a href="{{ route('portfolio.index') }}" 
                   class="w-full inline-flex items-center justify-center py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-650 text-xs font-semibold rounded-[5px] transition-colors shadow-sm text-center">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Grid de Trabalhos -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
            <div class="bg-white border border-slate-200 rounded-[5px] overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-md transition-shadow duration-200 relative group">
                
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
                    <div class="absolute top-3 left-3 flex flex-col gap-1.5">
                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border 
                            {{ $item->status === 'publicado' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                            {{ $item->status }}
                        </span>
                        @if($item->is_featured)
                            <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border bg-purple-100 text-purple-800 border-purple-200 flex items-center gap-1.5 shadow-sm">
                                <span>★ Destaque</span>
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Detalhes do Conteúdo -->
                <div class="p-5 flex-1 flex flex-col justify-between space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">
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
                        <p class="text-xs text-slate-500 line-clamp-2 leading-relaxed">
                            {{ strip_tags($item->description) }}
                        </p>
                    </div>

                    <!-- Tecnologias & Autores -->
                    <div class="space-y-3 pt-3 border-t border-slate-100">
                        @if($item->technologies)
                            <div class="flex flex-wrap gap-1 items-center">
                                @foreach(explode(',', $item->technologies) as $tech)
                                    <span class="bg-slate-100 text-slate-600 text-[10px] font-bold px-2 py-0.5 rounded-[4px] border border-slate-200">
                                        {{ trim($tech) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <!-- Botões de Ações -->
                        <div class="flex items-center justify-end gap-1.5 pt-2">
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
                    </div>
                </div>

            </div>
        @empty
            <div class="col-span-full border-2 border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] text-sm">
                Nenhum trabalho de portfólio cadastrado ainda.
            </div>
        @endforelse
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
        return {}
    }
</script>
@endsection
