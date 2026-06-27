@extends('layouts.app')

@section('title', 'Editar Trabalho - Gestor de Freelas')
@section('page_title', 'Editar Trabalho')

@section('content')
<div class="space-y-6" x-data="portfolioForm()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Abas / Tabs de Navegação -->
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6">
            <button type="button" @click="activeTab = 'geral'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'geral' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                1. Dados Gerais
            </button>
            <button type="button" @click="activeTab = 'galeria'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'galeria' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                2. Galeria & Imagens
            </button>
            <button type="button" @click="activeTab = 'seo'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'seo' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                3. SEO & IA
            </button>
            <button type="button" @click="activeTab = 'revisao'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'revisao' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                4. Revisão Geral
            </button>
        </nav>
    </div>

    <!-- Formulário Principal -->
    <form action="{{ route('portfolio.update', $portfolio->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- ABA 1: DADOS GERAIS -->
        <div x-show="activeTab === 'geral'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6">
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Título do Trabalho -->
                <div class="space-y-1 sm:col-span-2">
                    <label for="title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Título do Trabalho <span class="text-red-500">*</span></label>
                    <input type="text" 
                           id="title" 
                           name="title" 
                           x-model="title"
                           required 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Categoria -->
                <div class="space-y-1">
                    <label for="portfolio_category_id" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoria de Trabalho <span class="text-red-500">*</span></label>
                    <select id="portfolio_category_id" 
                            name="portfolio_category_id" 
                            x-model="categoryId"
                            required
                            class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white text-slate-700">
                        <option value="">Selecione uma categoria...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Cliente -->
                <div class="space-y-1">
                    <label for="client_id" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente (Opcional)</label>
                    <select id="client_id" 
                            name="client_id" 
                            x-model="clientId"
                            class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white text-slate-700">
                        <option value="">Nenhum cliente associado</option>
                        @foreach($clients as $client)
                            <option value="{{ $client->id }}">{{ $client->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Descrição -->
                <div class="space-y-1 sm:col-span-2">
                    <label for="description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição do Trabalho <span class="text-red-500">*</span></label>
                    <textarea id="description" 
                              name="description" 
                              rows="6"
                              x-model="description"
                              required 
                              class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"></textarea>
                </div>

                <!-- Tecnologias utilizadas -->
                <div class="space-y-1">
                    <label for="technologies" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tecnologias Utilizadas (Opcional)</label>
                    <input type="text" 
                           id="technologies" 
                           name="technologies" 
                           x-model="technologies"
                           placeholder="Ex: Illustrator, Photoshop, Laravel, Vue (separados por vírgula)" 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- URL de direcionamento -->
                <div class="space-y-1">
                    <label for="redirect_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Link do Trabalho/Visualização (Opcional)</label>
                    <input type="url" 
                           id="redirect_url" 
                           name="redirect_url" 
                           x-model="redirectUrl"
                           placeholder="Ex: https://meutrabalho.com" 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Status de publicação -->
                <div class="space-y-1">
                    <label for="status" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status do Trabalho <span class="text-red-500">*</span></label>
                    <select id="status" 
                            name="status" 
                            x-model="status"
                            required
                            class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white text-slate-700">
                        <option value="rascunho">Rascunho (Salvar Internamente)</option>
                        <option value="publicado">Publicado (Exibir Publicamente)</option>
                    </select>
                </div>

                <!-- Destacar Trabalho -->
                <div class="flex items-center space-x-3 pt-6">
                    <input type="checkbox" 
                           id="is_featured" 
                           name="is_featured" 
                           value="1"
                           x-model="isFeatured"
                           class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                    <label for="is_featured" class="text-sm font-semibold text-slate-700 select-none cursor-pointer">
                        Destacar este trabalho na página principal do portfólio
                    </label>
                </div>
            </div>

            <!-- Autores -->
            <div class="space-y-3 pt-4 border-t border-slate-100">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Autores do Trabalho (Citá-los é importante)</span>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach($authors as $author)
                        <label class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-150 rounded-[5px] cursor-pointer hover:bg-slate-100/70 transition-colors select-none">
                            <input type="checkbox" 
                                   name="authors[]" 
                                   value="{{ $author->id }}"
                                   x-model="selectedAuthors"
                                   class="w-4 h-4 text-primary-600 border-slate-300 rounded focus:ring-primary-500">
                            <div class="min-w-0">
                                <span class="text-xs font-bold text-slate-800 block truncate">{{ $author->name }}</span>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $author->email }}</span>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Navegação -->
            <div class="flex justify-end pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'galeria'" 
                        class="py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    Ir para Galeria
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

        </div>

        <!-- ABA 2: GALERIA & IMAGENS -->
        <div x-show="activeTab === 'galeria'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6" x-cloak>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                
                <!-- Coluna Esquerda: Thumbnail / Capa -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Imagem de Capa (Thumbnail)</h5>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full aspect-video border-2 border-dashed border-slate-200 hover:border-primary-500 rounded-[5px] cursor-pointer bg-slate-50 hover:bg-slate-100/50 transition-colors relative overflow-hidden">
                                @if($portfolio->thumb_path)
                                    <img :src="thumbPreview || '{{ asset('storage/' . $portfolio->thumb_path) }}'" class="absolute inset-0 w-full h-full object-cover">
                                @else
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" x-show="!thumbPreview">
                                        <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-xs font-bold text-slate-700">Alterar Capa</p>
                                    </div>
                                    <template x-if="thumbPreview">
                                        <img :src="thumbPreview" class="absolute inset-0 w-full h-full object-cover">
                                    </template>
                                @endif
                                <input type="file" name="thumb" class="hidden" @change="handleThumbUpload($event)">
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-400">
                            Deixe vazio se não quiser alterar a imagem de capa atual.
                        </p>
                    </div>
                </div>

                <!-- Coluna Direita: Galeria de Fotos (Existentes + Novas) -->
                <div class="space-y-6">
                    <!-- Imagens Existentes na Galeria -->
                    @if($portfolio->images->count() > 0)
                        <div class="space-y-3">
                            <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-1.5">Fotos Salvas Atualmente</h6>
                            
                            <div class="space-y-2.5 max-h-[220px] overflow-y-auto pr-1">
                                @foreach($portfolio->images as $img)
                                    <div class="flex items-center justify-between gap-3 p-2 bg-slate-50 border border-slate-150 rounded-[5px]">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <div class="w-10 h-10 rounded bg-slate-200 overflow-hidden shrink-0">
                                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover">
                                            </div>
                                            <span class="text-xs text-slate-500 font-mono truncate max-w-[120px]">{{ basename($img->image_path) }}</span>
                                        </div>
                                        
                                        <!-- Controle de Ordem e Deletar -->
                                        <div class="flex items-center gap-3 shrink-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="text-[10px] text-slate-400 font-semibold uppercase">Ordem:</span>
                                                <input type="number" 
                                                       name="existing_gallery_orders[{{ $img->id }}]" 
                                                       value="{{ $img->order }}"
                                                       class="w-12 px-1.5 py-1 rounded border border-slate-200 text-xs font-bold text-center focus:outline-none focus:ring-1 focus:ring-primary-500">
                                            </div>
                                            
                                            <!-- Checkbox para deletar -->
                                            <label class="flex items-center gap-1 cursor-pointer select-none text-red-500 hover:text-red-700">
                                                <input type="checkbox" 
                                                       name="delete_images[]" 
                                                       value="{{ $img->id }}"
                                                       class="w-3.5 h-3.5 text-red-600 border-slate-300 rounded focus:ring-red-500">
                                                <span class="text-[10px] font-bold uppercase">Excluir</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Novas Fotos para Adicionar -->
                    <div class="space-y-4 pt-2">
                        <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider border-b border-slate-100 pb-1.5">Adicionar Novas Fotos</h6>
                        
                        <div class="space-y-3">
                            <div class="flex items-center justify-center w-full">
                                <label class="flex items-center justify-center gap-2 w-full py-3 border border-dashed border-slate-200 rounded-[5px] cursor-pointer bg-slate-50 hover:bg-slate-100/50 hover:border-primary-500 transition-colors text-xs font-bold text-slate-650">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Adicionar Fotos à Galeria
                                    <input type="file" name="gallery[]" multiple class="hidden" @change="handleGalleryUpload($event)">
                                </label>
                            </div>

                            <!-- Lista de novas fotos com controle de ordem -->
                            <template x-if="galleryFiles.length > 0">
                                <div class="space-y-2 max-h-[180px] overflow-y-auto pr-1">
                                    <template x-for="(item, index) in galleryFiles" :key="index">
                                        <div class="flex items-center justify-between gap-3 p-2 bg-slate-50 border border-slate-150 rounded-[5px]">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-10 h-10 rounded bg-slate-200 overflow-hidden shrink-0">
                                                    <img :src="item.url" class="w-full h-full object-cover">
                                                </div>
                                                <span class="text-xs text-slate-700 font-medium truncate max-w-[120px]" x-text="item.name"></span>
                                            </div>
                                            
                                            <!-- Ordem -->
                                            <div class="flex items-center gap-3 shrink-0">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="text-[10px] text-slate-400 font-semibold uppercase">Ordem:</span>
                                                    <input type="number" 
                                                           :name="'gallery_orders[' + index + ']'" 
                                                           x-model="item.order"
                                                           class="w-12 px-1.5 py-1 rounded border border-slate-200 text-xs font-bold text-center focus:outline-none focus:ring-1 focus:ring-primary-500">
                                                </div>
                                                <button type="button" @click="removeGalleryFile(index)" class="text-red-500 hover:text-red-700 p-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegação -->
            <div class="flex justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'geral'" 
                        class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para Geral
                </button>

                <button type="button" @click="activeTab = 'seo'" 
                        class="py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    Ir para SEO & IA
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

        </div>

        <!-- ABA 3: SEO & IA -->
        <div x-show="activeTab === 'seo'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6" x-cloak>
            
            <!-- Caixa IA -->
            <div class="bg-gradient-to-r from-violet-50 to-indigo-50 border border-violet-100 p-5 rounded-[5px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-full bg-violet-600 text-white flex items-center justify-center shrink-0 shadow-sm animate-pulse">
                        ✨
                    </div>
                    <div>
                        <h4 class="font-extrabold text-violet-950 text-sm">Assistente de Copywriting & SEO com IA</h4>
                        <p class="text-xs text-violet-800 mt-1 font-normal leading-relaxed">Gere tags meta (Title, Meta Description, Meta Keywords) otimizadas para busca de forma automática baseada no título e descrição do trabalho.</p>
                    </div>
                </div>
                
                <button type="button" 
                        @click="generateSEO()"
                        class="py-2 px-3.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm whitespace-nowrap"
                        :class="aiLoading ? 'opacity-70 cursor-not-allowed' : ''"
                        :disabled="aiLoading">
                    <span x-show="aiLoading" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span x-show="!aiLoading">✨ Otimizar SEO</span>
                </button>
            </div>

            <!-- Campos SEO -->
            <div class="grid grid-cols-1 gap-6 text-sm">
                <!-- Meta Title -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="meta_title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Meta Title (Título de Busca)</label>
                        <span class="text-[10px] text-slate-400 font-semibold" x-text="metaTitle.length + ' / 60 car.'"></span>
                    </div>
                    <input type="text" 
                           id="meta_title" 
                           name="meta_title" 
                           x-model="metaTitle"
                           maxlength="60"
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Meta Description -->
                <div class="space-y-1">
                    <div class="flex items-center justify-between">
                        <label for="meta_description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Meta Description (Resumo para Google)</label>
                        <span class="text-[10px] text-slate-400 font-semibold" x-text="metaDescription.length + ' / 160 car.'"></span>
                    </div>
                    <textarea id="meta_description" 
                              name="meta_description" 
                              rows="3"
                              x-model="metaDescription"
                              maxlength="160"
                              class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"></textarea>
                </div>

                <!-- Meta Keywords -->
                <div class="space-y-1">
                    <label for="meta_keywords" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Meta Keywords (Palavras-chave separadas por vírgula)</label>
                    <input type="text" 
                           id="meta_keywords" 
                           name="meta_keywords" 
                           x-model="metaKeywords"
                           placeholder="Ex: design grafico, desenvolvimento laravel, portfolio..." 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>
            </div>

            <!-- Navegação -->
            <div class="flex justify-between pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'galeria'" 
                        class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para Galeria
                </button>

                <button type="button" @click="activeTab = 'revisao'" 
                        class="py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    Ir para Revisão Geral
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

        </div>

        <!-- ABA 4: REVISÃO GERAL -->
        <div x-show="activeTab === 'revisao'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6" x-cloak>
            
            <div class="border-l-4 border-emerald-500 bg-emerald-50/50 p-4 rounded-r-[5px] text-sm text-emerald-800 leading-relaxed font-normal">
                <strong class="font-bold block">Pronto para salvar!</strong>
                Revise os detalhes abaixo para garantir que todas as alterações do trabalho estão corretas antes de salvar.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm divide-y md:divide-y-0 md:divide-x divide-slate-100">
                
                <!-- Resumo Geral -->
                <div class="space-y-4 pr-0 md:pr-6">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">Resumo do Trabalho</h5>
                    
                    <div class="space-y-2.5 text-slate-600 leading-relaxed">
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Título:</span>
                            <strong class="text-slate-800" x-text="title || 'Não informado'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Categoria:</span>
                            <span class="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded font-bold" x-text="getCategoryName()"></span>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Cliente:</span>
                            <strong class="text-slate-800" x-text="getClientName()"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Tecnologias:</span>
                            <strong class="text-slate-800" x-text="technologies || 'Não informada'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Link de Direcionamento:</span>
                            <strong class="text-slate-800" x-text="redirectUrl || 'Não informado'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Destaque do Portfólio:</span>
                            <strong class="text-slate-800" x-text="isFeatured ? 'Sim' : 'Não'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Status de Publicação:</span>
                            <span class="px-2 py-0.5 rounded font-bold text-xs uppercase" 
                                  :class="status === 'publicado' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                  x-text="status"></span>
                        </p>
                    </div>
                </div>

                <!-- Resumo Galeria & SEO -->
                <div class="space-y-4 pt-6 md:pt-0 pl-0 md:pl-6">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100">Galeria & Metatags</h5>
                    
                    <div class="space-y-3 text-slate-600">
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Imagem de Capa (Thumb):</span>
                            <strong class="text-emerald-600">Salva</strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Novas Fotos:</span>
                            <strong class="text-slate-800" x-text="galleryFiles.length + ' novas fotos'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Meta Title (SEO):</span>
                            <strong class="text-slate-800 truncate max-w-[200px]" x-text="metaTitle || 'Não configurado'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Meta Description:</span>
                            <strong class="text-slate-800 truncate max-w-[200px]" x-text="metaDescription || 'Não configurado'"></strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Navegação e Submissão -->
            <div class="flex justify-between pt-6 border-t border-slate-100">
                <button type="button" @click="activeTab = 'seo'" 
                        class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para SEO
                </button>

                <button type="submit" 
                        class="py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Alterações do Trabalho
                </button>
            </div>

        </div>

    </form>
