@extends('layouts.app')

@section('title', 'Novo Trabalho de Portfólio - Gestor de Freelas')
@section('page_title', 'Cadastrar Trabalho')

@section('content')
<div class="space-y-6" x-data="portfolioForm()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Cancelar e Voltar
        </a>
    </div>

    <!-- Abas / Tabs de Navegação -->
    <div class="border-b border-slate-200 overflow-x-auto whitespace-nowrap scrollbar-none pb-1">
        <nav class="flex space-x-6 min-w-max">
            <button type="button" @click="activeTab = 'geral'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'geral' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                1. Dados Gerais
            </button>
            <button type="button" @click="activeTab = 'galeria'" 
                    class="pb-4 text-sm font-bold border-b-2 transition-all"
                    :class="activeTab === 'galeria' ? 'border-primary-600 text-primary-650' : 'border-transparent text-slate-400 hover:text-slate-600'">
                2. Imagens & Galeria
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

    <!-- Erros de Validação -->
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 p-4 rounded-[5px] text-sm space-y-1.5 shadow-sm">
            <div class="flex items-center gap-2 font-bold">
                <svg class="w-4 h-4 text-red-650" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                Por favor, verifique os campos obrigatórios:
            </div>
            <ul class="list-disc list-inside text-xs font-normal space-y-0.5 text-red-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Formulário Principal -->
    <form action="{{ route('portfolio.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit="syncGalleryInput($event)">
        @csrf

        @if($projectData)
            <input type="hidden" name="project_id" value="{{ $projectData['id'] }}">
        @endif

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
                           placeholder="Ex: Identidade Visual Studio Criativo, Site E-commerce X..." 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                </div>

                <!-- Categoria -->
                <div class="space-y-1">
                    <label for="portfolio_category_id" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Categoria de Trabalho <span class="text-red-500">*</span></label>
                    <select id="portfolio_category_id" 
                            name="portfolio_category_id" 
                            x-model="categoryId"
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

                <!-- Descrição com Editor WYSIWYG -->
                <div class="space-y-1 sm:col-span-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição do Trabalho <span class="text-red-500">*</span></label>
                    
                    <style>
                        .wysiwyg-editor ul, .wysiwyg-content ul {
                            list-style-type: disc !important;
                            padding-left: 1.5rem !important;
                            margin-top: 0.5rem !important;
                            margin-bottom: 0.5rem !important;
                        }
                        .wysiwyg-editor ol, .wysiwyg-content ol {
                            list-style-type: decimal !important;
                            padding-left: 1.5rem !important;
                            margin-top: 0.5rem !important;
                            margin-bottom: 0.5rem !important;
                        }
                        .wysiwyg-editor a, .wysiwyg-content a {
                            color: #2563eb !important;
                            text-decoration: underline !important;
                        }
                        .wysiwyg-editor u, .wysiwyg-content u {
                            text-decoration: underline !important;
                        }
                    </style>

                    <div class="border border-slate-200 rounded-[5px] overflow-hidden focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all bg-white mt-1">
                        <!-- Toolbar -->
                        <div class="bg-slate-50 border-b border-slate-200 p-2 flex flex-wrap gap-1 items-center select-none">
                            <button type="button" @click="format('bold')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Negrito">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
                            </button>
                            <button type="button" @click="format('italic')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Itálico">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
                            </button>
                            <button type="button" @click="format('underline')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Sublinhado">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
                            </button>
                            <button type="button" @click="format('insertUnorderedList')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Lista">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-7v2h14V6H7z"/></svg>
                            </button>
                            <button type="button" @click="insertLink()" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Inserir Link">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V6.4H7c-3.09 0-5.6 2.51-5.6 5.6s2.51 5.6 5.6 5.6h4v-2.5H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6.6h-4v2.5h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4v2.5h4c3.09 0 5.6-2.51 5.6-5.6s-2.51-5.6-5.6-5.6z"/></svg>
                            </button>
                            <button type="button" @click="format('undo')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Desfazer">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
                            </button>
                            <button type="button" @click="format('insertLineBreak')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors border-0" title="Quebra de Linha">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Área Editável com Auto-resize -->
                        <div x-ref="editor" 
                             contenteditable="true" 
                             x-init="$refs.editor.innerHTML = description || '';"
                             @input="description = $el.innerHTML; $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'" 
                             @blur="description = $el.innerHTML" 
                             class="w-full px-4 py-3 min-h-[150px] text-sm outline-none bg-white wysiwyg-editor prose max-w-none focus:outline-none overflow-hidden resize-none"
                             style="min-height: 150px;"></div>
                    </div>
                    <!-- Input oculto para submissão do formulário -->
                    <input type="hidden" name="description" :value="description">
                </div>

                <!-- Tecnologias utilizadas (Tags Input com Autocomplete) -->
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tecnologias Utilizadas (Opcional)</label>
                    <div x-data="tagsInput({
                        initialTags: technologies,
                        suggestions: {{ json_encode($existingTechnologies) }}
                    })" class="relative">
                        <!-- Campo oculto para submissão do formulário -->
                        <input type="hidden" name="technologies" :value="tags.join(', ')">
                        
                        <!-- Conteiner dos Tags -->
                        <div class="flex flex-wrap gap-1.5 p-2.5 border border-slate-200 rounded-[5px] bg-slate-50 min-h-[42px] focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all">
                            <template x-for="(tag, idx) in tags" :key="idx">
                                <span class="inline-flex items-center gap-1.5 bg-primary-50 text-primary-750 text-xs font-bold px-2 py-0.5 rounded-[4px] border border-primary-200 shadow-sm">
                                    <span x-text="tag"></span>
                                    <button type="button" @click="removeTag(idx)" class="text-primary-450 hover:text-primary-750 font-extrabold focus:outline-none border-0 bg-transparent p-0 leading-none">×</button>
                                </span>
                            </template>
                            
                            <input type="text"
                                   x-model="inputValue"
                                   @keydown.enter.prevent="addTag()"
                                   @keydown.comma.prevent="addTag()"
                                   @keydown.tab.prevent="addTag()"
                                   @keydown.escape="showSuggestions = false"
                                   @input="filterSuggestions()"
                                   @focus="showSuggestions = true"
                                   @click.away="showSuggestions = false"
                                   placeholder="Digite e pressione Enter ou vírgula"
                                   class="flex-1 min-w-[120px] bg-transparent border-none p-0 text-sm focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                        </div>

                        <!-- Dropdown de Sugestões / Autocomplete -->
                        <div x-show="showSuggestions && filteredSuggestions.length > 0"
                             class="absolute left-0 right-0 mt-1 max-h-40 overflow-y-auto bg-white border border-slate-200 rounded-[5px] shadow-lg z-50 divide-y divide-slate-100"
                             x-cloak>
                            <template x-for="suggestion in filteredSuggestions" :key="suggestion">
                                <button type="button"
                                        @click="selectSuggestion(suggestion)"
                                        class="w-full text-left px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary-750 transition-colors border-0">
                                    <span x-text="suggestion"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- URL de direcionamento -->
                <div class="space-y-1">
                    <label for="redirect_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Link do Trabalho/Visualização (Opcional)</label>
                    <input type="url" 
                           id="redirect_url" 
                           name="redirect_url" 
                           x-model="redirectUrl"
                           placeholder="Ex: https://meutrabalho.com" 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                </div>

                <!-- Status de publicação -->
                <div class="space-y-1">
                    <label for="status" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Status do Trabalho <span class="text-red-500">*</span></label>
                    <select id="status" 
                            name="status" 
                            x-model="status"
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

            <!-- Autores do Trabalho (Multiseleção estilizada) -->
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

        <!-- ABA 2: IMAGENS & GALERIA -->
        <div x-show="activeTab === 'galeria'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6" x-cloak>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">
                
                <!-- Coluna Esquerda: Thumbnail / Capa (Requisitado) -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Imagem de Capa (Thumbnail) <span class="text-red-500">*</span></h5>
                    
                    <div class="space-y-3">
                        <!-- Capa / Thumbnail com Drag & Drop -->
                        <div class="flex items-center justify-center w-full"
                             x-data="{ dragOver: false }"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleThumbDrop($event); dragOver = false">
                            <label :class="dragOver ? 'border-primary-500 bg-primary-50/20' : 'border-slate-200 bg-slate-50'"
                                   class="flex flex-col items-center justify-center w-full aspect-video border-2 border-dashed hover:border-primary-500 rounded-[5px] cursor-pointer hover:bg-slate-100/50 transition-colors relative overflow-hidden">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 text-center px-4" x-show="!thumbPreview">
                                    <svg class="w-8 h-8 text-slate-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <p class="text-xs font-bold text-slate-700">Selecione ou Arraste a Capa Aqui</p>
                                    <p class="text-[10px] text-slate-400 mt-1">PNG, JPG ou WEBP (Max 5MB)</p>
                                </div>
                                <template x-if="thumbPreview">
                                    <img :src="thumbPreview" class="absolute inset-0 w-full h-full object-cover">
                                </template>
                                <input type="file" id="thumb-input" name="thumb" class="hidden" @change="handleThumbUpload($event)">
                            </label>
                        </div>
                        <p class="text-[11px] text-slate-400 leading-normal text-justify">
                            * Essa imagem será otimizada automaticamente convertida em formato <strong class="text-slate-600">WebP</strong> com compressão adequada para garantir o menor tempo de carregamento no site público.
                        </p>
                    </div>
                </div>

                <!-- Coluna Direita: Galeria de Imagens Extras (Ordenação Simples) -->
                <div class="space-y-4">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">Galeria de Imagens do Trabalho</h5>
                    
                    <div class="space-y-4">
                        <!-- Botão upload galeria / Dragzone -->
                        <div class="flex items-center justify-center w-full"
                             x-data="{ dragOver: false }"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleGalleryDrop($event); dragOver = false">
                            <label :class="dragOver ? 'border-primary-500 bg-primary-50/20' : 'border-slate-200 bg-slate-50'"
                                   class="flex items-center justify-center gap-2 w-full py-4 border border-dashed hover:border-primary-500 rounded-[5px] cursor-pointer hover:bg-slate-100/50 transition-colors text-xs font-bold text-slate-650">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                Adicionar ou Arrastar Fotos da Galeria Aqui
                                <input type="file" id="gallery-input" name="gallery[]" multiple class="hidden" @change="handleGalleryUpload($event)">
                            </label>
                        </div>

                        <!-- Lista de fotos em Cards com ordenação por setas e exclusão -->
                        <template x-if="galleryFiles.length > 0">
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-[350px] overflow-y-auto pr-1">
                                <template x-for="(item, index) in galleryFiles" :key="index">
                                    <div class="relative bg-slate-50 border border-slate-200 rounded-[5px] overflow-hidden aspect-video flex flex-col justify-between group shadow-sm">
                                        <!-- Imagem de Fundo -->
                                        <img :src="item.url" class="w-full h-full object-cover absolute inset-0">
                                        
                                        <!-- Header da foto com botão de deletar flutuante -->
                                        <div class="relative p-1.5 flex justify-end z-10">
                                            <button type="button" 
                                                    @click="removeGalleryFile(index)" 
                                                    class="w-6 h-6 rounded-full bg-red-600/90 text-white flex items-center justify-center hover:bg-red-750 transition-colors shadow border-0"
                                                    title="Remover Imagem">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                        <!-- Footer da foto com controles de ordenação por setas -->
                                        <div class="relative bg-slate-900/80 text-white px-2 py-1.5 flex items-center justify-between z-10">
                                            <!-- Indicador de Ordem -->
                                            <div class="flex items-center gap-1">
                                                <span class="text-[10px] font-bold text-slate-300">Pos:</span>
                                                <span class="text-xs font-black" x-text="'#' + item.order"></span>
                                                <input type="hidden" :name="'gallery_orders[' + index + ']'" :value="item.order">
                                            </div>
                                            
                                            <!-- Setas de Mover -->
                                            <div class="flex items-center gap-1">
                                                <!-- Esquerda / Cima -->
                                                <button type="button" 
                                                        @click="moveUp(index)"
                                                        :disabled="index === 0"
                                                        class="w-5 h-5 rounded bg-slate-800 text-slate-200 flex items-center justify-center hover:bg-slate-700 disabled:opacity-30 disabled:hover:bg-slate-800 transition-colors border-0"
                                                        title="Mover para Cima">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"></path>
                                                    </svg>
                                                </button>
                                                
                                                <!-- Direita / Baixo -->
                                                <button type="button" 
                                                        @click="moveDown(index)"
                                                        :disabled="index === galleryFiles.length - 1"
                                                        class="w-5 h-5 rounded bg-slate-800 text-slate-200 flex items-center justify-center hover:bg-slate-700 disabled:opacity-30 disabled:hover:bg-slate-800 transition-colors border-0"
                                                        title="Mover para Baixo">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <div class="p-8 text-center text-slate-400 text-xs italic border border-slate-150 rounded-[5px] bg-slate-50/30" x-show="galleryFiles.length === 0">
                            Nenhuma imagem extra selecionada ainda.
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
                           placeholder="Ex: Trabalho X | Portfólio Exclusivo" 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
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
                              placeholder="Resumo do projeto focado em ranqueamento..." 
                              class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400"></textarea>
                </div>

                <!-- Meta Keywords -->
                <div class="space-y-1">
                    <label for="meta_keywords" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Meta Keywords (Palavras-chave separadas por vírgula)</label>
                    <input type="text" 
                           id="meta_keywords" 
                           name="meta_keywords" 
                           x-model="metaKeywords"
                           placeholder="Ex: design grafico, desenvolvimento laravel, portfolio..." 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
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
                Revise os detalhes abaixo para garantir que todas as informações do trabalho estão corretas antes de finalizar o cadastro.
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
                            <strong :class="thumbPreview ? 'text-emerald-600' : 'text-red-500'" x-text="thumbPreview ? 'Selecionada' : 'Pendente *'"></strong>
                        </p>
                        <p class="flex justify-between">
                            <span class="text-slate-400 font-medium">Fotos na Galeria:</span>
                            <strong class="text-slate-800" x-text="galleryFiles.length + ' fotos'"></strong>
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
                    Salvar e Publicar Trabalho
                </button>
            </div>

        </div>

    </form>
