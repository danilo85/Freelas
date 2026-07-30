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
        /* SCROLLBAR DISCRETA E ELEGANTE */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
        /* MARCAÇÃO AMARELA PERMANENTE NAS LINHAS ALTERADAS */
        .word-paper-content .edited-line, mark.edited-line {
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
            const shareToken = '{{ $revision->share_token }}';
            const streamBaseUrl = '{{ url("/revisao-editorial/" . $revision->share_token . "/file") }}';

            const savedFileId = localStorage.getItem('revisor_file_' + shareToken);
            const initialFileId = (savedFileId && filesData.some(f => f.id == savedFileId)) ? savedFileId : (filesData.length > 0 ? filesData[0].id : null);

            return {
                leftSidebarOpen: false,
                rightSidebarOpen: false,
                openUploadVersionModal: false,
                openDuvidasChatModal: false,
                categoryFilter: 'todas',
                viewMode: 'track',
                viewerMode: 'iframe',
                toastMessage: '',
                selectedFileId: initialFileId,
                correctionsList: @json($revision->corrections),
                
                // POPUP DE CATEGORIZAÇÃO AO EDITAR/SELECIONAR
                showCategoryMenu: false,
                categoryMenuPos: { x: 0, y: 0 },
                pendingEditedNode: null,
                pendingSelectedText: '',

                // MENU DE BOTÃO DIREITO DO MOUSE (CONTEXT MENU)
                showContextMenu: false,
                contextMenuPos: { x: 0, y: 0 },

                // State Chat de Dúvidas
                activeDuvidaId: null,
                replyMessageInput: '',

                // Track Changes state
                originalContent: '',
                revisedContent: '',
                loadingWord: false,
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
                        localStorage.setItem('revisor_file_' + shareToken, id);
                        this.loadContentForSelectedFile();
                        this.handleFileChange(id);
                    });

                    // Oculta menus ao clicar fora
                    document.addEventListener('click', (e) => {
                        if (!e.target.closest('#category-popover-menu')) {
                            this.showCategoryMenu = false;
                        }
                        if (!e.target.closest('#custom-context-menu')) {
                            this.showContextMenu = false;
                        }
                    });
                },

                get currentFile() {
                    return filesData.find(f => f.id == this.selectedFileId) || null;
                },

                get duvidasList() {
                    return this.correctionsList.filter(c => c.category === 'duvida');
                },

                loadContentForSelectedFile() {
                    if (!this.selectedFileId) return;

                    const file = this.currentFile;
                    if (file && file.file_type === 'word') {
                        this.loadingWord = true;
                        const url = streamBaseUrl + '/' + this.selectedFileId + '/text-content';

                        fetch(url)
                            .then(res => res.json())
                            .then(data => {
                                const text = data.content || '';
                                this.originalContent = text;
                                this.revisedContent = text;
                                this.loadingWord = false;

                                this.$nextTick(() => {
                                    const editor = this.$refs.wordEditor;
                                    if (editor) {
                                        editor.innerHTML = text;
                                    }

                                    const savedScroll = localStorage.getItem('revisor_scroll_' + shareToken + '_' + this.selectedFileId);
                                    const container = this.$refs.documentViewport;
                                    if (savedScroll && container) {
                                        container.scrollTop = parseInt(savedScroll);
                                    }
                                });
                            })
                            .catch(() => {
                                this.loadingWord = false;
                            });
                    }
                },

                handleViewportScroll(event) {
                    if (!this.selectedFileId) return;
                    localStorage.setItem('revisor_scroll_' + shareToken + '_' + this.selectedFileId, event.target.scrollTop);
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
                    this.persistWordContent();
                },

                syncEditorContent() {
                    const editor = this.$refs.wordEditor;
                    if (editor) {
                        this.revisedContent = editor.innerHTML;
                    }
                },

                // PEQUENO MENU FLUTUANTE AO EDITAR/SELECIONAR
                handleEditorInput(event) {
                    this.syncEditorContent();

                    const sel = window.getSelection();
                    if (sel && sel.anchorNode) {
                        let node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                        
                        while (node && node !== this.$refs.wordEditor && !['P', 'DIV', 'LI', 'H1', 'H2', 'H3', 'MARK'].includes(node.nodeName)) {
                            node = node.parentNode;
                        }

                        if (node && node !== this.$refs.wordEditor) {
                            this.pendingEditedNode = node;
                            this.pendingSelectedText = sel.toString().trim() || node.textContent.trim().substring(0, 60);

                            // Exibe o pequeno menu de categorização próximo ao cursor
                            if (event.clientX && event.clientY) {
                                this.categoryMenuPos = { x: Math.min(event.clientX, window.innerWidth - 300), y: Math.max(event.clientY - 50, 80) };
                            } else {
                                const rect = node.getBoundingClientRect();
                                this.categoryMenuPos = { x: Math.min(rect.left, window.innerWidth - 300), y: Math.max(rect.top - 45, 80) };
                            }
                            this.showCategoryMenu = true;
                        }
                    }

                    // Salva alterações no banco em segundo plano de forma contínua
                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(() => {
                        this.persistWordContent();
                    }, 1000);
                },

                // APLICA A CATEGORIA ESCOLHIDA NO MENU FLUTUANTE, MARCA EM AMARELO E CRIA O APONTAMENTO
                selectCategory(cat) {
                    this.showCategoryMenu = false;

                    if (this.pendingEditedNode) {
                        this.pendingEditedNode.classList.add('edited-line');
                    }

                    this.syncEditorContent();
                    this.persistWordContent();

                    // Cria o apontamento na Coluna 1
                    fetch('{{ route("public.editorial.revisor.corrections.store", $revision->share_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            editorial_revision_file_id: this.selectedFileId,
                            category: cat,
                            original_text: this.pendingSelectedText || 'Edição no documento',
                            suggested_text: 'Texto atualizado',
                            justification: 'Alteração categorizada como ' + cat.toUpperCase()
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.showToast('Marcado em amarelo e adicionado à categoria ' + cat.toUpperCase() + '!');
                        if (data.correction) {
                            data.correction.comments = data.correction.comments || [];
                            this.correctionsList.unshift(data.correction);
                        }
                    });
                },

                // SALVA O CONTEÚDO HTML DO WORD COM TODAS AS MARCAÇÕES AMARELAS PERMANENTES
                persistWordContent() {
                    const editor = this.$refs.wordEditor;
                    if (!editor || !this.selectedFileId) return;

                    const contentToSave = editor.innerHTML;
                    this.revisedContent = contentToSave;
                    this.savingText = true;

                    fetch('{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + this.selectedFileId + '/content', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ revised_content: contentToSave })
                    })
                    .then(() => {
                        this.savingText = false;
                        this.showToast('Documento salvo no banco de dados!');
                    })
                    .catch(() => {
                        this.savingText = false;
                    });
                },

                // MENU DE BOTÃO DIREITO DO MOUSE (CONTEXT MENU)
                openContextMenu(event) {
                    event.preventDefault();
                    this.contextMenuPos = { 
                        x: Math.min(event.clientX, window.innerWidth - 220), 
                        y: Math.min(event.clientY, window.innerHeight - 280) 
                    };
                    this.showContextMenu = true;
                },

                // DESFAZ A MARCAÇÃO AMARELA DA LINHA OU SELEÇÃO
                removeHighlight() {
                    this.showContextMenu = false;
                    const sel = window.getSelection();
                    if (sel && sel.anchorNode) {
                        let node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                        while (node && node !== this.$refs.wordEditor) {
                            if (node.classList.contains('edited-line')) {
                                node.classList.remove('edited-line');
                            }
                            node = node.parentNode;
                        }
                    }
                    this.syncEditorContent();
                    this.persistWordContent();
                    this.showToast('Marcação amarela removida!');
                },

                sendDuvidaMessage(correction) {
                    if (!this.replyMessageInput || this.replyMessageInput.trim() === '') return;

                    const messageText = this.replyMessageInput.trim();
                    this.replyMessageInput = '';

                    fetch('{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/corrections") }}/' + correction.id + '/comments', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            message: messageText,
                            sender_name: 'Revisor'
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.comment) {
                            if (!correction.comments) correction.comments = [];
                            correction.comments.push(data.comment);
                            correction.status = 'respondida';
                            this.showToast('Mensagem enviada com sucesso no chat!');
                        }
                    })
                    .catch(() => {
                        this.showToast('Erro ao enviar mensagem no chat.');
                    });
                },

                saveEditedText() {
                    this.persistWordContent();
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
                    setTimeout(() => { this.toastMessage = ''; }, 2500);
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
                <svg class="w-10 h-10 mx-auto text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
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
        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
        </svg>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- PEQUENO MENU FLUTUANTE DE CATEGORIZAÇÃO AO EDITAR/SELECIONAR (POPUP) -->
    <div id="category-popover-menu"
         x-show="showCategoryMenu"
         x-cloak
         class="fixed z-[99999] bg-slate-900 text-white p-1.5 rounded-lg shadow-2xl flex items-center gap-1 border border-slate-700 text-xs font-bold transition-all"
         :style="'left: ' + categoryMenuPos.x + 'px; top: ' + categoryMenuPos.y + 'px;'">
        
        <span class="text-[10px] text-slate-400 uppercase tracking-wider px-2 border-r border-slate-700">Categorizar:</span>
        <button type="button" @click="selectCategory('ortografia')" class="px-2.5 py-1 bg-rose-600 hover:bg-rose-700 rounded text-[11px]">Ortografia</button>
        <button type="button" @click="selectCategory('gramatica')" class="px-2.5 py-1 bg-amber-600 hover:bg-amber-700 rounded text-[11px]">Gramática</button>
        <button type="button" @click="selectCategory('duvida')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 rounded text-[11px]">Dúvida (Chat)</button>
        <button type="button" @click="selectCategory('padronizacao')" class="px-2.5 py-1 bg-purple-600 hover:bg-purple-700 rounded text-[11px]">Padronização</button>
        <button type="button" @click="showCategoryMenu = false" class="px-1.5 py-1 text-slate-400 hover:text-white">✕</button>
    </div>

    <!-- MENU DE BOTÃO DIREITO DO MOUSE (CUSTOM CONTEXT MENU) -->
    <div id="custom-context-menu"
         x-show="showContextMenu"
         x-cloak
         class="fixed z-[99999] bg-white border border-slate-200 text-slate-800 rounded-lg shadow-2xl py-1.5 w-52 text-xs font-medium"
         :style="'left: ' + contextMenuPos.x + 'px; top: ' + contextMenuPos.y + 'px;'">
        
        <button type="button" @click="execCmd('undo'); showContextMenu = false" class="w-full px-4 py-2 hover:bg-slate-100 text-left flex items-center justify-between">
            <span>↩️ Desfazer</span>
            <span class="text-[10px] text-slate-400">Ctrl+Z</span>
        </button>

        <button type="button" @click="execCmd('redo'); showContextMenu = false" class="w-full px-4 py-2 hover:bg-slate-100 text-left flex items-center justify-between">
            <span>↪️ Refazer</span>
            <span class="text-[10px] text-slate-400">Ctrl+Y</span>
        </button>

        <div class="h-px bg-slate-200 my-1"></div>

        <button type="button" @click="execCmd('bold'); showContextMenu = false" class="w-full px-4 py-2 hover:bg-slate-100 text-left font-black">
            B Negrito
        </button>

        <button type="button" @click="execCmd('italic'); showContextMenu = false" class="w-full px-4 py-2 hover:bg-slate-100 text-left italic">
            I Itálico
        </button>

        <button type="button" @click="execCmd('underline'); showContextMenu = false" class="w-full px-4 py-2 hover:bg-slate-100 text-left underline">
            U Sublinhado
        </button>

        <div class="h-px bg-slate-200 my-1"></div>

        <button type="button" @click="removeHighlight()" class="w-full px-4 py-2 hover:bg-amber-50 text-amber-800 text-left font-bold flex items-center gap-2">
            <span>⚡ Remove Marcação Amarela</span>
        </button>
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
            <!-- CHAT FLUTUANTE DE DÚVIDAS -->
            <button type="button" @click="openDuvidasChatModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-[5px] text-xs transition-all flex items-center gap-2 shadow-sm" title="Abrir Chat de Dúvidas">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span>Chat de Dúvidas</span>
                <span class="px-1.5 py-0.5 bg-emerald-800 text-white rounded-full text-[10px]" x-text="duvidasList.length"></span>
            </button>

            <button type="button" @click="checkLanguageTool()" class="w-9 h-9 bg-purple-600 hover:bg-purple-700 text-white font-bold rounded-[5px] transition-all flex items-center justify-center shadow-sm" title="Analisar com LanguageTool">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </button>

            <button type="button" @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" class="w-9 h-9 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-[5px] transition-all flex items-center justify-center shadow-sm" title="Copiar Link do Autor">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- CORPO PRINCIPAL DE 3 COLUNAS COM ALTURA FLUIDA 100% -->
    <main class="flex-1 flex overflow-hidden min-h-0">

        <!-- COLUNA 1 (ESQUERDA - 320px): LISTA DE APONTAMENTOS SEM SELECT NOS CARDS -->
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
                <button @click="categoryFilter = 'duvida'" class="flex-1 py-2.5 text-center border-b-2 text-[10px]" :class="categoryFilter === 'duvida' ? 'border-emerald-600 text-emerald-600 bg-emerald-50' : 'border-transparent text-slate-400'">Dúvidas</button>
            </div>

            <!-- Feed de Apontamentos Automáticos sem Selects Indesejados -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="cor in correctionsList" :key="cor.id">
                    <div x-show="categoryFilter === 'todas' || categoryFilter === cor.category" class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-[5px] text-xs space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded text-white"
                                  :class="{
                                      'bg-rose-600': cor.category === 'ortografia',
                                      'bg-amber-600': cor.category === 'gramatica',
                                      'bg-emerald-600': cor.category === 'duvida',
                                      'bg-purple-600': cor.category === 'padronizacao'
                                  }" x-text="cor.category"></span>
                            <span class="text-[10px] text-slate-400 font-bold">Apontamento</span>
                        </div>

                        <p class="font-mono text-amber-950 font-bold bg-amber-100/90 px-1.5 py-0.5 rounded" x-text="cor.original_text || 'Edição direta'"></p>
                        <p class="text-slate-600 italic text-[11px]" x-text="(cor.justification || 'Edição no documento.')"></p>

                        <template x-if="cor.category === 'duvida'">
                            <button type="button" @click="activeDuvidaId = cor.id; openDuvidasChatModal = true" class="w-full py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] uppercase tracking-wider rounded flex items-center justify-center gap-1 mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>Abrir Chat (<span x-text="cor.comments ? cor.comments.length : 0"></span>)</span>
                            </button>
                        </template>
                    </div>
                </template>

                <template x-if="correctionsList.length === 0">
                    <div class="text-center text-slate-400 py-12 font-semibold text-xs border border-dashed border-slate-200 rounded-[5px]">
                        Nenhum apontamento marcado. Altere o texto no Word para destacar em amarelo e categorizar!
                    </div>
                </template>
            </div>

        </aside>

        <!-- COLUNA 2 (CENTRO - FLEX-1): VIEWPORT DO DOCUMENTO (COM MENU CONTEXTUAL DE BOTÃO DIREITO) -->
        <section class="flex-1 bg-slate-200/70 flex flex-col min-w-0 relative overflow-hidden h-full">
            
            <!-- Barra Secundária Superior do Visualizador / Editor de Texto -->
            <div class="h-12 border-b border-slate-200 bg-white px-4 flex items-center justify-between shrink-0 z-10 shadow-xs">
                
                <!-- Ferramentas para PDF -->
                <template x-if="currentFile && currentFile.file_type === 'pdf'">
                    <div class="flex items-center gap-3 text-xs font-bold text-slate-600">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="viewerMode = 'iframe'" class="px-3 py-1 rounded-[3px]" :class="viewerMode === 'iframe' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                Leitor Nativo
                            </button>
                            <button type="button" @click="viewerMode = 'native'; loadPdfDocument()" class="px-3 py-1 rounded-[3px]" :class="viewerMode === 'native' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                Plugin PDF.js
                            </button>
                        </div>

                        <template x-if="viewerMode === 'native'">
                            <div class="flex items-center gap-2 pl-3 border-l border-slate-200">
                                <button type="button" @click="prevPage()" class="w-7 h-7 bg-slate-100 hover:bg-slate-200 rounded-[3px] flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <span>PÁG. <span x-text="currentPage"></span> DE <span x-text="totalPages"></span></span>
                                <button type="button" @click="nextPage()" class="w-7 h-7 bg-slate-100 hover:bg-slate-200 rounded-[3px] flex items-center justify-center">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                                <button type="button" @click="zoomPdf(-0.1)" class="w-7 h-7 bg-slate-100 hover:bg-slate-200 rounded-[3px] ml-2 flex items-center justify-center font-bold text-xs">-</button>
                                <button type="button" @click="zoomPdf(0.1)" class="w-7 h-7 bg-slate-100 hover:bg-slate-200 rounded-[3px] flex items-center justify-center font-bold text-xs">+</button>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- BARRA DE FERRAMENTAS WYSIWYG PARA DOCUMENTOS WORD -->
                <template x-if="currentFile && currentFile.file_type === 'word'">
                    <div class="flex items-center gap-2 text-xs font-bold w-full justify-between">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="execCmd('bold')" class="w-7 h-7 hover:bg-white rounded font-black text-slate-800 flex items-center justify-center" title="Negrito">B</button>
                            <button type="button" @click="execCmd('italic')" class="w-7 h-7 hover:bg-white rounded italic text-slate-800 flex items-center justify-center" title="Itálico">I</button>
                            <button type="button" @click="execCmd('underline')" class="w-7 h-7 hover:bg-white rounded underline text-slate-800 flex items-center justify-center" title="Sublinhado">U</button>
                            <span class="h-4 w-px bg-slate-300 mx-1"></span>
                            <button type="button" @click="execCmd('justifyLeft')" class="w-7 h-7 hover:bg-white rounded text-slate-700 flex items-center justify-center" title="Esquerda">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/></svg>
                            </button>
                            <button type="button" @click="execCmd('justifyCenter')" class="w-7 h-7 hover:bg-white rounded text-slate-700 flex items-center justify-center" title="Centro">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/></svg>
                            </button>
                            <button type="button" @click="execCmd('justifyRight')" class="w-7 h-7 hover:bg-white rounded text-slate-700 flex items-center justify-center" title="Direita">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/></svg>
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-slate-400 font-bold" x-text="savingText ? 'Salvando...' : 'Salvo no banco de dados'"></span>
                        </div>
                    </div>
                </template>

            </div>

            <!-- CANVAS PRINCIPAL (COM MENU DE BOTÃO DIREITO HABILITADO VIA @contextmenu.prevent) -->
            <div x-ref="documentViewport"
                 @scroll="handleViewportScroll($event)"
                 @contextmenu.prevent="openContextMenu($event)"
                 class="flex-1 overflow-auto flex items-start justify-center p-8 relative bg-slate-200/60">
                
                <!-- INDICADOR DE CARREGAMENTO DO WORD -->
                <div x-show="loadingWord" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-600 z-20 gap-2">
                    <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Carregando documento Word...</span>
                </div>

                <!-- PDF EM IFRAME NATIVO DO NAVEGADOR -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'iframe'">
                    <div class="w-full max-w-6xl h-full bg-white paper-shadow rounded-[5px] border border-slate-200 p-2 flex flex-col my-auto">
                        <iframe :src="getFileStreamUrl(currentFile.id)" class="w-full h-full rounded border-0" frameborder="0"></iframe>
                    </div>
                </template>

                <!-- CANVAS PDF.JS PLUGIN -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'native'">
                    <div class="bg-white paper-shadow rounded border border-slate-200 p-4 relative max-w-4xl max-h-full overflow-auto flex flex-col items-center my-auto">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-500 z-10">
                            Renderizando PDF via Plugin PDF.js...
                        </div>
                        <canvas id="pdf-canvas" class="max-w-full block mx-auto shadow-sm border border-slate-200"></canvas>
                    </div>
                </template>

                <!-- FORMATO DE PÁGINA CORRIDA DO WORD COM DIGITAÇÃO E SALVAMENTO EM TEMPO REAL -->
                <template x-if="currentFile && currentFile.file_type === 'word'">
                    <div class="w-full flex flex-col items-center">
                        
                        <div class="word-page-a4 paper-shadow border border-slate-300 text-slate-900 rounded-[2px] transition-all select-text relative"
                             id="word-paper-container">

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
                            <a :href="getFileDownloadUrl({{ $file->id }})" target="_blank" @click.stop class="text-blue-600 hover:underline flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Baixar
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Ações do Rodapé -->
            <div class="p-4 border-t border-slate-200 bg-slate-50/50 space-y-2 shrink-0">
                <button type="button" @click="openUploadVersionModal = true" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow-xs">
                    Subir Nova Versão
                </button>
            </div>

        </aside>

    </main>

    <!-- MODAL / DRAWER CHAT FLUTUANTE DE DÚVIDAS -->
    <div x-show="openDuvidasChatModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-end bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openDuvidasChatModal = false" class="bg-white border-l border-slate-200 text-slate-800 h-full max-w-md w-full shadow-2xl flex flex-col justify-between">
            
            <div class="p-4 border-b border-slate-200 bg-slate-50/80 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="font-outfit font-black text-slate-900 text-sm uppercase">Chat de Dúvidas</h3>
                </div>
                <button type="button" @click="openDuvidasChatModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <template x-for="cor in duvidasList" :key="cor.id">
                    <div class="p-3.5 bg-emerald-50/60 border border-emerald-200 rounded-lg text-xs space-y-3">
                        <div class="flex items-center justify-between border-b border-emerald-200/80 pb-1.5">
                            <span class="font-bold text-emerald-950 uppercase text-[10px]">Dúvida Marcada</span>
                            <span class="text-[9px] font-bold px-2 py-0.5 rounded bg-emerald-200 text-emerald-800" x-text="cor.status"></span>
                        </div>

                        <p class="font-mono text-emerald-950 font-bold bg-white p-2 rounded border border-emerald-200" x-text="'💬 ' + (cor.original_text || 'Dúvida no documento')"></p>

                        <div class="space-y-2 pt-1">
                            <template x-for="cmt in (cor.comments || [])" :key="cmt.id">
                                <div class="p-2.5 rounded bg-white border border-slate-200 space-y-1">
                                    <div class="flex items-center justify-between text-[10px] font-bold">
                                        <span class="text-slate-800" x-text="cmt.author_name || 'Usuário'"></span>
                                        <span class="text-slate-400" x-text="cmt.created_at || ''"></span>
                                    </div>
                                    <p class="text-slate-600 text-[11px]" x-text="cmt.message"></p>
                                </div>
                            </template>
                        </div>

                        <div class="pt-2 flex items-center gap-2">
                            <input type="text" 
                                   x-model="replyMessageInput"
                                   @keydown.enter="sendDuvidaMessage(cor)"
                                   placeholder="Escreva sua resposta..."
                                   class="flex-1 px-3 py-2 border border-slate-200 rounded text-xs focus:ring-1 focus:ring-emerald-500 bg-white">
                            <button type="button" @click="sendDuvidaMessage(cor)" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs">
                                Enviar
                            </button>
                        </div>
                    </div>
                </template>

                <template x-if="duvidasList.length === 0">
                    <div class="text-center text-slate-400 py-12 text-xs font-medium">
                        Nenhuma dúvida registrada. Altere a categoria de um apontamento para "Dúvida" para abrir uma conversa com o Autor!
                    </div>
                </template>
            </div>

        </div>
    </div>

</body>
</html>
