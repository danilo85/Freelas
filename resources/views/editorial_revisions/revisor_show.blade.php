<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Workspace do Revisor - {{ $revision->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- PDF.js CDN para Renderização Interativa de PDFs -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
    </script>

    <style>
        [x-cloak] { display: none !important; }
    </style>

    <script>
        function revisorWorkspace() {
            const filesData = @json($revision->files);
            const textsData = @json($extractedTexts);

            return {
                openCorrectionModal: false,
                openUploadVersionModal: false,
                categoryFilter: 'todas',
                toastMessage: '',
                selectedFileId: filesData.length > 0 ? filesData[0].id : null,
                languageToolMatches: [],
                
                // Login State
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
                    this.$watch('selectedFileId', (id) => {
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

                get extractedText() {
                    return textsData[this.selectedFileId] || 'Conteúdo disponível para download em formato original.';
                },

                handleFileChange(id) {
                    const file = this.currentFile;
                    if (file && file.file_type === 'pdf') {
                        this.loadPdfDocument();
                    }
                },

                loadPdfDocument() {
                    if (!this.currentFile) return;
                    const url = '{{ asset("storage") }}/' + this.currentFile.file_path;
                    this.renderingPdf = true;

                    pdfjsLib.getDocument(url).promise.then(pdf => {
                        this.pdfDoc = pdf;
                        this.totalPages = pdf.numPages;
                        this.currentPage = 1;
                        this.renderPdfPage(1);
                    }).catch(err => {
                        this.renderingPdf = false;
                        this.showToast('Falha ao carregar o PDF para visualização interativa.');
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
                        this.loginError = 'Ocorreu um erro no servidor ao validar o acesso.';
                    });
                },

                captureSelectedText() {
                    const sel = window.getSelection().toString().trim();
                    if (sel) {
                        this.modalOriginalText = sel;
                    }
                },

                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                copyAuthorLink(url) {
                    navigator.clipboard.writeText(url);
                    this.showToast('Link do Autor copiado com sucesso!');
                },

                checkLanguageTool() {
                    const text = this.extractedText;
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
                    const orig = this.extractedText.substring(match.offset, match.offset + match.length);
                    const replacement = (match.replacements && match.replacements.length > 0) ? match.replacements[0].value : '';
                    
                    this.modalCategory = 'ortografia';
                    this.modalOriginalText = orig;
                    this.modalSuggestedText = replacement;
                    this.modalJustification = 'Sugestão automática do LanguageTool: ' + match.message;
                    this.openCorrectionModal = true;
                }
            }
        }
    </script>
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-full flex flex-col justify-between" x-data="revisorWorkspace()">

    <!-- Modal de Autenticação / Login do Revisor (Se não estiver logado) -->
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
                    <input type="email" x-model="loginEmail" required placeholder="revisora@exemplo.com" class="w-full px-4 py-3 border rounded-[5px] bg-slate-50 font-medium text-sm">
                </div>

                <div>
                    <label class="font-bold block mb-1 uppercase tracking-wider text-slate-500">Senha de Acesso</label>
                    <input type="password" x-model="loginPassword" required placeholder="••••••••" class="w-full px-4 py-3 border rounded-[5px] bg-slate-50 font-medium text-sm">
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
         class="fixed bottom-24 right-6 z-[99999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- Botão Flutuante de Adicionar Apontamento e LanguageTool -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3" x-show="isAuth">
        <button type="button" 
                @click="checkLanguageTool()" 
                class="bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs uppercase tracking-wider px-4 py-3 rounded-full shadow-lg hover:shadow-xl transition-all flex items-center gap-2 cursor-pointer transform hover:scale-105 border-2 border-white">
            <span class="text-base">🔍</span>
            <span>Verificar Ortografia (LanguageTool)</span>
        </button>

        <button type="button" 
                @click="openCorrectionModal = true" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider px-5 py-3.5 rounded-full shadow-xl hover:shadow-2xl transition-all flex items-center gap-2.5 cursor-pointer transform hover:scale-105 border-2 border-white">
            <span class="text-lg leading-none">➕</span>
            <span>Novo Apontamento</span>
        </button>
    </div>

    <!-- Topo / Cabeçalho do Workspace -->
    <header class="bg-white border-b border-slate-200 shadow-xs py-4 px-6 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✍️</span>
                <div>
                    <h1 class="text-base font-black font-outfit uppercase tracking-tight text-slate-900">Workspace de Revisão Editorial</h1>
                    <p class="text-[11px] text-slate-400 font-medium">{{ $revision->title }}</p>
                </div>
            </div>

            <div class="flex items-center gap-3 flex-wrap">
                <button type="button" 
                        @click="openUploadVersionModal = true" 
                        class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-[5px] transition-colors border border-slate-200 flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>📤</span> Salvar Nova Versão
                </button>

                <button type="button" 
                        @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>🔗</span> Link do Autor
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

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-[5px] text-xs font-bold shadow-xs">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Grid Principal: Leitor e Renderizador (Esquerda) vs Apontamentos (Direita) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Coluna Esquerda: Leitor (PDF.js / Extração Word / Imagem) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Barra do Leitor: Seletor de Arquivos e Controles de Zoom/Página -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Arquivo em Análise</span>
                            <select x-model="selectedFileId" class="px-3 py-1.5 border border-slate-200 rounded-[5px] text-xs font-bold bg-slate-50 text-slate-800 focus:outline-none">
                                @foreach($revision->files as $file)
                                    <option value="{{ $file->id }}">{{ $file->filename }} ({{ strtoupper($file->file_type) }} - v{{ $file->version }})</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Botão de Download do Arquivo Original -->
                        <template x-if="currentFile">
                            <a :href="'{{ asset('storage') }}/' + currentFile.file_path" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[5px] transition-colors flex items-center gap-1.5 border border-slate-200 shrink-0">
                                <span>⬇️ Baixar Original</span>
                            </a>
                        </template>
                    </div>

                    <!-- Controles Específicos para PDF (Navegação por Página & Zoom) -->
                    <template x-if="currentFile && currentFile.file_type === 'pdf'">
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs font-bold text-slate-600">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="prevPage()" class="px-3 py-1 bg-slate-100 rounded-[5px] hover:bg-slate-200">◀ Anterior</button>
                                <span>Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span></span>
                                <button type="button" @click="nextPage()" class="px-3 py-1 bg-slate-100 rounded-[5px] hover:bg-slate-200">Próxima ▶</button>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="zoomPdf(-0.2)" class="px-2.5 py-1 bg-slate-100 rounded-[5px] hover:bg-slate-200">🔍-</button>
                                <button type="button" @click="zoomPdf(0.2)" class="px-2.5 py-1 bg-slate-100 rounded-[5px] hover:bg-slate-200">🔍+</button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Canvas de Renderização para PDFs -->
                <template x-if="currentFile && currentFile.file_type === 'pdf'">
                    <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col items-center justify-center min-h-[600px] overflow-auto relative">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-500 z-10">
                            Renderizando página do PDF...
                        </div>
                        <canvas id="pdf-canvas" class="max-w-full shadow-md rounded border border-slate-200"></canvas>
                    </div>
                </template>

                <!-- Leitor de Texto formatado para Documentos Word (.docx) -->
                <template x-if="currentFile && currentFile.file_type === 'word'">
                    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4 min-h-[500px]">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-outfit font-black text-xs uppercase tracking-wider text-slate-400">Texto Extraído do Documento Word</h3>
                            <span class="text-[10px] text-slate-400 italic">Selecione trechos com o mouse para capturar automaticamente</span>
                        </div>

                        <div class="prose max-w-none text-xs leading-relaxed font-serif text-slate-800 bg-slate-50/50 p-6 rounded-[5px] border border-slate-150 max-h-[600px] overflow-y-auto whitespace-pre-wrap select-text"
                             @mouseup="captureSelectedText">
                            <span x-text="extractedText"></span>
                        </div>
                    </div>
                </template>

                <!-- Visualizador de Imagens para Scans e Fotografias -->
                <template x-if="currentFile && currentFile.file_type === 'image'">
                    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm flex items-center justify-center min-h-[500px]">
                        <img :src="'{{ asset('storage') }}/' + currentFile.file_path" class="max-h-[600px] object-contain rounded shadow-sm">
                    </div>
                </template>

                <!-- Painel de Sugestões Automáticas do LanguageTool -->
                <div x-show="languageToolMatches.length > 0" x-cloak class="bg-purple-50/60 border border-purple-200 rounded-[5px] p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-outfit font-black text-xs uppercase tracking-wider text-purple-800 flex items-center gap-2">
                            <span>🔍</span> Sugestões Ortográficas do LanguageTool (<span x-text="languageToolMatches.length"></span>)
                        </h4>
                        <button type="button" @click="languageToolMatches = []" class="text-purple-400 hover:text-purple-700 font-bold text-xs">Fechar</button>
                    </div>

                    <div class="space-y-2 max-h-60 overflow-y-auto">
                        <template x-for="(match, index) in languageToolMatches" :key="index">
                            <div class="p-3 bg-white rounded-[5px] border border-purple-100 text-xs space-y-1">
                                <p class="font-bold text-purple-950" x-text="match.message"></p>
                                <p class="text-[11px] text-slate-500">
                                    Sugestões: <strong class="text-emerald-700" x-text="match.replacements ? match.replacements.slice(0, 3).map(r => r.value).join(', ') : ''"></strong>
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
                
                <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-outfit font-black text-slate-900 text-sm uppercase tracking-tight">Apontamentos Cadastrados</h3>
                        <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-[10px] font-bold rounded-full">
                            {{ $revision->corrections->count() }} Registros
                        </span>
                    </div>

                    <!-- Filtro por Categoria -->
                    <div class="flex items-center gap-1.5 flex-wrap text-xs">
                        <button type="button" @click="categoryFilter = 'todas'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'todas' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-600'">Todas</button>
                        <button type="button" @click="categoryFilter = 'ortografia'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'ortografia' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700'">Ortografia</button>
                        <button type="button" @click="categoryFilter = 'gramatica'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'gramatica' ? 'bg-amber-600 text-white' : 'bg-amber-50 text-amber-700'">Gramática</button>
                        <button type="button" @click="categoryFilter = 'duvida'" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase" :class="categoryFilter === 'duvida' ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700'">Dúvidas</button>
                    </div>

                    <!-- Lista de Correções -->
                    <div class="space-y-3 max-h-[650px] overflow-y-auto">
                        @forelse($revision->corrections as $cor)
                            @php
                                $badgeClass = match($cor->category) {
                                    'ortografia' => 'bg-rose-100 text-rose-800',
                                    'gramatica' => 'bg-amber-100 text-amber-800',
                                    'duvida' => 'bg-blue-100 text-blue-800',
                                    'padronizacao' => 'bg-purple-100 text-purple-800',
                                    default => 'bg-slate-100 text-slate-800',
                                };
                            @endphp
                            <div x-show="categoryFilter === 'todas' || categoryFilter === '{{ $cor->category }}'" class="p-3.5 bg-slate-50 border border-slate-200 rounded-[5px] text-xs space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[5px] {{ $badgeClass }}">
                                        {{ ucfirst($cor->category) }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-bold">Pág. {{ $cor->page_number ?: 1 }} • {{ ucfirst($cor->status) }}</span>
                                </div>

                                @if($cor->original_text)
                                    <p class="font-mono text-slate-700 line-through">"{{ $cor->original_text }}"</p>
                                @endif

                                @if($cor->suggested_text)
                                    <p class="font-mono text-emerald-800 font-bold">➔ {{ $cor->suggested_text }}</p>
                                @endif

                                @if($cor->justification)
                                    <p class="text-slate-500 italic">💡 {{ $cor->justification }}</p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center text-slate-400 py-8 font-semibold text-xs border border-dashed border-slate-200 rounded-[5px]">
                                Nenhum apontamento cadastrado ainda.
                            </div>
                        @endforelse
                    </div>

                </div>

            </div>

        </div>

    </main>

    <!-- Modal Criar Apontamento no Portal do Revisor -->
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
                        <select name="category" x-model="modalCategory" required class="w-full px-3 py-2 border rounded-[5px]">
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
                        <input type="number" name="page_number" x-model="modalPageNumber" placeholder="Ex: 5" class="w-full px-3 py-2 border rounded-[5px]">
                    </div>
                </div>

                <div>
                    <label class="font-bold block mb-1">Texto Original do Autor</label>
                    <textarea name="original_text" x-model="modalOriginalText" rows="2" placeholder="Trecho extraído do texto..." class="w-full px-3 py-2 border rounded-[5px]"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Sugestão de Correção</label>
                    <textarea name="suggested_text" x-model="modalSuggestedText" rows="2" placeholder="Digite a correção sugerida..." class="w-full px-3 py-2 border rounded-[5px]"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Justificativa / Comentário</label>
                    <input type="text" name="justification" x-model="modalJustification" placeholder="Explicação da regra ou orientação..." class="w-full px-3 py-2 border rounded-[5px]">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openCorrectionModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-bold rounded-[5px]">Salvar Apontamento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Upload de Nova Versão Revisada (Histórico de Versões) -->
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
                    <input type="file" name="file" required class="w-full px-3 py-2 border rounded-[5px]">
                    <p class="text-[10px] text-slate-400 mt-1">Este arquivo criará uma nova versão no histórico (ex: Versão 2).</p>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="openUploadVersionModal = false" class="px-4 py-2 bg-slate-100 text-slate-600 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Salvar Nova Versão</button>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
