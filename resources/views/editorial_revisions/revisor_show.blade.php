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
        .word-paper-content .edited-line, mark.edited-line, [style*="fef08a"] {
            background-color: #fef08a !important;
            color: #713f12 !important;
            padding: 2px 6px;
            border-radius: 4px;
            border-left: 4px solid #facc15 !important;
            transition: all 0.2s ease;
        }
        .pulse-highlight-target {
            outline: 3px solid #3b82f6 !important;
            animation: flashHighlight 1.5s ease-out;
        }
        @keyframes flashHighlight {
            0% { background-color: #60a5fa; }
            100% { background-color: #fef08a; }
        }

        /* MARCAÇÃO DE PALAVRA DO LANGUAGETOOL EM ROXO (APENAS NA PALAVRA) */
        mark.purple-word-mark {
            background-color: #e9d5ff !important;
            color: #581c87 !important;
            padding: 2px 6px;
            border-radius: 4px;
            border: 1px solid #c084fc !important;
            box-shadow: 0 1px 3px rgba(168, 85, 247, 0.2);
            display: inline-block;
        }

        /* CAPA DE SELEÇÃO E DESTAQUE DE TEXTO SOBRE O PDF ORIGINAL */
        .pdf-text-layer-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            color: transparent;
            line-height: 1;
            pointer-events: auto;
            user-select: text;
            -webkit-user-select: text;
        }
        .pdf-text-layer-overlay span {
            color: transparent;
            position: absolute;
            white-space: pre;
            cursor: text;
        }
        .pdf-text-layer-overlay ::selection {
            background-color: rgba(250, 204, 21, 0.6) !important;
            color: transparent;
        }
    </style>

    <script>
        function revisorWorkspace() {
            const filesData = @json($revision->files);
            const initialTexts = @json($extractedTexts);
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
                viewerMode: 'native', // Plugin PDF.js como PRIORIDADE PADRÃO
                pdfEditMode: false, // Permite alternar para edição de texto extraído do PDF
                toastMessage: '',
                selectedFileId: initialFileId,
                correctionsList: @json($revision->corrections),
                
                // POPUP DE CATEGORIZAÇÃO AO EDITAR/SELECIONAR
                showCategoryMenu: false,
                categoryMenuPos: { x: 0, y: 0 },
                pendingEditedNode: null,
                pendingSelectedText: '',

                // MENU DE BOTÃO DIREITO DO MOUSE
                showContextMenu: false,
                contextMenuPos: { x: 0, y: 0 },

                // State Chat de Dúvidas
                activeDuvidaId: null,
                replyMessageInput: '',
                isSendingChat: false,

                // LanguageTool Modal State
                openLanguageToolModal: false,
                languageToolMatches: [],
                loadingLanguageTool: false,

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
                    this.handleFileChange(this.selectedFileId);

                    this.$watch('selectedFileId', (id) => {
                        localStorage.setItem('revisor_file_' + shareToken, id);
                        this.pdfEditMode = false;
                        this.loadContentForSelectedFile();
                        this.handleFileChange(id);
                    });

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
                    if (file && (file.file_type === 'word' || this.pdfEditMode)) {
                        this.loadingWord = true;
                        
                        if (initialTexts && initialTexts[this.selectedFileId]) {
                            const preText = initialTexts[this.selectedFileId];
                            this.originalContent = preText;
                            this.revisedContent = preText;
                            this.$nextTick(() => {
                                const editor = this.$refs.wordEditor;
                                if (editor) editor.innerHTML = preText;
                            });
                        }

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
                    if (file && file.file_type === 'pdf' && !this.pdfEditMode) {
                        if (this.viewerMode === 'native') {
                            this.loadPdfDocument();
                        }
                    }
                },

                loadPdfDocument() {
                    if (!this.currentFile) return;
                    const url = this.getFileStreamUrl(this.currentFile.id);
                    this.renderingPdf = true;

                    fetch(url)
                        .then(res => {
                            if (!res.ok) throw new Error('Falha no stream');
                            return res.arrayBuffer();
                        })
                        .then(buffer => pdfjsLib.getDocument({ data: buffer }).promise)
                        .then(pdf => {
                            window._activePdfDoc = pdf;
                            this.totalPages = pdf.numPages;
                            this.currentPage = 1;
                            this.renderPdfPage(1);
                        })
                        .catch(() => {
                            this.renderingPdf = false;
                            // FALLBACK AUTOMÁTICO SE O PLUGIN PDF.JS FALHAR
                            this.viewerMode = 'iframe';
                            this.showToast('Alternado para o Leitor Nativo do Navegador.');
                        });
                },

                renderPdfPage(num) {
                    if (!window._activePdfDoc) return;
                    this.renderingPdf = true;

                    window._activePdfDoc.getPage(num).then(page => {
                        const canvas = document.getElementById('pdf-canvas');
                        const textLayer = document.getElementById('pdf-text-layer');
                        if (!canvas) return;
                        const ctx = canvas.getContext('2d');
                        const viewport = page.getViewport({ scale: this.pdfScale });

                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        if (textLayer) {
                            textLayer.style.height = viewport.height + 'px';
                            textLayer.style.width = viewport.width + 'px';
                            textLayer.innerHTML = '';
                        }

                        page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                            this.renderingPdf = false;

                            if (textLayer && page.getTextContent) {
                                page.getTextContent().then(textContent => {
                                    if (pdfjsLib.renderTextLayer) {
                                        pdfjsLib.renderTextLayer({
                                            textContent: textContent,
                                            container: textLayer,
                                            viewport: viewport,
                                            textDivs: []
                                        });
                                    }
                                });
                            }
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

                            if (event.clientX && event.clientY) {
                                this.categoryMenuPos = { x: Math.min(event.clientX, window.innerWidth - 300), y: Math.max(event.clientY - 50, 80) };
                            } else {
                                const rect = node.getBoundingClientRect();
                                this.categoryMenuPos = { x: Math.min(rect.left, window.innerWidth - 300), y: Math.max(rect.top - 45, 80) };
                            }
                            this.showCategoryMenu = true;
                        }
                    }

                    clearTimeout(this.typingTimer);
                    this.typingTimer = setTimeout(() => {
                        this.persistWordContent();
                    }, 1000);
                },

                selectCategory(cat) {
                    this.showCategoryMenu = false;

                    if (this.pendingEditedNode) {
                        this.pendingEditedNode.classList.add('edited-line');
                        this.pendingEditedNode.setAttribute('style', 'background-color: #fef08a !important; color: #713f12 !important; border-left: 4px solid #facc15 !important; padding: 4px 8px; border-radius: 4px; margin-bottom: 8px;');
                    }

                    this.syncEditorContent();
                    this.persistWordContent();

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
                        this.showToast('Salvo em amarelo e adicionado em ' + cat.toUpperCase() + '!');
                        if (data.correction) {
                            data.correction.comments = data.correction.comments || [];
                            this.correctionsList.unshift(data.correction);
                        }
                    });
                },

                scrollToCorrection(cor) {
                    const editor = this.$refs.wordEditor;
                    if (!editor || !cor.original_text) return;

                    const searchText = cor.original_text.toLowerCase().trim();
                    const allElements = editor.querySelectorAll('.edited-line, p, div, li, mark, [style*="fef08a"]');

                    let foundElement = null;
                    for (let el of allElements) {
                        if (el.textContent.toLowerCase().includes(searchText)) {
                            foundElement = el;
                            break;
                        }
                    }

                    if (!foundElement) {
                        foundElement = editor.querySelector('.edited-line, [style*="fef08a"]');
                    }

                    if (foundElement) {
                        foundElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        foundElement.classList.add('pulse-highlight-target');
                        setTimeout(() => {
                            foundElement.classList.remove('pulse-highlight-target');
                        }, 2000);
                        this.showToast('Rolou até a marcação no documento!');
                    }
                },

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
                    .then(res => res.json())
                    .then(data => {
                        this.savingText = false;
                        this.showToast('Alterações e marcações salvas no banco!');
                    })
                    .catch(() => {
                        this.savingText = false;
                    });
                },

                openContextMenu(event) {
                    event.preventDefault();
                    this.contextMenuPos = { 
                        x: Math.min(event.clientX, window.innerWidth - 220), 
                        y: Math.min(event.clientY, window.innerHeight - 280) 
                    };
                    this.showContextMenu = true;
                },

                removeHighlight() {
                    this.showContextMenu = false;
                    const sel = window.getSelection();
                    if (sel && sel.anchorNode) {
                        let node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                        while (node && node !== this.$refs.wordEditor) {
                            if (node.classList.contains('edited-line') || node.hasAttribute('style')) {
                                node.classList.remove('edited-line');
                                node.removeAttribute('style');
                            }
                            node = node.parentNode;
                        }
                    }
                    this.syncEditorContent();
                    this.persistWordContent();
                    this.showToast('Marcação amarela removida!');
                },

                // ENVIO DE RESPOSTA NO CHAT COM ANIMAÇÃO DE PONTINHOS E BALÕES ESTILIZADOS
                sendDuvidaMessage(correction) {
                    if (!this.replyMessageInput || this.replyMessageInput.trim() === '') return;

                    const messageText = this.replyMessageInput.trim();
                    this.replyMessageInput = '';
                    this.isSendingChat = true;

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
                        this.isSendingChat = false;
                        if (data.success && data.comment) {
                            if (!correction.comments) correction.comments = [];
                            correction.comments.push(data.comment);
                            correction.status = 'respondida';
                            this.showToast('Mensagem enviada no chat!');

                            this.$nextTick(() => {
                                const chatContainer = this.$refs.chatMessagesBox;
                                if (chatContainer) {
                                    chatContainer.scrollTop = chatContainer.scrollHeight;
                                }
                            });
                        }
                    })
                    .catch(() => {
                        this.isSendingChat = false;
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
                    const editor = this.$refs.wordEditor;
                    const text = editor ? editor.innerText : (this.revisedContent || this.originalContent);
                    
                    if (!text || text.trim().length < 5) {
                        this.showToast('Não há texto suficiente para análise ortográfica.');
                        return;
                    }

                    this.loadingLanguageTool = true;
                    this.openLanguageToolModal = true;
                    this.languageToolMatches = [];
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
                        this.loadingLanguageTool = false;
                        this.languageToolMatches = data.matches || [];
                        this.showToast('Análise concluída! Encontradas ' + this.languageToolMatches.length + ' sugestões.');
                    })
                    .catch(() => {
                        this.loadingLanguageTool = false;
                        this.showToast('Falha ao conectar ao serviço de ortografia.');
                    });
                },

                highlightAndScrollToMatch(match) {
                    const editor = this.$refs.wordEditor;
                    if (!match || !match.context) return;

                    const matchText = match.context.text.substring(match.context.offset, match.context.offset + match.context.length).trim();
                    if (!matchText || matchText.length < 1) return;

                    if (!editor) {
                        this.showToast('Abra a edição de texto para localizar o trecho.');
                        return;
                    }

                    // Limpa destaques roxos anteriores em palavras
                    const oldMarks = editor.querySelectorAll('.purple-word-mark');
                    oldMarks.forEach(m => {
                        const parentNode = m.parentNode;
                        if (parentNode) {
                            m.replaceWith(document.createTextNode(m.textContent));
                            parentNode.normalize();
                        }
                    });

                    // Procura o parágrafo ou item de lista mais específico contendo a palavra
                    const allLeafElements = editor.querySelectorAll('p, li, h3, h4, span');
                    let foundElement = null;

                    for (let el of allLeafElements) {
                        if (el.classList.contains('pdf-page-card') || el.id === 'word-paper-container') continue;
                        if (el.textContent.toLowerCase().includes(matchText.toLowerCase())) {
                            foundElement = el;
                            break;
                        }
                    }

                    if (foundElement) {
                        foundElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        // Destaca APENAS a palavra exata em roxo
                        const escapedText = matchText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                        const regex = new RegExp('(' + escapedText + ')', 'gi');
                        foundElement.innerHTML = foundElement.innerHTML.replace(regex, '<mark class="purple-word-mark bg-purple-200 text-purple-950 font-bold px-1.5 py-0.5 rounded border border-purple-400 inline-block">$1</mark>');

                        this.showToast('Palavra "' + matchText + '" destacada em roxo!');
                    } else {
                        this.showToast('Trecho "' + matchText + '" não localizado no layout ativo.');
                    }
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

    <!-- PEQUENO MENU FLUTUANTE DE CATEGORIZAÇÃO AO EDITAR/SELECIONAR -->
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

        <button type="button" @click="checkLanguageTool(); showContextMenu = false" class="w-full px-4 py-2 hover:bg-purple-50 text-purple-900 text-left font-bold flex items-center justify-between">
            <span class="flex items-center gap-2">
                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Verificar Ortografia (LanguageTool)</span>
            </span>
        </button>

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
            <button type="button" @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" class="w-9 h-9 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-[5px] transition-all flex items-center justify-center shadow-sm" title="Copiar Link do Autor">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1"/>
                </svg>
            </button>
        </div>
    </header>

    <!-- CORPO PRINCIPAL DE 3 COLUNAS COM ALTURA FLUIDA 100% -->
    <main class="flex-1 flex overflow-hidden min-h-0 relative">

        <!-- COLUNA 1 (ESQUERDA - 320px): LISTA DE APONTAMENTOS LINKADOS COM O ARQUIVO -->
        <aside class="w-80 border-r border-slate-200 bg-white flex flex-col justify-between shrink-0 h-full overflow-hidden z-20">
            
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

            <!-- Feed de Apontamentos Linkados -->
            <div class="flex-1 overflow-y-auto p-4 space-y-3">
                <template x-for="cor in correctionsList" :key="cor.id">
                    <div x-show="categoryFilter === 'todas' || categoryFilter === cor.category" 
                         @click="scrollToCorrection(cor)"
                         class="p-3.5 bg-amber-50/60 border border-amber-200 rounded-[5px] text-xs space-y-2 cursor-pointer hover:bg-amber-100/70 hover:border-amber-400 transition-all group">
                        
                        <div class="flex items-center justify-between">
                            <span class="text-[9px] font-black uppercase px-2 py-0.5 rounded text-white"
                                  :class="{
                                      'bg-rose-600': cor.category === 'ortografia',
                                      'bg-amber-600': cor.category === 'gramatica',
                                      'bg-emerald-600': cor.category === 'duvida',
                                      'bg-purple-600': cor.category === 'padronizacao'
                                  }" x-text="cor.category"></span>
                            <span class="text-[10px] text-slate-400 group-hover:text-blue-600 font-bold flex items-center gap-1">
                                <span>Ir para o texto</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </div>

                        <p class="font-mono text-amber-950 font-bold bg-amber-100/90 px-1.5 py-0.5 rounded" x-text="cor.original_text || 'Edição direta'"></p>
                        <p class="text-slate-600 italic text-[11px]" x-text="(cor.justification || 'Edição no documento.')"></p>

                        <template x-if="cor.category === 'duvida'">
                            <button type="button" @click.stop="activeDuvidaId = cor.id; openDuvidasChatModal = true" class="w-full py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] uppercase tracking-wider rounded flex items-center justify-center gap-1 mt-1">
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

        <!-- COLUNA 2 (CENTRO - FLEX-1): VIEWPORT DO DOCUMENTO -->
        <section class="flex-1 bg-slate-200/70 flex flex-col min-w-0 relative overflow-hidden h-full">
            
            <div class="h-12 border-b border-slate-200 bg-white px-4 flex items-center justify-between shrink-0 z-10 shadow-xs">
                
                <!-- FERRAMENTAS PARA PDF (PLUGIN PDF.JS COMO PRIORIDADE + MODO DE EDIÇÃO DE TEXTO) -->
                <template x-if="currentFile && currentFile.file_type === 'pdf'">
                    <div class="flex items-center justify-between w-full text-xs font-bold text-slate-600">
                        <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-[5px]">
                            <button type="button" @click="pdfEditMode = false; viewerMode = 'native'; loadPdfDocument()" class="px-3 py-1 rounded-[3px]" :class="(viewerMode === 'native' && !pdfEditMode) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                Plugin PDF.js (Prioridade)
                            </button>
                            <button type="button" @click="pdfEditMode = false; viewerMode = 'iframe'" class="px-3 py-1 rounded-[3px]" :class="(viewerMode === 'iframe' && !pdfEditMode) ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-500'">
                                Leitor Nativo (iFrame)
                            </button>
                            <button type="button" @click="pdfEditMode = true; loadContentForSelectedFile()" class="px-3 py-1 rounded-[3px] bg-purple-100 text-purple-800 hover:bg-purple-200" :class="pdfEditMode ? 'bg-purple-600 text-white shadow-xs' : ''">
                                📝 Editar / Destacar Texto do PDF
                            </button>
                        </div>

                        <template x-if="viewerMode === 'native' && !pdfEditMode">
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

                        <template x-if="pdfEditMode">
                            <div class="flex items-center gap-2">
                                <button type="button" @click="persistWordContent()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition-all shadow-xs flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Salvar Edição do PDF</span>
                                </button>
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
                            <button type="button" @click="persistWordContent()" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded text-xs transition-all shadow-xs flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                <span>Salvar Agora</span>
                            </button>
                            <span class="text-[10px] text-slate-400 font-bold" x-text="savingText ? 'Salvando...' : 'Salvo no banco'"></span>
                        </div>
                    </div>
                </template>

            </div>

            <!-- CANVAS PRINCIPAL -->
            <div x-ref="documentViewport"
                 @scroll="handleViewportScroll($event)"
                 @contextmenu.prevent="openContextMenu($event)"
                 class="flex-1 overflow-auto flex items-start justify-center p-8 relative bg-slate-200/60">
                
                <div x-show="loadingWord" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-600 z-20 gap-2">
                    <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <span>Carregando documento...</span>
                </div>

                <!-- PDF EM IFRAME NATIVO DO NAVEGADOR -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'iframe' && !pdfEditMode">
                    <div class="w-full max-w-6xl h-full bg-white paper-shadow rounded-[5px] border border-slate-200 p-2 flex flex-col my-auto">
                        <iframe :src="getFileStreamUrl(currentFile.id)" class="w-full h-full rounded border-0" frameborder="0"></iframe>
                    </div>
                </template>

                <!-- CANVAS PDF.JS PLUGIN (PRIORIDADE PADRÃO - PRESERVA 100% LAYOUT, FONTES E IMAGENS ORIGINAIS) -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'native' && !pdfEditMode">
                    <div class="bg-white paper-shadow rounded border border-slate-200 p-4 relative max-w-5xl max-h-full overflow-auto flex flex-col items-center my-auto">
                        <div x-show="renderingPdf" class="absolute inset-0 bg-white/80 flex items-center justify-center font-bold text-xs text-slate-500 z-10">
                            Renderizando PDF via Plugin PDF.js...
                        </div>
                        <div class="relative inline-block">
                            <canvas id="pdf-canvas" class="max-w-full block mx-auto shadow-sm border border-slate-200"></canvas>
                            <div id="pdf-text-layer" class="pdf-text-layer-overlay"></div>
                        </div>
                    </div>
                </template>

                <!-- FORMATO DE PÁGINA CORRIDA DO WORD OU MODO EDIÇÃO DO PDF COM MARCAÇÕES EM AMARELO -->
                <template x-if="(currentFile && currentFile.file_type === 'word') || (currentFile && currentFile.file_type === 'pdf' && pdfEditMode)">
                    <div class="w-full flex flex-col items-center">
                        <div :class="(currentFile && currentFile.file_type === 'pdf') ? 'w-full flex flex-col items-center space-y-8 select-text' : 'word-page-a4 paper-shadow border border-slate-300 text-slate-900 rounded-[2px] transition-all select-text relative'"
                             id="word-paper-container">
                            <div x-ref="wordEditor"
                                 contenteditable="true"
                                 class="word-paper-content focus:outline-none w-full flex flex-col items-center"
                                 @input="handleEditorInput($event)">
                            </div>
                        </div>
                    </div>
                </template>

                <template x-if="currentFile && currentFile.file_type === 'image'">
                    <div class="bg-white paper-shadow rounded p-2 border border-slate-200 max-w-4xl my-auto">
                        <img :src="getFileStreamUrl(currentFile.id)" class="max-h-[75vh] object-contain">
                    </div>
                </template>

                <!-- WIDGET FLUTUANTE DE CHAT DE DÚVIDAS ESTILO APPS DE MENSAGEM -->
                <button type="button" 
                        @click="openDuvidasChatModal = true" 
                        class="fixed bottom-6 right-80 z-40 bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-3 rounded-full shadow-2xl flex items-center gap-2.5 transition-all transform hover:scale-105" 
                        title="Abrir Chat de Dúvidas">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <span class="text-xs font-bold">Chat de Dúvidas</span>
                    <span class="px-2 py-0.5 bg-emerald-800 text-white rounded-full text-[10px]" x-text="duvidasList.length"></span>
                </button>

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

            <div class="p-4 border-t border-slate-200 bg-slate-50/50 space-y-2 shrink-0">
                <button type="button" @click="openUploadVersionModal = true" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow-xs">
                    Subir Nova Versão
                </button>
            </div>

        </aside>

    </main>

    <!-- DRAWER CHAT MODERNO DE DÚVIDAS COM BALÕES DE FALA E PONTINHOS DE DIGITAÇÃO -->
    <div x-show="openDuvidasChatModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-end bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openDuvidasChatModal = false" class="bg-white border-l border-slate-200 text-slate-800 h-full max-w-md w-full shadow-2xl flex flex-col justify-between overflow-hidden">
            
            <!-- Header do Chat Estilo WhatsApp/Telegram -->
            <div class="p-4 border-b border-slate-200 bg-slate-900 text-white flex items-center justify-between shrink-0 shadow-md">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <div class="w-10 h-10 rounded-full bg-emerald-600 flex items-center justify-center font-bold text-sm text-white">
                            💬
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-emerald-400 border-2 border-slate-900 rounded-full"></span>
                    </div>
                    <div>
                        <h3 class="font-outfit font-black text-base uppercase tracking-tight">Chat de Dúvidas</h3>
                        <p class="text-xs text-slate-400">Revisor & Autor em tempo real</p>
                    </div>
                </div>
                <button type="button" @click="openDuvidasChatModal = false" class="w-8 h-8 rounded-full bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white flex items-center justify-center font-bold text-sm">✕</button>
            </div>

            <!-- Lista de Tópicos e Balões de Fala -->
            <div x-ref="chatMessagesBox" class="flex-1 overflow-y-auto p-4 space-y-5 bg-slate-50/70">
                <template x-for="cor in duvidasList" :key="cor.id">
                    <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-xs space-y-3.5">
                        
                        <!-- Cabeçalho da Dúvida no Documento -->
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                            <span class="text-xs font-black uppercase px-3 py-1 rounded-full bg-emerald-100 text-emerald-800">Dúvida Marcada</span>
                            <span class="text-xs font-bold text-slate-400" x-text="cor.status"></span>
                        </div>

                        <!-- Citação do Texto no Arquivo -->
                        <div @click="scrollToCorrection(cor)" class="p-3 bg-slate-100 border-l-4 border-emerald-500 rounded-r-lg text-sm font-serif italic text-slate-700 cursor-pointer hover:bg-slate-200/80 transition-colors" title="Clique para ir até este ponto no documento">
                            <span class="font-sans font-bold text-xs text-emerald-800 uppercase block not-italic mb-1">Trecho Citado:</span>
                            <span x-text="'“' + (cor.original_text || 'Dúvida no documento') + '”'"></span>
                        </div>

                        <!-- Mensagens da Dúvida em Formato Balão de Fala -->
                        <div class="space-y-3 pt-2">
                            <template x-for="cmt in (cor.comments || [])" :key="cmt.id">
                                <div class="flex flex-col" :class="(cmt.author_name === 'Revisor' || cmt.user_id) ? 'items-end' : 'items-start'">
                                    
                                    <!-- Balão de Fala do Revisor (Direita - Azul) / Autor (Esquerda - Cinza) -->
                                    <div class="max-w-[85%] p-3.5 text-sm leading-relaxed shadow-xs transition-all"
                                         :class="(cmt.author_name === 'Revisor' || cmt.user_id) 
                                                 ? 'bg-blue-600 text-white rounded-2xl rounded-tr-xs' 
                                                 : 'bg-slate-200 text-slate-800 rounded-2xl rounded-tl-xs border border-slate-300'">
                                        
                                        <div class="flex items-center justify-between gap-4 text-[11px] font-bold mb-1 opacity-90 border-b border-current/20 pb-0.5">
                                            <span x-text="cmt.author_name || 'Usuário'"></span>
                                            <span x-text="cmt.created_at || ''"></span>
                                        </div>

                                        <p class="whitespace-pre-wrap font-sans text-sm font-medium" x-text="cmt.message"></p>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- ANIMAÇÃO DE PONTINHOS DE DIGITAÇÃO AO ENVIAR -->
                        <template x-if="isSendingChat">
                            <div class="flex justify-end pt-1">
                                <div class="px-3.5 py-2.5 bg-blue-100 rounded-2xl rounded-tr-xs flex items-center gap-1.5 shadow-xs">
                                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-bounce"></span>
                                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                                    <span class="w-2 h-2 bg-blue-600 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                                </div>
                            </div>
                        </template>

                        <!-- Input para Responder esta Dúvida -->
                        <div class="pt-3 border-t border-slate-100 flex items-center gap-2">
                            <input type="text" 
                                   x-model="replyMessageInput"
                                   @keydown.enter="sendDuvidaMessage(cor)"
                                   placeholder="Escreva sua resposta..."
                                   class="flex-1 px-4 py-2.5 border border-slate-200 rounded-full text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-slate-50">
                            <button type="button" @click="sendDuvidaMessage(cor)" class="w-10 h-10 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-full flex items-center justify-center shadow-sm transition-all transform hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            </button>
                        </div>

                    </div>
                </template>

                <template x-if="duvidasList.length === 0">
                    <div class="text-center text-slate-400 py-16 text-xs font-medium space-y-2">
                        <div class="w-12 h-12 rounded-full bg-slate-200 flex items-center justify-center mx-auto text-xl text-slate-400">
                            💬
                        </div>
                        <p class="font-bold text-slate-600">Nenhuma dúvida no momento</p>
                        <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Altere a categoria de um apontamento para "Dúvida" para abrir um tópico de conversa com o Autor!</p>
                    </div>
                </template>
            </div>

        </div>
    </div>

    <!-- PAINEL FLUTUANTE LATERAL DE ANÁLISE ORTOGRÁFICA (LANGUAGETOOL) -->
    <div x-show="openLanguageToolModal" x-cloak class="fixed top-20 right-80 z-[9999] w-96 max-h-[82vh] bg-white border border-purple-200 rounded-2xl shadow-2xl flex flex-col overflow-hidden select-none">
        
        <div class="p-3.5 border-b border-purple-200 bg-purple-900 text-white flex items-center justify-between shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-purple-800 flex items-center justify-center font-bold text-xs">
                    🔍
                </div>
                <div>
                    <h3 class="font-outfit font-black text-xs uppercase tracking-tight">Análise Ortográfica</h3>
                    <p class="text-[10px] text-purple-200" x-text="loadingLanguageTool ? 'Analisando documento...' : languageToolMatches.length + ' sugestões encontradas'"></p>
                </div>
            </div>
            <button type="button" @click="openLanguageToolModal = false" class="w-7 h-7 rounded-full bg-purple-800 hover:bg-purple-700 text-purple-200 hover:text-white flex items-center justify-center font-bold text-xs">✕</button>
        </div>

        <div class="p-3.5 flex-1 overflow-y-auto space-y-3 bg-purple-50/40">
            <template x-if="loadingLanguageTool">
                <div class="py-12 text-center text-slate-500 font-medium text-xs space-y-3">
                    <svg class="animate-spin w-8 h-8 text-purple-600 mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <p class="font-bold text-slate-700">Verificando erros no documento...</p>
                </div>
            </template>

            <template x-if="!loadingLanguageTool && languageToolMatches.length === 0">
                <div class="py-10 text-center text-emerald-700 font-medium text-xs space-y-2 bg-emerald-50 rounded-xl border border-emerald-200 p-4">
                    <div class="w-10 h-10 rounded-full bg-emerald-200 text-emerald-800 flex items-center justify-center mx-auto text-lg font-bold">✓</div>
                    <p class="font-bold text-sm text-emerald-900">Nenhum erro ortográfico detectado!</p>
                    <p class="text-xs text-emerald-700">Seu texto está de acordo com as regras de ortografia em Português.</p>
                </div>
            </template>

            <template x-for="(match, idx) in languageToolMatches" :key="idx">
                <div @click="highlightAndScrollToMatch(match)" 
                     class="bg-white border border-purple-200 hover:border-purple-500 rounded-xl p-3.5 shadow-xs space-y-2 text-xs cursor-pointer hover:bg-purple-50/60 transition-all group">
                    
                    <div class="flex items-center justify-between border-b border-purple-100 pb-1.5">
                        <span class="font-black text-purple-900 uppercase text-[9px] bg-purple-100 px-2 py-0.5 rounded-full" x-text="match.rule ? match.rule.category.name : 'Ortografia'"></span>
                        <span class="text-[10px] text-purple-600 group-hover:text-purple-800 font-bold flex items-center gap-1">
                            <span>Ir para o texto</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </span>
                    </div>

                    <p class="text-slate-800 font-medium text-xs leading-relaxed" x-text="match.message"></p>

                    <div class="p-2 bg-purple-50 border-l-4 border-purple-500 rounded-r text-xs font-serif text-slate-900 flex items-center justify-between">
                        <div>
                            <span class="font-sans font-bold text-[9px] text-purple-800 uppercase block">No Texto:</span>
                            <span class="line-through text-rose-600 font-bold" x-text="match.context ? match.context.text.substring(match.context.offset, match.context.offset + match.context.length) : ''"></span>
                        </div>
                    </div>

                    <template x-if="match.replacements && match.replacements.length > 0">
                        <div class="pt-1 flex items-center gap-1.5 flex-wrap">
                            <span class="text-[9px] font-bold text-slate-400 uppercase">Sugestões:</span>
                            <template x-for="(rep, rIdx) in match.replacements.slice(0, 3)" :key="rIdx">
                                <span class="px-2 py-0.5 bg-emerald-100 text-emerald-900 font-bold rounded text-[11px] border border-emerald-300" x-text="rep.value"></span>
                            </template>
                        </div>
                    </template>

                </div>
            </template>
        </div>

    </div>

</body>
</html>
