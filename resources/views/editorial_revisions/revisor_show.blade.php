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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&family=Lora:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                        serif: ['Lora', 'Georgia', 'serif'],
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

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.min.js"></script>
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.16.105/pdf.worker.min.js';
        window._activePdfDoc = null;
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        .paper-shadow {
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.12), 0 0 5px rgba(0, 0, 0, 0.05);
        }
        /* FOLHA CORRIDA DO WORD COM RISCO SUAVE DE DEMARCAÇÃO */
        .word-page-a4 {
            width: 210mm;
            min-height: 297mm;
            padding: 25mm 20mm;
            background-color: #ffffff;
            background-image: linear-gradient(to bottom, transparent 296mm, #e2e8f0 296mm, #ffffff 297mm);
            background-size: 100% 297mm;
            border: 1px solid #cbd5e1;
            margin: 20px auto;
            box-sizing: border-box;
            border-radius: 2px;
        }
        .word-paper-content img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 1.5rem auto;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        /* MARCAÇÃO AMARELA AUTOMÁTICA NAS LINHAS ALTERADAS */
        .word-paper-content .edited-line {
            background-color: #fef08a !important;
            color: #713f12 !important;
            padding: 2px 6px;
            border-radius: 4px;
            border-left: 4px solid #facc15;
            transition: all 0.2s ease;
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
                openUploadVersionModal: false,
                categoryFilter: 'todas',
                viewMode: 'track',
                viewerMode: 'iframe', // 'iframe' como padrão para PDFs (renderiza 100% de layout, cores e orientação)
                toastMessage: '',
                selectedFileId: filesData.length > 0 ? filesData[0].id : null,
                correctionsList: @json($revision->corrections),
                
                // Track Changes state
                originalContent: '',
                revisedContent: '',
                savingText: false,
                typingTimer: null,

                // Auth State
                isAuth: @json($isAuthenticated),
                loginEmail: '',
                loginPassword: '',
                loginError: '',

                // PDF.js State
                currentPage: 1,
                totalPages: 1,
                pdfScale: 1.2,
                renderingPdf: false,

                init() {
                    this.loadContentForSelectedFile();

                    this.$watch('selectedFileId', (id) => {
                        this.loadContentForSelectedFile();
                        this.handleFileChange(id);
                    });
                },

                get currentFile() {
                    return filesData.find(f => f.id == this.selectedFileId) || null;
                },

                loadContentForSelectedFile() {
                    if (!this.selectedFileId) return;
                    const text = textsData[this.selectedFileId] || textsData[String(this.selectedFileId)] || '';
                    this.originalContent = text;
                    this.revisedContent = text;

                    this.$nextTick(() => {
                        const editor = this.$refs.wordEditor;
                        if (editor) {
                            editor.innerHTML = text;
                        }
                    });
                },

                getFileStreamUrl(id) {
                    return streamBaseUrl + '/' + id + '/stream';
                },

                getFileDownloadUrl(id) {
                    return streamBaseUrl + '/' + id + '/download';
                },

                handleFileChange(id) {
                    const file = this.currentFile;
                    if (file && file.file_type === 'pdf' && this.viewerMode === 'native') {
                        this.loadPdfDocument();
                    }
                },

                // PDF.JS SEM ERROS DE PROXY (UTILIZA INSTÂNCIA BRUTA WINDOW._ACTIVEPDFDOC)
                loadPdfDocument() {
                    if (!this.currentFile) return;
                    const url = this.getFileStreamUrl(this.currentFile.id);
                    this.renderingPdf = true;

                    fetch(url)
                        .then(res => res.arrayBuffer())
                        .then(buffer => pdfjsLib.getDocument({ data: buffer }).promise)
                        .then(pdf => {
                            window._activePdfDoc = pdf;
                            this.totalPages = pdf.numPages;
                            this.currentPage = 1;
                            this.renderPdfPage(1);
                        })
                        .catch(() => {
                            this.renderingPdf = false;
                        });
                },

                renderPdfPage(num) {
                    if (!window._activePdfDoc) return;
                    this.renderingPdf = true;

                    window._activePdfDoc.getPage(num).then(page => {
                        const canvas = document.getElementById('pdf-canvas');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const viewport = page.getViewport({ scale: this.pdfScale });

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                            this.renderingPdf = false;
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

                execCmd(command, value = null) {
                    document.execCommand(command, false, value);
                    this.syncEditorContent();
                },

                syncEditorContent() {
                    const editor = this.$refs.wordEditor;
                    if (editor) {
                        this.revisedContent = editor.innerHTML;
                    }
                },

                // DIGITAÇÃO NATIVA NO WORD (SEM PULAR O CURSOR E SEM RELOAD)
                handleEditorInput(event) {
                    this.syncEditorContent();

                    const sel = window.getSelection();
                    if (sel && sel.anchorNode) {
                        let node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                        
                        while (node && node !== this.$refs.wordEditor && !['P', 'DIV', 'LI', 'H1', 'H2', 'H3'].includes(node.nodeName)) {
                            node = node.parentNode;
                        }

                        if (node && node !== this.$refs.wordEditor) {
                            node.classList.add('edited-line');
                        }
                    }

                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(() => {
                        this.autoSaveAndRegisterCorrection();
                    }, 1500);
                },

                // SALVAMENTO SILÊNCIOSO SEM NENHUM RELOAD NA PÁGINA
                autoSaveAndRegisterCorrection() {
                    const editor = this.$refs.wordEditor;
                    const contentToSave = editor ? editor.innerHTML : this.revisedContent;

                    fetch('{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + this.selectedFileId + '/content', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ revised_content: contentToSave })
                    });

                    fetch('{{ route("public.editorial.revisor.corrections.store", $revision->share_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            editorial_revision_file_id: this.selectedFileId,
                            category: 'ortografia',
                            original_text: 'Edição no documento Word',
                            suggested_text: 'Texto atualizado pelo revisor',
                            justification: 'Alteração salva no documento.'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.showToast('Documento Word atualizado!');
                        if (data.correction) {
                            this.correctionsList.unshift(data.correction);
                        }
                    })
                    .catch(() => {});
                },

                saveEditedText() {
                    this.autoSaveAndRegisterCorrection();
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

                showToast(msg) {
                    this.toastMessage = msg;
                    setTimeout(() => { this.toastMessage = ''; }, 3000);
                },

                copyAuthorLink(url) {
                    navigator.clipboard.writeText(url);
                    this.showToast('Link do Autor copiado com sucesso!');
                },

                checkLanguageTool() {
                    const text = this.revisedContent || this.originalContent;
                    if (!text || text.length < 5) {
                        this.showToast('Não há texto suficiente para análise ortográfica.');
                        return;
                    }

                    this.showToast('Analisando documento com LanguageTool...');

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

    <!-- HEADER SUPERIOR DE REVISÃO EDITORIAL -->
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

            <button type="button" @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-4 py-2 rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm">
                <span>🔗</span> Link do Autor
            </button>
        </div>
    </header>

    <!-- CORPO PRINCIPAL DE 3 COLUNAS COM ALTURA FLUIDA 100% -->
    <main class="flex-1 flex overflow-hidden min-h-0">

        <!-- COLUNA 1 (ESQUERDA - 320px): LISTA DE APONTAMENTOS AUTOMÁTICOS -->
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

            <!-- Feed de Apontamentos Automáticos em Tempo Real -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="cor in correctionsList" :key="cor.id">
                    <div x-show="categoryFilter === 'todas' || categoryFilter === cor.category" class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-[5px] text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[3px] bg-amber-100 text-amber-900 border border-amber-300" x-text="cor.category"></span>
                            <span class="text-[10px] text-slate-400 font-bold">Apontamento Marcado</span>
                        </div>

                        <p class="font-mono text-amber-950 font-bold bg-amber-100/90 px-1.5 py-0.5 rounded" x-text="cor.original_text || 'Edição direta'"></p>
                        <p class="text-slate-600 italic text-[11px]" x-text="'💡 ' + (cor.justification || 'Edição no documento.')"></p>
                    </div>
                </template>

                <template x-if="correctionsList.length === 0">
                    <div class="text-center text-slate-400 py-12 font-semibold text-xs border border-dashed border-slate-200 rounded-[5px]">
                        Nenhum apontamento marcado. Altere o texto no Word para destacar em amarelo e salvar automaticamente!
                    </div>
                </template>
            </div>

        </aside>

        <!-- COLUNA 2 (CENTRO - FLEX-1): VIEWPORT DO DOCUMENTO -->
        <section class="flex-1 bg-slate-200/70 flex flex-col min-w-0 relative overflow-hidden h-full">
            
            <!-- Barra Secundária Superior do Visualizador / Editor de Texto -->
            <div class="h-12 border-b border-slate-200 bg-white px-4 flex items-center justify-between shrink-0 z-10 shadow-xs">
                
                <!-- Ferramentas para PDF -->
                <template x-if="currentFile && currentFile.file_type === 'pdf'">
                    <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="viewerMode = 'iframe'" class="px-3 py-1 rounded-[3px]" :class="viewerMode === 'iframe' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                🖥️ Leitor Nativo Navegador (Preserva 100% de Layout e Cores)
                            </button>
                            <button type="button" @click="viewerMode = 'native'; loadPdfDocument()" class="px-3 py-1 rounded-[3px]" :class="viewerMode === 'native' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                📄 Plugin PDF.js Canvas
                            </button>
                        </div>

                        <template x-if="viewerMode === 'native'">
                            <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                                <button type="button" @click="prevPage()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">◀</button>
                                <span>PÁG. <span x-text="currentPage"></span> DE <span x-text="totalPages"></span></span>
                                <button type="button" @click="nextPage()" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">▶</button>
                                <button type="button" @click="zoomPdf(-0.1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px] ml-2">🔍-</button>
                                <button type="button" @click="zoomPdf(0.1)" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 rounded-[3px]">🔍+</button>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- BARRA DE FERRAMENTAS WYSIWYG PARA DOCUMENTOS WORD -->
                <template x-if="currentFile && currentFile.file_type === 'word'">
                    <div class="flex items-center gap-2 text-xs font-bold w-full justify-between">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="execCmd('bold')" class="px-2.5 py-1 hover:bg-white rounded font-black text-slate-800" title="Negrito">B</button>
                            <button type="button" @click="execCmd('italic')" class="px-2.5 py-1 hover:bg-white rounded italic text-slate-800" title="Itálico">I</button>
                            <button type="button" @click="execCmd('underline')" class="px-2.5 py-1 hover:bg-white rounded underline text-slate-800" title="Sublinhado">U</button>
                            <span class="h-4 w-px bg-slate-300 mx-1"></span>
                            <button type="button" @click="execCmd('justifyLeft')" class="px-2 py-1 hover:bg-white rounded text-slate-700" title="Esquerda">⬅️</button>
                            <button type="button" @click="execCmd('justifyCenter')" class="px-2 py-1 hover:bg-white rounded text-slate-700" title="Centro">⏹️</button>
                            <button type="button" @click="execCmd('justifyRight')" class="px-2 py-1 hover:bg-white rounded text-slate-700" title="Direita">➡️</button>
                            <button type="button" @click="execCmd('justifyFull')" class="px-2 py-1 hover:bg-white rounded text-slate-700" title="Justificado">↔️</button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="saveEditedText()" class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-[5px] uppercase tracking-wider transition-all flex items-center gap-1 shadow-sm">
                                <span>💾</span> <span x-text="savingText ? 'Salvando...' : 'Salvar Documento'">Salvar</span>
                            </button>
                        </div>
                    </div>
                </template>

            </div>

            <!-- CANVAS PRINCIPAL (Document Visualizer / Folha A4 do Word) -->
            <div class="flex-1 overflow-auto flex items-start justify-center p-8 relative bg-slate-200/60">
                
                <!-- PDF EM IFRAME NATIVO DO NAVEGADOR -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'iframe'">
                    <div class="w-full max-w-6xl h-full bg-white paper-shadow rounded-[5px] border border-slate-200 p-2 flex flex-col my-auto">
                        <iframe :src="getFileStreamUrl(currentFile.id)" class="w-full h-full rounded border-0" frameborder="0"></iframe>
                    </div>
                </template>

                <!-- CANVAS PDF.JS PLUGIN (SEM ERROS DE PROXY DE CLASSE) -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'native'">
                    <div class="bg-white paper-shadow rounded border border-slate-200 p-4 relative max-w-4xl max-h-full overflow-auto flex flex-col items-center my-auto">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-500 z-10">
                            Renderizando PDF via Plugin PDF.js...
                        </div>
                        <canvas id="pdf-canvas" class="max-w-full block mx-auto shadow-sm border border-slate-200"></canvas>
                    </div>
                </template>

                <!-- FORMATO DE PÁGINA CORRIDA DO WORD COM DIGITAÇÃO NATIVA SEM PULAR O CURSOR -->
                <template x-if="currentFile && currentFile.file_type === 'word'">
                    <div class="w-full flex flex-col items-center">
                        
                        <div class="word-page-a4 paper-shadow border border-slate-300 text-slate-900 rounded-[2px] transition-all select-text relative"
                             id="word-paper-container">

                            <!-- Conteúdo Editável do Word com Injeção Segura e $refs -->
                            <div x-ref="wordEditor"
                                 contenteditable="true"
                                 class="word-paper-content focus:outline-none min-h-[250mm]"
                                 @input="handleEditorInput($event)">
                            </div>

                        </div>
                    </div>
                </template>

                <!-- VISUALIZADOR DE IMAGENS -->
                <template x-if="currentFile && currentFile.file_type === 'image'">
                    <div class="bg-white paper-shadow rounded p-2 border border-slate-200 max-w-4xl my-auto">
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
            <div class="flex-1 overflow-y-auto p-3 space-y-2.5">
                @foreach($revision->files as $file)
                    <div @click="selectedFileId = {{ $file->id }}" 
                         class="p-3 rounded-[5px] border transition-all cursor-pointer select-none space-y-2"
                         :class="selectedFileId == {{ $file->id }} ? 'bg-blue-50/90 border-blue-500 shadow-xs' : 'bg-slate-50/60 border-slate-200 hover:bg-slate-100'">
                        
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded-[3px]"
                                  :class="selectedFileId == {{ $file->id }} ? 'bg-blue-600 text-white' : 'bg-slate-200 text-slate-600'">
                                {{ strtoupper($file->file_type) }}
                            </span>
                            <span class="text-[10px] text-slate-400 font-bold">v{{ $file->version }}</span>
                        </div>

                        <h5 class="text-xs font-bold text-slate-800 truncate" title="{{ $file->filename }}">{{ $file->filename }}</h5>
                        
                        <div class="flex items-center justify-between pt-1 border-t border-slate-100 text-[10px] font-bold text-slate-500">
                            <span>{{ number_format($file->file_size / 1024, 1) }} KB</span>
                            <a :href="getFileDownloadUrl({{ $file->id }})" target="_blank" @click.stop class="text-blue-600 hover:underline">
                                ⬇️ Baixar
                            </a>
                        </div>
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
