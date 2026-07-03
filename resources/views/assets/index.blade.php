@extends('layouts.app')

@section('title', 'Banco de Assets - Gestor de Freelas')
@section('page_title', 'Banco de Assets')

@section('content')
<div x-data="assetsManager()" class="space-y-6">

    <!-- Highlight.js para destaque de sintaxe do editor de código -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/atom-one-dark.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>

    <!-- Estilos dinâmicos para renderização de fontes -->
    @foreach($assets as $asset)
        @if($asset->type === 'fonte' && $asset->file_path)
            <style>
                @font-face {
                    font-family: 'font_preview_{{ $asset->id }}';
                    src: url('{{ asset('storage/' . $asset->file_path) }}');
                }
            </style>
        @endif
    @endforeach

    <!-- Barra Flutuante de Ação em Lote (Batch Toolbar) -->
    <div x-show="selectedIds.length > 0" 
         x-transition.opacity
         x-cloak
         class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-slate-900 border border-slate-800 text-white px-6 py-4 rounded-full shadow-2xl z-50 flex items-center gap-6 select-none"
    >
        <span class="text-xs font-black uppercase tracking-wider text-slate-350">
            <span x-text="selectedIds.length"></span> selecionado(s)
        </span>
        <div class="h-4 w-px bg-slate-700"></div>
        <div class="flex items-center gap-3">
            <!-- Baixar Lote -->
            <button type="button" @click="downloadSelected()" class="text-xs font-bold uppercase tracking-wider text-blue-400 hover:text-blue-300 transition-colors flex items-center gap-1.5 cursor-pointer">
                📥 Baixar (.ZIP)
            </button>
            <!-- Excluir Lote -->
            <button type="button" @click="deleteSelected()" class="text-xs font-bold uppercase tracking-wider text-rose-400 hover:text-rose-350 transition-colors flex items-center gap-1.5 cursor-pointer">
                🗑️ Excluir
            </button>
        </div>
    </div>

    <!-- Top Cards (Métricas com Cores Diferentes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total de Recursos (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total de Assets</p>
                <h3 class="text-2xl font-extrabold text-white mt-2">
                    {{ $assets->count() }}
                </h3>
                <span class="text-xs text-blue-100/95 font-medium block mt-1.5">
                    Armazenamento: {{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($totalStorage) }}
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">📦</span>
            </div>
        </div>

        <!-- Imagens e Vetores (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Imagens & Vetores</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $imagesCount }}
                </h3>
                <span class="text-xs text-emerald-100/95 font-medium block mt-1.5">
                    Imagens, SVGs e ilustrações
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">🎨</span>
            </div>
        </div>

        <!-- Tipografias/Fontes (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-purple-100 uppercase tracking-wider">Tipografias / Fontes</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $fontsCount }}
                </h3>
                <span class="text-xs text-purple-100/95 font-medium block mt-1.5">
                    Fontes TTF, OTF, WOFF
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">🔤</span>
            </div>
        </div>

        <!-- Códigos e Outros (Card Amarelo) -->
        <div class="bg-amber-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-amber-100 uppercase tracking-wider">Códigos & Arquivos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $codeCount + $filesCount }}
                </h3>
                <span class="text-xs text-amber-100/95 font-medium block mt-1.5">
                    {{ $codeCount }} trechos e {{ $filesCount }} arquivos
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">💾</span>
            </div>
        </div>

    </div>

    <!-- Busca e Filtros -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm select-none">
        <div class="space-y-3">
            
            <!-- Pesquisa Moderna -->
            <div class="relative w-full">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </span>
                <input type="text" 
                       x-model="searchQuery"
                       placeholder="Pesquise por nome ou descrição do asset..." 
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

            <!-- Tags de Filtro Rápidas -->
            <div class="flex flex-wrap items-center gap-2 pt-1">
                <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mr-1">Filtrar por:</span>
                
                <!-- Tag: Todos -->
                <button type="button" 
                        @click="filterType = ''" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="!filterType ? 'background-color: #0f172a; border-color: #0f172a; color: #ffffff;' : 'background-color: #f1f5f9; border-color: #e2e8f0; color: #475569;'">
                    Todos
                </button>

                <!-- Tag: Imagens -->
                <button type="button" 
                        @click="filterType = 'imagem'" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterType === 'imagem' ? 'background-color: #10b981; border-color: #10b981; color: #ffffff;' : 'background-color: #ecfdf5; border-color: #d1fae5; color: #047857;'">
                    Imagens
                </button>

                <!-- Tag: Fontes -->
                <button type="button" 
                        @click="filterType = 'fonte'" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterType === 'fonte' ? 'background-color: #8b5cf6; border-color: #8b5cf6; color: #ffffff;' : 'background-color: #f5f3ff; border-color: #ede9fe; color: #6d28d9;'">
                    Fontes
                </button>

                <!-- Tag: Códigos -->
                <button type="button" 
                        @click="filterType = 'codigo'" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterType === 'codigo' ? 'background-color: #3b82f6; border-color: #3b82f6; color: #ffffff;' : 'background-color: #eff6ff; border-color: #dbeafe; color: #1d4ed8;'">
                    Códigos
                </button>

                <!-- Tag: Arquivos -->
                <button type="button" 
                        @click="filterType = 'arquivo'" 
                        class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                        :style="filterType === 'arquivo' ? 'background-color: #f59e0b; border-color: #f59e0b; color: #ffffff;' : 'background-color: #fffbeb; border-color: #fef3c7; color: #b45309;'">
                    Arquivos
                </button>
            </div>
        </div>
    </div>

    <!-- Grid de Assets -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($assets as $asset)
            @php
                $cardBgClass = 'bg-white border-slate-200';
                if ($asset->type === 'imagem') {
                    $cardBgClass = 'bg-emerald-50/15 border-emerald-200/80 dark:bg-emerald-950/5';
                } elseif ($asset->type === 'fonte') {
                    $cardBgClass = 'bg-purple-50/15 border-purple-200/80 dark:bg-purple-950/5';
                } elseif ($asset->type === 'codigo') {
                    $cardBgClass = 'bg-blue-50/15 border-blue-200/80 dark:bg-blue-950/5';
                } elseif ($asset->type === 'arquivo') {
                    $cardBgClass = 'bg-amber-50/15 border-amber-200/80 dark:bg-amber-950/5';
                }
            @endphp
            <div x-show="shouldShowAsset('{{ addslashes($asset->title) }}', '{{ addslashes($asset->description) }}', '{{ $asset->type }}')"
                 class="{{ $cardBgClass }} border rounded-[5px] shadow-sm hover:shadow-md transition-all duration-200 relative group overflow-hidden flex flex-col justify-between"
                 x-transition>
                
                <!-- Checkbox superior de seleção (Lote) -->
                <div class="absolute top-3 left-3 z-30 select-none">
                    <input type="checkbox" 
                           value="{{ $asset->id }}" 
                           x-model="selectedIds"
                           class="w-4.5 h-4.5 text-primary-600 bg-white border-slate-300 rounded focus:ring-primary-500/20 cursor-pointer shadow-sm">
                </div>

                <!-- Preview Area por tipo -->
                <div class="aspect-video bg-slate-50 border-b border-slate-100 flex items-center justify-center relative overflow-hidden select-none">
                    
                    <!-- IMAGEM -->
                    @if($asset->type === 'imagem')
                        <img src="{{ asset('storage/' . $asset->file_path) }}" class="w-full h-full object-cover">
                    
                    <!-- FONTE -->
                    @elseif($asset->type === 'fonte')
                        <div class="p-4 text-center w-full">
                            <span class="text-[9px] font-black uppercase text-slate-400 tracking-wider block mb-2">Tipografia</span>
                            <div style="font-family: 'font_preview_{{ $asset->id }}';" class="text-3xl text-slate-800 truncate px-2" title="Prévia da Fonte">
                                Aa Bb Cc 123
                            </div>
                        </div>
                    
                    <!-- CODIGO -->
                    @elseif($asset->type === 'codigo')
                        <div class="p-3 w-full h-full overflow-hidden text-left bg-slate-900 font-mono text-[9px] text-emerald-400 leading-relaxed select-text">
                            <span class="text-[8px] font-bold text-slate-500 uppercase tracking-widest block border-b border-slate-800 pb-1 mb-1">Snippet de Código</span>
                            <pre class="line-clamp-5">{{ $asset->code_snippet }}</pre>
                        </div>

                    <!-- ARQUIVO DIVERSO -->
                    @else
                        <div class="text-center p-4">
                            <span class="text-4xl">📁</span>
                            <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mt-2">
                                {{ strtoupper(pathinfo($asset->file_path, PATHINFO_EXTENSION)) ?: 'ARQUIVO' }}
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Corpo do Card -->
                <div class="p-4 space-y-2.5 flex-1 flex flex-col justify-between">
                    <div class="space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold text-slate-800 text-sm leading-snug truncate" title="{{ $asset->title }}">
                                {{ $asset->title }}
                            </h4>
                            <!-- Tag de Tipo -->
                            <span class="text-[9px] font-extrabold uppercase px-1.5 py-0.5 rounded-[3px] shrink-0
                                {{ $asset->type === 'imagem' ? 'bg-emerald-50 text-emerald-600' : ($asset->type === 'fonte' ? 'bg-purple-50 text-purple-600' : ($asset->type === 'codigo' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600')) }}">
                                {{ $asset->type }}
                            </span>
                        </div>
                        @if($asset->description)
                            <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed" title="{{ $asset->description }}">{{ $asset->description }}</p>
                        @endif
                    </div>

                    <!-- Meta e Ações -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between no-print">
                        <span class="text-[10px] font-bold text-slate-400">
                            {{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($asset->file_size) }}
                        </span>

                        <div class="flex items-center gap-1">
                            <!-- Visualizar -->
                            <button type="button" 
                                    @click="openPreviewModal({{ $asset }})"
                                    class="w-7 h-7 flex items-center justify-center bg-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-[5px] transition-colors cursor-pointer"
                                    title="Visualizar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                            <!-- Baixar -->
                            <a href="{{ route('revisoes.assets.download', $asset->id) }}" 
                               class="w-7 h-7 flex items-center justify-center bg-transparent text-slate-500 hover:bg-slate-50 hover:text-slate-800 rounded-[5px] transition-colors cursor-pointer"
                               title="Baixar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                            <!-- Editar -->
                            <button type="button"
                                    @click="openEditModal({{ $asset }})"
                                    class="w-7 h-7 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-colors cursor-pointer"
                                    title="Editar">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>
                            <!-- Excluir -->
                            <button type="button" 
                                    @click="$dispatch('trigger-global-delete', { title: 'Excluir Asset', message: 'Deseja realmente remover este recurso do seu banco de assets?', action: '{{ route('revisoes.assets.destroy', $asset->id) }}', highSecurity: false })"
                                    class="w-7 h-7 flex items-center justify-center bg-transparent text-rose-600 hover:bg-rose-50 rounded-[5px] transition-colors cursor-pointer"
                                    title="Excluir">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        @empty
            <div class="col-span-full border border-dashed border-slate-200 bg-white p-12 text-center text-slate-400 rounded-[5px] font-semibold text-sm shadow-xs">
                Nenhum recurso cadastrado no seu banco de assets.
            </div>
        @endforelse
    </div>

    <!-- Estado de Filtro Vazio (Client-side) -->
    <div x-show="assetsList.filter(a => shouldShowAsset(a.title, a.description, a.type)).length === 0 && assetsList.length > 0" 
         class="text-center py-12 bg-white border border-slate-200 rounded-[5px] shadow-sm select-none" 
         x-cloak>
        <span class="text-5xl block">🔍</span>
        <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight mt-4">Nenhum asset corresponde à busca</h3>
        <p class="text-xs text-slate-400 mt-1">Experimente limpar a sua busca ou trocar o filtro de categoria.</p>
    </div>

    <!-- Modal de Cadastro / Upload Rápido (Upload File vs Code snippet) -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 9999;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-lg w-full p-6 space-y-4 text-left select-none" @click.away="createModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight">Adicionar Novo Asset</h3>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Abas (Tabs) -->
            <div class="flex border-b border-slate-150 text-xs font-bold uppercase tracking-wider">
                <button type="button" @click="uploadType = 'file'" class="flex-1 text-center py-2.5 border-b-2 transition-colors cursor-pointer" :class="uploadType === 'file' ? 'border-primary-500 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    📁 Upload de Arquivo
                </button>
                <button type="button" @click="uploadType = 'code'" class="flex-1 text-center py-2.5 border-b-2 transition-colors cursor-pointer" :class="uploadType === 'code' ? 'border-primary-500 text-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    💻 Trecho de Código
                </button>
            </div>

            @if ($errors->any())
                <div class="bg-rose-50 border border-rose-250 text-rose-700 text-xs p-3 rounded-[5px] space-y-1">
                    <span class="font-extrabold uppercase block text-[10px]">Erro ao salvar asset:</span>
                    <ul class="list-disc pl-4 space-y-0.5 font-medium">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('revisoes.assets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 pt-2">
                @csrf
                <input type="hidden" name="upload_type" x-bind:value="uploadType" />

                <!-- Título -->
                <div class="space-y-1.5">
                    <label for="title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome / Título do Recurso</label>
                    <input type="text" name="title" id="title" required placeholder="Ex: Logotipo Principal PNG, Fonte Futura Bold..." class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Descrição -->
                <div class="space-y-1.5">
                    <label for="description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição (Opcional)</label>
                    <textarea name="description" id="description" rows="2" placeholder="Onde usar, direitos, restrições ou notas..." class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400"></textarea>
                </div>

                <!-- Tab 1: Enviar Arquivo -->
                <div x-show="uploadType === 'file'" class="space-y-2">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Selecionar Arquivo</label>
                    <div 
                        @dragover.prevent="modalDragging = true"
                        @dragleave.prevent="modalDragging = false"
                        @drop.prevent="handleModalDrop($event)"
                        @click="$refs.modalFileInput.click()"
                        class="border-2 border-dashed rounded-[5px] p-6 text-center cursor-pointer transition-all duration-200 select-none flex flex-col items-center justify-center bg-slate-50/50"
                        :class="modalDragging ? 'border-primary-500 bg-primary-50/15 ring-4 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50'"
                    >
                        <span class="text-3xl mb-2">📁</span>
                        <h4 class="font-extrabold text-xs text-slate-700">Clique ou arraste o arquivo aqui</h4>
                        <p class="text-[10px] text-slate-450 mt-1 max-w-xs leading-relaxed">Imagens, Fontes, Códigos ou Arquivos Diversos.</p>
                        
                        <div x-show="selectedFileName" class="mt-3 text-xs font-bold text-primary-650 bg-primary-50 px-2.5 py-1 rounded-full uppercase tracking-wider" x-text="selectedFileName" x-cloak></div>
                    </div>
                    <input type="file" name="file" x-ref="modalFileInput" @change="handleModalFileChange($event)" class="hidden" />
                    <p class="text-[10px] text-slate-400 mt-1">Classificação automática baseada na extensão do arquivo.</p>
                </div>

                <!-- Tab 2: Trecho de Código -->
                <div x-show="uploadType === 'code'" class="space-y-4">
                    <div class="space-y-1.5">
                        <label for="code_extension" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Extensão do Arquivo / Linguagem</label>
                        <select name="code_extension" id="code_extension" class="w-full border border-slate-200 rounded-[5px] text-sm px-4 py-2.5 bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 text-slate-650 cursor-pointer font-bold">
                            <option value="txt">Texto (.txt)</option>
                            <option value="js">JavaScript (.js)</option>
                            <option value="ts">TypeScript (.ts)</option>
                            <option value="css">CSS (.css)</option>
                            <option value="html">HTML (.html)</option>
                            <option value="php">PHP (.php)</option>
                            <option value="json">JSON (.json)</option>
                            <option value="sql">SQL (.sql)</option>
                            <option value="py">Python (.py)</option>
                            <option value="bat">Windows Batch (.bat)</option>
                            <option value="sh">Shell Script (.sh)</option>
                            <option value="md">Markdown (.md)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="code_snippet" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Código Fonte / Snippet</label>
                        <textarea name="code_snippet" id="code_snippet" rows="6" placeholder="Cole o código aqui..." class="w-full p-3 rounded-[5px] border border-slate-200 text-xs font-mono bg-slate-900 text-emerald-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 border border-slate-200 text-xs font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-600">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-[5px] transition-colors">
                        Salvar Asset
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Modal de Edição Rápida -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 9999;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-lg w-full p-6 space-y-4 text-left select-none" @click.away="editModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight">Editar Recurso</h3>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="'{{ route('revisoes.assets.index') }}' + '/' + editAssetData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Título -->
                <div class="space-y-1.5">
                    <label for="edit_title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome / Título</label>
                    <input type="text" name="title" id="edit_title" x-model="editAssetData.title" required class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Descrição -->
                <div class="space-y-1.5">
                    <label for="edit_description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição</label>
                    <textarea name="description" id="edit_description" x-model="editAssetData.description" rows="2" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400"></textarea>
                </div>

                <!-- Edição de Código Snippet se for código -->
                <div x-show="editAssetData.type === 'codigo'" class="space-y-1.5">
                    <label for="edit_code_snippet" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Editar Código Fonte / Snippet</label>
                    <textarea name="code_snippet" id="edit_code_snippet" x-model="editAssetData.code_snippet" rows="6" class="w-full p-3 rounded-[5px] border border-slate-200 text-xs font-mono bg-slate-900 text-emerald-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 border border-slate-200 text-xs font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-600">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-[5px] transition-colors">
                        Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>



    <!-- Botão de Adicionar Flutuante -->
    <button type="button" 
            @click="createModalOpen = true; uploadType = 'file';"
            class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30 cursor-pointer" 
            title="Adicionar Asset">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>

</div>

<!-- Helper Form for Batch Downloads -->
<form id="batchDownloadForm" action="{{ route('revisoes.assets.download-batch') }}" method="POST" class="hidden">
    @csrf
</form>

<!-- Helper Form for Batch Delete -->
<form id="batchDeleteForm" action="{{ route('revisoes.assets.destroy-batch') }}" method="POST" class="hidden">
    @csrf
</form>

<script>
    function assetsManager() {
        return {
            selectedIds: [],
            createModalOpen: {{ $errors->any() ? 'true' : 'false' }},
            editModalOpen: false,
            uploadType: '{{ old('upload_type', 'file') }}',
            editAssetData: {},

            assetsList: @json($assets),
            searchQuery: '{{ request('search', '') }}',
            filterType: '{{ request('type', '') }}',

            shouldShowAsset(title, description, type) {
                if (this.filterType && type !== this.filterType) return false;
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const t = (title || '').toLowerCase();
                    const d = (description || '').toLowerCase();
                    return t.includes(q) || d.includes(q);
                }
                return true;
            },
            
            // Preview lightbox states
            previewModalOpen: false,
            previewAsset: {},
            imageDimensions: '',

            openPreviewModal(asset) {
                this.$dispatch('trigger-global-preview', { asset: asset });
            },

            getImageDetails(e) {
                this.imageDimensions = e.target.naturalWidth + ' × ' + e.target.naturalHeight + ' px';
            },

            isVideoAsset(asset) {
                if (!asset || !asset.file_path) return false;
                const ext = asset.file_path.split('.').pop().toLowerCase();
                const videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                return videoExtensions.includes(ext) || (asset.mime_type && asset.mime_type.startsWith('video/'));
            },

            formatBytes(bytes) {
                if (!bytes) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            },
            
            // Drag and drop states
            selectedFileName: '',
            modalDragging: false,

            handleModalFileChange(e) {
                const file = e.target.files[0];
                if (file) {
                    this.selectedFileName = file.name;
                    // Auto-fill title
                    const titleInput = document.getElementById('title');
                    if (titleInput && !titleInput.value) {
                        titleInput.value = file.name;
                    }
                }
            },

            handleModalDrop(e) {
                this.modalDragging = false;
                const file = e.dataTransfer.files[0];
                if (file) {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    this.$refs.modalFileInput.files = dt.files;
                    this.selectedFileName = file.name;
                    
                    // Auto-fill title
                    const titleInput = document.getElementById('title');
                    if (titleInput && !titleInput.value) {
                        titleInput.value = file.name;
                    }
                }
            },

            openEditModal(asset) {
                this.editAssetData = Object.assign({}, asset);
                this.editModalOpen = true;
            },

            downloadSelected() {
                const form = document.getElementById('batchDownloadForm');
                // Remove previous inputs
                form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
                
                this.selectedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                form.submit();
            },

            deleteSelected() {
                if (confirm('Deseja realmente excluir todos os ' + this.selectedIds.length + ' assets selecionados permanentemente?')) {
                    const form = document.getElementById('batchDeleteForm');
                    form.querySelectorAll('input[name="ids[]"]').forEach(el => el.remove());
                    
                    this.selectedIds.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    form.submit();
                }
            }
        }
    }
</script>
@endsection
