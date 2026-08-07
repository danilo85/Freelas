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

    <!-- Erros de Validação Dinâmicos (AJAX) -->
    <div x-show="validationErrors.length > 0" x-cloak class="bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-900 text-rose-800 dark:text-rose-200 p-4 rounded-[5px] text-sm space-y-1.5 shadow-sm">
        <div class="flex items-center gap-2 font-bold">
            <svg class="w-4 h-4 text-rose-650" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            Por favor, verifique os campos obrigatórios:
        </div>
        <ul class="list-disc list-inside text-xs font-normal space-y-0.5 text-rose-700 dark:text-rose-300">
            <template x-for="err in validationErrors">
                <li x-text="err"></li>
            </template>
        </ul>
    </div>

    <!-- Formulário Principal -->
    <form action="{{ route('portfolio.update', $portfolio->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6" @submit.prevent="submitForm($event)">
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
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
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

                <!-- Descrição -->
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
                                   x-ref="tagInput"
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
                                        @mousedown.prevent="selectSuggestion(suggestion)"
                                        class="w-full text-left px-3.5 py-2 text-xs font-bold text-slate-700 hover:bg-primary-50 hover:text-primary-750 transition-colors border-0 cursor-pointer">
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
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
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

            <!-- Autores do Trabalho (Busca Inteligente + Auto-Detecção na Descrição) -->
            <div class="space-y-3 pt-4 border-t border-slate-100" 
                 x-data="authorsInput({
                    allAuthors: {{ json_encode($authors) }},
                    initialSelected: selectedAuthors
                 })">
                
                <div class="flex items-center justify-between">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">
                        Autores do Trabalho (Citá-los é importante)
                    </label>
                    <span class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
                        ✨ Detecção automática ativada na descrição
                    </span>
                </div>

                <!-- Campos ocultos para submissão dos IDs dos autores -->
                <template x-for="id in selectedIds" :key="id">
                    <input type="hidden" name="authors[]" :value="id">
                </template>

                <!-- Input e Lista de Tags Selecionadas -->
                <div class="relative">
                    <div class="flex flex-wrap gap-2 p-2.5 border border-slate-200 rounded-[5px] bg-slate-50 min-h-[46px] focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all items-center">
                        
                        <!-- Tags dos Autores Selecionados -->
                        <template x-for="author in getSelectedAuthorObjects()" :key="author.id">
                            <span class="inline-flex items-center gap-1.5 bg-white text-slate-800 text-xs font-bold px-2.5 py-1 rounded-[5px] border border-slate-200 shadow-sm">
                                <span class="w-5 h-5 rounded-full bg-primary-100 text-primary-750 text-[10px] font-black flex items-center justify-center uppercase shrink-0" x-text="author.name.substring(0, 1)"></span>
                                <span x-text="author.name"></span>
                                <button type="button" @click="removeAuthor(author.id)" class="text-slate-400 hover:text-red-600 font-extrabold focus:outline-none border-0 bg-transparent p-0 leading-none ml-1 cursor-pointer">×</button>
                            </span>
                        </template>

                        <!-- Input de Busca -->
                        <input type="text"
                               x-ref="authorInput"
                               x-model="searchQuery"
                               @focus="showDropdown = true"
                               @input="filterAuthors()"
                               @keydown.escape="showDropdown = false"
                               @click.away="showDropdown = false"
                               placeholder="Digite para buscar ou adicionar autor..."
                               class="flex-1 min-w-[180px] bg-transparent border-none p-0 text-sm focus:ring-0 focus:outline-none text-slate-700 placeholder-slate-400">
                    </div>

                    <!-- Dropdown de Autores Disponíveis -->
                    <div x-show="showDropdown && filteredAuthors.length > 0"
                         class="absolute left-0 right-0 mt-1 max-h-48 overflow-y-auto bg-white border border-slate-200 rounded-[5px] shadow-lg z-50 divide-y divide-slate-100"
                         x-cloak>
                        <template x-for="author in filteredAuthors" :key="author.id">
                            <button type="button"
                                    @mousedown.prevent="selectAuthor(author.id)"
                                    class="w-full text-left px-3.5 py-2.5 text-xs font-medium text-slate-700 hover:bg-primary-50 hover:text-primary-800 transition-colors flex items-center justify-between border-0 cursor-pointer">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 text-[11px] font-bold flex items-center justify-center uppercase shrink-0" x-text="author.name.substring(0, 1)"></span>
                                    <div>
                                        <span class="font-bold block text-slate-800" x-text="author.name"></span>
                                        <span class="text-[10px] text-slate-400 block" x-text="author.email"></span>
                                    </div>
                                </div>
                                <span class="text-[10px] font-bold text-primary-600">+ Adicionar</span>
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Navegação -->
            <div class="flex flex-col sm:flex-row sm:justify-end gap-3 pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'galeria'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    Ir para Galeria
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>

        </div>

        <!-- ABA 2: GALERIA & IMAGENS -->
        <div x-show="activeTab === 'galeria'" class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm space-y-6" x-cloak>
            
            <!-- Barra Superior de Ações & Configurações da Galeria -->
            <div class="bg-slate-50 border border-slate-200 p-4 rounded-[5px] flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                
                <!-- Slider de Espaçamento entre Imagens -->
                <div class="flex-1 space-y-1.5 min-w-[240px]">
                    <div class="flex items-center justify-between text-xs">
                        <label class="font-bold text-slate-700 uppercase tracking-wider flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path>
                            </svg>
                            Distância / Espaçamento entre Fotos no Site
                        </label>
                        <span class="font-extrabold text-primary-750 bg-primary-50 px-2 py-0.5 rounded text-xs border border-primary-200" x-text="gallerySpacing + ' px'"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="text-[10px] font-bold text-slate-400">0px</span>
                        <input type="range" min="0" max="64" step="4" x-model="gallerySpacing" class="w-full h-1.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-primary-600">
                        <span class="text-[10px] font-bold text-slate-400">64px</span>
                    </div>
                    <input type="hidden" name="gallery_spacing" :value="gallerySpacing">
                </div>

                <!-- Botão Modal de Preview -->
                <button type="button" 
                        @click="showPreviewModal = true"
                        class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 shadow-sm shrink-0 border-0 cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>✨ Visualizar Sequência no Site</span>
                </button>
            </div>

            <!-- Upload Zones Lado a Lado de Tamanho Igual -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start">
                
                <!-- Coluna Esquerda: Thumbnail Dropzone -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                        Imagem de Capa (Thumbnail)
                    </label>
                    
                    <div class="flex items-center justify-center w-full"
                         x-data="{ dragOver: false }"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handleThumbDrop($event); dragOver = false">
                        <label :class="dragOver ? 'border-primary-500 bg-primary-50/20' : 'border-slate-200 bg-slate-50'"
                               class="flex flex-col items-center justify-center w-full min-h-[110px] border-2 border-dashed hover:border-primary-500 rounded-[5px] cursor-pointer hover:bg-slate-100/50 transition-colors relative overflow-hidden p-3 text-center">
                            @if($portfolio->thumb_path)
                                <div class="relative w-full h-24 rounded overflow-hidden">
                                    <img :src="thumbPreview || '{{ asset('storage/' . $portfolio->thumb_path) }}'" class="w-full h-full object-cover">
                                    <span class="absolute top-1 right-1 bg-slate-900/80 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">Capa Atual</span>
                                </div>
                            @else
                                <template x-if="!thumbPreview">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <div class="text-left">
                                            <p class="text-xs font-bold text-slate-700">Selecione ou Arraste a Capa</p>
                                        </div>
                                    </div>
                                </template>
                                <template x-if="thumbPreview">
                                    <div class="relative w-full h-24 rounded overflow-hidden">
                                        <img :src="thumbPreview" class="w-full h-full object-cover">
                                        <span class="absolute top-1 right-1 bg-slate-900/80 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">Nova Capa</span>
                                    </div>
                                </template>
                            @endif
                            <input type="file" id="thumb-input" name="thumb" class="hidden" @change="handleThumbUpload($event)">
                        </label>
                    </div>
                </div>

                <!-- Coluna Direita: Galeria Dropzone -->
                <div class="space-y-2">
                    <label class="text-xs font-bold text-slate-800 uppercase tracking-wider block">
                        Adicionar Novas Fotos da Galeria
                    </label>

                    <div class="flex items-center justify-center w-full"
                         x-data="{ dragOver: false }"
                         @dragover.prevent="dragOver = true"
                         @dragleave.prevent="dragOver = false"
                         @drop.prevent="handleGalleryDrop($event); dragOver = false">
                        <label :class="dragOver ? 'border-primary-500 bg-primary-50/20' : 'border-slate-200 bg-slate-50'"
                               class="flex flex-col items-center justify-center w-full min-h-[110px] border-2 border-dashed hover:border-primary-500 rounded-[5px] cursor-pointer hover:bg-slate-100/50 transition-colors text-center p-3">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                </svg>
                                <div class="text-left">
                                    <p class="text-xs font-bold text-slate-700">Selecione ou Arraste Várias Fotos</p>
                                    <p class="text-[10px] text-slate-400">Adicione novas fotos ao trabalho</p>
                                </div>
                            </div>
                            <input type="file" id="gallery-input" name="gallery[]" multiple class="hidden" @change="handleGalleryUpload($event)">
                        </label>
                    </div>
                </div>

            </div>

            <!-- Imagens Existentes na Galeria -->
            @if($portfolio->images->count() > 0)
                <div class="space-y-3 pt-2 border-t border-slate-100">
                    <h6 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Fotos Salvas Atualmente</h6>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[250px] overflow-y-auto p-1">
                        @foreach($portfolio->images as $img)
                            <div class="relative bg-slate-50 border border-slate-200 rounded-[5px] overflow-hidden aspect-video flex flex-col justify-between group shadow-sm transition-opacity">
                                <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-full object-cover absolute inset-0">
                                
                                <div class="relative p-1.5 flex justify-end z-10">
                                    <label class="w-6 h-6 rounded-full bg-slate-900/80 text-white flex items-center justify-center hover:bg-red-600 transition-colors shadow-sm cursor-pointer select-none" title="Marcar para remover">
                                        <input type="checkbox" 
                                               name="delete_images[]" 
                                               value="{{ $img->id }}"
                                               class="hidden"
                                               @change="$el.closest('.relative').classList.toggle('opacity-35', $el.checked)">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </label>
                                </div>

                                <div class="relative bg-slate-900/80 text-white px-2 py-1 flex items-center justify-between z-10">
                                    <span class="text-[10px] font-bold text-slate-300">Pos:</span>
                                    <input type="number" 
                                           name="existing_gallery_orders[{{ $img->id }}]" 
                                           value="{{ $img->order }}"
                                           class="w-10 px-1 py-0.5 rounded bg-slate-800 border border-slate-700 text-[11px] font-bold text-center text-white focus:outline-none focus:ring-1 focus:ring-primary-500">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Novas Fotos com Drag & Drop Reordering -->
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider">
                        Novas Fotos Selecionadas (Arraste para Reordenar)
                    </h5>
                    <span class="text-[10px] text-slate-400 font-medium">
                        💡 Clique e segure para mover de posição
                    </span>
                </div>

                <template x-if="galleryFiles.length > 0">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 max-h-[300px] overflow-y-auto p-1">
                        <template x-for="(item, index) in galleryFiles" :key="index">
                            <div draggable="true"
                                 @dragstart="draggedIndex = index"
                                 @dragover.prevent="dragOverIndex = index"
                                 @dragleave="dragOverIndex = null"
                                 @drop.prevent="swapGalleryFiles(draggedIndex, index)"
                                 :class="dragOverIndex === index ? 'ring-2 ring-primary-500 scale-105' : ''"
                                 class="relative bg-slate-50 border border-slate-200 rounded-[5px] overflow-hidden aspect-video flex flex-col justify-between group shadow-sm transition-all cursor-grab active:cursor-grabbing select-none">
                                
                                <img :src="item.url" class="w-full h-full object-cover absolute inset-0 pointer-events-none">
                                
                                <div class="relative p-1.5 flex justify-between items-center z-10 bg-gradient-to-b from-slate-900/60 to-transparent">
                                    <span class="bg-primary-600 text-white text-[10px] font-black px-2 py-0.5 rounded shadow" x-text="'#' + item.order"></span>
                                    
                                    <button type="button" 
                                            @click="removeGalleryFile(index)" 
                                            class="w-6 h-6 rounded-full bg-red-600/90 text-white flex items-center justify-center hover:bg-red-750 transition-colors shadow border-0 cursor-pointer"
                                            title="Remover Imagem">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="relative bg-slate-900/80 text-white px-2 py-1.5 flex items-center justify-between z-10">
                                    <span class="text-[9px] text-slate-300 font-bold flex items-center gap-1">
                                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"></path></svg>
                                        Arraste
                                    </span>
                                    
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="moveUp(index)" :disabled="index === 0" class="w-5 h-5 rounded bg-slate-800 text-slate-200 flex items-center justify-center hover:bg-slate-700 disabled:opacity-30 border-0" title="Mover para esquerda">‹</button>
                                        <button type="button" @click="moveDown(index)" :disabled="index === galleryFiles.length - 1" class="w-5 h-5 rounded bg-slate-800 text-slate-200 flex items-center justify-center hover:bg-slate-700 disabled:opacity-30 border-0" title="Mover para direita">›</button>
                                    </div>
                                    <input type="hidden" :name="'gallery_orders[' + index + ']'" :value="item.order">
                                </div>
                            </div>
                        </template>
                    </div>
                </template>

                <div class="p-6 text-center text-slate-400 text-xs italic border border-slate-150 rounded-[5px] bg-slate-50/30" x-show="galleryFiles.length === 0">
                    Nenhuma nova foto selecionada nesta edição.
                </div>
            </div>

            <!-- Navegação -->
            <div class="flex flex-col sm:flex-row justify-between gap-3 pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'geral'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5 border-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para Geral
                </button>

                <button type="button" @click="activeTab = 'seo'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5 border-0 cursor-pointer">
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
                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                    <button type="button" 
                            @click="generateSEO()"
                            class="py-2 px-3.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm whitespace-nowrap"
                            :class="aiLoading ? 'opacity-70 cursor-not-allowed' : ''"
                            :disabled="aiLoading">
                        <span x-show="aiLoading" class="w-3.5 h-3.5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-show="!aiLoading">✨ Otimizar SEO</span>
                    </button>
                    <div x-show="aiError" class="text-xs text-red-600 font-bold flex items-center gap-1 animate-pulse" x-cloak>
                        ⚠️ <span x-text="aiError"></span>
                    </div>
                </div>
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
            <div class="flex flex-col sm:flex-row justify-between gap-3 pt-4 border-t border-slate-100">
                <button type="button" @click="activeTab = 'galeria'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para Galeria
                </button>

                <button type="button" @click="activeTab = 'revisao'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
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
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Resumo do Trabalho</span>
                        <button type="button" @click="activeTab = 'geral'" class="text-[10px] font-bold text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200 hover:border-primary-300 transition-colors border-0 cursor-pointer flex items-center gap-1">
                            ✏️ Editar Dados Gerais
                        </button>
                    </h5>
                    
                    <div class="space-y-3 text-slate-600 leading-relaxed">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Título:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800 truncate max-w-[180px]" x-text="title || 'Não informado'"></strong>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Título">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Categoria:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="bg-slate-100 text-slate-700 text-xs px-2 py-0.5 rounded font-bold" x-text="getCategoryName()"></span>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Categoria">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Cliente:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800" x-text="getClientName()"></strong>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Cliente">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Tecnologias:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800" x-text="technologies || 'Não informada'"></strong>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Tecnologias">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Link do Trabalho:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800 truncate max-w-[180px]" x-text="redirectUrl || 'Não informado'"></strong>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Link">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Destaque do Portfólio:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800" x-text="isFeatured ? 'Sim' : 'Não'"></strong>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Destaque">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-400 font-medium">Status de Publicação:</span>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded font-bold text-xs uppercase" 
                                      :class="status === 'publicado' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'"
                                      x-text="status"></span>
                                <button type="button" @click="activeTab = 'geral'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Status">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Resumo Galeria & SEO -->
                <div class="space-y-4 pt-6 md:pt-0 pl-0 md:pl-6">
                    <h5 class="text-xs font-bold text-slate-800 uppercase tracking-wider pb-2 border-b border-slate-100 flex items-center justify-between">
                        <span>Galeria & Metatags</span>
                        <button type="button" @click="activeTab = 'galeria'" class="text-[10px] font-bold text-primary-600 hover:text-primary-800 bg-primary-50 px-2 py-0.5 rounded border border-primary-200 hover:border-primary-300 transition-colors border-0 cursor-pointer flex items-center gap-1">
                            🖼️ Editar Galeria
                        </button>
                    </h5>
                    
                    <div class="space-y-3 text-slate-600">
                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Imagem de Capa (Thumb):</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-emerald-600" x-text="thumbPreview ? 'Nova capa enviada' : 'Mantida'"></strong>
                                <button type="button" @click="activeTab = 'galeria'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Imagem de Capa">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Novas Fotos Adicionadas:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800" x-text="galleryFiles.length + ' fotos'"></strong>
                                <button type="button" @click="activeTab = 'galeria'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Fotos da Galeria">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2 border-b border-slate-50 pb-2">
                            <span class="text-slate-400 font-medium">Meta Title (SEO):</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800 truncate max-w-[180px]" x-text="metaTitle || 'Não configurado'"></strong>
                                <button type="button" @click="activeTab = 'seo'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Meta Title">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <span class="text-slate-400 font-medium">Meta Description:</span>
                            <div class="flex items-center gap-1.5">
                                <strong class="text-slate-800 truncate max-w-[180px]" x-text="metaDescription || 'Não configurado'"></strong>
                                <button type="button" @click="activeTab = 'seo'" class="p-1 rounded text-slate-400 hover:text-primary-600 hover:bg-primary-50 transition-colors border-0 bg-transparent cursor-pointer" title="Alterar Meta Description">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navegação e Submissão -->
            <div class="flex flex-col sm:flex-row justify-between gap-3 pt-6 border-t border-slate-100">
                <button type="button" @click="activeTab = 'seo'" 
                        class="w-full sm:w-auto justify-center py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Voltar para SEO
                </button>

                <button type="submit" 
                        class="w-full sm:w-auto justify-center py-3 px-6 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-extrabold rounded-[5px] transition-colors shadow-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salvar Alterações do Trabalho
                </button>
            </div>

        </div>

    </form>

    <!-- Modal de Preview da Sequência de Imagens no Site -->
    <div x-show="showPreviewModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-950/80 backdrop-blur-md p-4" x-cloak>
        <div class="bg-slate-900 border border-slate-800 rounded-xl max-w-4xl w-full max-h-[90vh] flex flex-col shadow-2xl overflow-hidden">
            
            <!-- Header do Modal -->
            <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/50">
                <div class="flex items-center gap-3">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-pulse"></span>
                    <h3 class="text-sm font-bold text-white uppercase tracking-wider">
                        Pré-visualização do Portfólio no Site Público
                    </h3>
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Ajuste ao vivo no modal -->
                    <div class="flex items-center gap-2 text-xs text-slate-300">
                        <span>Espaçamento:</span>
                        <input type="range" min="0" max="64" step="4" x-model="gallerySpacing" class="w-24 h-1.5 bg-slate-700 rounded appearance-none cursor-pointer accent-primary-500">
                        <span class="font-mono text-primary-400 font-bold" x-text="gallerySpacing + 'px'"></span>
                    </div>

                    <button type="button" @click="showPreviewModal = false" class="text-slate-400 hover:text-white text-xl font-bold p-1 border-0 bg-transparent cursor-pointer">×</button>
                </div>
            </div>

            <!-- Corpo de Preview Simulando o Site Público -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6 bg-[#070a13] text-slate-100">
                
                <!-- Título Simulado -->
                <div class="space-y-1 text-center max-w-2xl mx-auto border-b border-white/10 pb-4">
                    <span class="text-[10px] font-bold uppercase tracking-widest text-primary-400" x-text="getCategoryName()"></span>
                    <h2 class="text-xl font-extrabold text-white" x-text="title || 'Título do Trabalho'"></h2>
                </div>

                <!-- Sequência das Imagens com o Espaçamento Selecionado -->
                <div class="max-w-2xl mx-auto flex flex-col" :style="'gap: ' + gallerySpacing + 'px;'">
                    <!-- Capa -->
                    <template x-if="thumbPreview">
                        <img :src="thumbPreview" class="w-full h-auto object-cover rounded-none block m-0 p-0 border-0 outline-none">
                    </template>
                    @if($portfolio->thumb_path)
                        <template x-if="!thumbPreview">
                            <img src="{{ asset('storage/' . $portfolio->thumb_path) }}" class="w-full h-auto object-cover rounded-none block m-0 p-0 border-0 outline-none">
                        </template>
                    @endif

                    <!-- Imagens Existentes da Galeria -->
                    @if($portfolio->images->count() > 0)
                        @foreach($portfolio->images as $img)
                            <img src="{{ asset('storage/' . $img->image_path) }}" class="w-full h-auto object-cover rounded-none block m-0 p-0 border-0 outline-none">
                        @endforeach
                    @endif

                    <!-- Fotos da Galeria Novas Adicionadas na Edição -->
                    <template x-for="(item, idx) in galleryFiles" :key="idx">
                        <img :src="item.url" class="w-full h-auto object-cover rounded-none block m-0 p-0 border-0 outline-none">
                    </template>
                </div>
            </div>

            <!-- Footer do Modal -->
            <div class="p-4 border-t border-slate-800 bg-slate-950 flex justify-end">
                <button type="button" @click="showPreviewModal = false" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded transition-colors border-0 cursor-pointer">
                    Fechar Pré-visualização
                </button>
            </div>

        </div>
    </div>

    <!-- Modal Loader Overlay de Upload -->
    <div x-show="isUploading" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-lg p-6 max-w-sm w-full mx-4 shadow-2xl text-center space-y-4">
            <!-- Spinner -->
            <div class="relative flex items-center justify-center">
                <div class="w-12 h-12 border-4 border-slate-200 border-t-primary-600 rounded-full animate-spin"></div>
                <span class="absolute text-[10px] font-extrabold text-slate-700 dark:text-slate-350" x-text="uploadPercentage + '%'"></span>
            </div>
            <div>
                <h4 class="text-sm font-bold text-slate-900 dark:text-slate-100">Enviando arquivos...</h4>
                <p class="text-[10px] text-slate-400 mt-1">Carregando mídias para o servidor. Por favor, aguarde.</p>
            </div>
            <!-- Progress Bar -->
            <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                <div class="bg-primary-655 h-2.5 rounded-full transition-all duration-150" :style="'width: ' + uploadPercentage + '%'"></div>
            </div>
        </div>
    </div>
</div>

<script>
    function portfolioForm() {
        return {
            activeTab: 'geral',
            title: {!! json_encode($portfolio->title) !!},
            categoryId: '{{ $portfolio->portfolio_category_id }}',
            clientId: '{{ $portfolio->client_id ?? "" }}',
            description: {!! json_encode($portfolio->description) !!},
            technologies: {!! json_encode($portfolio->technologies ?? "") !!},
            redirectUrl: {!! json_encode($portfolio->redirect_url ?? "") !!},
            status: '{{ $portfolio->status }}',
            isFeatured: {{ $portfolio->is_featured ? 'true' : 'false' }},
            selectedAuthors: {!! json_encode($portfolio->authors->pluck('id')->toArray()) !!},
            
            // Imagens
            thumbPreview: '',
            galleryFiles: [],
            gallerySpacing: {{ intval($portfolio->gallery_spacing ?? 0) }},
            showPreviewModal: false,
            draggedIndex: null,
            dragOverIndex: null,
            
            // SEO
            metaTitle: {!! json_encode($portfolio->meta_title ?? "") !!},
            metaDescription: {!! json_encode($portfolio->meta_description ?? "") !!},
            metaKeywords: {!! json_encode($portfolio->meta_keywords ?? "") !!},
            
            // AI simulated loading
            aiLoading: false,
            aiError: '',
            
            // Upload progress & Validation errors
            isUploading: false,
            uploadPercentage: 0,
            validationErrors: [],

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

            submitForm(event) {
                this.syncGalleryInput();

                const form = event.target;
                const formData = new FormData(form);

                // Add list orders
                this.galleryFiles.forEach((item, index) => {
                    formData.append(`gallery_orders[${index}]`, item.order);
                });

                this.validationErrors = [];
                this.isUploading = true;
                this.uploadPercentage = 0;

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        this.uploadPercentage = Math.round((e.loaded / e.total) * 100);
                    }
                });

                xhr.addEventListener('load', () => {
                    this.isUploading = false;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        const res = JSON.parse(xhr.responseText);
                        if (res.success && res.redirect_url) {
                            window.location.href = res.redirect_url;
                        }
                    } else if (xhr.status === 422) {
                        const res = JSON.parse(xhr.responseText);
                        if (res.errors) {
                            let errorsList = [];
                            Object.keys(res.errors).forEach(key => {
                                res.errors[key].forEach(msg => {
                                    errorsList.push(msg);
                                });
                            });
                            this.validationErrors = errorsList;
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        }
                    } else {
                        alert('Erro ao salvar. Por favor, tente novamente.');
                    }
                });

                xhr.addEventListener('error', () => {
                    this.isUploading = false;
                    alert('Erro de rede ou tamanho de arquivo excedido.');
                });

                xhr.send(formData);
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

            swapGalleryFiles(from, to) {
                if (from === null || to === null || from === to) return;
                const item = this.galleryFiles.splice(from, 1)[0];
                this.galleryFiles.splice(to, 0, item);
                this.galleryFiles.forEach((f, idx) => {
                    f.order = idx + 1;
                });
                this.draggedIndex = null;
                this.dragOverIndex = null;
            },

            removeGalleryFile(index) {
                this.galleryFiles.splice(index, 1);
                // Reordena
                this.galleryFiles.forEach((item, idx) => {
                    item.order = idx + 1;
                });
            },

            generateSEO() {
                this.aiError = '';
                if (!this.title) {
                    this.aiError = 'Insira o título do trabalho primeiro.';
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
                const clean = (this.inputValue || '').trim().replace(/,/g, '');
                if (clean && !this.tags.includes(clean)) {
                    this.tags.push(clean);
                    if (this.$parent) {
                        this.$parent.technologies = this.tags.join(', ');
                    }
                }
                this.clearInput();
            },

            removeTag(index) {
                this.tags.splice(index, 1);
                if (this.$parent) {
                    this.$parent.technologies = this.tags.join(', ');
                }
                this.filterSuggestions();
            },

            filterSuggestions() {
                const search = (this.inputValue || '').toLowerCase().trim();
                this.filteredSuggestions = this.allSuggestions.filter(s => {
                    return s.toLowerCase().includes(search) && !this.tags.includes(s);
                });
            },

            selectSuggestion(val) {
                if (!this.tags.includes(val)) {
                    this.tags.push(val);
                    if (this.$parent) {
                        this.$parent.technologies = this.tags.join(', ');
                    }
                }
                this.clearInput();
            },

            clearInput() {
                this.inputValue = '';
                this.showSuggestions = false;
                if (this.$refs.tagInput) {
                    this.$refs.tagInput.value = '';
                }
                this.$nextTick(() => {
                    this.inputValue = '';
                    if (this.$refs.tagInput) {
                        this.$refs.tagInput.value = '';
                    }
                    this.filterSuggestions();
                });
            }
        }
    }

    // Componente Alpine.js para Autores com Busca Inteligente e Auto-Detecção na Descrição
    function authorsInput(config) {
        return {
            allAuthors: config.allAuthors || [],
            selectedIds: (config.initialSelected || []).map(id => parseInt(id)),
            manuallyRemovedIds: [],
            searchQuery: '',
            filteredAuthors: [],
            showDropdown: false,

            init() {
                this.filterAuthors();

                const checkDescription = () => {
                    const editorEl = document.querySelector('[x-ref="editor"]') || document.querySelector('.wysiwyg-editor');
                    const text = editorEl ? editorEl.innerHTML : (this.$parent ? this.$parent.description : '');
                    if (text) {
                        this.scanTextForAuthors(text);
                    }
                };

                checkDescription();
                setTimeout(checkDescription, 300);
                setTimeout(checkDescription, 1000);

                if (this.$parent) {
                    this.$watch('$parent.description', (newVal) => {
                        this.scanTextForAuthors(newVal);
                    });
                }

                document.addEventListener('input', (e) => {
                    if (e.target && (e.target.getAttribute('x-ref') === 'editor' || e.target.classList.contains('wysiwyg-editor'))) {
                        this.scanTextForAuthors(e.target.innerHTML);
                    }
                });
            },

            scanTextForAuthors(htmlContent) {
                if (!htmlContent) return;

                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                const plainText = (tempDiv.textContent || tempDiv.innerText || '').toLowerCase().replace(/\s+/g, ' ');

                if (!plainText.trim()) return;

                let addedAny = false;
                this.allAuthors.forEach(author => {
                    const authorId = parseInt(author.id);
                    if (this.selectedIds.includes(authorId) || this.manuallyRemovedIds.includes(authorId)) {
                        return;
                    }

                    const name = author.name.trim().toLowerCase().replace(/\s+/g, ' ');
                    if (!name || name.length < 3) return;

                    if (plainText.includes(name)) {
                        this.selectedIds.push(authorId);
                        addedAny = true;
                    } else {
                        const parts = name.split(' ').filter(p => p.length > 2);
                        if (parts.length >= 2) {
                            const firstLast = parts[0] + ' ' + parts[parts.length - 1];
                            if (plainText.includes(firstLast)) {
                                this.selectedIds.push(authorId);
                                addedAny = true;
                            }
                        }
                    }
                });

                if (addedAny) {
                    if (this.$parent) {
                        this.$parent.selectedAuthors = this.selectedIds;
                    }
                    this.filterAuthors();
                }
            },

            getSelectedAuthorObjects() {
                return this.allAuthors.filter(a => this.selectedIds.includes(parseInt(a.id)));
            },

            filterAuthors() {
                const q = (this.searchQuery || '').toLowerCase().trim();
                this.filteredAuthors = this.allAuthors.filter(a => {
                    const isSelected = this.selectedIds.includes(parseInt(a.id));
                    if (isSelected) return false;

                    if (!q) return true;
                    return a.name.toLowerCase().includes(q) || (a.email && a.email.toLowerCase().includes(q));
                });
            },

            selectAuthor(id) {
                const numId = parseInt(id);
                if (!this.selectedIds.includes(numId)) {
                    this.selectedIds.push(numId);
                    if (this.$parent) {
                        this.$parent.selectedAuthors = this.selectedIds;
                    }
                }
                this.clearInput();
            },

            removeAuthor(id) {
                const numId = parseInt(id);
                this.selectedIds = this.selectedIds.filter(i => i !== numId);
                if (!this.manuallyRemovedIds.includes(numId)) {
                    this.manuallyRemovedIds.push(numId);
                }
                if (this.$parent) {
                    this.$parent.selectedAuthors = this.selectedIds;
                }
                this.filterAuthors();
            },

            clearInput() {
                this.searchQuery = '';
                this.showDropdown = false;
                if (this.$refs.authorInput) {
                    this.$refs.authorInput.value = '';
                }
                this.$nextTick(() => {
                    this.searchQuery = '';
                    if (this.$refs.authorInput) {
                        this.$refs.authorInput.value = '';
                    }
                    this.filterAuthors();
                });
            }
        }
    }
</script>
@endsection