</div>

<script>
    function portfolioForm() {
        return {
            activeTab: 'geral',
            title: '{{ addslashes($portfolio->title) }}',
            categoryId: '{{ $portfolio->portfolio_category_id }}',
            clientId: '{{ $portfolio->client_id ?? "" }}',
            description: `{!! addslashes($portfolio->description) !!}`,
            technologies: '{{ addslashes($portfolio->technologies) }}',
            redirectUrl: '{{ addslashes($portfolio->redirect_url) }}',
            status: '{{ $portfolio->status }}',
            isFeatured: {{ $portfolio->is_featured ? 'true' : 'false' }},
            selectedAuthors: {!! json_encode($portfolio->authors->pluck('id')->toArray()) !!},
            
            // Imagens
            thumbPreview: '',
            galleryFiles: [],
            
            // SEO
            metaTitle: '{{ addslashes($portfolio->meta_title) }}',
            metaDescription: '{{ addslashes($portfolio->meta_description) }}',
            metaKeywords: '{{ addslashes($portfolio->meta_keywords) }}',
            
            // AI simulated loading
            aiLoading: false,

            handleThumbUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    this.thumbPreview = URL.createObjectURL(file);
                }
            },

            handleGalleryUpload(event) {
                const files = event.target.files;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    this.galleryFiles.push({
                        file: file,
                        name: file.name,
                        url: URL.createObjectURL(file),
                        order: this.galleryFiles.length + 1
                    });
                }
            },

            removeGalleryFile(index) {
                this.galleryFiles.splice(index, 1);
                // Reordena
                this.galleryFiles.forEach((item, idx) => {
                    item.order = idx + 1;
                });
            },

            generateSEO() {
                if (!this.title) {
                    alert('Insira o título do trabalho primeiro.');
                    return;
                }
                
                this.aiLoading = true;
                
                setTimeout(() => {
                    this.metaTitle = (this.title + ' | Portfólio').substring(0, 60);
                    let descText = this.description.replace(/(<([^>]+)>)/gi, "").trim();
                    this.metaDescription = descText.substring(0, 155) + '...';
                    
                    let keys = ['portfolio', 'trabalho', 'design', 'freelancer'];
                    if (this.technologies) {
                        this.technologies.split(',').forEach(t => {
                            if(t.trim()) keys.push(t.trim().toLowerCase());
                        });
                    }
                    this.metaKeywords = keys.slice(0, 8).join(', ');
                    
                    this.aiLoading = false;
                }, 1000);
            },

            getCategoryName() {
                const select = document.getElementById('portfolio_category_id');
                if (select && select.selectedIndex > 0) {
                    return select.options[select.selectedIndex].text;
                }
                return 'Não informada';
            },

            getClientName() {
                const select = document.getElementById('client_id');
                if (select && select.selectedIndex > 0) {
                    return select.options[select.selectedIndex].text;
                }
                return 'Nenhum';
            }
        }
    }
</script>
@endsection