</div>

<script>
    function portfolioForm() {
        return {
            activeTab: 'geral',
            title: '{{ $projectData["title"] ?? "" }}',
            categoryId: '',
            clientId: '{{ $projectData["client_id"] ?? "" }}',
            description: {!! json_encode($projectData["description"] ?? "") !!},
            technologies: '',
            redirectUrl: '',
            status: 'rascunho',
            isFeatured: false,
            selectedAuthors: {!! json_encode($projectData["author_ids"] ?? []) !!},
            
            // Imagens
            thumbPreview: '',
            galleryFiles: [],
            
            // SEO
            metaTitle: '',
            metaDescription: '',
            metaKeywords: '',
            
            // AI simulated loading
            aiLoading: false,

            // Formatador do Editor WYSIWYG
            format(command, value = null) {
                if (command === 'insertLineBreak') {
                    document.execCommand('insertHTML', false, '<br>');
                } else {
                    document.execCommand(command, false, value);
                }
                this.description = this.$refs.editor.innerHTML;
            },

            insertLink() {
                let url = prompt('Digite a URL do link:');
                if (url) {
                    if (!/^https?:\/\//i.test(url)) {
                        url = 'http://' + url;
                    }
                    this.format('createLink', url);
                }
            },

            handleThumbUpload(event) {
                const file = event.target.files[0];
                if (file) {
                    this.thumbPreview = URL.createObjectURL(file);
                }
            },

            handleThumbDrop(event) {
                const file = event.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    const input = document.getElementById('thumb-input');
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(file);
                    input.files = dataTransfer.files;
                    this.thumbPreview = URL.createObjectURL(file);
                }
            },

            handleGalleryDrop(event) {
                const files = event.dataTransfer.files;
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (file.type.startsWith('image/')) {
                        this.galleryFiles.push({
                            file: file,
                            name: file.name,
                            url: URL.createObjectURL(file),
                            order: this.galleryFiles.length + 1
                        });
                    }
                }
            },

            syncGalleryInput(event) {
                const input = document.getElementById('gallery-input');
                const dataTransfer = new DataTransfer();
                this.galleryFiles.forEach(item => {
                    if (item.file) {
                        dataTransfer.items.add(item.file);
                    }
                });
                input.files = dataTransfer.files;
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

            moveUp(index) {
                if (index > 0) {
                    const temp = this.galleryFiles[index];
                    this.galleryFiles[index] = this.galleryFiles[index - 1];
                    this.galleryFiles[index - 1] = temp;
                    this.galleryFiles.forEach((item, idx) => {
                        item.order = idx + 1;
                    });
                }
            },

            moveDown(index) {
                if (index < this.galleryFiles.length - 1) {
                    const temp = this.galleryFiles[index];
                    this.galleryFiles[index] = this.galleryFiles[index + 1];
                    this.galleryFiles[index + 1] = temp;
                    this.galleryFiles.forEach((item, idx) => {
                        item.order = idx + 1;
                    });
                }
            },

            removeGalleryFile(index) {
                this.galleryFiles.splice(index, 1);
                this.galleryFiles.forEach((item, idx) => {
                    item.order = idx + 1;
                });
            },

            // Simulated Local AI SEO content optimization generator
            generateSEO() {
                if (!this.title) {
                    alert('Insira o título do trabalho primeiro.');
                    return;
                }
                
                this.aiLoading = true;
                
                setTimeout(() => {
                    // Title logic
                    this.metaTitle = (this.title + ' | Portfólio').substring(0, 60);
                    
                    // Description logic
                    let descText = this.description.replace(/(<([^>]+)>)/gi, "").trim();
                    this.metaDescription = descText.substring(0, 155) + '...';
                    
                    // Keywords logic
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

    // Componente Alpine.js para o campo de Tags (Tecnologias) com Autocomplete
    function tagsInput(config) {
        return {
            tags: [],
            inputValue: '',
            allSuggestions: config.suggestions || [],
            filteredSuggestions: [],
            showSuggestions: false,
            
            init() {
                if (config.initialTags) {
                    this.tags = config.initialTags.split(',')
                        .map(t => t.trim())
                        .filter(t => t.length > 0);
                }
                this.filteredSuggestions = this.allSuggestions.filter(s => !this.tags.includes(s));
            },

            addTag() {
                const clean = this.inputValue.trim().replace(/,/g, '');
                if (clean && !this.tags.includes(clean)) {
                    this.tags.push(clean);
                    // Atualiza a propriedade technologies no escopo pai (portfolioForm)
                    this.$parent.technologies = this.tags.join(', ');
                }
                this.inputValue = '';
                this.showSuggestions = false;
                this.filterSuggestions();
            },

            removeTag(index) {
                this.tags.splice(index, 1);
                this.$parent.technologies = this.tags.join(', ');
                this.filterSuggestions();
            },

            filterSuggestions() {
                const search = this.inputValue.toLowerCase().trim();
                this.filteredSuggestions = this.allSuggestions.filter(s => {
                    return s.toLowerCase().includes(search) && !this.tags.includes(s);
                });
            },

            selectSuggestion(val) {
                if (!this.tags.includes(val)) {
                    this.tags.push(val);
                    this.$parent.technologies = this.tags.join(', ');
                }
                this.inputValue = '';
                this.showSuggestions = false;
                this.filterSuggestions();
            }
        }
    }
</script>
@endsection
