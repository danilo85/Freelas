<!DOCTYPE html>
<html lang="pt-BR" class="h-full w-full overflow-hidden bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Painel de Revisão Editorial | {{ $revision->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- PDF.js CDN para Renderização de PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .paper-shadow {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.1), 0 0 5px rgba(0, 0, 0, 0.05);
        }
    </style>

    <script>
        function revisorWorkspace() {
            const filesData = @json($revision->files);
            const textsData = @json($extractedTexts);
            const streamBaseUrl = '{{ url("/revisao-editorial/" . $revision->share_token . "/file") }}';

            return {
                leftSidebarOpen: false,
                rightSidebarOpen: false,
                openCorrectionModal: false,
                openUploadVersionModal: false,
                categoryFilter: 'todas',
                viewMode: 'track', // 'track', 'original', 'final'
                viewerMode: 'native', // 'native' ou 'google'
                toastMessage: '',
                selectedFileId: filesData.length > 0 ? filesData[0].id : null,
                languageToolMatches: [],
                
                // Track Changes state
                originalContent: '',
                revisedContent: '',
                savingText: false,

                // Auth State
                isAuth: @json($isAuthenticated),
                loginEmail: '',
                loginPassword: '',
                loginError: '',

                // PDF.js State
                pdfDoc: null,
                currentPage: 1,
                totalPages: 1,
                pdfScale: 1.2,
                renderingPdf: false,

                // Modal Correction State
                modalCategory: 'ortografia',
                modalOriginalText: '',
                modalSuggestedText: '',
                modalJustification: '',
                modalPageNumber: 1,

                init() {
                    this.loadContentForSelectedFile();

                    this.$watch('selectedFileId', (id) => {
                        this.loadContentForSelectedFile();
                        this.handleFileChange(id);
                    });

                    this.$nextTick(() => {
                        if (this.currentFile && this.currentFile.file_type === 'pdf') {
                            this.loadPdfDocument();
                        }
                    });
                },

                get currentFile() {
                    return filesData.find(f => f.id == this.selectedFileId) || null;
                },

                loadContentForSelectedFile() {
                    const text = textsData[this.selectedFileId] || '';
                    this.originalContent = text;
                    this.revisedContent = text;
                },

                getFileStreamUrl(id) {
                    return streamBaseUrl + '/' + id + '/stream';
                },

                getFileDownloadUrl(id) {
                    return streamBaseUrl + '/' + id + '/download';
                },

                getGoogleDocsViewerUrl(id) {
                    const fullStreamUrl = window.location.origin + this.getFileStreamUrl(id);
                    return 'https://docs.google.com/gview?url=' + encodeURIComponent(fullStreamUrl) + '&embedded=true';
                },

                handleFileChange(id) {
                    const file = this.currentFile;
                    if (file && file.file_type === 'pdf' && this.viewerMode === 'native') {
                        this.loadPdfDocument();
                    }
                },

                loadPdfDocument() {
                    if (!this.currentFile) return;
                    const url = this.getFileStreamUrl(this.currentFile.id);
                    this.renderingPdf = true;

                    pdfjsLib.getDocument(url).promise.then(pdf => {
                        this.pdfDoc = pdf;
                        this.totalPages = pdf.numPages;
                        this.currentPage = 1;
                        this.renderPdfPage(1);
                    }).catch(err => {
                        this.renderingPdf = false;
                    });
                },

                renderPdfPage(num) {
                    if (!this.pdfDoc) return;
                    this.renderingPdf = true;

                    this.pdfDoc.getPage(num).then(page => {
                        const canvas = document.getElementById('pdf-canvas');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const viewport = page.getViewport({ scale: this.pdfScale });

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };

                        page.render(renderContext).promise.then(() => {
                            this.renderingPdf = false;
                            this.modalPageNumber = num;
                        });
                    });
                },

                prevPage() {
                    if (this.currentPage <= 1) return;
                    this.currentPage--;
                    this.renderPdfPage(this.currentPage);
                },

                nextPage() {
                    if (this.currentPage >= this.totalPages) return;
                    this.currentPage++;
                    this.renderPdfPage(this.currentPage);
                },

                zoomPdf(delta) {
                    this.pdfScale = Math.max(0.5, Math.min(3.0, this.pdfScale + delta));
                    this.renderPdfPage(this.currentPage);
                },

                saveEditedText() {
                    if (!this.currentFile) return;
                    this.savingText = true;

                    fetch('{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + this.currentFile.id + '/content', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ revised_content: this.revisedContent })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.savingText = false;
                        this.showToast('Alterações do texto salvas com sucesso!');
                    })
                    .catch(() => {
                        this.savingText = false;
                        this.showToast('Erro ao salvar alterações no servidor.');
                    });
                },

                submitRevisorLogin() {
                    if (!this.loginEmail || !this.loginPassword) {
                        this.loginError = 'Por favor, preencha e-mail e senha.';
                        return;
                    }

                    fetch('{{ route("public.editorial.revisor.login", $revision->share_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ email: this.loginEmail, password: this.loginPassword })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.isAuth = true;
                            this.showToast('Autenticado com sucesso no Workspace!');
                        } else {
                            this.loginError = data.message || 'Credenciais inválidas.';
                        }
                    })
                    .catch(() => {
                        this.loginError = 'Erro no servidor ao validar o acesso.';
                    });
                },

                captureSelectedText() {
                    const sel = window.getSelection().toString().trim();
                    if (sel) {
                        this.modalOriginalText = sel;
                        this.showToast('Trecho capturado do texto! Clique em + Novo Apontamento para usar.');
                    }
                },

                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                copyAuthorLink(url) {
                    navigator.clipboard.writeText(url);
                    this.showToast('Link do Autor copiado com sucesso para a área de transferência!');
                },

                checkLanguageTool() {
                    const text = this.revisedContent || this.originalContent;
                    if (!text || text.length < 5) {
                        this.showToast('Não há texto suficiente extraído para análise ortográfica.');
                        return;
                    }

                    this.showToast('Analisando texto com LanguageTool...');

                    fetch('{{ route("public.editorial.revisor.languagetool", $revision->share_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ text: text })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.languageToolMatches = data.matches || [];
                        this.showToast('Análise concluída! Encontradas ' + this.languageToolMatches.length + ' sugestões.');
                    })
                    .catch(() => {
                        this.showToast('Falha ao conectar ao serviço de ortografia.');
                    });
                },

                applyLanguageToolMatch(match) {
                    const text = this.revisedContent;
                    const orig = text.substring(match.offset, match.offset + match.length);
                    const replacement = (match.replacements && match.replacements.length > 0) ? match.replacements[0].value : '';

                    this.modalCategory = 'ortografia';
                    this.modalOriginalText = orig;
                    this.modalSuggestedText = replacement;
                    this.modalJustification = 'Sugestão do LanguageTool: ' + match.message;
                    this.openCorrectionModal = true;
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800 h-screen w-screen overflow-hidden flex flex-col" x-data="revisorWorkspace()">

    <!-- Modal de Autenticação / Login do Revisor -->
    <div x-show="!isAuth" x-cloak class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm select-none">
        <div class="bg-white border border-slate-200 text-slate-800 rounded-xl p-8 shadow-2xl max-w-md w-full space-y-6">
            <div class="text-center space-y-2">
                <span class="text-4xl block">🔐</span>
                <h3 class="font-outfit font-black text-xl uppercase tracking-tight text-slate-900">Acesso Restrito ao Revisor</h3>
                <p class="text-xs text-slate-500 font-medium">Digite os seus dados de acesso para desbloquear o Workspace do projeto <strong class="text-slate-700">{{ $revision->title }}</strong>.</p>
            </div>

            <template x-if="loginError">
                <div class="bg-rose-50 border border-rose-200 text-rose-700 p-3 rounded-[5px] text-xs font-bold" x-text="loginError"></div>
            </template>

            <form @submit.prevent="submitRevisorLogin" class="space-y-4 text-xs">
                <div>
                    <label class="font-bold block mb-1 uppercase tracking-wider text-slate-500">E-mail do Revisor</label>
                    <input type="email" x-model="loginEmail" required placeholder="revisora@exemplo.com" class="w-full px-4 py-3 border border-slate-200 rounded-[5px] bg-slate-50 text-slate-800 font-medium text-sm">
                </div>

                <div>
                    <label class="font-bold block mb-1 uppercase tracking-wider text-slate-500">Senha de Acesso</label>
                    <input type="password" x-model="loginPassword" required placeholder="••••••••" class="w-full px-4 py-3 border border-slate-200 rounded-[5px] bg-slate-50 text-slate-800 font-medium text-sm">
                </div>

                <button type="submit" class="w-full py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-md transition-all">
                    Entrar no Workspace
                </button>
            </form>
        </div>
    </div>

    <!-- Toast Notification Banner -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition
         class="fixed bottom-20 right-6 z-[99999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- HEADER SUPERIOR (Igual ao Painel de Provas do Autor) -->
    <header class="h-16 border-b border-slate-200 glassmorphism px-6 flex items-center justify-between z-30 shrink-0 select-none">
        <div class="flex items-center gap-3">
            <span class="font-outfit font-black text-sm tracking-tight text-slate-800">
                DANILO<span class="text-blue-600">MIGUEL</span>
            </span>
            <span class="h-4 w-px bg-slate-200"></span>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-widest">Painel de Revisão Editorial</span>
        </div>

        <div class="flex items-center gap-2 bg-slate-50 border border-slate-200 px-3.5 py-1.5 rounded-[5px] text-xs font-medium">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Revisor Atribuído:</span>
            <span class="font-bold text-slate-800">{{ $revision->revisor ? $revision->revisor->name : 'Revisor Geral / Cliente' }}</span>
        </div>

        <div class="flex items-center gap-2">
            <button type="button" @click="checkLanguageTool()" class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs uppercase tracking-wider px-3.5 py-2 rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm">
                <span>🔍</span> LanguageTool
            </button>

            <button type="button" @click="openCorrectionModal = true" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm">
                <span>➕</span> Novo Apontamento
            </button>

            <button type="button" @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm">
                <span>🔗</span> Link do Autor
            </button>
        </div>
    </header>

    <!-- CORPO PRINCIPAL DE 3 COLUNAS COM ALTURA FLUIDA 100% -->
    <main class="flex-1 flex overflow-hidden min-h-0">

        <!-- COLUNA 1 (ESQUERDA - 320px): LISTA DE APONTAMENTOS E CATEGORIAS -->
        <aside class="w-80 border-r border-slate-200 bg-white flex flex-col justify-between shrink-0 h-full overflow-hidden z-20">
            
            <!-- Informação do Arquivo Selecionado -->
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between shrink-0">
                <div class="min-w-0">
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest block">Arquivo Ativo</span>
                    <h5 class="text-xs font-bold text-slate-800 truncate mt-0.5" x-text="currentFile ? currentFile.filename : 'Selecione um arquivo'"></h5>
                    <span class="text-[9px] text-slate-500 font-bold block mt-0.5" x-text="currentFile ? 'Versão ' + currentFile.version + ' • ' + currentFile.file_type.toUpperCase() : ''"></span>
                </div>
            </div>

            <!-- Filtros de Apontamentos -->
            <div class="flex border-b border-slate-200 text-xs font-bold uppercase tracking-wider shrink-0 bg-white">
                <button @click="categoryFilter = 'todas'" class="flex-1 py-2.5 text-center border-b-2 text-[10px]" :class="categoryFilter === 'todas' ? 'border-blue-600 text-blue-600 bg-slate-50' : 'border-transparent text-slate-400'">Todas</button>
                <button @click="categoryFilter = 'ortografia'" class="flex-1 py-2.5 text-center border-b-2 text-[10px]" :class="categoryFilter === 'ortografia' ? 'border-rose-600 text-rose-600 bg-rose-50' : 'border-transparent text-slate-400'">Ortografia</button>
                <button @click="categoryFilter = 'gramatica'" class="flex-1 py-2.5 text-center border-b-2 text-[10px]" :class="categoryFilter === 'gramatica' ? 'border-amber-600 text-amber-600 bg-amber-50' : 'border-transparent text-slate-400'">Gramática</button>
                <button @click="categoryFilter = 'duvida'" class="flex-1 py-2.5 text-center border-b-2 text-[10px]" :class="categoryFilter === 'duvida' ? 'border-blue-600 text-blue-600 bg-blue-50' : 'border-transparent text-slate-400'">Dúvidas</button>
            </div>

            <!-- Feed de Apontamentos -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                @forelse($revision->corrections as $cor)
                    @php
                        $badgeClass = match($cor->category) {
                            'ortografia' => 'bg-rose-100 text-rose-800 border border-rose-200',
                            'gramatica' => 'bg-amber-100 text-amber-800 border border-amber-200',
                            'duvida' => 'bg-blue-100 text-blue-800 border border-blue-200',
                            'padronizacao' => 'bg-purple-100 text-purple-800 border border-purple-200',
                            default => 'bg-slate-100 text-slate-800 border border-slate-200',
                        };
                    @endphp
                    <div x-show="categoryFilter === 'todas' || categoryFilter === '{{ $cor->category }}'" class="p-3.5 bg-slate-50 border border-slate-200 rounded-[5px] text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[3px] {{ $badgeClass }}">
                                {{ ucfirst($cor->category) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold">Pág. {{ $cor->page_number ?: 1 }}</span>
                        </div>

                        @if($cor->original_text)
                            <p class="font-mono text-slate-600 line-through">"{{ $cor->original_text }}"</p>
                        @endif

                        @if($cor->suggested_text)
                            <p class="font-mono text-emerald-800 font-bold">➔ {{ $cor->suggested_text }}</p>
                        @endif

                        @if($cor->justification)
                            <p class="text-slate-500 italic">💡 {{ $cor->justification }}</p>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-slate-400 py-12 font-semibold text-xs border border-dashed border-slate-200 rounded-[5px]">
                        Nenhum apontamento cadastrado nesta revisão.
                    </div>
                @endforelse
            </div>

        </aside>

        <!-- COLUNA 2 (CENTRO - FLEX-1): VIEWPORT DO DOCUMENTO -->
        <section class="flex-1 bg-slate-200/70 flex flex-col min-w-0 relative overflow-hidden h-full">
            
            <!-- Barra Secundária Superior do Visualizador -->
            <div class="h-12 border-b border-slate-200 bg-white px-4 flex items-center justify-between shrink-0 z-10 shadow-xs">
                
                <!-- Ferramentas de Visualização (Modo Leitor Interno vs Google Docs Viewer) -->
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px] text-xs font-bold">
                        <button type="button" @click="viewerMode = 'native'" class="px-3 py-1 rounded-[3px] transition-all" :class="viewerMode === 'native' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                            👁️ Leitor/Editor Interno
                        </button>
                        <button type="button" @click="viewerMode = 'google'" class="px-3 py-1 rounded-[3px] transition-all" :class="viewerMode === 'google' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500 hover:text-slate-800'">
                            🌐 Google Docs Viewer
                        </button>
                    </div>

                    <!-- Controles para PDF (Navegação & Zoom) -->
                    <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'pdf'">
                        <div class="flex items-center gap-2 text-xs font-bold text-slate-600 pl-4 border-l border-slate-200">
                            <button type="button" @click="prevPage()" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">◀</button>
                            <span>PÁG. <span x-text="currentPage"></span> DE <span x-text="totalPages"></span></span>
                            <button type="button" @click="nextPage()" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">▶</button>
                            <button type="button" @click="zoomPdf(-0.1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px] ml-2">🔍-</button>
                            <button type="button" @click="zoomPdf(0.1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">🔍+</button>
                        </div>
                    </template>
                </div>

                <!-- Ferramentas do Track Changes (Para Documentos Word) -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'word'">
                    <div class="flex items-center gap-2 text-xs font-bold">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="viewMode = 'track'" class="px-2.5 py-1 rounded-[3px]" :class="viewMode === 'track' ? 'bg-blue-600 text-white' : 'text-slate-500'">✨ Track Changes</button>
                            <button type="button" @click="viewMode = 'original'" class="px-2.5 py-1 rounded-[3px]" :class="viewMode === 'original' ? 'bg-rose-600 text-white' : 'text-slate-500'">🔴 Versão Antiga</button>
                            <button type="button" @click="viewMode = 'final'" class="px-2.5 py-1 rounded-[3px]" :class="viewMode === 'final' ? 'bg-emerald-600 text-white' : 'text-slate-500'">🟢 Versão Final</button>
                        </div>

                        <button type="button" @click="saveEditedText()" class="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-[5px] uppercase tracking-wider transition-all flex items-center gap-1">
                            <span>💾</span> <span x-text="savingText ? 'Salvando...' : 'Salvar Alterações'">Salvar</span>
                        </button>
                    </div>
                </template>

            </div>

            <!-- CANVAS PRINCIPAL (Document Visualizer na Folha Branca) -->
            <div class="flex-1 overflow-auto flex items-center justify-center p-6 relative">
                
                <!-- CANVAS PDF.JS -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'pdf'">
                    <div class="bg-white paper-shadow rounded border border-slate-200 p-2 relative max-w-4xl max-h-full overflow-auto">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-500 z-10">
                            Renderizando PDF...
                        </div>
                        <canvas id="pdf-canvas" class="max-w-full block mx-auto"></canvas>
                    </div>
                </template>

                <!-- EDITOR WORD TRACK CHANGES -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'word'">
                    <div class="w-full max-w-4xl bg-white paper-shadow rounded-[5px] border border-slate-200 p-8 space-y-4 my-auto max-h-full overflow-y-auto">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h4 class="font-outfit font-black text-xs uppercase tracking-wider text-slate-400">Editor Directo do Revisor (Track Changes)</h4>
                            <span class="text-[10px] text-slate-400 italic">Digite alterações diretamente no texto abaixo</span>
                        </div>

                        <div x-show="viewMode === 'track'" class="space-y-2">
                            <textarea x-model="revisedContent" rows="18" class="w-full bg-slate-50 text-slate-800 font-serif text-sm p-6 border border-slate-200 rounded-[5px] leading-relaxed focus:outline-none focus:ring-1 focus:ring-blue-500 select-text" @mouseup="captureSelectedText"></textarea>
                        </div>

                        <div x-show="viewMode === 'original'" class="p-6 bg-rose-50 border border-rose-200 rounded-[5px] text-xs font-serif leading-relaxed text-rose-900 max-h-[500px] overflow-y-auto whitespace-pre-wrap">
                            <span x-text="originalContent"></span>
                        </div>

                        <div x-show="viewMode === 'final'" class="p-6 bg-emerald-50 border border-emerald-200 rounded-[5px] text-xs font-serif leading-relaxed text-emerald-900 max-h-[500px] overflow-y-auto whitespace-pre-wrap">
                            <span x-text="revisedContent"></span>
                        </div>
                    </div>
                </template>

                <!-- GOOGLE DOCS VIEWER EMBED -->
                <template x-if="viewerMode === 'google' && currentFile">
                    <div class="w-full max-w-5xl h-full bg-white paper-shadow rounded-[5px] border border-slate-200 p-2 flex flex-col">
                        <iframe :src="getGoogleDocsViewerUrl(currentFile.id)" class="w-full h-full rounded border-0" frameborder="0"></iframe>
                    </div>
                </template>

                <!-- VISUALIZADOR DE IMAGENS -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'image'">
                    <div class="bg-white paper-shadow rounded p-2 border border-slate-200 max-w-4xl">
                        <img :src="getFileStreamUrl(currentFile.id)" class="max-h-[75vh] object-contain">
                    </div>
                </template>

            </div>

        </section>

        <!-- COLUNA 3 (DIREITA - 280px): NAVEGADOR DE ARQUIVOS -->
        <aside class="w-72 border-l border-slate-200 bg-white flex flex-col justify-between shrink-0 h-full overflow-hidden z-20">
            
            <div class="p-4 border-b border-slate-200 bg-slate-50/50 flex items-center justify-between shrink-0">
                <h4 class="font-outfit font-black text-slate-800 text-xs uppercase tracking-tight">Navegador de Arquivos</h4>
                <span class="px-2 py-0.5 bg-slate-200 text-slate-700 text-[10px] font-bold rounded-full">
                    {{ $revision->files->count() }}
                </span>
            </div>

            <!-- Lista de Arquivos do Projeto -->
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @foreach($revision->files as $file)
                    <div @click="selectedFileId = {{ $file->id }}" 
                         class="p-3 rounded-[5px] border transition-all cursor-pointer select-none space-y-1"
                         :class="selectedFileId == {{ $file->id }} ? 'bg-blue-50/80 border-blue-400 shadow-xs' : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100'">
                        
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[3px]"
                                  :class="selectedFileId == {{ $file->id }} ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'">
                                {{ strtoupper($file->file_type) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold">v{{ $file->version }}</span>
                        </div>

                        <h5 class="text-xs font-bold text-slate-800 truncate" title="{{ $file->filename }}">{{ $file->filename }}</h5>
                        <p class="text-[10px] text-slate-400 font-medium">{{ number_format($file->file_size / 1024, 1) }} KB</p>
                    </div>
                @endforeach
            </div>

            <!-- Ações do Rodapé -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50 space-y-2 shrink-0">
                <button type="button" @click="openUploadVersionModal = true" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow-xs">
                    📤 Subir Nova Versão
                </button>
            </div>

        </aside>

    </main>

    <!-- Modal Criar Apontamento -->
    <div x-show="openCorrectionModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openCorrectionModal = false" class="bg-white border border-slate-200 text-slate-800 rounded-xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-black text-slate-900 text-md uppercase">➕ Novo Apontamento de Revisão</h3>
                <button type="button" @click="openCorrectionModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('public.editorial.revisor.corrections.store', $revision->share_token) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="editorial_revision_file_id" :value="selectedFileId">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold block mb-1">Categoria</label>
                        <select name="category" x-model="modalCategory" required class="w-full px-3 py-2 border border-slate-200 rounded-[5px]">
                            <option value="ortografia">Ortografia</option>
                            <option value="gramatica">Gramática</option>
                            <option value="pontuacao">Pontuação</option>
                            <option value="clareza">Clareza de Frase</option>
                            <option value="padronizacao">Padronização</option>
                            <option value="duvida">Dúvida para o Autor</option>
                            <option value="termo_tecnico">Termo Técnico</option>
                            <option value="observacao">Observação Geral</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold block mb-1">Página (Opcional)</label>
                        <input type="number" name="page_number" x-model="modalPageNumber" placeholder="Ex: 5" class="w-full px-3 py-2 border border-slate-200 rounded-[5px]">
                    </div>
                </div>

                <div>
                    <label class="font-bold block mb-1">Texto Original do Autor</label>
                    <textarea name="original_text" x-model="modalOriginalText" rows="2" placeholder="Trecho extraído do texto..." class="w-full px-3 py-2 border border-slate-200 rounded-[5px]"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Sugestão de Correção</label>
                    <textarea name="suggested_text" x-model="modalSuggestedText" rows="2" placeholder="Digite a correção sugerida..." class="w-full px-3 py-2 border border-slate-200 rounded-[5px]"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Justificativa / Comentário</label>
                    <input type="text" name="justification" x-model="modalJustification" placeholder="Explicação da regra ou orientação..." class="w-full px-3 py-2 border border-slate-200 rounded-[5px]">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openCorrectionModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-[5px]">Salvar Apontamento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Upload de Nova Versão -->
    <div x-show="openUploadVersionModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openUploadVersionModal = false" class="bg-white border border-slate-200 text-slate-800 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-black text-slate-900 text-md uppercase">📤 Salvar Nova Versão Revisada</h3>
                <button type="button" @click="openUploadVersionModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <form action="{{ route('public.editorial.revisor.version.store', $revision->share_token) }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="parent_file_id" :value="selectedFileId">

                <div>
                    <label class="font-bold block mb-1">Arquivo Revisado (Word / PDF / Imagem)</label>
                    <input type="file" name="file" required class="w-full px-3 py-2 border border-slate-200 rounded-[5px]">
                    <p class="text-[10px] text-slate-400 mt-1">Este arquivo criará uma nova versão no histórico (ex: Versão 2).</p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openUploadVersionModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-bold rounded-[5px]">Salvar Nova Versão</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
