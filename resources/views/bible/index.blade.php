<!DOCTYPE html>
<html lang="pt-BR" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Veredas - Leitura e Estudo Bíblico</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/assets/veredas.png') }}">
    
    <!-- Google Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=source-serif-4:400,500,600,700|nunito:400,500,600,700" rel="stylesheet" />
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        :root {
            --bible-bg: #0b0b0d;
            --bible-surface: #151518;
            --bible-surface-soft: #202025;
            --bible-surface-muted: rgba(255, 255, 255, 0.05);
            --bible-border: rgba(255, 255, 255, 0.08);
            --bible-text: #f5f1e8;
            --bible-muted: #b0aa9d;
            --bible-accent: #f4efe3;
            --bible-accent-soft: rgba(255, 255, 255, 0.08);
            --bible-verse-selected: rgba(255, 255, 255, 0.05);
            --bible-verse-glow: rgba(244, 239, 227, 0.22);
        }
        
        .theme-sepia {
            --bible-bg: #1a1511;
            --bible-surface: #251d17;
            --bible-surface-soft: #31271f;
            --bible-surface-muted: rgba(255, 245, 230, 0.06);
            --bible-border: rgba(255, 232, 207, 0.08);
            --bible-text: #f4eadf;
            --bible-muted: #c0ad98;
            --bible-accent: #fff7ea;
            --bible-accent-soft: rgba(255, 247, 234, 0.08);
            --bible-verse-selected: rgba(255, 247, 234, 0.05);
            --bible-verse-glow: rgba(255, 247, 234, 0.22);
        }
        
        .theme-claro {
            --bible-bg: #f7f4ee;
            --bible-surface: #ffffff;
            --bible-surface-soft: #f3efe7;
            --bible-surface-muted: rgba(15, 23, 42, 0.04);
            --bible-border: rgba(15, 23, 42, 0.08);
            --bible-text: #1f2937;
            --bible-muted: #6b7280;
            --bible-accent: #111827;
            --bible-accent-soft: rgba(17, 24, 39, 0.06);
            --bible-verse-selected: rgba(17, 24, 39, 0.05);
            --bible-verse-glow: rgba(17, 24, 39, 0.12);
        }
        
        .theme-escuro {
            --bible-bg: #0b0b0d;
            --bible-surface: #151518;
            --bible-surface-soft: #202025;
            --bible-surface-muted: rgba(255, 255, 255, 0.05);
            --bible-border: rgba(255, 255, 255, 0.08);
            --bible-text: #f5f1e8;
            --bible-muted: #b0aa9d;
            --bible-accent: #f4efe3;
            --bible-accent-soft: rgba(255, 255, 255, 0.08);
            --bible-verse-selected: rgba(255, 255, 255, 0.05);
            --bible-verse-glow: rgba(244, 239, 227, 0.22);
        }

        body {
            background-color: var(--bible-bg);
            color: var(--bible-text);
            font-family: 'Nunito', sans-serif;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .font-reading {
            font-family: 'Source Serif 4', Georgia, serif;
        }

        .bible-surface { background-color: var(--bible-surface); }
        .bible-surface-soft { background-color: var(--bible-surface-soft); }
        
        .bible-chip {
            background-color: var(--bible-surface-muted);
            border: 1px solid var(--bible-border);
            color: var(--bible-text);
        }

        .bible-border-app { border-color: var(--bible-border); }
        .bible-text-muted { color: var(--bible-muted); }

        .verse-card {
            transition: background-color 180ms ease, box-shadow 180ms ease;
            border-radius: 4px;
            padding: 2px 4px;
        }
        
        .verse-card.selected {
            background-color: var(--bible-verse-selected);
            box-shadow: 
                0 0 0 1px var(--bible-verse-glow),
                0 0 0 0.35rem color-mix(in srgb, var(--bible-verse-glow) 28%, transparent);
        }

        .verse-card:hover {
            background-color: var(--bible-verse-selected);
        }

        .verse-card[data-highlight="yellow"] { background-color: rgba(250, 204, 21, 0.18); }
        .verse-card[data-highlight="blue"] { background-color: rgba(96, 165, 250, 0.16); }
        .verse-card[data-highlight="green"] { background-color: rgba(74, 222, 128, 0.16); }
        .verse-card[data-highlight="rose"] { background-color: rgba(251, 113, 133, 0.14); }

        .verse-card.selected[data-highlight="yellow"] {
            box-shadow: 0 0 0 1px rgba(250, 204, 21, 0.52), 0 0 0 0.35rem rgba(250, 204, 21, 0.18);
        }
        .verse-card.selected[data-highlight="blue"] {
            box-shadow: 0 0 0 1px rgba(96, 165, 250, 0.5), 0 0 0 0.35rem rgba(96, 165, 250, 0.16);
        }
        .verse-card.selected[data-highlight="green"] {
            box-shadow: 0 0 0 1px rgba(74, 222, 128, 0.5), 0 0 0 0.35rem rgba(74, 222, 128, 0.16);
        }
        .verse-card.selected[data-highlight="rose"] {
            box-shadow: 0 0 0 1px rgba(251, 113, 133, 0.48), 0 0 0 0.35rem rgba(251, 113, 133, 0.15);
        }

        .verse-note-marker {
            display: inline-flex;
            width: 0.45rem;
            height: 0.45rem;
            border-radius: 9999px;
            background-color: var(--bible-accent);
            box-shadow: 0 0 0 0.2rem color-mix(in srgb, var(--bible-accent) 18%, transparent);
            margin-left: 0.25rem;
        }

        .verse-context-menu {
            position: absolute;
            z-index: 70;
            width: 16rem;
            padding: 0.45rem;
            border-radius: 0.75rem;
            background-color: var(--bible-surface);
            border: 1px solid var(--bible-border);
            box-shadow: 0 18px 50px rgba(0, 0, 0, 0.4);
        }

        .verse-context-color {
            width: 100%;
            min-height: 2rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            background-color: var(--bible-surface-soft);
        }

        .verse-context-color-dot {
            width: 0.85rem;
            height: 0.85rem;
            border-radius: 9999px;
        }

        .verse-context-color[data-context-color="yellow"] .verse-context-color-dot { background-color: #facc15; }
        .verse-context-color[data-context-color="blue"] .verse-context-color-dot { background-color: #60a5fa; }
        .verse-context-color[data-context-color="green"] .verse-context-color-dot { background-color: #4ade80; }
        .verse-context-color[data-context-color="rose"] .verse-context-color-dot { background-color: #fb7185; }

        .summary-card {
            border-radius: 0.75rem;
            padding: 0.85rem 0.95rem;
            background-color: var(--bible-surface-soft);
            border: 1px solid var(--bible-border);
        }

        .note-card, .support-card {
            border-radius: 0.75rem;
            padding: 1rem;
            background-color: var(--bible-surface-soft);
            border: 1px solid var(--bible-border);
        }

        .note-card {
            cursor: pointer;
            transition: transform 160ms ease, background-color 160ms ease;
        }

        .note-card:hover {
            transform: translateY(-1px);
            background-color: var(--bible-accent-soft);
        }

        .action-dot {
            width: 1.25rem;
            height: 1.25rem;
            border-radius: 9999px;
            border: 0;
            cursor: pointer;
            transition: transform 0.1s ease;
        }

        .action-dot:hover {
            transform: scale(1.1);
        }

        .action-dot[data-color="yellow"] { background-color: #facc15; }
        .action-dot[data-color="blue"] { background-color: #60a5fa; }
        .action-dot[data-color="green"] { background-color: #4ade80; }
        .action-dot[data-color="rose"] { background-color: #fb7185; }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--bible-bg);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--bible-border);
            border-radius: 9999px;
            border: 2px solid var(--bible-bg);
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--bible-muted);
        }

        /* Custom Cursor support on desktop */
        @media (min-width: 768px) {
            body, a, button, input, select, textarea, .verse-card, .note-card, .picker-option {
                cursor: none !important;
            }
        }
    </style>
</head>
<body class="theme-sepia h-full flex flex-col justify-between">

    <!-- Custom Cursor Elements -->
    <div id="custom-cursor" class="pointer-events-none fixed top-0 left-0 w-8 h-8 rounded-full border border-slate-500/40 mix-blend-difference z-[9999] transition-transform duration-75 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>
    <div id="custom-cursor-dot" class="pointer-events-none fixed top-0 left-0 w-1.5 h-1.5 bg-slate-400 rounded-full z-[9999] transition-transform duration-75 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>

    <div class="flex-1 max-w-6xl w-full mx-auto p-4 sm:p-6 space-y-6">
        
        <!-- Header de Controles -->
        <div class="flex flex-row items-center justify-between gap-1 pb-4 border-b bible-border-app select-none w-full flex-nowrap">
            <!-- Left Side: Return & Font Actions -->
            <div class="flex items-center gap-1 shrink-0">
                <a href="/" class="text-xs font-bold bible-text-muted hover:text-white mr-1.5 uppercase tracking-wider flex items-center shrink-0">
                    ←<span class="hidden xs:inline ml-0.5"> Voltar</span>
                </a>
                <button id="decreaseFontBtn" class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bible-chip font-bold hover:opacity-80 text-xs shrink-0" style="width: 30px; height: 30px;">A-</button>
                <button id="increaseFontBtn" class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bible-chip font-bold hover:opacity-80 text-xs shrink-0" style="width: 30px; height: 30px;">A+</button>
                <button id="runSearchBtn" class="w-7.5 h-7.5 flex items-center justify-center rounded-lg bible-chip font-bold hover:opacity-80 text-sm shrink-0" style="width: 30px; height: 30px;">⌕</button>
            </div>

            <!-- Right Side: Theme & Version -->
            <div class="flex items-center gap-1.5 shrink-0">
                <div class="flex items-center gap-0.5 bg-black/10 dark:bg-white/5 p-0.5 rounded-lg shrink-0">
                    <button class="flex items-center justify-center rounded-md text-xs transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="width: 25px; height: 25px;" data-theme-btn="sepia" title="Sépia">◐</button>
                    <button class="flex items-center justify-center rounded-md text-xs transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="width: 25px; height: 25px;" data-theme-btn="claro" title="Claro">☼</button>
                    <button class="flex items-center justify-center rounded-md text-xs transition-colors hover:bg-black/5 dark:hover:bg-white/5" style="width: 25px; height: 25px;" data-theme-btn="escuro" title="Escuro">☾</button>
                </div>

                <button id="openVersionPickerBtn" class="px-2 py-1.5 rounded-lg text-[9px] font-bold bible-chip flex items-center gap-1 shrink-0">
                    <span id="versionPickerLabel">NVI</span> ▾
                </button>
                <select id="versionSelect" class="hidden"></select>
            </div>
        </div>

        <!-- Seção Principal de Leitura -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- Área do Texto Bíblico -->
            <div class="lg:col-span-2 space-y-6">
                <div class="text-center py-4 select-none">
                    <p id="readingMeta" class="bible-text-muted text-xs uppercase tracking-widest font-bold">Carregando...</p>
                    <h1 id="bookLabelDisplay" class="font-reading mt-3 text-3xl sm:text-4xl font-bold">Livro</h1>
                    <div id="chapterNumberDisplay" class="font-reading text-6xl font-bold mt-2 text-slate-400">1</div>
                </div>

                <!-- Barra de Ações Rápidas Sticky -->
                <div class="sticky top-2 z-30 flex flex-col sm:flex-row items-center justify-between p-2 rounded-xl bible-surface shadow-md border bible-border-app select-none gap-2 sm:gap-4">
                    <!-- Bloco 1: Navegação e Controles Básicos -->
                    <div class="flex items-center justify-center gap-1.5 w-full sm:w-auto">
                        <button id="prevChapterBtn" class="w-8 h-8 flex items-center justify-center rounded-lg bible-chip hover:opacity-85 font-black">&lsaquo;</button>
                        <button id="openReferenceBtn" class="px-4 py-1.5 rounded-lg text-xs font-bold bible-chip hover:opacity-85 truncate max-w-[130px] sm:max-w-none">Escolher Referência</button>
                        <button id="nextChapterBtn" class="w-8 h-8 flex items-center justify-center rounded-lg bible-chip hover:opacity-85 font-black">&rsaquo;</button>
                        
                        <div class="w-[1px] h-6 bg-slate-600/30 mx-1"></div>

                        <button id="openNoteModalBtn" class="w-8 h-8 flex items-center justify-center rounded-lg bible-chip hover:opacity-85 text-xs" title="Escrever nota" disabled>✎</button>
                        <button id="favoriteBtn" class="w-8 h-8 flex items-center justify-center rounded-lg bible-chip hover:opacity-85 text-sm" title="Favoritar" disabled>☆</button>
                    </div>
                    
                    <!-- Bloco 2: Paleta de Cores e Marcações -->
                    <div class="flex items-center justify-center gap-2 w-full sm:w-auto mt-0">
                        <div class="flex items-center gap-1.5">
                            <button class="action-dot" data-color="yellow" disabled></button>
                            <button class="action-dot" data-color="blue" disabled></button>
                            <button class="action-dot" data-color="green" disabled></button>
                            <button class="action-dot" data-color="rose" disabled></button>
                        </div>
                        <div class="w-[1px] h-6 bg-slate-600/30 mx-1 hidden sm:block"></div>
                        <button id="clearHighlightBtn" class="text-[9px] font-bold text-slate-400 hover:text-slate-200 px-2 disabled:opacity-40 uppercase tracking-wider" disabled>Limpar</button>
                    </div>
                </div>

                <!-- Área do Conteúdo do Texto -->
                <div id="readingArea" class="reader-text font-reading leading-relaxed space-y-4 text-justify px-2 py-4 select-text"></div>
            </div>

            <!-- Barra Lateral de Estudo (Anotações e Apoio) -->
            <div class="space-y-6 select-none">
                
                <!-- Caixa de Anotações -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-4">
                    <div class="flex items-center justify-between border-b bible-border-app pb-2">
                        <h3 class="text-sm font-bold uppercase tracking-wider bible-text-muted">Anotações</h3>
                        <button id="openNoteModalSecondaryBtn" class="w-6 h-6 flex items-center justify-center rounded-full bg-slate-700 hover:bg-slate-600 text-white font-bold text-sm" title="Nova Anotação">＋</button>
                    </div>
                    <div id="notesList" class="space-y-3 max-h-[300px] overflow-y-auto pr-1"></div>
                </div>

                <!-- Caixa de Informações de Apoio -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider bible-text-muted border-b bible-border-app pb-2">Resumos e Apoio</h3>
                    <div id="supportOverview" class="space-y-3"></div>
                </div>

                <!-- Cronologia -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Cronologia</h4>
                    <div id="timelineList" class="space-y-2 max-h-[220px] overflow-y-auto"></div>
                </div>

                <!-- Curiosidades -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Curiosidades</h4>
                    <div id="curiositiesList" class="space-y-2 max-h-[220px] overflow-y-auto"></div>
                </div>

                <!-- Conexões -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Conexões</h4>
                    <div id="connectionsList" class="space-y-2 max-h-[220px] overflow-y-auto"></div>
                </div>

                <!-- Ilustrações -->
                <div class="bible-surface rounded-xl border bible-border-app p-4 space-y-3">
                    <h4 class="font-bold text-xs uppercase tracking-widest text-slate-400">Ilustrações</h4>
                    <div id="illustrationsList" class="space-y-2 max-h-[220px] overflow-y-auto"></div>
                </div>

            </div>
        </div>

        <!-- Modais -->
        <!-- Modal Selecionar Livro / Capítulo -->
        <div id="referenceModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
            <div class="bible-surface border bible-border-app rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b bible-border-app pb-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider bible-text-muted">Escolher Referência</h3>
                    <button type="button" class="w-6 h-6 flex items-center justify-center rounded-lg bible-chip hover:opacity-80 text-sm" onclick="toggleModal('referenceModal', false)">×</button>
                </div>
                
                <div class="grid grid-cols-3 gap-3">
                    <button id="openBookPickerBtn" type="button" class="col-span-2 px-3 py-2 text-xs font-semibold bible-chip rounded-lg flex items-center justify-between">
                        <span id="bookPickerLabel">Selecione o livro</span> ▾
                    </button>
                    <select id="bookSelect" class="hidden"></select>
                    <input id="chapterInput" type="number" min="1" class="px-3 py-2 text-xs font-semibold bible-chip rounded-lg text-center bg-black/10 dark:bg-white/5" value="1">
                </div>

                <div class="flex justify-end gap-2 pt-2 select-none">
                    <button type="button" class="px-4 py-2 rounded-lg text-xs bible-chip hover:opacity-85" onclick="toggleModal('referenceModal', false)">Cancelar</button>
                    <button id="loadChapterBtn" type="button" class="px-5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm">Abrir</button>
                </div>
            </div>
        </div>

        <!-- Modal Pesquisa -->
        <div id="searchModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
            <div class="bible-surface border bible-border-app rounded-xl max-w-lg w-full p-5 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b bible-border-app pb-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider bible-text-muted">Pesquisar Trecho</h3>
                    <button type="button" class="w-6 h-6 flex items-center justify-center rounded-lg bible-chip hover:opacity-80 text-sm" onclick="toggleModal('searchModal', false)">×</button>
                </div>

                <div class="flex gap-2">
                    <input id="searchInput" type="text" class="flex-1 px-4 py-2 text-sm bible-chip rounded-lg bg-black/10 dark:bg-white/5" placeholder="Digite palavra ou ref. (Ex: 'amor' ou 'Gênesis 1')">
                    <button id="runSearchModalBtn" type="button" class="w-10 h-10 flex items-center justify-center rounded-lg bible-chip hover:opacity-85 text-lg font-bold">⌕</button>
                </div>

                <div id="searchMeta" class="text-xs bible-text-muted italic">Digite um termo para iniciar a busca.</div>
                <div id="searchResults" class="space-y-3 max-h-[300px] overflow-y-auto pr-1"></div>
            </div>
        </div>

        <!-- Modal Anotações -->
        <div id="noteModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
            <div class="bible-surface border bible-border-app rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b bible-border-app pb-2">
                    <h3 class="text-sm font-bold uppercase tracking-wider bible-text-muted">Anotar</h3>
                    <button type="button" class="w-6 h-6 flex items-center justify-center rounded-lg bible-chip hover:opacity-80 text-sm" onclick="toggleModal('noteModal', false)">×</button>
                </div>

                <div id="noteModalReference" class="text-xs bible-text-muted font-semibold">Gênesis 1:1</div>
                <textarea id="noteInput" rows="5" class="w-full px-4 py-3 text-sm bible-chip rounded-lg bg-black/10 dark:bg-white/5 focus:outline-none" placeholder="Escreva algo sobre este versículo..."></textarea>
                
                <div class="flex justify-end gap-2 select-none">
                    <button type="button" class="px-4 py-2 rounded-lg text-xs bible-chip hover:opacity-85" onclick="toggleModal('noteModal', false)">Cancelar</button>
                    <button id="saveNoteBtn" type="button" class="px-5 py-2 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm">Salvar Anotação</button>
                </div>
            </div>
        </div>

        <!-- Picker Modal (Substituição Limpa do select nativo) -->
        <div id="pickerModal" class="hidden fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs select-none">
            <div class="bible-surface border bible-border-app rounded-xl max-w-md w-full p-5 space-y-4 shadow-xl">
                <div class="flex items-center justify-between border-b bible-border-app pb-2">
                    <h3 id="pickerModalTitle" class="text-sm font-bold uppercase tracking-wider bible-text-muted">Escolher</h3>
                    <button type="button" class="w-6 h-6 flex items-center justify-center rounded-lg bible-chip hover:opacity-80 text-sm" onclick="toggleModal('pickerModal', false)">×</button>
                </div>
                
                <input id="pickerSearchInput" type="text" class="w-full px-3 py-2 text-sm bible-chip rounded-lg bg-black/10 dark:bg-white/5" placeholder="Buscar...">
                <div id="pickerOptionsList" class="space-y-2 max-h-[300px] overflow-y-auto pr-1"></div>
            </div>
        </div>

        <!-- Menu Contextual de Clique (Popup flutuante de Versículo) -->
        <div id="verseContextMenu" class="verse-context-menu hidden select-none">
            <div id="verseContextLabel" class="text-[10px] font-bold uppercase tracking-wider bible-text-muted px-2 py-1">Versículo</div>
            
            <!-- Cores -->
            <div class="grid grid-cols-4 gap-1 px-2 py-1.5 border-b bible-border-app">
                <button type="button" class="verse-context-color" data-context-color="yellow">
                    <span class="verse-context-color-dot"></span>
                </button>
                <button type="button" class="verse-context-color" data-context-color="blue">
                    <span class="verse-context-color-dot"></span>
                </button>
                <button type="button" class="verse-context-color" data-context-color="green">
                    <span class="verse-context-color-dot"></span>
                </button>
                <button type="button" class="verse-context-color" data-context-color="rose">
                    <span class="verse-context-color-dot"></span>
                </button>
            </div>

            <div class="flex flex-col text-xs font-semibold select-none pt-1">
                <button type="button" class="w-full text-left px-3 py-2 hover:bg-white/5 flex items-center gap-2" data-context-action="note-open">
                    <span>✎</span> Escrever nota
                </button>
                <button type="button" class="w-full text-left px-3 py-2 hover:bg-white/5 flex items-center gap-2" data-context-action="note-edit">
                    <span>≡</span> Editar nota
                </button>
                <button type="button" class="w-full text-left px-3 py-2 hover:bg-white/5 flex items-center gap-2" data-context-action="highlight-clear">
                    <span>○</span> Limpar marcação
                </button>
                <button type="button" class="w-full text-left px-3 py-2 hover:bg-white/5 flex items-center gap-2 text-rose-500 hover:bg-rose-500/10" data-context-action="note-delete">
                    <span>×</span> Apagar nota
                </button>
            </div>
        </div>

    </div>

    <script>
        // Configurações e endpoints
        window.veredasConfig = {
            initialBook: "gn",
            initialChapter: 1,
            initialVersion: "nvi",
            endpoints: {
                books: "/veredas-api/livros",
                versions: "/veredas-api/versoes",
                chapter: "/veredas-api/capitulo",
                search: "/veredas-api/pesquisa",
                context: "/veredas-api/contexto",
            }
        };

        const state = {
            books: [],
            versions: [],
            chapterData: null,
            selectedVerse: null,
            selectedVerseEnd: null,
            activePicker: null,
            readingNotes: [],
            readingHighlights: [],
            readingFavorites: [],
            // Preferências locais salvas no LocalStorage de forma 100% segura e offline
            localStore: JSON.parse(localStorage.getItem('veredas-local-store') || '{"notes":[],"highlights":[],"favorites":[],"preferences":{"theme":"sepia","fontScale":1}}')
        };

        const $ = (id) => document.getElementById(id);
        const escapeHtml = (value) => String(value || '').replace(/[&<>"']/g, (char) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));
        const veredasContainer = document.body;
        const readingArea = $('readingArea');
        const notesList = $('notesList');
        const timelineList = $('timelineList');
        const curiositiesList = $('curiositiesList');
        const connectionsList = $('connectionsList');
        const illustrationsList = $('illustrationsList');
        const supportOverview = $('supportOverview');
        const chapterNumberDisplay = $('chapterNumberDisplay');
        const bookLabelDisplay = $('bookLabelDisplay');
        const openReferenceBtn = $('openReferenceBtn');
        const openVersionPickerBtn = $('openVersionPickerBtn');
        const openBookPickerBtn = $('openBookPickerBtn');
        const versionPickerLabel = $('versionPickerLabel');
        const bookPickerLabel = $('bookPickerLabel');
        const pickerModalTitle = $('pickerModalTitle');
        const pickerSearchInput = $('pickerSearchInput');
        const pickerOptionsList = $('pickerOptionsList');
        const verseContextMenu = $('verseContextMenu');
        const verseContextLabel = $('verseContextLabel');

        function saveLocalStore() {
            localStorage.setItem('veredas-local-store', JSON.stringify(state.localStore));
        }

        function applyPreferences() {
            // Remover classes de temas antigos
            veredasContainer.classList.remove('theme-sepia', 'theme-claro', 'theme-escuro');
            veredasContainer.classList.add(`theme-${state.localStore.preferences.theme}`);

            // Setar status de ativo nos botões
            document.querySelectorAll('[data-theme-btn]').forEach(btn => {
                if (btn.getAttribute('data-theme-btn') === state.localStore.preferences.theme) {
                    btn.classList.add('bg-black/15', 'dark:bg-white/10', 'font-bold');
                } else {
                    btn.classList.remove('bg-black/15', 'dark:bg-white/10', 'font-bold');
                }
            });

            readingArea.style.fontSize = `${1.25 * (state.localStore.preferences.fontScale || 1)}rem`;
        }

        function toggleModal(modalId, open) {
            const modal = $(modalId);
            if (!modal) return;
            modal.classList.toggle('hidden', !open);
        }

        function closeAllModals() {
            document.querySelectorAll('.floating-modal, #referenceModal, #searchModal, #noteModal, #pickerModal').forEach((modal) => {
                modal.classList.add('hidden');
            });
        }

        function hideVerseContextMenu() {
            verseContextMenu.classList.add('hidden');
        }

        function positionVerseContextMenu(x, y) {
            verseContextMenu.style.left = `${x}px`;
            verseContextMenu.style.top = `${y}px`;
            verseContextMenu.classList.remove('hidden');

            const rect = verseContextMenu.getBoundingClientRect();
            const relativeX = x;
            const relativeY = y + window.scrollY;

            verseContextMenu.style.left = `${relativeX + 10}px`;
            verseContextMenu.style.top = `${relativeY + 10}px`;
            verseContextMenu.style.position = 'absolute';
        }

        function getSelectionRange() {
            if (!state.selectedVerse) return null;
            const start = Number(state.selectedVerse);
            const end = Number(state.selectedVerseEnd || state.selectedVerse);
            return {
                start: Math.min(start, end),
                end: Math.max(start, end)
            };
        }

        function setVerseSelection(start = null, end = null) {
            state.selectedVerse = start ? Number(start) : null;
            state.selectedVerseEnd = end ? Number(end) : null;
        }

        function clearVerseSelection() {
            state.selectedVerse = null;
            state.selectedVerseEnd = null;
        }

        function isVerseSelected(verseNumber) {
            const selection = getSelectionRange();
            if (!selection) return false;
            return verseNumber >= selection.start && verseNumber <= selection.end;
        }

        function getHighlightForVerse(verseNumber) {
            const h = [...state.readingHighlights].reverse().find(item => {
                const start = Number(item.versiculo_inicial);
                const end = Number(item.versiculo_final || item.versiculo_inicial);
                return verseNumber >= start && verseNumber <= end;
            });
            return h ? h.cor : '';
        }

        function getNoteCountForVerse(verseNumber) {
            return state.readingNotes.filter(item => {
                const start = Number(item.versiculo_inicial);
                const end = Number(item.versiculo_final || item.versiculo_inicial);
                return verseNumber >= start && verseNumber <= end;
            }).length;
        }

        function updateActionState() {
            const hasSelection = !!state.selectedVerse;
            
            $('openNoteModalBtn').disabled = !hasSelection;
            $('favoriteBtn').disabled = !hasSelection;
            $('clearHighlightBtn').disabled = !hasSelection;
            document.querySelectorAll('.action-dot').forEach(btn => btn.disabled = !hasSelection);

            if (hasSelection) {
                // Verificar favorito
                const selection = getSelectionRange();
                const isFav = state.readingFavorites.some(f => {
                    const start = Number(f.versiculo_inicial);
                    const end = Number(f.versiculo_final || f.versiculo_inicial);
                    return start === selection.start && end === selection.end;
                });
                $('favoriteBtn').innerHTML = isFav ? '★' : '☆';
                $('favoriteBtn').title = isFav ? 'Desfavoritar' : 'Favoritar';
            } else {
                $('favoriteBtn').innerHTML = '☆';
            }
        }

        async function fetchJson(url, options = {}) {
            if (!options.headers) options.headers = {};
            options.headers['Accept'] = 'application/json';

            const response = await fetch(url, options);
            const data = await response.json();
            if (!response.ok) {
                throw new Error(data.message || 'Falha na requisição.');
            }
            return data.data !== undefined ? data.data : data;
        }

        async function loadMeta() {
            const [books, versions] = await Promise.all([
                fetchJson(window.veredasConfig.endpoints.books),
                fetchJson(window.veredasConfig.endpoints.versions),
            ]);
            state.books = books;
            state.versions = versions;
            
            $('bookSelect').innerHTML = books.map((book) => `<option value="${book.abbrev.pt}">${book.name}</option>`).join('');
            $('versionSelect').innerHTML = versions.map((version) => `<option value="${version.version}">${String(version.version).toUpperCase()}</option>`).join('');
            
            // Tentar recuperar último livro e capítulo do LocalStore como bookmark
            const lastBook = state.localStore.lastBook || window.veredasConfig.initialBook;
            const lastChapter = state.localStore.lastChapter || window.veredasConfig.initialChapter;
            
            $('bookSelect').value = lastBook;
            $('versionSelect').value = window.veredasConfig.initialVersion;
            
            syncReferenceDisplay();
            
            await loadChapter(lastBook, lastChapter);
        }

        function syncReferenceDisplay() {
            const bookName = $('bookSelect').selectedOptions[0]?.text || state.chapterData?.book?.name || 'Livro';
            const chapter = Number($('chapterInput').value || state.chapterData?.chapter?.number || 1);
            const label = `${bookName} ${chapter}`;
            
            openReferenceBtn.textContent = label;
            bookPickerLabel.textContent = bookName;
            versionPickerLabel.textContent = String($('versionSelect').value || 'Versão').toUpperCase();
        }

        function openPicker(type) {
            state.activePicker = type;
            pickerModalTitle.textContent = type === 'books' ? 'Livro' : 'Versão';
            pickerSearchInput.value = '';
            renderPickerOptions();
            toggleModal('pickerModal', true);
            setTimeout(() => pickerSearchInput.focus(), 10);
        }

        function getPickerItems(type) {
            if (type === 'books') {
                return state.books.map((book) => ({
                    value: book.abbrev.pt,
                    title: book.name,
                    meta: [book.group, book.testament].filter(Boolean).join(' · '),
                }));
            }
            return state.versions.map((version) => ({
                value: version.version,
                title: String(version.version || '').toUpperCase(),
                meta: version.verses ? `${version.verses.toLocaleString('pt-BR')} versículos` : '',
            }));
        }

        function renderPickerOptions() {
            const type = state.activePicker;
            if (!type) return;

            const currentValue = type === 'books' ? $('bookSelect').value : $('versionSelect').value;
            const query = pickerSearchInput.value.trim().toLowerCase();
            const items = getPickerItems(type).filter((item) => {
                if (!query) return true;
                return `${item.title} ${item.meta} ${item.value}`.toLowerCase().includes(query);
            });

            pickerOptionsList.innerHTML = items.length ? items.map((item) => `
                <button type="button" class="w-full text-left px-3 py-2 rounded-lg hover:bg-black/5 dark:hover:bg-white/5 flex items-center justify-between border bible-border-app text-sm ${item.value === currentValue ? 'bg-emerald-600/10 border-emerald-600/40 text-emerald-400' : ''}" data-picker-value="${escapeHtml(item.value)}">
                    <span class="min-w-0">
                        <span class="font-bold">${escapeHtml(item.title)}</span>
                        ${item.meta ? `<span class="block text-[10px] text-slate-400">${escapeHtml(item.meta)}</span>` : ''}
                    </span>
                    <span class="text-xs font-bold">${item.value === currentValue ? '✓' : ''}</span>
                </button>
            `).join('') : `<div class="p-3 text-xs bible-text-muted">Nenhuma opção encontrada.</div>`;

            pickerOptionsList.querySelectorAll('[data-picker-value]').forEach((button) => {
                button.addEventListener('click', async () => {
                    if (type === 'books') {
                        $('bookSelect').value = button.dataset.pickerValue;
                        syncReferenceDisplay();
                        toggleModal('pickerModal', false);
                        toggleModal('referenceModal', true);
                        return;
                    }

                    $('versionSelect').value = button.dataset.pickerValue;
                    syncReferenceDisplay();
                    toggleModal('pickerModal', false);
                    await loadChapter($('bookSelect').value, $('chapterInput').value, $('versionSelect').value);
                });
            });
        }

        async function loadChapter(book = $('bookSelect').value, chapter = $('chapterInput').value, version = $('versionSelect').value) {
            try {
                const url = new URL(window.veredasConfig.endpoints.chapter, window.location.origin);
                url.searchParams.set('livro', book);
                url.searchParams.set('capitulo', chapter);
                url.searchParams.set('versao', version);
                
                const data = await fetchJson(url);
                state.chapterData = data;
                clearVerseSelection();
                
                $('readingMeta').textContent = `Versão ${String(version).toUpperCase()}`;
                bookLabelDisplay.textContent = data.book.name;
                chapterNumberDisplay.textContent = data.chapter.number;
                
                $('chapterInput').value = data.chapter.number;
                $('bookSelect').value = data.book.abbrev?.pt || book;
                
                syncReferenceDisplay();
                
                // Carregar destaques locais
                loadLocalStudyState(book, chapter);
                
                renderChapter();
                updateActionState();
                
                // Salvar bookmark local
                state.localStore.lastBook = book;
                state.localStore.lastChapter = Number(chapter);
                saveLocalStore();

                // Carregar contexto de apoio
                loadContext(book, chapter, version);

            } catch (error) {
                console.error(error);
                $('readingMeta').textContent = 'Erro ao carregar';
                readingArea.innerHTML = `<div class="p-3 text-xs text-rose-500 font-semibold">Não foi possível carregar a referência. Tente novamente mais tarde.</div>`;
            }
        }

        function loadLocalStudyState(book, chapter) {
            const ch = Number(chapter);
            state.readingNotes = (state.localStore.notes || []).filter(n => n.livro === book && n.capitulo === ch);
            state.readingHighlights = (state.localStore.highlights || []).filter(h => h.livro === book && h.capitulo === ch);
            state.readingFavorites = (state.localStore.favorites || []).filter(f => f.livro === book && f.capitulo === ch);
            
            renderNotes();
        }

        function renderChapter() {
            if (!state.chapterData) return;
            
            const verses = state.chapterData.verses || [];
            readingArea.innerHTML = verses.map(v => {
                const color = getHighlightForVerse(v.number);
                const notesCount = getNoteCountForVerse(v.number);
                const isSelected = isVerseSelected(v.number);
                
                return `
                    <span class="verse-card select-text cursor-pointer ${isSelected ? 'selected' : ''}" 
                          data-verse="${v.number}"
                          data-highlight="${color}"
                          onclick="onVerseClick(event, ${v.number})">
                        <sup class="text-[10px] text-slate-400 font-bold ml-1">${v.number}</sup>
                        ${escapeHtml(v.text)}
                        ${notesCount > 0 ? `<span class="verse-note-marker"></span>` : ''}
                    </span>
                `;
            }).join(' ');
        }

        function onVerseClick(e, verseNumber) {
            e.stopPropagation();
            
            if (e.shiftKey) {
                if (!state.selectedVerse) {
                    setVerseSelection(verseNumber);
                } else {
                    setVerseSelection(state.selectedVerse, verseNumber);
                }
            } else {
                if (state.selectedVerse === verseNumber && !state.selectedVerseEnd) {
                    clearVerseSelection();
                } else {
                    setVerseSelection(verseNumber);
                }
            }
            
            renderChapter();
            updateActionState();
            hideVerseContextMenu();
        }

        document.addEventListener('contextmenu', (e) => {
            const verseEl = e.target.closest('.verse-card');
            if (verseEl) {
                e.preventDefault();
                const verseNumber = Number(verseEl.getAttribute('data-verse'));
                setVerseSelection(verseNumber);
                renderChapter();
                updateActionState();
                
                verseContextLabel.textContent = `${$('bookSelect').selectedOptions[0]?.text} ${$('chapterInput').value}:${verseNumber}`;
                positionVerseContextMenu(e.pageX, e.pageY);
            } else {
                hideVerseContextMenu();
            }
        });

        async function loadContext(book, chapter, version) {
            const url = new URL(window.veredasConfig.endpoints.context, window.location.origin);
            url.searchParams.set('livro', book);
            url.searchParams.set('capitulo', chapter);
            url.searchParams.set('versao', version);

            try {
                const data = await fetchJson(url);
                const ext = data.apoio_externo || {};

                const resumoLivro = ext.resumo_livro || {};
                const panoramaCapitulo = ext.panorama_capitulo || {};

                supportOverview.innerHTML = `
                    <div class="summary-card space-y-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">${escapeHtml(resumoLivro.titulo || 'Sobre o livro')}</p>
                        <p class="text-xs leading-relaxed">${escapeHtml(resumoLivro.descricao || 'Carregando resumo do livro...')}</p>
                    </div>
                    <div class="summary-card space-y-2">
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">${escapeHtml(panoramaCapitulo.titulo || 'Panorama do Capítulo')}</p>
                        <p class="text-xs leading-relaxed">${escapeHtml(panoramaCapitulo.descricao || 'Carregando panorama...')}</p>
                    </div>
                `;

                renderSimpleList(timelineList, (data.cronologia && data.cronologia.length) ? data.cronologia : (ext.cronologia || []), 'titulo', 'descricao');
                renderSimpleList(curiositiesList, (data.curiosidades && data.curiosidades.length) ? data.curiosidades : (ext.curiosidades || []), 'titulo', 'descricao');
                
                renderSimpleList(connectionsList, (data.conexoes && data.conexoes.length) ? data.conexoes : (ext.conexoes || []), 'livro_destino', 'explicacao', (item) => {
                    return `${item.livro_destino || ''} ${item.capitulo_destino || ''}`.trim();
                });

                renderSimpleList(illustrationsList, (data.ilustracoes && data.ilustracoes.length) ? data.ilustracoes : (ext.ilustracoes || []), 'titulo', 'legenda', (item) => {
                    if (item.imagem_url) {
                        return `<img src="${item.imagem_url}" alt="${escapeHtml(item.titulo)}" class="mt-2 w-full rounded-lg border bible-border-app" />`;
                    }
                    return '';
                });

            } catch (e) {
                console.error(e);
                supportOverview.innerHTML = `<div class="text-xs bible-text-muted italic">Apoios indisponíveis.</div>`;
            }
        }

        function renderSimpleList(container, items, titleField, textField, extraRenderer = null) {
            if (!items || items.length === 0) {
                container.innerHTML = `<div class="text-[11px] bible-text-muted italic">Nenhum registro para este capítulo.</div>`;
                return;
            }
            container.innerHTML = items.map(item => `
                <div class="p-2 border bible-border-app rounded-lg space-y-1">
                    <p class="text-xs font-bold">${escapeHtml(item[titleField] || 'Detalhe')}</p>
                    ${item[textField] ? `<p class="text-[11px] bible-text-muted leading-relaxed">${escapeHtml(item[textField])}</p>` : ''}
                    ${extraRenderer ? `<div class="pt-1">${extraRenderer(item)}</div>` : ''}
                </div>
            `).join('');
        }

        function renderNotes() {
            if (state.readingNotes.length === 0) {
                notesList.innerHTML = `<div class="text-xs bible-text-muted italic p-2">Nenhuma anotação neste capítulo.</div>`;
                return;
            }

            notesList.innerHTML = state.readingNotes.map((note, index) => `
                <div class="note-card space-y-2 border bible-border-app" onclick="goToVerse(${note.versiculo_inicial})">
                    <div class="flex items-center justify-between">
                        <span class="px-2 py-0.5 text-[9px] font-bold bg-slate-700/50 rounded bible-text-muted">v. ${note.versiculo_inicial}${note.versiculo_final ? `-${note.versiculo_final}` : ''}</span>
                        <button class="text-slate-400 hover:text-rose-500 font-bold text-xs" onclick="deleteNote(event, ${index})">×</button>
                    </div>
                    <p class="text-xs leading-relaxed font-semibold">${escapeHtml(note.conteudo)}</p>
                </div>
            `).join('');
        }

        function goToVerse(v) {
            setVerseSelection(v);
            renderChapter();
            updateActionState();
            
            const card = document.querySelector(`.verse-card[data-verse="${v}"]`);
            if (card) {
                card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }

        function deleteNote(event, index) {
            event.stopPropagation();
            if (!confirm('Deseja excluir esta anotação?')) return;
            
            const targetNote = state.readingNotes[index];
            state.localStore.notes = (state.localStore.notes || []).filter(n => !(n.livro === targetNote.livro && n.capitulo === targetNote.capitulo && n.versiculo_inicial === targetNote.versiculo_inicial && n.versiculo_final === targetNote.versiculo_final));
            saveLocalStore();
            
            loadLocalStudyState($('bookSelect').value, $('chapterInput').value);
            renderChapter();
        }

        function saveNote() {
            const selection = getSelectionRange();
            if (!selection) return;

            const content = $('noteInput').value.trim();
            if (!content) return;

            const book = $('bookSelect').value;
            const chapter = Number($('chapterInput').value);

            // Filtrar notas existentes na mesma posição
            state.localStore.notes = (state.localStore.notes || []).filter(n => !(n.livro === book && n.capitulo === chapter && n.versiculo_inicial === selection.start && n.versiculo_final === (selection.end !== selection.start ? selection.end : null)));
            
            state.localStore.notes.push({
                livro: book,
                capitulo: chapter,
                versiculo_inicial: selection.start,
                versiculo_final: selection.end !== selection.start ? selection.end : null,
                conteudo: content
            });
            
            saveLocalStore();
            toggleModal('noteModal', false);
            
            loadLocalStudyState(book, chapter);
            renderChapter();
        }

        function saveHighlight(color) {
            const selection = getSelectionRange();
            if (!selection) return;

            const book = $('bookSelect').value;
            const chapter = Number($('chapterInput').value);

            state.localStore.highlights = (state.localStore.highlights || []).filter(h => !(h.livro === book && h.capitulo === chapter && h.versiculo_inicial === selection.start && h.versiculo_final === (selection.end !== selection.start ? selection.end : null)));
            
            state.localStore.highlights.push({
                livro: book,
                capitulo: chapter,
                versiculo_inicial: selection.start,
                versiculo_final: selection.end !== selection.start ? selection.end : null,
                cor: color
            });
            
            saveLocalStore();
            
            loadLocalStudyState(book, chapter);
            renderChapter();
            updateActionState();
        }

        function clearHighlight() {
            const selection = getSelectionRange();
            if (!selection) return;

            const book = $('bookSelect').value;
            const chapter = Number($('chapterInput').value);

            state.localStore.highlights = (state.localStore.highlights || []).filter(h => !(h.livro === book && h.capitulo === chapter && h.versiculo_inicial === selection.start && h.versiculo_final === (selection.end !== selection.start ? selection.end : null)));
            
            saveLocalStore();
            
            loadLocalStudyState(book, chapter);
            renderChapter();
            updateActionState();
        }

        function toggleFavorite() {
            const selection = getSelectionRange();
            if (!selection) return;

            const book = $('bookSelect').value;
            const chapter = Number($('chapterInput').value);

            const exists = (state.localStore.favorites || []).some(f => f.livro === book && f.capitulo === chapter && f.versiculo_inicial === selection.start && f.versiculo_final === (selection.end !== selection.start ? selection.end : null));

            if (exists) {
                state.localStore.favorites = (state.localStore.favorites || []).filter(f => !(f.livro === book && f.capitulo === chapter && f.versiculo_inicial === selection.start && f.versiculo_final === (selection.end !== selection.start ? selection.end : null)));
            } else {
                state.localStore.favorites.push({
                    livro: book,
                    capitulo: chapter,
                    versiculo_inicial: selection.start,
                    versiculo_final: selection.end !== selection.start ? selection.end : null
                });
            }
            
            saveLocalStore();
            
            loadLocalStudyState(book, chapter);
            updateActionState();
        }

        async function runSearch() {
            const query = $('searchInput').value.trim();
            if (!query) return;

            $('searchMeta').textContent = "Buscando...";
            $('searchResults').innerHTML = '';

            try {
                const url = new URL(window.veredasConfig.endpoints.search, window.location.origin);
                url.searchParams.set('q', query);
                url.searchParams.set('versao', $('versionSelect').value);
                
                const res = await fetchJson(url);
                const results = res.results || [];
                
                $('searchMeta').textContent = `${results.length} resultado(s) encontrado(s).`;
                
                if (results.length === 0) {
                    $('searchResults').innerHTML = `<div class="p-3 text-xs bible-text-muted italic">Nenhum trecho encontrado.</div>`;
                    return;
                }

                $('searchResults').innerHTML = results.map(r => `
                    <div class="p-2 border bible-border-app rounded-lg hover:bg-black/5 dark:hover:bg-white/5 cursor-pointer text-xs space-y-1" onclick="selectSearchResult('${r.book}', ${r.chapter}, ${r.verse})">
                        <span class="font-bold text-emerald-400">${escapeHtml(r.book_name)} ${r.chapter}:${r.verse}</span>
                        <p class="bible-text-muted">${escapeHtml(r.text)}</p>
                    </div>
                `).join('');
            } catch (e) {
                $('searchMeta').textContent = "Falha ao realizar busca.";
            }
        }

        function selectSearchResult(book, chapter, verse) {
            toggleModal('searchModal', false);
            $('bookSelect').value = book;
            $('chapterInput').value = chapter;
            setVerseSelection(verse);
            
            loadChapter(book, chapter).then(() => {
                goToVerse(verse);
            });
        }

        // Inicialização do DOM
        document.addEventListener('DOMContentLoaded', async () => {
            applyPreferences();
            
            document.querySelectorAll('[data-theme-btn]').forEach(btn => {
                btn.addEventListener('click', () => {
                    state.localStore.preferences.theme = btn.getAttribute('data-theme-btn');
                    saveLocalStore();
                    applyPreferences();
                });
            });

            $('increaseFontBtn').addEventListener('click', () => {
                state.localStore.preferences.fontScale = Math.min(1.4, Number((state.localStore.preferences.fontScale || 1) + 0.05).toFixed(2));
                saveLocalStore();
                applyPreferences();
            });
            
            $('decreaseFontBtn').addEventListener('click', () => {
                state.localStore.preferences.fontScale = Math.max(0.8, Number((state.localStore.preferences.fontScale || 1) - 0.05).toFixed(2));
                saveLocalStore();
                applyPreferences();
            });

            openVersionPickerBtn.addEventListener('click', () => openPicker('versions'));
            openBookPickerBtn.addEventListener('click', () => openPicker('books'));
            pickerSearchInput.addEventListener('input', () => renderPickerOptions());

            $('runSearchBtn').addEventListener('click', () => toggleModal('searchModal', true));
            $('runSearchModalBtn').addEventListener('click', runSearch);
            $('searchInput').addEventListener('keydown', (e) => {
                if (e.key === 'Enter') runSearch();
            });

            $('openNoteModalBtn').addEventListener('click', () => {
                const range = getSelectionRange();
                if (!range) return;
                
                const bookName = $('bookSelect').selectedOptions[0]?.text || '';
                $('noteModalReference').textContent = `Anotando em ${bookName} ${$('chapterInput').value}:${range.start}${range.end !== range.start ? `-${range.end}` : ''}`;
                
                const existing = state.readingNotes.find(n => n.versiculo_inicial === range.start && (n.versiculo_final || n.versiculo_inicial) === range.end);
                $('noteInput').value = existing ? existing.conteudo : '';
                
                toggleModal('noteModal', true);
            });

            $('openNoteModalSecondaryBtn').addEventListener('click', () => {
                if (!state.selectedVerse) {
                    alert('Selecione primeiro um versículo no texto.');
                    return;
                }
                $('openNoteModalBtn').click();
            });

            $('saveNoteBtn').addEventListener('click', saveNote);
            $('favoriteBtn').addEventListener('click', toggleFavorite);
            $('clearHighlightBtn').addEventListener('click', clearHighlight);

            document.querySelectorAll('.action-dot').forEach(btn => {
                btn.addEventListener('click', () => {
                    saveHighlight(btn.getAttribute('data-color'));
                });
            });

            $('prevChapterBtn').addEventListener('click', () => {
                const current = Math.max(1, Number($('chapterInput').value || 1));
                if (current <= 1) return;
                $('chapterInput').value = current - 1;
                loadChapter();
            });

            $('nextChapterBtn').addEventListener('click', () => {
                $('chapterInput').value = Number($('chapterInput').value || 1) + 1;
                loadChapter();
            });

            $('openReferenceBtn').addEventListener('click', () => {
                toggleModal('referenceModal', true);
            });

            $('loadChapterBtn').addEventListener('click', () => {
                loadChapter();
                toggleModal('referenceModal', false);
            });

            document.querySelectorAll('.verse-context-color').forEach(btn => {
                btn.addEventListener('click', () => {
                    saveHighlight(btn.getAttribute('data-context-color'));
                    hideVerseContextMenu();
                });
            });

            document.querySelector('[data-context-action="note-open"]').addEventListener('click', () => {
                hideVerseContextMenu();
                $('openNoteModalBtn').click();
            });

            document.querySelector('[data-context-action="note-edit"]').addEventListener('click', () => {
                hideVerseContextMenu();
                $('openNoteModalBtn').click();
            });

            document.querySelector('[data-context-action="highlight-clear"]').addEventListener('click', () => {
                clearHighlight();
                hideVerseContextMenu();
            });

            document.querySelector('[data-context-action="note-delete"]').addEventListener('click', (e) => {
                hideVerseContextMenu();
                const range = getSelectionRange();
                if (!range) return;
                const existingIndex = state.readingNotes.findIndex(n => n.versiculo_inicial === range.start && (n.versiculo_final || n.versiculo_inicial) === range.end);
                if (existingIndex !== -1) {
                    deleteNote(e, existingIndex);
                }
            });

            document.addEventListener('click', (e) => {
                if (!e.target.closest('#verseContextMenu')) {
                    hideVerseContextMenu();
                }
            });

            // Cursor Follower Logic
            const cursor = document.getElementById('custom-cursor');
            const cursorDot = document.getElementById('custom-cursor-dot');
            if (cursor && cursorDot) {
                window.addEventListener('mousemove', (e) => {
                    cursor.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                    cursorDot.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                });

                document.addEventListener('mouseover', (e) => {
                    const clickable = e.target.closest('a, button, input, select, textarea, [role="button"], .group, .verse-card, .note-card, .picker-option');
                    if (clickable) {
                        cursor.classList.add('scale-[2.5]', 'bg-white');
                        cursor.classList.remove('border-slate-500/40');
                        cursorDot.classList.add('opacity-0');
                    } else {
                        cursor.classList.remove('scale-[2.5]', 'bg-white');
                        cursor.classList.add('border-slate-500/40');
                        cursorDot.classList.remove('opacity-0');
                    }
                });
            }

            await loadMeta();
        });
    </script>
</body>
</html>
