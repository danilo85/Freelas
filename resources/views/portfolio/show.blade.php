@extends('layouts.app')

@section('title', 'Visualizar Trabalho - Gestor de Freelas')
@section('page_title', 'Visualizar Trabalho')

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
<div class="space-y-6">
    
    <!-- Link de Voltar & Ações -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>

        <div class="flex items-center gap-2">
            <!-- Editar -->
            <a href="{{ route('portfolio.edit', $portfolio->id) }}" class="flex items-center justify-center gap-2.5 py-2 px-4 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Editar Trabalho
            </a>

            <!-- Excluir -->
            <button type="button" @click="$dispatch('trigger-global-delete', { 
                title: 'Excluir Trabalho do Portfólio', 
                message: 'Tem certeza de que deseja excluir o trabalho <strong class=\'text-slate-850\'>{{ addslashes($portfolio->title) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1 block\'>Aviso: Esta ação é definitiva e removerá as fotos salvas.</span>', 
                action: '{{ route('portfolio.destroy', $portfolio->id) }}', 
                highSecurity: false 
            })" class="flex items-center justify-center gap-2.5 py-2 px-4 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                Excluir Trabalho
            </button>
        </div>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Bloco Imagens & Galeria (Esquerda - 2 Colunas) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Imagem de Capa (Thumb) -->
            <div class="bg-white border border-slate-200 rounded-[5px] overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Imagem de Capa (Thumbnail)</h5>
                    <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border 
                        {{ $portfolio->status === 'publicado' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200' }}">
                        {{ $portfolio->status }}
                    </span>
                </div>
                
                <div class="relative aspect-video bg-slate-100 overflow-hidden">
                    <div class="absolute inset-0 bg-slate-200 animate-pulse skeleton-loader"></div>
                    @if($portfolio->thumb_path)
                        <img src="{{ asset('storage/' . $portfolio->thumb_path) }}" 
                             class="w-full h-full object-cover"
                             loading="lazy"
                             onload="this.previousElementSibling.remove()">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-slate-350" onload="this.previousElementSibling.remove()">
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Galeria de Fotos Extras -->
            @if($portfolio->images->count() > 0)
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Galeria de Imagens ({{ $portfolio->images->count() }})</h5>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($portfolio->images as $img)
                            <div class="relative aspect-video bg-slate-50 border border-slate-150 rounded-[5px] overflow-hidden group">
                                <div class="absolute inset-0 bg-slate-200 animate-pulse skeleton-loader"></div>
                                <img src="{{ asset('storage/' . $img->image_path) }}" 
                                     class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                     loading="lazy"
                                     onload="this.previousElementSibling.remove()">
                                <div class="absolute bottom-2 right-2 bg-slate-900/60 text-white text-[9px] px-1.5 py-0.5 rounded font-mono font-bold">
                                    Seq. #{{ $img->order }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        <!-- Bloco Informações do Trabalho (Direita - 1 Coluna) -->
        <div class="space-y-6">
            
            <!-- Ficha Técnica -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Ficha Técnica</h5>
                
                <div class="space-y-3.5 text-xs">
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Título do Trabalho</span>
                        <span class="text-slate-900 font-extrabold text-sm block leading-tight">{{ $portfolio->title }}</span>
                    </div>

                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block mb-1">Categoria</span>
                        @php
                            $style = getCategoryColorStyle($portfolio->category->name);
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-[5px] text-[10px] font-bold uppercase tracking-wider border {{ $style['bg'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $style['badge'] }}"></span>
                            {{ $portfolio->category->name }}
                        </span>
                    </div>

                    <!-- Métricas Pré-implementadas -->
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-[5px] flex items-center justify-between">
                            <div>
                                <span class="text-slate-400 font-bold uppercase tracking-wider block text-[9px]">Visualizações</span>
                                <span class="text-slate-800 font-extrabold text-sm block mt-0.5">{{ $portfolio->views }}</span>
                            </div>
                            <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 p-2.5 rounded-[5px] flex items-center justify-between">
                            <div>
                                <span class="text-slate-400 font-bold uppercase tracking-wider block text-[9px]">Curtidas</span>
                                <span class="text-slate-800 font-extrabold text-sm block mt-0.5">{{ $portfolio->likes }}</span>
                            </div>
                            <svg class="w-4 h-4 text-rose-550 fill-rose-500" viewBox="0 0 24 24">
                                <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                            </svg>
                        </div>
                    </div>

                    @if($portfolio->client)
                        <div class="space-y-1">
                            <span class="text-slate-400 font-semibold uppercase tracking-wider block">Cliente Associado</span>
                            <span class="text-slate-850 font-bold block">{{ $portfolio->client->name }}</span>
                        </div>
                    @endif

                    @if($portfolio->technologies)
                        <div class="space-y-1.5">
                            <span class="text-slate-400 font-semibold uppercase tracking-wider block">Tecnologias Utilizadas</span>
                            <div class="flex flex-wrap gap-1">
                                @foreach(explode(',', $portfolio->technologies) as $tech)
                                    <span class="bg-slate-50 text-slate-650 text-[10px] font-bold px-2 py-0.5 rounded border border-slate-200">{{ trim($tech) }}</span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if($portfolio->redirect_url)
                        <div class="pt-2">
                            <a href="{{ $portfolio->redirect_url }}" target="_blank" 
                               class="flex items-center justify-center gap-2.5 w-full py-2 px-4 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-[5px] transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Acessar Trabalho Online
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Autores Creditados -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Autores Creditados</h5>
                
                @if($portfolio->authors->count() > 0)
                    <div class="space-y-3">
                        @foreach($portfolio->authors as $author)
                            <div class="flex items-center gap-3 p-2 bg-slate-50 border border-slate-150 rounded-[5px]">
                                <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                                    @if($author->avatar)
                                        <img src="{{ asset('storage/' . $author->avatar) }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-full h-full text-slate-350 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h6 class="font-bold text-slate-800 text-xs truncate">{{ $author->name }}</h6>
                                    <span class="text-[10px] text-slate-400 block truncate">{{ $author->email }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-400 text-xs italic">Nenhum autor creditado neste trabalho.</span>
                @endif
            </div>

            <!-- SEO Meta Tags -->
            @if($portfolio->meta_title || $portfolio->meta_description)
                <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Visualização SEO (Google)</h5>
                    
                    <div class="bg-slate-50 border border-slate-100 p-4 rounded-[5px] text-xs space-y-2.5 font-normal leading-relaxed">
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[9px] mb-0.5">Meta Title</span>
                            <span class="text-blue-700 font-semibold block text-sm leading-tight hover:underline cursor-pointer">{{ $portfolio->meta_title ?? $portfolio->title }}</span>
                        </div>
                        <div>
                            <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[9px] mb-0.5">Meta Description</span>
                            <p class="text-slate-600 block text-xs leading-normal">{{ $portfolio->meta_description ?? 'Nenhuma meta description configurada.' }}</p>
                        </div>
                        @if($portfolio->meta_keywords)
                            <div>
                                <span class="text-slate-400 font-semibold block uppercase tracking-wider text-[9px] mb-0.5">Palavras-Chave</span>
                                <span class="text-slate-500 font-mono text-[10px]">{{ $portfolio->meta_keywords }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Descrição Completa -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Descrição Completa</h5>
                <div class="text-slate-700 text-xs leading-relaxed font-normal text-justify whitespace-pre-wrap break-words">
                    {!! $portfolio->description !!}
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
