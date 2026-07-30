<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-900">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Workspace de Revisão Editorial | {{ $revision->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        .track-added { background-color: #d1fae5; color: #065f46; font-weight: bold; text-decoration: none; padding: 0 4px; border-radius: 3px; }
        .track-removed { background-color: #ffe4e6; color: #9f1239; text-decoration: line-through; padding: 0 4px; border-radius: 3px; }
    </style>

    <script>
        function revisorWorkspace() {
            const filesData = @json($revision->files);
            const textsData = @json($extractedTexts);
            const streamBaseUrl = '{{ url("/revisao-editorial/" . $revision->share_token . "/file") }}';

            return {
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
                isEditingText: false,
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
<body class="font-sans antialiased bg-slate-900 text-slate-100 min-h-full flex flex-col justify-between" x-data="revisorWorkspace()">

    <!-- Modal de Autenticação / Login do Revisor -->
    <div x-show="!isAuth" x-cloak class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-sm select-none">
        <div class="bg-slate-800 border border-slate-700 text-slate-100 rounded-xl p-8 shadow-2xl max-w-md w-full space-y-6">
            <div class="text-center space-y-2">
                <span class="text-4xl block">🔐</span>
                <h3 class="font-outfit font-black text-xl uppercase tracking-tight text-white">Acesso Restrito ao Revisor</h3>
                <p class="text-xs text-slate-400 font-medium">Digite os seus dados de acesso para desbloquear o Workspace do projeto <strong class="text-slate-200">{{ $revision->title }}</strong>.</p>
            </div>

            <template x-if="loginError">
                <div class="bg-rose-900/50 border border-rose-700 text-rose-200 p-3 rounded-[5px] text-xs font-bold" x-text="loginError"></div>
            </template>

            <form @submit.prevent="submitRevisorLogin" class="space-y-4 text-xs">
                <div>
                    <label class="font-bold block mb-1 uppercase tracking-wider text-slate-400">E-mail do Revisor</label>
                    <input type="email" x-model="loginEmail" required placeholder="revisora@exemplo.com" class="w-full px-4 py-3 border border-slate-700 rounded-[5px] bg-slate-900 text-white font-medium text-sm">
                </div>

                <div>
                    <label class="font-bold block mb-1 uppercase tracking-wider text-slate-400">Senha de Acesso</label>
                    <input type="password" x-model="loginPassword" required placeholder="••••••••" class="w-full px-4 py-3 border border-slate-700 rounded-[5px] bg-slate-900 text-white font-medium text-sm">
                </div>

                <button type="submit" class="w-full py-3.5 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-md transition-all">
                    Entrar no Workspace
                </button>
            </form>
        </div>
    </div>

    <!-- Toast Notification Banner -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition
         class="fixed bottom-24 right-6 z-[99999] bg-emerald-600 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-emerald-500">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-emerald-200 hover:text-white ml-2">✕</button>
    </div>

    <!-- Topo Escuro Moderno (Igual ao Portal do Autor de Revisão) -->
    <header class="bg-slate-950 border-b border-slate-800 shadow-md py-4 px-6 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✍️</span>
                <div>
                    <h1 class="text-base font-black font-outfit uppercase tracking-tight text-white">Workspace de Revisão Editorial</h1>
                    <p class="text-[11px] text-slate-400 font-medium">{{ $revision->title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <button type="button" 
                        @click="openUploadVersionModal = true" 
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold rounded-[5px] transition-colors border border-slate-700 flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>📤</span> Salvar Nova Versão
                </button>

                <button type="button" 
                        @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" 
                        class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>🔗</span> Link para o Autor
                </button>

                <button type="button" 
                        @click="openCorrectionModal = true" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>➕</span> Criar Apontamento
                </button>
            </div>
        </div>
    </header>

    <!-- Conteúdo Principal do Revisor -->
    <main class="max-w-7xl mx-auto px-4 py-8 w-full flex-1 space-y-6">

        <!-- Grid Principal: Leitor/Editor Interativo (Esquerda) vs Apontamentos (Direita) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Coluna Esquerda: Editor de Texto com Track Changes / PDF / Imagem (7 Colunas) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Barra de Ferramentas de Edição e Alternador de Visão (Track Changes) -->
                <div class="bg-slate-800 border border-slate-700 rounded-[5px] p-4 shadow-sm space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1 flex-1">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Arquivo Selecionado</span>
                            <select x-model="selectedFileId" class="w-full px-3 py-2 border border-slate-700 rounded-[5px] text-xs font-bold bg-slate-900 text-white focus:outline-none">
                                @foreach($revision->files as $file)
                                    <option value="{{ $file->id }}">{{ $file->filename }} ({{ strtoupper($file->file_type) }} - v{{ $file->version }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Alternador Leitor Interno vs Google Docs Viewer -->
                        <div class="flex items-center gap-1 shrink-0 bg-slate-900 p-1 rounded-[5px] text-xs font-bold border border-slate-700">
                            <button type="button" @click="viewerMode = 'native'" class="px-3 py-1.5 rounded-[5px] transition-all" :class="viewerMode === 'native' ? 'bg-primary-600 text-white' : 'text-slate-400 hover:text-white'">
                                👁️ Editor Interno
                            </button>
                            <button type="button" @click="viewerMode = 'google'" class="px-3 py-1.5 rounded-[5px] transition-all" :class="viewerMode === 'google' ? 'bg-primary-600 text-white' : 'text-slate-400 hover:text-white'">
                                🌐 Google Docs Viewer
                            </button>
                        </div>
                    </div>

                    <!-- Ferramentas do Track Changes (Modos Antes / Alterações / Depois) -->
                    <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'word'">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pt-3 border-t border-slate-700 text-xs font-bold">
                            <div class="flex items-center gap-1">
                                <button type="button" @click="viewMode = 'track'" class="px-2.5 py-1 rounded-[5px]" :class="viewMode === 'track' ? 'bg-emerald-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'">✨ Edição com Marcas</button>
                                <button type="button" @click="viewMode = 'original'" class="px-2.5 py-1 rounded-[5px]" :class="viewMode === 'original' ? 'bg-rose-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'">🔴 Versão Antiga</button>
                                <button type="button" @click="viewMode = 'final'" class="px-2.5 py-1 rounded-[5px]" :class="viewMode === 'final' ? 'bg-blue-600 text-white' : 'bg-slate-900 text-slate-400 hover:text-white'">🟢 Versão Atualizada</button>
                            </div>

                            <button type="button" @click="saveEditedText()" class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-[5px] uppercase tracking-wider transition-all shadow-sm flex items-center gap-1.5">
                                <span>💾</span> <span x-text="savingText ? 'Salvando...' : 'Salvar Alterações'">Salvar Alterações</span>
                            </button>
                        </div>
                    </template>
                </div>

                <!-- EDITOR INTERATIVO DO REVISOR (Para Word / Text) -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'word'">
                    <div class="bg-slate-800 border border-slate-700 rounded-[5px] p-6 shadow-sm space-y-4 min-h-[550px]">
                        <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                            <h3 class="font-outfit font-black text-xs uppercase tracking-wider text-slate-400">Editor de Texto Directo (Track Changes)</h3>
                            <button type="button" @click="checkLanguageTool()" class="text-xs font-bold text-purple-400 hover:text-purple-300 flex items-center gap-1">
                                🔍 LanguageTool Spellcheck
                            </button>
                        </div>

                        <!-- Modo 1: Edição Direta com Controle de Alterações -->
                        <div x-show="viewMode === 'track'" class="space-y-3">
                            <textarea x-model="revisedContent" rows="16" class="w-full bg-slate-900 text-slate-100 font-serif text-sm p-5 border border-slate-700 rounded-[5px] leading-relaxed focus:outline-none focus:border-primary-500 select-text" @mouseup="captureSelectedText"></textarea>
                            <p class="text-[11px] text-slate-400 italic">💡 Altere o texto diretamente na caixa acima. As palavras adicionadas ou corrigidas serão destacadas no projeto.</p>
                        </div>

                        <!-- Modo 2: Visualização de Versão Antiga (Original) -->
                        <div x-show="viewMode === 'original'" class="p-5 bg-rose-950/40 border border-rose-900/60 rounded-[5px] text-xs font-serif leading-relaxed text-rose-200 max-h-[500px] overflow-y-auto whitespace-pre-wrap">
                            <span x-text="originalContent"></span>
                        </div>

                        <!-- Modo 3: Visualização da Versão Final Atualizada -->
                        <div x-show="viewMode === 'final'" class="p-5 bg-emerald-950/40 border border-emerald-900/60 rounded-[5px] text-xs font-serif leading-relaxed text-emerald-200 max-h-[500px] overflow-y-auto whitespace-pre-wrap">
                            <span x-text="revisedContent"></span>
                        </div>
                    </div>
                </template>

                <!-- MODO GOOGLE DOCS VIEWER -->
                <template x-if="viewerMode === 'google' && currentFile">
                    <div class="bg-slate-800 border border-slate-700 rounded-[5px] p-2 shadow-sm min-h-[650px] flex flex-col space-y-2">
                        <div class="flex items-center justify-between px-3 py-1.5 text-xs font-bold text-slate-400 border-b border-slate-700">
                            <span>🌐 Google Docs Viewer Online</span>
                            <a :href="getGoogleDocsViewerUrl(currentFile.id)" target="_blank" class="text-primary-400 hover:underline">Abrir em nova guia ↗</a>
                        </div>
                        <iframe :src="getGoogleDocsViewerUrl(currentFile.id)" class="w-full h-[600px] rounded border border-slate-700 bg-white" frameborder="0"></iframe>
                    </div>
                </template>

                <!-- RENDERIZADOR DE PDF -->
                <template x-if="viewerMode === 'native' && currentFile && currentFile.file_type === 'pdf'">
                    <div class="bg-slate-800 border border-slate-700 rounded-[5px] p-4 shadow-sm flex flex-col items-center justify-center min-h-[600px] overflow-auto relative">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-slate-900/80 flex items-center justify-center font-bold text-xs text-slate-400 z-10">
                            Renderizando página do PDF...
                        </div>
                        <canvas id="pdf-canvas" class="max-w-full shadow-md rounded border border-slate-700"></canvas>
                    </div>
                </template>

                <!-- Sugestões da Verificação Ortográfica -->
                <div x-show="languageToolMatches.length > 0" x-cloak class="bg-purple-950/60 border border-purple-800 rounded-[5px] p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-outfit font-black text-xs uppercase tracking-wider text-purple-300 flex items-center gap-2">
                            <span>🔍</span> Sugestões Ortográficas do LanguageTool (<span x-text="languageToolMatches.length"></span>)
                        </h4>
                        <button type="button" @click="languageToolMatches = []" class="text-purple-400 hover:text-purple-200 font-bold text-xs">Fechar</button>
                    </div>

                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <template x-for="(match, index) in languageToolMatches" :key="index">
                            <div class="p-3 bg-slate-900 rounded-[5px] border border-purple-800 text-xs space-y-1">
                                <p class="font-bold text-purple-200" x-text="match.message"></p>
                                <p class="text-[11px] text-slate-400">
                                    Sugestões: <strong class="text-emerald-400" x-text="match.replacements ? match.replacements.slice(0, 3).map(r => r.value).join(', ') : ''"></strong>
                                </p>
                                <button type="button" @click="applyLanguageToolMatch(match)" class="mt-1 px-2.5 py-1 bg-purple-600 text-white font-bold text-[10px] rounded-[5px] uppercase tracking-wider">
                                    + Criar Apontamento desta Sugestão
                                </button>
                            </div>
                        </template>
                    </div>
                </div>

            </div>

            <!-- Coluna Direita: Painel de Apontamentos por Categoria (5 Colunas) -->
            <div class="lg:col-span-5 space-y-4">
                
                <div class="bg-slate-800 border border-slate-700 rounded-[5px] p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                        <h3 class="font-outfit font-black text-white text-sm uppercase tracking-tight">Apontamentos Cadastrados</h3>
                        <span class="px-2.5 py-0.5 bg-slate-900 text-slate-300 text-[10px] font-bold rounded-full border border-slate-700">
                            {{ $revision->corrections->count() }} Registros
                        </span>
                    </div>

                    <!-- Filtro por Categoria -->
                    <div class="flex items-center gap-1.5 flex-wrap text-xs">
                        <button type="button" @click="categoryFilter = 'todas'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'todas' ? 'bg-white text-slate-900' : 'bg-slate-900 text-slate-400'">Todas</button>
                        <button type="button" @click="categoryFilter = 'ortografia'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'ortografia' ? 'bg-rose-600 text-white' : 'bg-rose-950/60 text-rose-300'">Ortografia</button>
                        <button type="button" @click="categoryFilter = 'gramatica'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'gramatica' ? 'bg-amber-600 text-white' : 'bg-amber-950/60 text-amber-300'">Gramática</button>
                        <button type="button" @click="categoryFilter = 'duvida'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'duvida' ? 'bg-blue-600 text-white' : 'bg-blue-950/60 text-blue-300'">Dúvidas</button>
                    </div>

                    <!-- Lista de Correções -->
                    <div class="space-y-3 max-h-[650px] overflow-y-auto">
                        @forelse($revision->corrections as $cor)
                            @php
                                $badgeClass = match($cor->category) {
                                    'ortografia' => 'bg-rose-900/60 text-rose-200 border border-rose-700',
                                    'gramatica' => 'bg-amber-900/60 text-amber-200 border border-amber-700',
                                    'duvida' => 'bg-blue-900/60 text-blue-200 border border-blue-700',
                                    'padronizacao' => 'bg-purple-900/60 text-purple-200 border border-purple-700',
                                    default => 'bg-slate-700 text-slate-200',
                                };
                            @endphp
                            <div x-show="categoryFilter === 'todas' || categoryFilter === '{{ $cor->category }}'" class="p-3.5 bg-slate-900 border border-slate-700 rounded-[5px] text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[5px] {{ $badgeClass }}">
                                        {{ ucfirst($cor->category) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold">Pág. {{ $cor->page_number ?: 1 }} • {{ ucfirst($cor->status) }}</span>
                                </div>

                                @if($cor->original_text)
                                    <p class="font-mono text-slate-400 line-through">"{{ $cor->original_text }}"</p>
                                @endif

                                @if($cor->suggested_text)
                                    <p class="font-mono text-emerald-400 font-bold">➔ {{ $cor->suggested_text }}</p>
                                @endif

                                @if($cor->justification)
                                    <p class="text-slate-400 italic">💡 {{ $cor->justification }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-slate-500 py-8 font-semibold text-xs border border-dashed border-slate-700 rounded-[5px]">
                                Nenhum apontamento cadastrado ainda.
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Modal Criar Apontamento -->
    <div x-show="openCorrectionModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs select-none">
        <div @click.away="openCorrectionModal = false" class="bg-slate-800 border border-slate-700 text-slate-100 rounded-xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="font-outfit font-black text-white text-md uppercase">➕ Novo Apontamento de Revisão</h3>
                <button type="button" @click="openCorrectionModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>

            <form action="{{ route('public.editorial.revisor.corrections.store', $revision->share_token) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="editorial_revision_file_id" :value="selectedFileId">
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold block mb-1">Categoria</label>
                        <select name="category" x-model="modalCategory" required class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white">
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
                        <input type="number" name="page_number" x-model="modalPageNumber" placeholder="Ex: 5" class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white">
                    </div>
                </div>

                <div>
                    <label class="font-bold block mb-1">Texto Original do Autor</label>
                    <textarea name="original_text" x-model="modalOriginalText" rows="2" placeholder="Trecho extraído do texto..." class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Sugestão de Correção</label>
                    <textarea name="suggested_text" x-model="modalSuggestedText" rows="2" placeholder="Digite a correção sugerida..." class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Justificativa / Comentário</label>
                    <input type="text" name="justification" x-model="modalJustification" placeholder="Explicação da regra ou orientação..." class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-700">
                    <button type="button" @click="openCorrectionModal = false" class="px-4 py-2 bg-slate-700 text-slate-200 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-[5px]">Salvar Apontamento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Upload de Nova Versão -->
    <div x-show="openUploadVersionModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-xs select-none">
        <div @click.away="openUploadVersionModal = false" class="bg-slate-800 border border-slate-700 text-slate-100 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-700 pb-3">
                <h3 class="font-outfit font-black text-white text-md uppercase">📤 Salvar Nova Versão Revisada</h3>
                <button type="button" @click="openUploadVersionModal = false" class="text-slate-400 hover:text-white font-bold">✕</button>
            </div>

            <form action="{{ route('public.editorial.revisor.version.store', $revision->share_token) }}" method="POST" enctype="multipart/form-data" class="space-y-3 text-xs">
                @csrf
                <input type="hidden" name="parent_file_id" :value="selectedFileId">

                <div>
                    <label class="font-bold block mb-1">Arquivo Revisado (Word / PDF / Imagem)</label>
                    <input type="file" name="file" required class="w-full px-3 py-2 border border-slate-700 rounded-[5px] bg-slate-900 text-white">
                    <p class="text-[10px] text-slate-400 mt-1">Este arquivo criará uma nova versão no histórico (ex: Versão 2).</p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-700">
                    <button type="button" @click="openUploadVersionModal = false" class="px-4 py-2 bg-slate-700 text-slate-200 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Salvar Nova Versão</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
