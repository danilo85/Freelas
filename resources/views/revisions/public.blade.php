<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Revisão de Trabalho | {{ $revision->title }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PDF.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>

    <style>
        [x-cloak] { display: none !important; }
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(0, 0, 0, 0.08) solid;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.03);
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.15);
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, 0.25);
        }

        /* Resizing Dividers */
        .resizer-bar-v {
            width: 4px;
            cursor: col-resize;
            background: rgba(0, 0, 0, 0.04);
            transition: background 0.2s;
        }
        .resizer-bar-v:hover {
            background: #3b82f6;
        }

        /* Tour highlight ring styling */
        .tour-highlight {
            position: relative;
            z-index: 99999;
            box-shadow: 0 0 0 9999px rgba(15, 23, 42, 0.75) !important;
            border-radius: 4px;
            pointer-events: none;
        }
    </style>
</head>
<body class="font-sans antialiased h-screen overflow-hidden flex flex-col justify-between" x-data="proofingSystem({{ json_encode($annotations) }})">

    <!-- Premium Screen Loading Loader Overlay -->
    <div x-show="isInitialLoading" 
         class="fixed inset-0 bg-slate-900 flex flex-col items-center justify-center space-y-6"
         style="z-index: 999999;"
         x-transition:leave="transition ease-in duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <span class="font-outfit font-black text-xl tracking-tight text-white select-none">
            DANILO<span class="text-blue-500">MIGUEL</span>
        </span>
        <div class="w-64 bg-slate-800 rounded-full h-2.5 overflow-hidden shadow-inner">
            <div class="bg-blue-500 h-2.5 rounded-full transition-all duration-200 shadow-md shadow-blue-500/50" :style="'width: ' + loadPercentage + '%'"></div>
        </div>
        <div class="flex flex-col items-center gap-1.5 text-center select-none">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-widest" x-text="'Carregando Documento... ' + loadPercentage + '%'"></span>
            <span class="text-[10px] text-slate-500 font-medium">Processando recursos gráficos e fontes de prova</span>
        </div>
    </div>

    <!-- TOP HEADER -->
    <header class="h-16 border-b border-slate-200 glassmorphism px-6 flex items-center justify-between z-30 shrink-0">
        <div class="flex items-center gap-3">
            <span class="font-outfit font-black text-sm tracking-tight text-slate-800">
                DANILO<span class="text-blue-500">MIGUEL</span>
            </span>
            <span class="h-4 w-px bg-slate-200"></span>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Painel de Provas</span>
        </div>

        <!-- Seletor de Autor Ativo -->
        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3 py-1 rounded-[5px] text-xs font-medium" id="tour-step-author">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Quem está revisando:</span>
            <select x-model="selectedAuthorId" 
                    @change="persistAuthor()"
                    class="bg-transparent border-none focus:outline-none font-bold text-slate-700 cursor-pointer">
                <option value="">Revisor Geral / Cliente</option>
                @foreach($authors as $author)
                    <option value="{{ $author->id }}">{{ $author->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex items-center gap-2">
            <!-- Botão Iniciar Tour / Ajuda -->
            <button @click="startInteractiveTour()" class="text-blue-600 hover:text-blue-800 font-bold text-xs uppercase tracking-wider px-3 py-2 rounded-[5px] hover:bg-blue-50 flex items-center gap-1">
                🚀 Guia Rápido
            </button>

            <!-- Botão Atalhos de Teclado -->
            <button @click="showShortcutGuide = true" class="text-slate-600 hover:text-slate-900 font-bold text-xs uppercase tracking-wider px-3 py-2 rounded-[5px] hover:bg-slate-100 flex items-center gap-1">
                Atalhos
            </button>

            <!-- Dropdown Downloads -->
            <div class="relative" x-data="{ open: false }" @click.away="open = false">
                <button @click="open = !open" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] transition-all flex items-center gap-1.5 shadow-md shadow-blue-600/25">
                    📥 Download
                    <svg class="w-3 h-3 text-white transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" class="absolute right-0 mt-2 w-56 rounded-[5px] bg-white border border-slate-200 shadow-2xl py-1 text-left z-50 glassmorphism" x-cloak>
                    @if($activeRound)
                        <a href="{{ route('public.revisao.download.all', $activeRound->id) }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-50">
                            🗂️ Baixar Arquivos Originais (.ZIP)
                        </a>
                        <a href="{{ route('public.revisao.download.report', $activeRound->id) }}" class="block px-4 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-50 border-t border-slate-100">
                            📄 Baixar Relatório de Comentários (.TXT)
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- MAIN BODY: THREE COLUMNS WITH DIVIDERS -->
    <main class="flex-1 flex overflow-hidden h-[calc(100vh-4rem)]">
        
        <!-- COLUMN 1 (LEFT): ADJUSTMENTS LIST -->
        <aside id="aside-left" 
               class="w-80 border-r border-slate-200 glassmorphism flex flex-col justify-between shrink-0 h-full overflow-hidden hidden lg:flex">
            
            <!-- Active File Info -->
            @if($activeFile)
                <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between shrink-0">
                    <div class="min-w-0">
                        <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block">Arquivo Ativo</span>
                        <h5 class="text-xs font-bold text-slate-800 truncate mt-1" title="{{ $activeFile->filename }}">{{ $activeFile->filename }}</h5>
                    </div>
                    <span class="text-[9px] font-bold bg-slate-200 px-2 py-0.5 rounded-full uppercase tracking-wider text-slate-600 shrink-0">
                        {{ strtoupper($activeFile->file_type) }}
                    </span>
                </div>
            @endif

            <!-- Tabs de Ajustes (Aberto / Resolvido) -->
            <div class="flex border-b border-slate-200 text-xs font-bold uppercase tracking-wider shrink-0" id="tour-step-tabs">
                <button @click="activeTab = 'aberto'" 
                        class="flex-1 py-3 text-center border-b-2"
                        :class="activeTab === 'aberto' ? 'border-blue-500 text-blue-600 bg-slate-50/50' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    Pendentes
                </button>
                <button @click="activeTab = 'resolvido'" 
                        class="flex-1 py-3 text-center border-b-2"
                        :class="activeTab === 'resolvido' ? 'border-blue-500 text-blue-600 bg-slate-50/50' : 'border-transparent text-slate-400 hover:text-slate-600'">
                    Resolvidos
                </button>
            </div>

            <!-- Annotations Feed -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div id="annotations-list" class="space-y-3">
                    
                    <div class="text-center text-sm text-slate-450 py-12 italic" 
                         x-show="filteredAnnotationsCount() === 0">
                        Nenhuma anotação nesta categoria.
                    </div>

                    <template x-for="(anno, idx) in annotationsList" :key="anno.id">
                        <div class="bg-white border border-slate-200 rounded-[5px] p-3.5 hover:border-slate-350 transition-all hover:shadow-sm cursor-pointer relative"
                             :id="'anno-card-' + anno.id"
                             x-show="shouldShowAnno(anno.status)"
                             @click="focusAnnotation(anno.id, anno.page_number, anno.drawing_data)">
                            
                            <!-- Header Ajuste -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 mb-2 text-[10px] font-bold text-slate-500">
                                <span x-text="'Ajuste #' + (idx + 1) + ' • Página ' + anno.page_number"></span>
                                <span class="uppercase tracking-wider font-black px-2 py-0.5 rounded-full border text-[9px]"
                                      :class="anno.status === 'aberto' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'"
                                      :id="'anno-badge-' + anno.id"
                                      x-text="anno.status">
                                </span>
                            </div>

                            <!-- Autor que comentou -->
                            <div class="flex items-center gap-1.5 mb-2 text-[10px] font-bold text-slate-500" x-show="anno.author">
                                👤 <span x-text="anno.author ? anno.author.name : ''"></span>
                            </div>

                            <!-- Comentário Formatado -->
                            <div class="text-sm text-slate-700 leading-relaxed font-normal whitespace-pre-line" 
                                 :id="'anno-comment-' + anno.id"
                                 x-html="renderFormattedComment(anno.comment)">
                            </div>

                            <!-- Imagem de Anexo se houver -->
                            <div class="mt-2.5 rounded-[3px] border border-slate-200 overflow-hidden bg-slate-50 p-1" x-show="anno.attachment_path">
                                <a :href="'/storage/' + anno.attachment_path" target="_blank" class="block">
                                    <img :src="'/storage/' + anno.attachment_path" class="max-h-24 w-full object-cover rounded-[2px] hover:opacity-90 transition-opacity">
                                </a>
                                <span class="text-[9px] text-slate-400 font-bold block text-center mt-1">📎 Imagem de Referência</span>
                            </div>

                            <!-- Data e Ações -->
                            <div class="flex items-center justify-between mt-2.5 pt-2.5 border-t border-slate-100">
                                <span class="text-[9px] text-slate-400 font-medium" x-text="formatDate(anno.created_at)"></span>
                                <div class="flex gap-2">
                                    <button @click.stop="editAnnotation(anno.id)" class="text-[10px] font-bold uppercase text-blue-600 hover:text-blue-700 hover:underline">
                                        Editar
                                    </button>
                                    <button @click.stop="toggleResolve(anno.id)" 
                                            class="text-[10px] font-bold uppercase hover:underline"
                                            :class="anno.status === 'aberto' ? 'text-emerald-600 hover:text-emerald-700' : 'text-slate-500 hover:text-slate-800'">
                                        <span x-text="resolveBtnText[anno.id] || (anno.status === 'aberto' ? 'Resolvido' : 'Reabrir')"></span>
                                    </button>
                                    <button @click.stop="deleteAnnotation(anno.id)" class="text-[10px] font-bold uppercase text-rose-600 hover:text-rose-700 hover:underline">
                                        Excluir
                                    </button>
                                </div>
                            </div>

                        </div>
                    </template>
                </div>
            </div>
        </aside>

        <!-- Divider resizer 1 -->
        <div class="resizer-bar-v hidden lg:block" id="resizer-left"></div>

        <!-- COLUMN 2 (MIDDLE): VISUALIZER & DRAWING CANVAS -->
        <section id="middle-viewport" class="flex-1 flex flex-col justify-between overflow-hidden relative bg-slate-100 h-full" :class="isFullscreen ? 'fixed inset-0 z-50' : ''">
            
            <!-- Visualizer Toolbar -->
            <div class="h-12 border-b border-slate-200 glassmorphism px-4 flex items-center justify-between z-20 shrink-0 select-none" id="tour-step-toolbar">
                
                <!-- Tool Selector -->
                <div class="flex items-center gap-1.5">
                    <button @click="setTool('freehand')" 
                            class="p-2 rounded-[5px] transition-colors"
                            :class="activeTool === 'freehand' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
                            title="Desenho Livre (Caneta)">
                        🎨
                    </button>
                    <button @click="setTool('rectangle')" 
                            class="p-2 rounded-[5px] transition-colors"
                            :class="activeTool === 'rectangle' ? 'bg-blue-600 text-white' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
                            title="Marcação Retangular">
                        ⬜
                    </button>
                    <button @click="clearStrokes()" 
                            class="p-2 rounded-[5px] text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                            title="Limpar Desenho Atual">
                        🧹
                    </button>
                    
                    <span class="h-4 w-px bg-slate-300 mx-1"></span>

                    <!-- Zoom Controls -->
                    <button @click="zoomOut()" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-[5px]" title="Diminuir Zoom">🔍-</button>
                    <span class="text-[10px] font-bold text-slate-500 min-w-[35px] text-center" x-text="Math.round(zoomScale * 100) + '%'">100%</span>
                    <button @click="zoomIn()" class="p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 rounded-[5px]" title="Aumentar Zoom">🔍+</button>
                </div>

                <!-- Page Navigation -->
                @if($activeFile && $activeFile->file_type === 'pdf')
                    <div class="flex items-center gap-4 text-xs font-bold uppercase tracking-wider text-slate-700" id="tour-step-navigation">
                        
                        <!-- Toggle Página Simples / Dupla -->
                        <div class="flex items-center gap-1 bg-slate-200 p-0.5 rounded-[5px] border border-slate-300">
                            <button @click="setPageMode('single')" 
                                    class="px-2 py-1 rounded-[3px] text-[10px]"
                                    :class="pageMode === 'single' ? 'bg-white text-slate-850 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                                📄 Simples
                            </button>
                            <button @click="setPageMode('double')" 
                                    class="px-2 py-1 rounded-[3px] text-[10px]"
                                    :class="pageMode === 'double' ? 'bg-white text-slate-850 shadow-sm' : 'text-slate-500 hover:text-slate-800'">
                                📖 Dupla
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button @click="prevPage()" class="p-1.5 bg-slate-200 border border-slate-350 rounded hover:bg-slate-300 transition-colors" :disabled="currentPage <= 1">
                                ◀
                            </button>
                            <template x-if="pageMode === 'single'">
                                <span>Página <span x-text="currentPage">1</span> de <span x-text="numPages">1</span></span>
                            </template>
                            <template x-if="pageMode === 'double'">
                                <span>Pág. <span x-text="currentPage">1</span>-<span x-text="Math.min(numPages, currentPage + 1)">2</span> de <span x-text="numPages">1</span></span>
                            </template>
                            <button @click="nextPage()" class="p-1.5 bg-slate-200 border border-slate-350 rounded hover:bg-slate-300 transition-colors" :disabled="currentPage >= numPages || (pageMode === 'double' && currentPage + 1 >= numPages)">
                                ▶
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Visuals & Mode options -->
                <div class="flex items-center gap-1.5">
                    
                    <button @click="toggleVisibility()" 
                            class="p-2 rounded-[5px] transition-colors"
                            :class="hideAllAnnotations ? 'bg-amber-500 text-white' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
                            title="Alternar ocultação de marcações não selecionadas">
                        👁️
                    </button>

                    <button @click="toggleScrollLock()" 
                            class="p-2 rounded-[5px] transition-colors"
                            :class="scrollLocked ? 'bg-rose-600 text-white' : 'text-slate-500 hover:text-slate-800 hover:bg-slate-100'"
                            title="Bloquear/Travar rolagem ao desenhar">
                        🔒
                    </button>

                    <button @click="toggleFullscreen()" 
                            class="p-2 rounded-[5px] text-slate-555 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                            title="Modo Tela Inteira">
                        🖥️
                    </button>

                    <span class="h-4 w-px bg-slate-300 mx-1.5"></span>

                    <!-- Color Selector -->
                    <div class="flex items-center gap-1">
                        <template x-for="c in colorPalette" :key="c.value">
                            <button @click="setColor(c.value)" 
                                    class="w-5 h-5 rounded-full border border-white/20 transition-transform"
                                    :class="strokeColor === c.value ? 'scale-125 border-slate-800 ring-2 ring-blue-500/40' : 'hover:scale-110'"
                                    :style="'background-color: ' + c.value"
                                    :title="c.label">
                            </button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Visualizer Container -->
            <div class="flex-1 overflow-auto p-6 flex items-center justify-center relative select-none h-full" 
                 id="visualizer-scroll-container"
                 :style="scrollLocked ? 'overflow: hidden !important;' : ''">
                
                @if($activeFile)
                    <!-- Active File Proofing Area Wrapper -->
                    <div class="relative shadow-2xl border border-slate-250 bg-white max-w-full" 
                         id="proofing-viewport"
                         @contextmenu.prevent="handleRightClick($event)">
                        
                        <!-- Visual Loader Spinner during render -->
                        <div id="pdf-render-spinner" class="absolute inset-0 bg-white/70 z-30 flex items-center justify-center hidden">
                            <div class="flex flex-col items-center gap-3">
                                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Carregando Prova...</span>
                            </div>
                        </div>

                        <!-- 1. Background File Viewer -->
                        @if($activeFile->file_type === 'pdf')
                            <!-- Render PDF using canvas -->
                            <canvas id="pdf-canvas" class="max-w-full block"></canvas>
                        @else
                            <!-- Image file viewer -->
                            <img src="" 
                                 id="image-viewer" 
                                 class="max-w-full max-h-[75vh] object-contain block transition-all"
                                 :style="'transform: scale(' + zoomScale + '); transform-origin: center;'"
                                 @load="initImageDimensions()">
                        @endif

                        <!-- 2. Transparent Drawing Canvas Overlay -->
                        <canvas id="markup-canvas" 
                                class="absolute inset-0 cursor-crosshair z-10"
                                @mousedown="startDrawing($event)"
                                @mousemove="drawStrokes($event)"
                                @mouseup="endDrawing($event)"
                                @touchstart="startDrawing($event)"
                                @touchmove="drawStrokes($event)"
                                @touchend="endDrawing($event)"
                                @contextmenu.prevent="handleRightClick($event)"></canvas>

                    </div>
                @else
                    <div class="text-center text-slate-400 max-w-xs space-y-3">
                        <span class="text-4xl block">📂</span>
                        <h4 class="font-outfit font-black text-slate-850 text-md">Nenhum arquivo enviado</h4>
                        <p class="text-xs leading-relaxed">Crie pastas e envie PDFs ou Imagens no gerenciador administrativo para iniciar o processo de revisão pública.</p>
                    </div>
                @endif

                <!-- Draggable Comment Dialogue Input Box -->
                <div x-show="showCommentBox" 
                     id="draggable-dialog"
                     class="absolute bg-white border border-slate-255 shadow-2xl rounded-[5px] w-88 z-40 glassmorphism text-left overflow-hidden flex flex-col"
                     :style="'top: ' + commentBoxY + 'px; left: ' + commentBoxX + 'px;'"
                     x-transition
                     x-cloak>
                    
                    <!-- Uploading Progress overlay inside comment box -->
                    <div x-show="isUploading" 
                         class="absolute inset-0 bg-white/95 z-50 flex flex-col items-center justify-center p-6 space-y-4"
                         style="z-index: 100;"
                         x-transition>
                        <svg class="animate-spin h-7 w-7 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden max-w-[200px]">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-150" :style="'width: ' + uploadPercentage + '%'"></div>
                        </div>
                        <span class="text-xs font-bold text-slate-655 uppercase tracking-wider" x-text="'Enviando ajuste... ' + uploadPercentage + '%'"></span>
                    </div>

                    <!-- Drag handle header -->
                    <div @mousedown="startDragBox($event)" 
                         class="px-4 py-2.5 bg-slate-50 border-b border-slate-200 cursor-move flex items-center justify-between shrink-0 select-none">
                        <span class="text-xs font-black text-slate-655 uppercase tracking-widest flex items-center gap-1.5 flex-1 pr-4">
                            <span x-text="editingAnnoId ? '✏️' : '✍️'"></span> 
                            <span x-text="editingAnnoId ? 'Editar Ajuste' : 'Novo Ajuste / Sugestão'"></span>
                        </span>
                        <button type="button" @click="cancelAnnotation()" class="text-slate-500 hover:text-slate-800 text-xs font-bold">✕</button>
                    </div>
                    
                    <div class="p-4 space-y-3.5 overflow-y-auto max-h-[450px]">
                        
                        <!-- Rich Text formatting toolbar -->
                        <div class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 p-1.5 rounded-[5px]">
                            <button type="button" @click="formatText('bold')" class="px-2 py-0.5 rounded text-xs font-bold hover:bg-slate-200 text-slate-750" title="Negrito">B</button>
                            <button type="button" @click="formatText('italic')" class="px-2 py-0.5 rounded text-xs italic hover:bg-slate-200 text-slate-750" title="Itálico">I</button>
                            <button type="button" @click="formatText('underline')" class="px-2 py-0.5 rounded text-xs underline hover:bg-slate-200 text-slate-750" title="Sublinhado">U</button>
                            <button type="button" @click="formatText('superscript')" class="px-2 py-0.5 rounded text-xs hover:bg-slate-200 text-slate-750" title="Sobrescrito">X²</button>
                            <button type="button" @click="formatText('subscript')" class="px-2 py-0.5 rounded text-xs hover:bg-slate-200 text-slate-750" title="Subscrito">X₂</button>
                        </div>

                        <!-- WYSIWYG Editable content div -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">O que ajustar neste ponto? <span class="text-[8px] font-medium text-slate-400">(Ctrl + Enter para Salvar)</span></span>
                            <div id="wysiwyg-editor" 
                                 contenteditable="true"
                                 class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-[5px] p-2 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 min-h-[100px] max-h-[180px] overflow-y-auto"
                                 @blur="commentText = $el.innerHTML"></div>
                        </div>
                        
                        <!-- Drag & Drop Attachment Dropzone -->
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Referência visual (Imagem)</span>
                            <div @dragover.prevent="dragOverAttachment = true"
                                 @dragleave.prevent="dragOverAttachment = false"
                                 @drop.prevent="handleDropAttachment($event)"
                                 @click="document.getElementById('attachmentInput').click()"
                                 class="border-2 border-dashed rounded-[5px] p-4 text-center cursor-pointer transition-colors"
                                 :class="dragOverAttachment ? 'border-blue-500 bg-blue-50/20' : 'border-slate-200 hover:border-slate-350 bg-slate-50/30'">
                                
                                <span class="text-xs text-slate-500 block leading-snug" x-text="attachmentFileName || 'Solte imagem aqui ou clique para selecionar'"></span>
                                <input type="file" id="attachmentInput" accept="image/*" class="hidden" @change="handleFileSelect($event)">
                            </div>
                        </div>

                        <div class="flex justify-between items-center gap-2 pt-2 border-t border-slate-100">
                            <!-- Descartar desenho atual -->
                            <button type="button" @click="cancelAnnotation()" class="text-[10px] font-bold uppercase text-rose-500 hover:underline" x-show="!editingAnnoId">
                                Descartar
                            </button>
                            <div class="flex gap-2" :class="editingAnnoId ? 'w-full justify-between' : ''">
                                <button type="button" @click="cancelAnnotation()" class="px-3 py-1.5 border border-slate-200 text-[10px] font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-655">
                                    Cancelar
                                </button>
                                <button type="button" @click="saveAnnotation()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 text-[10px] font-bold uppercase rounded-[5px] transition-colors shadow-md shadow-blue-600/10">
                                    Salvar Ajuste
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom Right-Click Context Menu -->
                <div x-show="showContextMenu"
                     class="absolute bg-white border border-slate-200 shadow-2xl rounded-[5px] w-48 z-50 p-1 flex flex-col select-none"
                     :style="'top: ' + contextMenuY + 'px; left: ' + contextMenuX + 'px;'"
                     @click.away="showContextMenu = false"
                     x-transition
                     x-cloak>
                    
                    <template x-if="contextMenuMode === 'annotation'">
                        <div class="flex flex-col">
                            <button @click="resolveContextMenuAction('focus')" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2">🔍 Focar Ajuste</button>
                            <button @click="resolveContextMenuAction('edit')" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 border-t border-slate-100 flex items-center gap-2">✏️ Editar Ajuste</button>
                            <button @click="resolveContextMenuAction('toggle')" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 border-t border-slate-100 flex items-center gap-2">✅ Alternar Status</button>
                            <button @click="resolveContextMenuAction('delete')" class="text-left px-3 py-2 text-xs font-bold text-rose-600 hover:bg-rose-50 border-t border-slate-100 flex items-center gap-2">🗑️ Excluir permanente</button>
                        </div>
                    </template>

                    <template x-if="contextMenuMode === 'generic'">
                        <div class="flex flex-col">
                            <span class="px-3 py-1 text-[8px] font-extrabold uppercase text-slate-400 tracking-wider">Ações e Atalhos</span>
                            
                            <button @click="setTool('freehand'); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 border-t border-slate-100 flex items-center gap-2">🎨 Caneta Livre</button>
                            <button @click="setTool('rectangle'); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2">⬜ Retângulo</button>
                            <button @click="clearStrokes(); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2">🧹 Limpar Desenho</button>
                            
                            <span class="px-3 py-1 text-[8px] font-extrabold uppercase text-slate-400 tracking-wider border-t border-slate-100">Visualização</span>
                            <button @click="zoomIn(); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 border-t border-slate-100 flex items-center gap-2">🔍+ Aumentar Zoom</button>
                            <button @click="zoomOut(); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2">🔍- Diminuir Zoom</button>
                            <button @click="toggleScrollLock(); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2" x-text="scrollLocked ? '🔓 Destravar Rolagem' : '🔒 Travar Rolagem'"></button>
                            <button @click="toggleFullscreen(); showContextMenu = false;" class="text-left px-3 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50 flex items-center gap-2">🖥️ Tela Inteira</button>
                        </div>
                    </template>
                </div>

            </div>

        </section>

        <!-- Divider resizer 2 -->
        <div class="resizer-bar-v hidden md:block" id="resizer-right-bar"></div>

        <!-- COLUMN 3 (RIGHT): FILES TREE -->
        <aside id="aside-right" 
               class="w-80 border-l border-slate-200 glassmorphism flex flex-col justify-between shrink-0 h-full overflow-hidden hidden md:flex"
               id="tour-step-files">
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 shrink-0">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Rodada #{{ $activeRound->round_number ?? '1' }}</span>
                <h4 class="text-xs font-bold text-slate-800 leading-tight truncate mt-1">Navegador de Arquivos</h4>
            </div>

            <!-- Virtual Folder Structure Tree -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                @if($files->isEmpty())
                    <p class="text-xs text-slate-400 text-center py-6 italic">Sem arquivos para exibir.</p>
                @else
                    @php
                        $groupedFiles = $files->groupBy(function($item) {
                            return $item->folder_name ?: 'Diretório Raiz';
                        });
                    @endphp

                    @foreach($groupedFiles as $folder => $folderFiles)
                        <div x-data="{ openFolder: true }" class="space-y-1.5">
                            
                            <!-- Folder header -->
                            <div @click="openFolder = !openFolder" class="flex items-center justify-between text-xs font-bold text-slate-655 hover:text-slate-800 cursor-pointer select-none">
                                <div class="flex items-center gap-1.5 truncate">
                                    <span>📁</span>
                                    <span class="truncate uppercase tracking-wider text-[10px]">{{ $folder }}</span>
                                </div>
                                <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="openFolder ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>

                            <!-- Folder files list -->
                            <div x-show="openFolder" class="pl-4 space-y-1" x-cloak>
                                @foreach($folderFiles as $file)
                                    @php
                                        $icon = '📎';
                                        if(in_array($file->file_type, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) $icon = '🖼️';
                                        elseif($file->file_type === 'pdf') $icon = '📄';
                                    @endphp
                                    <a href="{{ route('public.revisao.show', $revision->share_token) }}?file={{ $file->id }}" 
                                       class="flex items-center justify-between gap-2 p-1.5 rounded-[5px] text-xs font-medium truncate transition-colors 
                                            {{ ($activeFile && $activeFile->id === $file->id) ? 'bg-blue-50 text-blue-600 border border-blue-200' : 'text-slate-600 hover:text-slate-800 hover:bg-slate-100' }}">
                                        
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span>{{ $icon }}</span>
                                            <span class="truncate" title="{{ $file->filename }}">{{ $file->filename }}</span>
                                        </div>
                                        
                                        @if($file->annotations->count() > 0)
                                            <span class="text-[9px] font-extrabold px-1.5 py-0.5 rounded-full bg-rose-50 text-rose-600 border border-rose-100 shrink-0">
                                                {{ $file->annotations->where('status', 'aberto')->count() }}
                                            </span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                @endif
            </div>

            <!-- Footer -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50 shrink-0">
                <div class="flex items-center gap-2 text-[10px] text-slate-400">
                    <span>💡 Dica: Arraste o mouse sobre o trabalho para desenhar marcações de ajuste.</span>
                </div>
            </div>
        </aside>

    </main>

    <!-- Interactive Onboarding Guided Tour Popup -->
    <div x-show="showTour" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/60 backdrop-blur-xs"
         style="z-index: 999999;"
         x-transition
         x-cloak>
        
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-sm w-full p-6 glassmorphism space-y-4 text-left select-none relative">
            <button @click="endTour()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 text-xs font-bold">✕ Encerrar</button>
            
            <div class="flex items-center gap-3">
                <span class="text-3xl" x-text="tourSteps[currentTourStep].icon">👋</span>
                <div class="min-w-0">
                    <span class="text-[9px] font-bold text-blue-500 uppercase tracking-widest block" x-text="'Passo ' + (currentTourStep + 1) + ' de ' + tourSteps.length"></span>
                    <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest mt-0.5" x-text="tourSteps[currentTourStep].title">Bem-vindo!</h4>
                </div>
            </div>
            
            <p class="text-xs text-slate-600 leading-relaxed font-medium" x-text="tourSteps[currentTourStep].description"></p>
            
            <div class="flex justify-between items-center pt-2.5 border-t border-slate-100">
                <!-- Progress Indicator dots -->
                <div class="flex gap-1.5">
                    <template x-for="(step, idx) in tourSteps">
                        <span class="w-1.5 h-1.5 rounded-full transition-colors"
                              :class="idx === currentTourStep ? 'bg-blue-600' : 'bg-slate-200'"></span>
                    </template>
                </div>
                
                <div class="flex gap-2">
                    <button @click="prevTourStep()" 
                            class="px-3.5 py-1.5 border border-slate-200 text-[10px] font-bold uppercase rounded-[5px] hover:bg-slate-50 transition-colors text-slate-655"
                            x-show="currentTourStep > 0">
                        Anterior
                    </button>
                    <button @click="nextTourStep()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-1.5 text-[10px] font-bold uppercase rounded-[5px] shadow-md shadow-blue-600/20 transition-colors">
                        <span x-text="currentTourStep === tourSteps.length - 1 ? 'Concluir!' : 'Próximo'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom Keyboard Shortcuts Guide Modal -->
    <div x-show="showShortcutGuide" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm shadow-2xl"
         style="z-index: 99999;"
         x-transition
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-sm w-full p-6 glassmorphism space-y-4 text-left select-none">
            <div class="flex items-center gap-3 border-b border-slate-150 pb-2.5">
                <span class="text-3xl">⌨️</span>
                <h4 class="text-sm font-black text-slate-850 uppercase tracking-widest">Atalhos do Teclado</h4>
            </div>
            
            <div class="space-y-3.5 py-1">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Salvar Novo Comentário</span>
                    <kbd class="bg-slate-100 border border-slate-300 px-2 py-1 rounded font-mono font-bold text-slate-800 text-[10px]">Ctrl + Enter</kbd>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Cancelar / Fechar Caixa</span>
                    <kbd class="bg-slate-100 border border-slate-300 px-2 py-1 rounded font-mono font-bold text-slate-800 text-[10px]">ESC</kbd>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Avançar Página</span>
                    <kbd class="bg-slate-100 border border-slate-300 px-2 py-1 rounded font-mono font-bold text-slate-800 text-[10px]">→ (Seta Direita)</kbd>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Voltar Página</span>
                    <kbd class="bg-slate-100 border border-slate-300 px-2 py-1 rounded font-mono font-bold text-slate-800 text-[10px]">← (Seta Esquerda)</kbd>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-500 font-semibold">Mais Zoom / Menos Zoom</span>
                    <kbd class="bg-slate-100 border border-slate-300 px-2 py-1 rounded font-mono font-bold text-slate-800 text-[10px]">Ctrl + / Ctrl -</kbd>
                </div>
            </div>

            <div class="flex justify-end pt-2">
                <button @click="showShortcutGuide = false" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] shadow-md shadow-blue-600/20 transition-colors">
                    Fechar Atalhos
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Reusable Notification Modal -->
    <div x-show="showNotification" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 99999;"
         x-transition
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-sm w-full p-6 glassmorphism space-y-4 text-left select-none">
            <div class="flex items-center gap-3">
                <span class="text-3xl" x-text="notificationType === 'error' ? '❌' : (notificationType === 'success' ? '✅' : '⚠️')">⚠️</span>
                <h4 class="text-sm font-black text-slate-800 uppercase tracking-widest" x-text="notificationTitle">Aviso</h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium" x-text="notificationMsg"></p>
            <div class="flex justify-end pt-2">
                <button @click="showNotification = false" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] shadow-md shadow-blue-600/20 transition-colors">
                    Entendi
                </button>
            </div>
        </div>
    </div>

    <!-- Custom Reusable Confirm Modal -->
    <div x-show="showConfirmModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 99999;"
         x-transition
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-sm w-full p-6 glassmorphism space-y-4 text-left select-none">
            <div class="flex items-center gap-3">
                <span class="text-3xl">🗑️</span>
                <h4 class="text-xs font-black text-slate-800 uppercase tracking-widest" x-text="confirmTitle">Confirmar</h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium" x-text="confirmMsg"></p>
            <div class="flex justify-end gap-2 pt-2">
                <button @click="showConfirmModal = false" class="px-4 py-2 border border-slate-200 text-xs font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-655">
                    Cancelar
                </button>
                <button @click="executeConfirmAction()" class="bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] shadow-md shadow-rose-600/20 transition-colors">
                    Excluir
                </button>
            </div>
        </div>
    </div>

    <!-- JS Markups drawing logics -->
    <script>
        let pdfDocInstance = null;
        let currentRenderTask = null; 
        let confirmCallback = null; 

        function proofingSystem(initialAnnotations) {
            return {
                copiedText: 'Copiar Link',
                resolveBtnText: {},
                showCommentBox: false,
                commentBoxX: 0,
                commentBoxY: 0,
                commentText: '',
                currentPage: 1,
                numPages: 1,
                
                // Onboarding tour state parameters
                showTour: false,
                currentTourStep: 0,
                tourSteps: [
                    {
                        icon: '👋',
                        title: 'Guia de Boas-vindas!',
                        description: 'Olá! Bem-vindo ao painel de provas. Vamos fazer um tour de 1 minuto para você aprender a sugerir e revisar ajustes de forma super simples.',
                        elementId: null
                    },
                    {
                        icon: '📁',
                        title: 'Navegador de Arquivos',
                        description: 'Aqui no lado direito você navega e abre qualquer imagem ou PDF pertencente a esta rodada de prova ativa.',
                        elementId: 'tour-step-files'
                    },
                    {
                        icon: '🎨',
                        title: 'Ferramentas de Marcação',
                        description: 'Utilize os botões da barra para alternar entre a caneta livre (desenho), o retângulo marcador, mudar de cor ou limpar o rascunho.',
                        elementId: 'tour-step-toolbar'
                    },
                    {
                        icon: '✍️',
                        title: 'Marcar no Documento',
                        description: 'Clique e arraste diretamente sobre a folha para marcar o erro. Ao soltar o mouse, uma caixa flutuante se abrirá para digitar seu ajuste.',
                        elementId: 'proofing-viewport'
                    },
                    {
                        icon: '📄',
                        title: 'Controle de Páginas',
                        description: 'Use estas setas para navegar pelas páginas do PDF. Você também pode ativar o modo de Página Dupla para ver lado a lado como se fosse um livro físico.',
                        elementId: 'tour-step-navigation'
                    },
                    {
                        icon: '👥',
                        title: 'Quem está revisando',
                        description: 'Selecione quem está fazendo a revisão no topo para associar os comentários ao autor correto (ex: Autor Principal, Editor, etc).',
                        elementId: 'tour-step-author'
                    },
                    {
                        icon: '📝',
                        title: 'Lista de Ajustes',
                        description: 'Todos os ajustes salvos ficam listados no painel esquerdo. Você pode clicar neles para focar no ponto exato, editá-los ou marcá-los como Resolvidos.',
                        elementId: 'tour-step-tabs'
                    }
                ],

                // Real loading state parameters
                isInitialLoading: true,
                loadPercentage: 0,

                // Real file upload progress parameters
                isUploading: false,
                uploadPercentage: 0,

                // Keyboard Shortcut Guide visibility
                showShortcutGuide: false,

                // Reusable Notification Modal Parameters
                showNotification: false,
                notificationTitle: 'Aviso',
                notificationMsg: '',
                notificationType: 'info',

                // Reusable Confirm Modal Parameters
                showConfirmModal: false,
                confirmTitle: 'Confirmar',
                confirmMsg: '',

                // Editing Annotation Parameters
                editingAnnoId: null,

                // Page Layout View Mode
                pageMode: localStorage.getItem('rev_page_mode') || 'single', // single, double
                zoomScale: parseFloat(localStorage.getItem('rev_zoom_scale')) || 1.0,

                // Right click context menu parameters
                showContextMenu: false,
                contextMenuMode: 'generic', // generic, annotation
                contextMenuX: 0,
                contextMenuY: 0,
                rightClickedAnnoId: null,

                // Drag & Drop zones
                dragOverAttachment: false,
                attachmentFileName: '',
                selectedFile: null,

                // Draggable Dialogue parameters
                isDraggingBox: false,
                boxStartX: 0,
                boxStartY: 0,
                mouseStartX: 0,
                mouseStartY: 0,
                
                // Active retry count for failed PDF rendering
                renderRetryCount: 0,

                // Reactive annotations array list
                annotationsList: initialAnnotations || [],
                
                // Sidebar Redesign Options
                activeTab: 'aberto', // aberto, resolvido
                selectedAuthorId: localStorage.getItem('rev_selected_author_id') || '',
                activeTool: localStorage.getItem('rev_active_tool') || 'freehand',
                strokeColor: localStorage.getItem('rev_stroke_color') || '#f43f5e',
                
                // Toolbar Mode States
                hideAllAnnotations: false,
                scrollLocked: false,
                isFullscreen: false,
                focusedAnnoId: null,

                colorPalette: [
                    { value: '#f43f5e', label: 'Rosa' },
                    { value: '#10b981', label: 'Verde' },
                    { value: '#f59e0b', label: 'Amarelo' },
                    { value: '#3b82f6', label: 'Azul' }
                ],

                // Drawing canvases state variables
                isDrawing: false,
                canvas: null,
                ctx: null,
                currentPoints: [],
                allMarkups: [],
                tempRect: null,

                init() {
                    this.canvas = document.getElementById('markup-canvas');
                    if (this.canvas) {
                        this.ctx = this.canvas.getContext('2d');
                        this.initCanvasSize();
                    }

                    // Render PDF if file is PDF
                    @if($activeFile && $activeFile->file_type === 'pdf')
                        const pdfUrl = "/storage/{{ str_replace('public/', '', $activeFile->file_path) }}";
                        this.loadPDF(pdfUrl);
                    @elseif($activeFile)
                        // If image, fetch file bytes dynamically to show real loading percentage bar
                        const imageUrl = "{{ Storage::url($activeFile->file_path) }}";
                        this.loadImageWithProgress(imageUrl);
                    @else
                        this.isInitialLoading = false;
                    @endif

                    // Init columns resize drag events
                    this.initResizers();

                    // Global window drag move events for popup dialog
                    document.addEventListener('mousemove', (e) => this.dragMoveBox(e));
                    document.addEventListener('mouseup', () => this.stopDragBox());

                    // Auto-trigger guided tour on first view of proof panel
                    setTimeout(() => {
                        if (!localStorage.getItem('rev_tour_completed')) {
                            this.startInteractiveTour();
                        }
                    }, 1200);

                    // Register Keyboard shortcuts listeners globally
                    window.addEventListener('keydown', (e) => {
                        const isTyping = e.target.closest('[contenteditable="true"]') || 
                                         e.target.tagName === 'INPUT' || 
                                         e.target.tagName === 'TEXTAREA' || 
                                         e.target.tagName === 'SELECT';

                        if (e.key === 'Escape') {
                            if (this.showCommentBox) {
                                this.cancelAnnotation();
                            }
                            if (this.showContextMenu) {
                                this.showContextMenu = false;
                            }
                            if (this.showNotification) {
                                this.showNotification = false;
                            }
                            if (this.showConfirmModal) {
                                this.showConfirmModal = false;
                            }
                            if (this.showShortcutGuide) {
                                this.showShortcutGuide = false;
                            }
                            if (this.showTour) {
                                this.endTour();
                            }
                        }

                        // Save comment on Ctrl + Enter inside WYSIWYG
                        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) {
                            if (this.showCommentBox) {
                                e.preventDefault();
                                this.saveAnnotation();
                            }
                        }

                        // Handle page navigation arrows when not typing text
                        if (!isTyping && !this.showCommentBox) {
                            if (e.key === 'ArrowRight' || e.key === 'Right') {
                                this.nextPage();
                            }
                            if (e.key === 'ArrowLeft' || e.key === 'Left') {
                                this.prevPage();
                            }
                            if (e.key === '=' || e.key === '+') {
                                if (e.ctrlKey) {
                                    e.preventDefault();
                                    this.zoomIn();
                                }
                            }
                            if (e.key === '-') {
                                if (e.ctrlKey) {
                                    e.preventDefault();
                                    this.zoomOut();
                                }
                            }
                        }
                    });
                },

                // Interactive Guided Tour Controller Functions
                startInteractiveTour() {
                    this.currentTourStep = 0;
                    this.showTour = true;
                    this.highlightTourStep();
                },

                highlightTourStep() {
                    // Remove previous highlights
                    document.querySelectorAll('.tour-highlight').forEach(el => {
                        el.classList.remove('tour-highlight');
                    });

                    const step = this.tourSteps[this.currentTourStep];
                    if (step && step.elementId) {
                        const target = document.getElementById(step.elementId);
                        if (target) {
                            target.classList.add('tour-highlight');
                        }
                    }
                },

                nextTourStep() {
                    if (this.currentTourStep < this.tourSteps.length - 1) {
                        this.currentTourStep++;
                        this.highlightTourStep();
                    } else {
                        this.endTour();
                    }
                },

                prevTourStep() {
                    if (this.currentTourStep > 0) {
                        this.currentTourStep--;
                        this.highlightTourStep();
                    }
                },

                endTour() {
                    this.showTour = false;
                    document.querySelectorAll('.tour-highlight').forEach(el => {
                        el.classList.remove('tour-highlight');
                    });
                    localStorage.setItem('rev_tour_completed', 'true');
                },

                triggerAlert(title, message, type = 'info') {
                    this.notificationTitle = title;
                    this.notificationMsg = message;
                    this.notificationType = type;
                    this.showNotification = true;
                },

                triggerConfirm(title, message, callback) {
                    this.confirmTitle = title;
                    this.confirmMsg = message;
                    confirmCallback = callback; 
                    this.showConfirmModal = true;
                },

                executeConfirmAction() {
                    this.showConfirmModal = false;
                    if (confirmCallback) {
                        confirmCallback();
                    }
                },

                zoomIn() {
                    if (this.zoomScale < 2.5) {
                        this.zoomScale = parseFloat((this.zoomScale + 0.15).toFixed(2));
                        localStorage.setItem('rev_zoom_scale', this.zoomScale);
                        this.renderPDFPage();
                        this.initCanvasSize();
                    }
                },

                zoomOut() {
                    if (this.zoomScale > 0.5) {
                        this.zoomScale = parseFloat((this.zoomScale - 0.15).toFixed(2));
                        localStorage.setItem('rev_zoom_scale', this.zoomScale);
                        this.renderPDFPage();
                        this.initCanvasSize();
                    }
                },

                setPageMode(mode) {
                    this.pageMode = mode;
                    localStorage.setItem('rev_page_mode', mode);
                    this.renderPDFPage();
                    this.cancelAnnotation();
                },

                // Right click actions context handler
                handleRightClick(e) {
                    const coords = this.getCoords(e);
                    
                    let foundAnno = null;
                    this.annotationsList.forEach(anno => {
                        const isCurrent = Number(anno.page_number) === Number(this.currentPage) || 
                            (this.pageMode === 'double' && Number(anno.page_number) === Number(this.currentPage) + 1);

                        if (!isCurrent || !anno.drawing_data) return;
                        
                        try {
                            const parsed = JSON.parse(anno.drawing_data);
                            const scaleX = this.canvas.width / parsed.canvasWidth;
                            const scaleY = this.canvas.height / parsed.canvasHeight;
                            
                            let minX = 99999, maxX = 0, minY = 99999, maxY = 0;
                            
                            let shiftX = 0;
                            if (this.pageMode === 'double' && Number(anno.page_number) === Number(this.currentPage) + 1) {
                                shiftX = this.canvas.width / 2;
                            }

                            if (parsed.type === 'freehand') {
                                parsed.points.forEach(pt => {
                                    const px = (pt.x * scaleX) + shiftX;
                                    const py = pt.y * scaleY;
                                    minX = Math.min(minX, px);
                                    maxX = Math.max(maxX, px);
                                    minY = Math.min(minY, py);
                                    maxY = Math.max(maxY, py);
                                });
                            } else if (parsed.type === 'rectangle') {
                                minX = (Math.min(parsed.rect.x1, parsed.rect.x2) * scaleX) + shiftX;
                                maxX = (Math.max(parsed.rect.x1, parsed.rect.x2) * scaleX) + shiftX;
                                minY = Math.min(parsed.rect.y1, parsed.rect.y2) * scaleY;
                                maxY = Math.max(parsed.rect.y1, parsed.rect.y2) * scaleY;
                            }
                            
                            if (coords.x >= minX && coords.x <= maxX && coords.y >= minY && coords.y <= maxY) {
                                foundAnno = anno;
                            }
                        } catch(err) {
                            console.error(err);
                        }
                    });

                    const container = document.getElementById('visualizer-scroll-container');
                    const rect = container.getBoundingClientRect();
                    
                    this.contextMenuX = e.clientX - rect.left + container.scrollLeft;
                    this.contextMenuY = e.clientY - rect.top + container.scrollTop;

                    if (foundAnno) {
                        this.rightClickedAnnoId = foundAnno.id;
                        this.contextMenuMode = 'annotation';
                    } else {
                        this.rightClickedAnnoId = null;
                        this.contextMenuMode = 'generic';
                    }
                    this.showContextMenu = true;
                },

                resolveContextMenuAction(action) {
                    this.showContextMenu = false;
                    if (!this.rightClickedAnnoId) return;

                    const id = this.rightClickedAnnoId;
                    if (action === 'focus') {
                        const item = this.annotationsList.find(a => a.id === id);
                        if (item) {
                            this.focusAnnotation(id, item.page_number, item.drawing_data);
                        }
                    } else if (action === 'edit') {
                        this.editAnnotation(id);
                    } else if (action === 'toggle') {
                        this.toggleResolve(id);
                    } else if (action === 'delete') {
                        this.deleteAnnotation(id);
                    }
                },

                editAnnotation(id) {
                    const anno = this.annotationsList.find(a => a.id === id);
                    if (!anno) return;

                    this.editingAnnoId = anno.id;
                    this.commentText = anno.comment;
                    
                    const editor = document.getElementById('wysiwyg-editor');
                    if (editor) {
                        editor.innerHTML = anno.comment;
                    }
                    
                    this.attachmentFileName = anno.attachment_path ? 'Imagem anexada. Clique para substituir.' : '';
                    this.selectedFile = null;

                    // Position dialogue next to the focused markup coordinates
                    try {
                        const parsed = JSON.parse(anno.drawing_data);
                        const scaleX = this.canvas.width / parsed.canvasWidth;
                        const scaleY = this.canvas.height / parsed.canvasHeight;
                        
                        let posX = 0, posY = 0;
                        if (parsed.type === 'rectangle') {
                            posX = parsed.rect.x2 * scaleX;
                            posY = parsed.rect.y2 * scaleY;
                        } else if (parsed.type === 'freehand') {
                            const lastPt = parsed.points[parsed.points.length - 1];
                            posX = lastPt.x * scaleX;
                            posY = lastPt.y * scaleY;
                        }
                        
                        if (this.pageMode === 'double' && Number(anno.page_number) === Number(this.currentPage) + 1) {
                            posX += (this.canvas.width / 2);
                        }

                        const container = document.getElementById('visualizer-scroll-container');
                        const rect = container.getBoundingClientRect();

                        this.commentBoxX = clamp(posX, 20, this.canvas.width - 340);
                        this.commentBoxY = clamp(posY, 20, this.canvas.height - 350);
                        this.showCommentBox = true;
                    } catch(e) {
                        console.error(e);
                        this.commentBoxX = 250;
                        this.commentBoxY = 150;
                        this.showCommentBox = true;
                    }
                },

                // Draggable popup dialog boxes
                startDragBox(e) {
                    this.isDraggingBox = true;
                    this.mouseStartX = e.clientX;
                    this.mouseStartY = e.clientY;
                    this.boxStartX = this.commentBoxX;
                    this.boxStartY = this.commentBoxY;
                },

                dragMoveBox(e) {
                    if (!this.isDraggingBox) return;
                    const deltaX = e.clientX - this.mouseStartX;
                    const deltaY = e.clientY - this.mouseStartY;
                    this.commentBoxX = this.boxStartX + deltaX;
                    this.commentBoxY = this.boxStartY + deltaY;
                },

                stopDragBox() {
                    this.isDraggingBox = false;
                },

                formatText(command) {
                    document.execCommand(command, false, null);
                    const editor = document.getElementById('wysiwyg-editor');
                    if (editor) {
                        this.commentText = editor.innerHTML;
                    }
                },

                renderFormattedComment(comment) {
                    return comment || '';
                },

                // Drag and drop zone attachment file selects
                handleFileSelect(e) {
                    if (e.target.files && e.target.files[0]) {
                        this.selectedFile = e.target.files[0];
                        this.attachmentFileName = this.selectedFile.name;
                    }
                },

                handleDropAttachment(e) {
                    this.dragOverAttachment = false;
                    if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                        this.selectedFile = e.dataTransfer.files[0];
                        this.attachmentFileName = e.dataTransfer.files[0].name;
                        
                        const input = document.getElementById('attachmentInput');
                        if (input) {
                            input.files = e.dataTransfer.files;
                        }
                    }
                },

                persistAuthor() {
                    localStorage.setItem('rev_selected_author_id', this.selectedAuthorId);
                },

                setTool(tool) {
                    this.activeTool = tool;
                    localStorage.setItem('rev_active_tool', tool);
                },

                setColor(color) {
                    this.strokeColor = color;
                    localStorage.setItem('rev_stroke_color', color);
                },

                // Filters helpers
                shouldShowAnno(status) {
                    return status === this.activeTab;
                },

                filteredAnnotationsCount() {
                    return this.annotationsList.filter(anno => anno.status === this.activeTab).length;
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    return date.getDate().toString().padStart(2, '0') + '/' + 
                           (date.getMonth() + 1).toString().padStart(2, '0') + ' ' + 
                           date.getHours().toString().padStart(2, '0') + ':' + 
                           date.getMinutes().toString().padStart(2, '0');
                },

                // Toolbar features
                toggleVisibility() {
                    this.hideAllAnnotations = !this.hideAllAnnotations;
                    this.clearStrokes();
                },

                toggleScrollLock() {
                    this.scrollLocked = !this.scrollLocked;
                },

                toggleFullscreen() {
                    const elem = document.getElementById('middle-viewport');
                    if (!this.isFullscreen) {
                        if (elem.requestFullscreen) {
                            elem.requestFullscreen();
                        } else if (elem.webkitRequestFullscreen) {
                            elem.webkitRequestFullscreen();
                        }
                        this.isFullscreen = true;
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                        }
                        this.isFullscreen = false;
                    }
                    setTimeout(() => { this.initCanvasSize(); }, 300);
                },

                // Resizable Sidebars Engine
                initResizers() {
                    const resizerLeft = document.getElementById('resizer-left');
                    const asideLeft = document.getElementById('aside-left');
                    const resizerRight = document.getElementById('resizer-right-bar');
                    const asideRight = document.getElementById('aside-right');

                    if (resizerLeft && asideLeft) {
                        resizerLeft.addEventListener('mousedown', (e) => {
                            e.preventDefault();
                            document.addEventListener('mousemove', resizeLeftAside);
                            document.addEventListener('mouseup', stopResizeLeft);
                        });

                        function resizeLeftAside(e) {
                            const newWidth = e.clientX;
                            if (newWidth > 180 && newWidth < 450) {
                                asideLeft.style.width = newWidth + 'px';
                            }
                        }

                        function stopResizeLeft() {
                            document.removeEventListener('mousemove', resizeLeftAside);
                            document.removeEventListener('mouseup', stopResizeLeft);
                        }
                    }

                    if (resizerRight && asideRight) {
                        resizerRight.addEventListener('mousedown', (e) => {
                            e.preventDefault();
                            document.addEventListener('mousemove', resizeRightAside);
                            document.addEventListener('mouseup', stopResizeRight);
                        });

                        function resizeRightAside(e) {
                            const newWidth = window.innerWidth - e.clientX;
                            if (newWidth > 180 && newWidth < 450) {
                                asideRight.style.width = newWidth + 'px';
                            }
                        }

                        function stopResizeRight() {
                            document.removeEventListener('mousemove', resizeRightAside);
                            document.removeEventListener('mouseup', stopResizeRight);
                        }
                    }
                },

                initCanvasSize() {
                    const view = document.getElementById('proofing-viewport');
                    if (view && this.canvas) {
                        this.canvas.width = view.offsetWidth;
                        this.canvas.height = view.offsetHeight;
                        this.drawSavedAnnotations();
                    }
                },

                initImageDimensions() {
                    setTimeout(() => {
                        this.initCanvasSize();
                    }, 100);
                },

                // PDF progress bar tracker
                loadPDF(url) {
                    const spinner = document.getElementById('pdf-render-spinner');
                    if (spinner) spinner.classList.remove('hidden');

                    const loadingTask = pdfjsLib.getDocument(url);
                    loadingTask.onProgress = (progress) => {
                        if (progress.total > 0) {
                            this.loadPercentage = Math.round((progress.loaded / progress.total) * 100);
                        }
                    };

                    loadingTask.promise.then(pdf => {
                        pdfDocInstance = pdf;
                        this.numPages = pdf.numPages;
                        this.loadPercentage = 100;
                        setTimeout(() => {
                            this.isInitialLoading = false;
                        }, 300);
                        this.renderPDFPage();
                    }).catch(err => {
                        console.error('Error loading PDF: ', err);
                        if (spinner) spinner.classList.add('hidden');
                        this.isInitialLoading = false;
                        this.triggerAlert('Erro', 'Falha ao obter arquivo PDF. Por favor, recarregue a página.', 'error');
                    });
                },

                // Track and load Image files using streams to display real loading percentage bar
                loadImageWithProgress(url) {
                    fetch(url)
                        .then(response => {
                            if (!response.ok) throw new Error("Erro de resposta HTTP");
                            const contentLength = +response.headers.get('Content-Length') || 0;
                            if (contentLength === 0) {
                                this.loadPercentage = 100;
                                return response.blob();
                            }
                            
                            let loaded = 0;
                            const reader = response.body.getReader();
                            const self = this;
                            
                            return new Response(
                                new ReadableStream({
                                    start(controller) {
                                        function read() {
                                            reader.read().then(({ done, value }) => {
                                                if (done) {
                                                    controller.close();
                                                    return;
                                                }
                                                loaded += value.byteLength;
                                                self.loadPercentage = Math.round((loaded / contentLength) * 100);
                                                controller.enqueue(value);
                                                read();
                                            });
                                        }
                                        read();
                                    }
                                })
                            ).blob();
                        })
                        .then(blob => {
                            const objectURL = URL.createObjectURL(blob);
                            const img = document.getElementById('image-viewer');
                            if (img) {
                                img.src = objectURL;
                            }
                            this.loadPercentage = 100;
                            setTimeout(() => {
                                this.isInitialLoading = false;
                            }, 300);
                        })
                        .catch(err => {
                            console.error('Image loading error:', err);
                            this.isInitialLoading = false;
                            const img = document.getElementById('image-viewer');
                            if (img) img.src = url;
                        });
                },

                renderPDFPage() {
                    if (!pdfDocInstance) return;

                    const spinner = document.getElementById('pdf-render-spinner');
                    if (spinner) spinner.classList.remove('hidden');

                    if (currentRenderTask) {
                        currentRenderTask.cancel();
                    }

                    const pdfCanvas = document.getElementById('pdf-canvas');
                    const context = pdfCanvas.getContext('2d');

                    if (this.pageMode === 'single') {
                        pdfDocInstance.getPage(this.currentPage).then(page => {
                            const viewport = page.getViewport({ scale: 1.25 * this.zoomScale });
                            pdfCanvas.width = viewport.width;
                            pdfCanvas.height = viewport.height;

                            const renderContext = {
                                canvasContext: context,
                                viewport: viewport
                            };

                            currentRenderTask = page.render(renderContext);
                            currentRenderTask.promise.then(() => {
                                this.renderRetryCount = 0; 
                                if (spinner) spinner.classList.add('hidden');
                                this.initCanvasSize();
                            }).catch(err => {
                                if (err.name === 'RenderingCancelledException') {
                                    return;
                                }
                                console.error('PDF rendering failed:', err);
                                if (this.renderRetryCount < 3) {
                                    this.renderRetryCount++;
                                    setTimeout(() => this.renderPDFPage(), 400);
                                } else {
                                    if (spinner) spinner.classList.add('hidden');
                                    this.triggerAlert('Erro', 'Ocorreu uma falha ao renderizar a página. Tente recarregar.', 'error');
                                }
                            });
                        });
                    } else {
                        pdfDocInstance.getPage(this.currentPage).then(pageLeft => {
                            const viewportLeft = pageLeft.getViewport({ scale: 1.0 * this.zoomScale });
                            
                            const nextPageIndex = this.currentPage + 1;
                            if (nextPageIndex <= this.numPages) {
                                pdfDocInstance.getPage(nextPageIndex).then(pageRight => {
                                    const viewportRight = pageRight.getViewport({ scale: 1.0 * this.zoomScale });

                                    pdfCanvas.width = viewportLeft.width + viewportRight.width;
                                    pdfCanvas.height = Math.max(viewportLeft.height, viewportRight.height);

                                    const renderContextLeft = {
                                        canvasContext: context,
                                        viewport: viewportLeft
                                    };
                                    
                                    pageLeft.render(renderContextLeft).promise.then(() => {
                                        context.save();
                                        context.translate(viewportLeft.width, 0);

                                        const renderContextRight = {
                                            canvasContext: context,
                                            viewport: viewportRight
                                        };
                                        
                                        currentRenderTask = pageRight.render(renderContextRight);
                                        currentRenderTask.promise.then(() => {
                                            context.restore(); 
                                            this.renderRetryCount = 0; 
                                            if (spinner) spinner.classList.add('hidden');
                                            this.initCanvasSize();
                                        }).catch(err => {
                                            context.restore();
                                            if (err.name === 'RenderingCancelledException') {
                                                return;
                                            }
                                            console.error('Right page rendering failed:', err);
                                            if (this.renderRetryCount < 3) {
                                                this.renderRetryCount++;
                                                setTimeout(() => this.renderPDFPage(), 400);
                                            } else {
                                                if (spinner) spinner.classList.add('hidden');
                                            }
                                        });
                                    });
                                });
                            } else {
                                pdfCanvas.width = viewportLeft.width * 2;
                                pdfCanvas.height = viewportLeft.height;
                                
                                const renderContextLeft = {
                                    canvasContext: context,
                                    viewport: viewportLeft,
                                    transform: [1, 0, 0, 1, viewportLeft.width / 2, 0]
                                };
                                pageLeft.render(renderContextLeft).promise.then(() => {
                                    if (spinner) spinner.classList.add('hidden');
                                    this.initCanvasSize();
                                });
                            }
                        });
                    }
                },

                prevPage() {
                    const step = this.pageMode === 'double' ? 2 : 1;
                    if (this.currentPage > 1) {
                        this.currentPage = Math.max(1, this.currentPage - step);
                        this.renderPDFPage();
                        this.cancelAnnotation();
                    }
                },

                nextPage() {
                    const step = this.pageMode === 'double' ? 2 : 1;
                    if (this.currentPage < this.numPages) {
                        if (this.pageMode === 'double' && this.currentPage + 1 >= this.numPages) return;
                        this.currentPage = Math.min(this.numPages, this.currentPage + step);
                        this.renderPDFPage();
                        this.cancelAnnotation();
                    }
                },

                // HTML5 Canvas Drawing Controls
                startDrawing(e) {
                    if (this.showCommentBox) return;
                    if (e.button !== 0 && !e.touches) return; 

                    this.isDrawing = true;
                    const coords = this.getCoords(e);
                    
                    if (this.activeTool === 'freehand') {
                        this.currentPoints = [coords];
                    } else if (this.activeTool === 'rectangle') {
                        this.tempRect = {
                            x1: coords.x,
                            y1: coords.y,
                            x2: coords.x,
                            y2: coords.y
                        };
                    }
                },

                drawStrokes(e) {
                    if (!this.isDrawing || this.showCommentBox) return;

                    const coords = this.getCoords(e);
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    
                    this.drawSavedAnnotations();

                    this.ctx.strokeStyle = this.strokeColor;
                    this.ctx.lineWidth = 3;
                    this.ctx.lineCap = 'round';
                    this.ctx.lineJoin = 'round';

                    if (this.activeTool === 'freehand') {
                        this.currentPoints.push(coords);
                        
                        this.ctx.beginPath();
                        this.ctx.moveTo(this.currentPoints[0].x, this.currentPoints[0].y);
                        for (let i = 1; i < this.currentPoints.length; i++) {
                            this.ctx.lineTo(this.currentPoints[i].x, this.currentPoints[i].y);
                        }
                        this.ctx.stroke();
                    } else if (this.activeTool === 'rectangle') {
                        this.tempRect.x2 = coords.x;
                        this.tempRect.y2 = coords.y;

                        this.ctx.fillStyle = this.strokeColor;
                        this.ctx.globalAlpha = 0.25; 
                        this.ctx.fillRect(
                            this.tempRect.x1, 
                            this.tempRect.y1, 
                            this.tempRect.x2 - this.tempRect.x1, 
                            this.tempRect.y2 - this.tempRect.y1
                        );
                        this.ctx.globalAlpha = 1.0; 
                    }
                },

                endDrawing(e) {
                    if (!this.isDrawing || this.showCommentBox) return;
                    this.isDrawing = false;

                    let clientX = 0;
                    let clientY = 0;

                    if (e.changedTouches && e.changedTouches[0]) {
                        clientX = e.changedTouches[0].clientX;
                        clientY = e.changedTouches[0].clientY;
                    } else {
                        clientX = e.clientX;
                        clientY = e.clientY;
                    }

                    const container = document.getElementById('visualizer-scroll-container');
                    const rect = container.getBoundingClientRect();

                    this.commentBoxX = clientX - rect.left + container.scrollLeft;
                    this.commentBoxY = clientY - rect.top + container.scrollTop;
                    
                    const editor = document.getElementById('wysiwyg-editor');
                    if (editor) editor.innerHTML = '';
                    this.commentText = '';
                    this.selectedFile = null;
                    this.attachmentFileName = '';
                    this.editingAnnoId = null; 

                    this.showCommentBox = true;
                },

                getCoords(e) {
                    const rect = this.canvas.getBoundingClientRect();
                    let clientX = 0;
                    let clientY = 0;

                    if (e.touches && e.touches[0]) {
                        clientX = e.touches[0].clientX;
                        clientY = e.touches[0].clientY;
                    } else {
                        clientX = e.clientX;
                        clientY = e.clientY;
                    }

                    return {
                        x: clientX - rect.left,
                        y: clientY - rect.top
                    };
                },

                clearStrokes() {
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.currentPoints = [];
                    this.tempRect = null;
                    this.drawSavedAnnotations();
                },

                cancelAnnotation() {
                    this.showCommentBox = false;
                    this.commentText = '';
                    this.editingAnnoId = null;
                    
                    const fileInput = document.getElementById('attachmentInput');
                    if (fileInput) fileInput.value = '';

                    this.clearStrokes();
                },

                saveAnnotation() {
                    const editor = document.getElementById('wysiwyg-editor');
                    const commentHtml = editor.innerHTML;

                    if (!commentHtml.trim()) {
                        this.triggerAlert('Campo Vazio', 'Por favor, digite um comentário antes de salvar o seu ajuste.', 'warning');
                        return;
                    }

                    let drawingData = null;
                    if (!this.editingAnnoId) {
                        if (this.activeTool === 'freehand' && this.currentPoints.length > 0) {
                            drawingData = JSON.stringify({
                                type: 'freehand',
                                points: this.currentPoints,
                                color: this.strokeColor,
                                canvasWidth: this.canvas.width,
                                canvasHeight: this.canvas.height
                            });
                        } else if (this.activeTool === 'rectangle' && this.tempRect) {
                            drawingData = JSON.stringify({
                                type: 'rectangle',
                                rect: this.tempRect,
                                color: this.strokeColor,
                                canvasWidth: this.canvas.width,
                                canvasHeight: this.canvas.height
                            });
                        }
                    }

                    const formData = new FormData();
                    formData.append('comment', commentHtml);
                    if (drawingData) formData.append('drawing_data', drawingData);
                    formData.append('page_number', this.currentPage);
                    if (this.selectedAuthorId) formData.append('author_id', this.selectedAuthorId);

                    const fileInput = document.getElementById('attachmentInput');
                    if (fileInput && fileInput.files[0]) {
                        formData.append('attachment', fileInput.files[0]);
                    }

                    @if($activeFile)
                        const url = this.editingAnnoId 
                            ? `/revisao/annotation/${this.editingAnnoId}/update`
                            : "{{ route('public.revisao.annotation.store', $activeFile->id) }}";

                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        this.isUploading = true;
                        this.uploadPercentage = 0;

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', url, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                        xhr.upload.addEventListener('progress', (e) => {
                            if (e.lengthComputable) {
                                this.uploadPercentage = Math.round((e.loaded / e.total) * 100);
                            }
                        });

                        xhr.addEventListener('load', () => {
                            this.isUploading = false;
                            this.uploadPercentage = 0;
                            
                            if (xhr.status >= 200 && xhr.status < 300) {
                                const data = JSON.parse(xhr.responseText);
                                if (data.success) {
                                    if (this.editingAnnoId) {
                                        const index = this.annotationsList.findIndex(a => a.id === data.annotation.id);
                                        if (index !== -1) {
                                            this.annotationsList[index] = data.annotation;
                                        }
                                        this.editingAnnoId = null;
                                    } else {
                                        this.annotationsList.push(data.annotation);
                                    }
                                    this.showCommentBox = false;
                                    this.clearStrokes();
                                }
                            } else {
                                this.triggerAlert('Erro', 'Falha ao salvar. Tamanho do arquivo pode ter excedido os limites do servidor.', 'error');
                            }
                        });

                        xhr.addEventListener('error', () => {
                            this.isUploading = false;
                            this.uploadPercentage = 0;
                            this.triggerAlert('Erro de Rede', 'Houve um erro de conexão ao realizar o upload.', 'error');
                        });

                        xhr.send(formData);
                    @endif
                },

                cleanTextForTag(html) {
                    const tmp = document.createElement("DIV");
                    tmp.innerHTML = html;
                    return tmp.textContent || tmp.innerText || "";
                },

                wrapText(text, maxWidth) {
                    const words = text.split(' ');
                    let line = '';
                    let lines = [];
                    for (let n = 0; n < words.length; n++) {
                        let testLine = line + words[n] + ' ';
                        let metrics = this.ctx.measureText(testLine);
                        if (metrics.width > maxWidth && n > 0) {
                            lines.push(line.trim());
                            line = words[n] + ' ';
                        } else {
                            line = testLine;
                        }
                    }
                    lines.push(line.trim());
                    return lines;
                },

                drawSavedAnnotations() {
                    if (!this.ctx) return;

                    const clamp = (val, min, max) => Math.min(Math.max(val, min), max);

                    this.annotationsList.forEach((anno, index) => {
                        const isCurrentPage = Number(anno.page_number) === Number(this.currentPage) || 
                            (this.pageMode === 'double' && Number(anno.page_number) === Number(this.currentPage) + 1);

                        if (!isCurrentPage || !anno.drawing_data) return;

                        if (this.hideAllAnnotations && this.focusedAnnoId !== anno.id) {
                            return;
                        }

                        try {
                            const parsed = JSON.parse(anno.drawing_data);
                            this.ctx.strokeStyle = parsed.color || '#f43f5e';
                            
                            this.ctx.globalAlpha = anno.status === 'resolvido' ? 0.3 : 1;
                            this.ctx.lineWidth = 3;
                            this.ctx.lineCap = 'round';
                            this.ctx.lineJoin = 'round';

                            const scaleX = this.canvas.width / parsed.canvasWidth;
                            const scaleY = this.canvas.height / parsed.canvasHeight;

                            let boundMaxX = 0;
                            let boundMinX = 99999;
                            let boundMinY = 99999;
                            let boundMaxY = 0;

                            let shiftX = 0;
                            if (this.pageMode === 'double' && Number(anno.page_number) === Number(this.currentPage) + 1) {
                                shiftX = this.canvas.width / 2;
                            }

                            if (parsed.type === 'freehand') {
                                this.ctx.beginPath();
                                const startX = (parsed.points[0].x * scaleX) + shiftX;
                                const startY = parsed.points[0].y * scaleY;
                                this.ctx.moveTo(startX, startY);
                                
                                parsed.points.forEach(pt => {
                                    const px = (pt.x * scaleX) + shiftX;
                                    const py = pt.y * scaleY;
                                    this.ctx.lineTo(px, py);

                                    boundMinX = Math.min(boundMinX, px);
                                    boundMaxX = Math.max(boundMaxX, px);
                                    boundMinY = Math.min(boundMinY, py);
                                    boundMaxY = Math.max(boundMaxY, py);
                                });
                                this.ctx.stroke();
                            } else if (parsed.type === 'rectangle') {
                                const rect = parsed.rect;
                                
                                const rx1 = (rect.x1 * scaleX) + shiftX;
                                const ry1 = rect.y1 * scaleY;
                                const rx2 = (rect.x2 * scaleX) + shiftX;
                                const ry2 = rect.y2 * scaleY;

                                this.ctx.fillStyle = parsed.color || '#f43f5e';
                                this.ctx.globalAlpha = anno.status === 'resolvido' ? 0.08 : 0.25;
                                this.ctx.fillRect(rx1, ry1, rx2 - rx1, ry2 - ry1);
                                
                                this.ctx.globalAlpha = anno.status === 'resolvido' ? 0.3 : 0.8;
                                this.ctx.strokeRect(rx1, ry1, rx2 - rx1, ry2 - ry1);

                                boundMinX = Math.min(rx1, rx2);
                                boundMaxX = Math.max(rx1, rx2);
                                boundMinY = Math.min(ry1, ry2);
                                boundMaxY = Math.max(ry1, ry2);
                            }

                            // RENDER CONNECTING LEADING LINES & TEXT BUBBLES
                            if (boundMaxX > 0 && boundMaxX < 99999) {
                                this.ctx.globalAlpha = anno.status === 'resolvido' ? 0.35 : 1.0;
                                const centerY = (boundMinY + boundMaxY) / 2;
                                
                                const alignRight = (boundMaxX + 180) <= this.canvas.width;
                                
                                this.ctx.font = '500 11px sans-serif';
                                const rawComment = this.cleanTextForTag(anno.comment);
                                
                                const maxWidthText = 160;
                                const textLines = this.wrapText(rawComment, maxWidthText);
                                
                                let maxLineWidth = 0;
                                textLines.forEach(ln => {
                                    const w = this.ctx.measureText(ln).width;
                                    maxLineWidth = Math.max(maxLineWidth, w);
                                });

                                const bubbleWidth = maxLineWidth + 16;
                                const lineHeightHeight = 14;
                                const bubbleHeight = (textLines.length * lineHeightHeight) + 8;

                                if (alignRight) {
                                    const targetLineX = boundMaxX + 35;
                                    const circleX = targetLineX + 10;
                                    const bubbleX = targetLineX + 23;

                                    this.ctx.beginPath();
                                    this.ctx.moveTo(boundMaxX, centerY);
                                    this.ctx.lineTo(targetLineX, centerY);
                                    this.ctx.strokeStyle = parsed.color || '#f43f5e';
                                    this.ctx.lineWidth = 1.5;
                                    this.ctx.stroke();

                                    // Circle Badge
                                    this.ctx.beginPath();
                                    this.ctx.arc(circleX, centerY, 10, 0, 2 * Math.PI);
                                    this.ctx.fillStyle = '#3b82f6';
                                    this.ctx.fill();
                                    
                                    this.ctx.fillStyle = '#ffffff';
                                    this.ctx.font = 'bold 11px sans-serif';
                                    this.ctx.textAlign = 'center';
                                    this.ctx.textBaseline = 'middle';
                                    this.ctx.fillText((index + 1).toString(), circleX, centerY);

                                    // Bubble card
                                    const finalBubbleX = clamp(bubbleX, 2, this.canvas.width - bubbleWidth - 2);
                                    const bubbleY = centerY - (bubbleHeight / 2);

                                    this.ctx.fillStyle = '#fef08a';
                                    this.ctx.beginPath();
                                    this.ctx.roundRect(finalBubbleX, bubbleY, bubbleWidth, bubbleHeight, 4);
                                    this.ctx.fill();

                                    this.ctx.fillStyle = '#1e293b';
                                    this.ctx.textAlign = 'left';
                                    this.ctx.font = '500 11px sans-serif';
                                    
                                    textLines.forEach((ln, idx) => {
                                        const textY = bubbleY + 6 + (idx * lineHeightHeight) + (lineHeightHeight / 2);
                                        this.ctx.fillText(ln, finalBubbleX + 8, textY);
                                    });

                                } else {
                                    const targetLineX = boundMinX - 35;
                                    const circleX = targetLineX - 10;
                                    const bubbleX = targetLineX - 24 - bubbleWidth;

                                    this.ctx.beginPath();
                                    this.ctx.moveTo(boundMinX, centerY);
                                    this.ctx.lineTo(targetLineX, centerY);
                                    this.ctx.strokeStyle = parsed.color || '#f43f5e';
                                    this.ctx.lineWidth = 1.5;
                                    this.ctx.stroke();

                                    // Circle Badge
                                    this.ctx.beginPath();
                                    this.ctx.arc(circleX, centerY, 10, 0, 2 * Math.PI);
                                    this.ctx.fillStyle = '#3b82f6';
                                    this.ctx.fill();
                                    
                                    this.ctx.fillStyle = '#ffffff';
                                    this.ctx.font = 'bold 11px sans-serif';
                                    this.ctx.textAlign = 'center';
                                    this.ctx.textBaseline = 'middle';
                                    this.ctx.fillText((index + 1).toString(), circleX, centerY);

                                    // Bubble card
                                    const finalBubbleX = clamp(bubbleX, 2, this.canvas.width - bubbleWidth - 2);
                                    const bubbleY = centerY - (bubbleHeight / 2);

                                    this.ctx.fillStyle = '#fef08a';
                                    this.ctx.beginPath();
                                    this.ctx.roundRect(finalBubbleX, bubbleY, bubbleWidth, bubbleHeight, 4);
                                    this.ctx.fill();

                                    this.ctx.fillStyle = '#1e293b';
                                    this.ctx.textAlign = 'left';
                                    this.ctx.font = '500 11px sans-serif';
                                    
                                    textLines.forEach((ln, idx) => {
                                        const textY = bubbleY + 6 + (idx * lineHeightHeight) + (lineHeightHeight / 2);
                                        this.ctx.fillText(ln, finalBubbleX + 8, textY);
                                    });
                                }
                            }

                        } catch (e) {
                            console.error('Erro ao renderizar marcação:', e);
                        }
                    });

                    // Reset values
                    this.ctx.globalAlpha = 1.0;
                    this.ctx.textAlign = 'left';
                    this.ctx.textBaseline = 'alphabetic';
                },

                focusAnnotation(id, page, data) {
                    this.focusedAnnoId = id;

                    let targetPage = page;
                    if (this.pageMode === 'double') {
                        if (targetPage === this.currentPage + 1) {
                            targetPage = this.currentPage;
                        }
                    }

                    if (this.currentPage !== targetPage) {
                        this.currentPage = targetPage;
                        this.renderPDFPage();
                    } else {
                        this.clearStrokes();
                    }

                    const cards = document.querySelectorAll('[id^="anno-card-"]');
                    cards.forEach(c => c.classList.remove('border-blue-500', 'ring-2', 'ring-blue-500/20'));

                    const card = document.getElementById('anno-card-' + id);
                    if (card) {
                        card.classList.add('border-blue-500', 'ring-2', 'ring-blue-500/20');
                        card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }
                },

                toggleResolve(id) {
                    const resolveUrl = `/revisao/annotation/${id}/resolve`;
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    this.resolveBtnText[id] = 'Processando...';

                    fetch(resolveUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            const item = this.annotationsList.find(a => a.id === id);
                            if (item) {
                                item.status = data.status;
                            }
                            this.resolveBtnText[id] = null;
                            this.clearStrokes();
                        }
                    })
                    .catch(err => {
                        console.error('Erro ao alterar status da anotação:', err);
                    });
                },

                deleteAnnotation(id) {
                    this.triggerConfirm('Excluir Ajuste', 'Deseja realmente excluir esta anotação permanente?', () => {
                        const deleteUrl = `/revisao/annotation/${id}`;
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data.success) {
                                this.annotationsList = this.annotationsList.filter(a => a.id !== id);
                                this.clearStrokes();
                            }
                        })
                        .catch(err => {
                            console.error('Erro ao deletar anotação:', err);
                            this.triggerAlert('Erro', 'Falha ao deletar ajuste.', 'error');
                        });
                    });
                }
            }
        }
    </script>

</body>
</html>
