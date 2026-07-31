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

        /* TAG DESTAQUE EM VERMELHO PARA A PALAVRA EDITADA NO MANUSCRITO */
        mark.edited-red-word, mark.edited-text-tag {
            background-color: #ffe4e6 !important;
            color: #9f1239 !important;
            border: 1px solid #fda4af !important;
            font-weight: 700;
            padding: 1px 5px;
            border-radius: 4px;
            display: inline-block;
            box-shadow: 0 1px 2px rgba(225, 29, 72, 0.15);
            margin: 0 2px;
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

                // MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE APONTAMENTO
                showDeleteModal: false,
                correctionToDelete: null,

                // State Chat de Dúvidas
                activeDuvidaId: null,
                replyMessageInput: '',
                isSendingChat: false,

                // LanguageTool Modal & Draggable State
                openLanguageToolModal: false,
                languageToolMatches: [],
                loadingLanguageTool: false,
                langToolLevel: 'default',
                langToolPos: { x: window.innerWidth > 768 ? window.innerWidth - 420 : 10, y: 80 },
                isDraggingLangTool: false,
                dragOffset: { x: 0, y: 0 },

                ignoreLanguageToolMatch(match) {
                    this.languageToolMatches = this.languageToolMatches.filter(m => m !== match);
                    this.showToast('Sugestão ignorada.');
                },

                startLangToolDrag(e) {
                    this.isDraggingLangTool = true;
                    this.dragOffset.x = e.clientX - this.langToolPos.x;
                    this.dragOffset.y = e.clientY - this.langToolPos.y;
                    
                    const onMove = (evt) => {
                        if (!this.isDraggingLangTool) return;
                        this.langToolPos.x = Math.max(10, Math.min(window.innerWidth - 390, evt.clientX - this.dragOffset.x));
                        this.langToolPos.y = Math.max(10, Math.min(window.innerHeight - 120, evt.clientY - this.dragOffset.y));
                    };
                    const onUp = () => {
                        this.isDraggingLangTool = false;
                        window.removeEventListener('mousemove', onMove);
                        window.removeEventListener('mouseup', onUp);
                    };
                    window.addEventListener('mousemove', onMove);
                    window.addEventListener('mouseup', onUp);
                },

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
                                        this.sanitizeCorruptedDocumentHtml();
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
                            this.$nextTick(() => {
                                this.renderAllPdfPages();
                            });
                        })
                        .catch(() => {
                            this.renderingPdf = false;
                            this.viewerMode = 'iframe';
                            this.showToast('Alternado para o Leitor Nativo do Navegador.');
                        });
                },

                renderAllPdfPages() {
                    if (!window._activePdfDoc) return;
                    const pdf = window._activePdfDoc;
                    this.renderingPdf = true;

                    const container = document.getElementById('pdf-continuous-container');
                    if (!container) return;
                    container.innerHTML = '';

                    let renderPromises = [];

                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const pageWrapper = document.createElement('div');
                        pageWrapper.id = 'pdf-page-wrapper-' + pageNum;
                        pageWrapper.className = 'my-6 flex flex-col items-center select-text relative shadow-lg bg-white border border-slate-300 rounded-[2px] p-4 w-full max-w-4xl mx-auto';
                        pageWrapper.setAttribute('data-page', pageNum);

                        const canvas = document.createElement('canvas');
                        canvas.id = 'pdf-canvas-page-' + pageNum;
                        canvas.className = 'max-w-full block mx-auto shadow-xs border border-slate-200';

                        const pageBadge = document.createElement('div');
                        pageBadge.className = 'w-full flex items-center justify-between border-b border-slate-200 pb-2 mb-4 text-[11px] font-bold text-slate-600 select-none';
                        pageBadge.innerHTML = `<span>📄 PÁGINA ${pageNum} DE ${pdf.numPages}</span><span class="text-[10px] text-slate-400 uppercase tracking-wider">PDF.js (Páginas Corridas)</span>`;

                        pageWrapper.appendChild(pageBadge);
                        pageWrapper.appendChild(canvas);
                        container.appendChild(pageWrapper);

                        const p = pdf.getPage(pageNum).then(page => {
                            const viewport = page.getViewport({ scale: this.pdfScale });
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            const ctx = canvas.getContext('2d');
                            return page.render({ canvasContext: ctx, viewport: viewport }).promise;
                        });
                        renderPromises.push(p);
                    }

                    Promise.all(renderPromises).then(() => {
                        this.renderingPdf = false;
                    });
                },

                scrollToPdfPage(pageNum) {
                    if (pageNum < 1 || pageNum > this.totalPages) return;
                    this.currentPage = pageNum;

                    const target = document.getElementById('pdf-page-wrapper-' + pageNum);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
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
                        this.wrapActiveEditedWordInRedTag();
                        this.syncEditorContent();
                        this.persistWordContent();
                    }, 800);
                },

                wrapActiveEditedWordInRedTag() {
                    const sel = window.getSelection();
                    if (!sel || !sel.anchorNode) return;

                    let node = sel.anchorNode;
                    let parent = node.nodeType === 3 ? node.parentNode : node;
                    
                    if (parent && parent.classList && (parent.classList.contains('edited-red-word') || parent.classList.contains('edited-text-tag') || parent.classList.contains('purple-word-mark'))) {
                        return;
                    }

                    if (node.nodeType === 3 && node.nodeValue) {
                        const val = node.nodeValue;
                        const trimmed = val.trim();
                        if (trimmed.length >= 2 && !/^\s*$/.test(trimmed)) {
                            const words = trimmed.split(/\s+/);
                            const lastWord = words[words.length - 1];

                            if (lastWord && lastWord.length >= 2) {
                                const range = document.createRange();
                                const startIdx = val.lastIndexOf(lastWord);
                                if (startIdx !== -1) {
                                    range.setStart(node, startIdx);
                                    range.setEnd(node, startIdx + lastWord.length);

                                    const mark = document.createElement('mark');
                                    mark.className = 'edited-red-word bg-rose-100 text-rose-900 border border-rose-300 font-bold px-1.5 py-0.5 rounded shadow-xs inline-block';
                                    mark.setAttribute('title', 'Texto alterado pelo revisor');
                                    mark.textContent = lastWord;

                                    range.deleteContents();
                                    range.insertNode(mark);

                                    const newRange = document.createRange();
                                    newRange.setStartAfter(mark);
                                    newRange.collapse(true);
                                    sel.removeAllRanges();
                                    sel.addRange(newRange);
                                }
                            }
                        }
                    }
                },

                restoreParaOriginal(node) {
                    if (!node) return;
                    const paraId = node.id || node.getAttribute('data-para-id');
                    if (paraId && this.paraHistoryMap[paraId] && this.paraHistoryMap[paraId].length > 0) {
                        node.innerHTML = this.paraHistoryMap[paraId][0];
                        node.classList.remove('edited-line');
                        node.style.backgroundColor = '';
                        node.style.color = '';
                        node.style.borderLeft = '';
                        node.removeAttribute('data-version-index');
                        this.syncEditorContent();
                        this.persistWordContent();
                        this.showToast('Texto restaurado para a versão limpa original!');
                    }
                },

                getSnippetText(htmlStr) {
                    if (!htmlStr) return '';
                    const temp = document.createElement('div');
                    temp.innerHTML = htmlStr;
                    const text = (temp.textContent || temp.innerText || '').trim();
                    return text.length > 35 ? text.substring(0, 35) + '...' : text;
                },

                selectCategory(cat) {
                    this.showCategoryMenu = false;

                    if (this.pendingEditedNode) {
                        const paraId = this.pendingEditedNode.id || ('para_' + Math.random().toString(36).substr(2, 9));
                        this.pendingEditedNode.id = paraId;

                        if (!this.paraHistoryMap[paraId]) {
                            this.paraHistoryMap[paraId] = [this.pendingEditedNode.innerHTML];
                        }

                        if (this.pendingSelectedText && this.pendingSelectedText.length > 0 && !this.pendingEditedNode.querySelector('.edited-red-word')) {
                            this.replaceInTextNodesOnly(
                                this.pendingEditedNode,
                                this.pendingSelectedText,
                                `<mark class="edited-red-word bg-rose-100 text-rose-900 border border-rose-300 font-bold px-1.5 py-0.5 rounded shadow-xs inline-block" title="Texto alterado pelo revisor">${this.pendingSelectedText}</mark>`
                            );
                        }

                        this.paraHistoryMap[paraId].push(this.pendingEditedNode.innerHTML);
                        this.pendingEditedNode.setAttribute('data-version-index', this.paraHistoryMap[paraId].length - 1);

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
                        this.showToast('Salvo com destaque em roxo e amarelo e adicionado em ' + cat.toUpperCase() + '!');
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

                confirmDeleteCorrection(cor) {
                    this.correctionToDelete = cor;
                    this.showDeleteModal = true;
                },

                deleteSelectedCorrectionOrHighlight() {
                    this.showContextMenu = false;
                    const sel = window.getSelection();
                    let targetText = '';
                    if (sel && sel.toString().trim().length > 0) {
                        targetText = sel.toString().trim();
                    }

                    let matchingCor = null;
                    if (targetText && this.correctionsList.length > 0) {
                        matchingCor = this.correctionsList.find(c => c.original_text && c.original_text.toLowerCase().includes(targetText.toLowerCase()));
                    }

                    if (!matchingCor && this.correctionsList.length > 0) {
                        let node = sel && sel.anchorNode ? (sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode) : null;
                        if (node) {
                            const nodeText = node.textContent.trim();
                            matchingCor = this.correctionsList.find(c => c.original_text && nodeText.toLowerCase().includes(c.original_text.toLowerCase()));
                        }
                    }

                    if (matchingCor) {
                        this.confirmDeleteCorrection(matchingCor);
                    } else {
                        this.removeHighlight();
                    }
                },

                executeDeleteCorrection() {
                    const cor = this.correctionToDelete;
                    this.showDeleteModal = false;
                    this.correctionToDelete = null;

                    if (cor && cor.id) {
                        fetch('{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/corrections") }}/' + cor.id, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            }
                        })
                        .then(res => res.json())
                        .then(data => {
                            this.correctionsList = this.correctionsList.filter(c => c.id !== cor.id);

                            if (cor.original_text) {
                                this.restoreOriginalTextInDocument(cor.original_text);
                            } else {
                                this.removeHighlight();
                            }

                            this.syncEditorContent();
                            this.persistWordContent();
                            this.showToast('Apontamento excluído e texto original restaurado!');
                        })
                        .catch(() => {
                            this.showToast('Erro ao excluir apontamento.');
                        });
                    } else {
                        this.removeHighlight();
                    }
                },

                restoreOriginalTextInDocument(targetText) {
                    const editor = this.$refs.wordEditor;
                    if (!editor || !targetText) return;

                    const searchText = targetText.toLowerCase().trim();
                    const allElements = editor.querySelectorAll('.edited-line, p, div, li, mark, [style*="fef08a"]');

                    for (let el of allElements) {
                        if (el.textContent.toLowerCase().includes(searchText)) {
                            const paraId = el.id || el.getAttribute('data-para-id');
                            if (paraId && this.paraHistoryMap[paraId] && this.paraHistoryMap[paraId].length > 0) {
                                el.innerHTML = this.paraHistoryMap[paraId][0];
                            }
                            el.classList.remove('edited-line');
                            el.style.backgroundColor = '';
                            el.style.color = '';
                            el.style.borderLeft = '';
                            el.style.padding = '';
                            el.style.marginBottom = '';
                            el.removeAttribute('data-version-index');
                        }
                    }
                },

                removeHighlight() {
                    this.showContextMenu = false;
                    const sel = window.getSelection();
                    if (sel && sel.anchorNode) {
                        let node = sel.anchorNode.nodeType === 3 ? sel.anchorNode.parentNode : sel.anchorNode;
                        while (node && node !== this.$refs.wordEditor) {
                            if (node.classList.contains('edited-line')) {
                                const paraId = node.id || node.getAttribute('data-para-id');
                                if (paraId && this.paraHistoryMap[paraId] && this.paraHistoryMap[paraId].length > 0) {
                                    node.innerHTML = this.paraHistoryMap[paraId][0];
                                }
                                node.classList.remove('edited-line');
                                node.style.backgroundColor = '';
                                node.style.color = '';
                                node.style.borderLeft = '';
                                node.style.padding = '';
                                node.style.marginBottom = '';
                                node.removeAttribute('data-version-index');
                                break;
                            }
                            node = node.parentNode;
                        }
                    }
                    this.syncEditorContent();
                    this.persistWordContent();
                    this.showToast('Marcação amarela removida e texto restaurado!');
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

                checkLanguageTool(customLevel = null) {
                    if (customLevel) {
                        this.langToolLevel = customLevel;
                    }
                    const editor = this.$refs.wordEditor;
                    const text = editor ? editor.innerText : (this.revisedContent || this.originalContent);
                    
                    if (!text || text.trim().length < 5) {
                        this.showToast('Não há texto suficiente para análise ortográfica.');
                        return;
                    }

                    this.loadingLanguageTool = true;
                    this.openLanguageToolModal = true;
                    this.languageToolMatches = [];
                    this.showToast('Analisando documento (Nível: ' + (this.langToolLevel === 'picky' ? 'Exigente/Acadêmico' : 'Padrão') + ')...');

                    fetch('{{ route("public.editorial.revisor.languagetool", $revision->share_token) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ text: text, level: this.langToolLevel })
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

                replaceInTextNodesOnly(container, searchText, replacementHtml) {
                    if (!container || !searchText) return false;
                    const walker = document.createTreeWalker(container, NodeFilter.SHOW_TEXT, null, false);
                    let node;
                    const textNodes = [];
                    while (node = walker.nextNode()) {
                        if (node.nodeValue && node.nodeValue.toLowerCase().includes(searchText.toLowerCase())) {
                            textNodes.push(node);
                        }
                    }

                    if (textNodes.length === 0) return false;

                    const escapedText = searchText.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const isWordOnly = /^[a-zA-Z0-9áàâãéèêíïóôõöúçñÁÀÂÃÉÈÊÍÏÓÔÕÖÚÇÑ]+$/.test(searchText);
                    const regex = new RegExp(isWordOnly ? ('\\b(' + escapedText + ')\\b') : ('(' + escapedText + ')'), 'gi');

                    let replacedAny = false;
                    for (let tNode of textNodes) {
                        const tempDiv = document.createElement('div');
                        const newHtml = tNode.nodeValue.replace(regex, replacementHtml);
                        if (newHtml !== tNode.nodeValue) {
                            tempDiv.innerHTML = newHtml;
                            const fragment = document.createDocumentFragment();
                            while (tempDiv.firstChild) {
                                fragment.appendChild(tempDiv.firstChild);
                            }
                            tNode.parentNode.replaceChild(fragment, tNode);
                            replacedAny = true;
                            break;
                        }
                    }
                    return replacedAny;
                },

                sanitizeCorruptedDocumentHtml() {
                    const editor = this.$refs.wordEditor;
                    if (!editor) return;
                    let html = editor.innerHTML;
                    if (html.includes('-word-word') || html.includes('border - purple') || html.includes('mark=""')) {
                        html = html.replace(/-word-word[^>]*>/gi, '');
                        html = html.replace(/\s*mark=""\s*bg\s*-\s*purple\s*-\s*[0-9]+=""\s*text\s*-\s*purple\s*-\s*[0-9]+=""\s*font\s*-\s*bold=""\s*px\s*-\s*1\.5=""\s*py\s*-\s*0\.5=""\s*rounded=""\s*border=""\s*border\s*-\s*purple\s*-\s*[0-9]+=""\s*inline\s*-\s*block[^>]*>/gi, '');
                        html = html.replace(/\s*tag\s*bg\s*-\s*purple\s*-\s*[0-9]+\s*text\s*-\s*purple\s*-\s*[0-9]+\s*border\s*border\s*-\s*purple\s*-\s*[0-9]+\s*font\s*-\s*bold\s*px\s*-\s*1\.5\s*py\s*-\s*0\.5\s*rounded\s*shadow\s*-\s*xs\s*inline\s*-\s*block[^>]*>/gi, '');
                        html = html.replace(/\s*mark=""\s*bg\s*-\s*word\s*-\s*mark=""/gi, '');
                        html = html.replace(/\s*mark=""/gi, '');
                        editor.innerHTML = html;
                        this.syncEditorContent();
                        this.persistWordContent();
                        this.showToast('Documento higienizado e tags corrompidas removidas!');
                    }
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

                    // Procura o elemento de texto contendo a palavra
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

                        const purpleReplacement = `<mark class="purple-word-mark bg-purple-200 text-purple-950 font-bold px-1.5 py-0.5 rounded border border-purple-400 inline-block">$1</mark>`;
                        this.replaceInTextNodesOnly(foundElement, matchText, purpleReplacement);

                        this.showToast('Palavra "' + matchText + '" destacada em roxo!');
                    } else {
                        this.showToast('Trecho "' + matchText + '" não localizado no layout ativo.');
                    }
                },

                // Controles da Linha do Tempo / Histórico do Trecho
                paraHistoryMap: {},

                navigateParaHistory(paraNode, direction) {
                    if (!paraNode || !paraNode.id || !this.paraHistoryMap[paraNode.id]) return;
                    const history = this.paraHistoryMap[paraNode.id];
                    let currentIndex = parseInt(paraNode.getAttribute('data-version-index') || (history.length - 1));
                    let nextIndex = currentIndex + direction;

                    if (nextIndex >= 0 && nextIndex < history.length) {
                        paraNode.setAttribute('data-version-index', nextIndex);
                        paraNode.innerHTML = history[nextIndex];
                        this.syncEditorContent();
                        this.persistWordContent();
                        this.showToast('Exibindo Versão ' + (nextIndex + 1) + ' de ' + history.length + ' do trecho!');
                    }
                },

                applyLanguageToolCorrection(match, replacementValue) {
                    const editor = this.$refs.wordEditor;
                    if (!match || !match.context) return;

                    const originalWord = match.context.text.substring(match.context.offset, match.context.offset + match.context.length).trim();
                    if (!originalWord || originalWord.length < 1) return;

                    if (!editor) {
                        this.showToast('Abra a edição de texto para aplicar a correção.');
                        return;
                    }

                    // Limpa destaques roxos residuais antes da substituição
                    const oldMarks = editor.querySelectorAll('.purple-word-mark');
                    oldMarks.forEach(m => {
                        const parentNode = m.parentNode;
                        if (parentNode) {
                            m.replaceWith(document.createTextNode(m.textContent));
                            parentNode.normalize();
                        }
                    });

                    const tagReplacement = `<mark class="edited-red-word bg-rose-100 text-rose-900 border border-rose-300 font-bold px-1.5 py-0.5 rounded shadow-xs inline-block" title="Texto alterado pelo revisor">${replacementValue}</mark>`;

                    // Busca o parágrafo ou item contendo a palavra original
                    const allLeafElements = editor.querySelectorAll('p, li, h3, h4, span');
                    let targetElement = null;

                    for (let el of allLeafElements) {
                        if (el.classList.contains('pdf-page-card') || el.id === 'word-paper-container') continue;
                        if (el.textContent.toLowerCase().includes(originalWord.toLowerCase())) {
                            targetElement = el;
                            break;
                        }
                    }

                    if (targetElement) {
                        const paraId = targetElement.id || ('para_' + Math.random().toString(36).substr(2, 9));
                        targetElement.id = paraId;

                        // Salva o histórico do estado LIMPO original antes da substituição
                        if (!this.paraHistoryMap[paraId]) {
                            this.paraHistoryMap[paraId] = [targetElement.innerHTML];
                        }

                        // Substituição SEGURA apenas nos nós de texto (sem tocar em atributos HTML)
                        const replaced = this.replaceInTextNodesOnly(targetElement, originalWord, tagReplacement);
                        
                        if (replaced) {
                            this.paraHistoryMap[paraId].push(targetElement.innerHTML);
                            targetElement.setAttribute('data-version-index', this.paraHistoryMap[paraId].length - 1);

                            // Aplica o destaque amarelo permanente na linha alterada
                            targetElement.classList.add('edited-line');
                            targetElement.setAttribute('style', 'background-color: #fef08a !important; color: #713f12 !important; border-left: 4px solid #facc15 !important; padding: 6px 10px; border-radius: 4px; margin-bottom: 8px;');
                            targetElement.scrollIntoView({ behavior: 'smooth', block: 'center' });

                            this.syncEditorContent();
                            this.persistWordContent();
                        }
                    }

                    // Remove o item da lista do LanguageTool
                    this.languageToolMatches = this.languageToolMatches.filter(m => m !== match);
                    this.showToast('Correção "' + replacementValue + '" aplicada e salva!');
                }
            };
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
        
        <template x-if="pendingEditedNode && pendingEditedNode.id && paraHistoryMap[pendingEditedNode.id] && paraHistoryMap[pendingEditedNode.id].length > 1">
            <div class="flex items-center gap-1.5 pl-2 border-l border-slate-700">
                <span class="text-[10px] text-purple-300 font-mono">Histórico:</span>
                <button type="button" @click="navigateParaHistory(pendingEditedNode, -1)" class="w-5 h-5 bg-purple-700 hover:bg-purple-600 rounded flex items-center justify-center text-[10px]" title="Versão Anterior">◀</button>
                <span class="text-[10px] font-mono text-purple-200" x-text="(parseInt(pendingEditedNode.getAttribute('data-version-index') || (paraHistoryMap[pendingEditedNode.id].length - 1)) + 1) + '/' + paraHistoryMap[pendingEditedNode.id].length"></span>
                <button type="button" @click="navigateParaHistory(pendingEditedNode, 1)" class="w-5 h-5 bg-purple-700 hover:bg-purple-600 rounded flex items-center justify-center text-[10px]" title="Versão Seguinte">▶</button>
            </div>
        </template>

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

        <button type="button" @click="deleteSelectedCorrectionOrHighlight()" class="w-full px-4 py-2 hover:bg-rose-50 text-rose-700 text-left font-bold flex items-center gap-2">
            <span>🗑️ Excluir Apontamento e Restaurar</span>
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
            <a :href="'{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + selectedFileId + '/export-docx'" target="_blank" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-[5px] text-xs transition-all shadow-xs flex items-center gap-2" title="Baixar arquivo editado preservando todas as marcações em amarelo">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span class="hidden sm:inline">Baixar Versão Editada</span>
            </a>

            <button type="button" @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" class="w-9 h-9 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-[5px] transition-all flex items-center justify-center shadow-sm" title="Copiar Link do Autor">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.101 1.101"/>
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
                            <div class="flex items-center gap-2">
                                <span class="text-[10px] text-slate-400 group-hover:text-blue-600 font-bold flex items-center gap-1">
                                    <span>Ir para o texto</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </span>
                                <button type="button" @click.stop="confirmDeleteCorrection(cor)" class="p-1 text-slate-400 hover:text-rose-600 hover:bg-rose-100 rounded transition-colors" title="Excluir Apontamento">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
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

            <!-- Botão de Exportação do Relatório de Apontamentos -->
            <div class="p-3 border-t border-slate-200 bg-slate-50 shrink-0">
                <a href="{{ route('public.editorial.revisor.export-report', $revision->share_token) }}" target="_blank" class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[11px] uppercase tracking-wider rounded flex items-center justify-center gap-1.5 transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Baixar Relatório de Apontamentos</span>
                </a>
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
                                <a :href="'{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + selectedFileId + '/export-docx'" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded text-xs transition-all shadow-xs flex items-center gap-1.5" title="Baixar PDF editado preservando todas as marcações em amarelo">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    <span>Baixar PDF Editado</span>
                                </a>
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
                            <a :href="'{{ url("/revisao-editorial/" . $revision->share_token . "/revisor/file") }}/' + selectedFileId + '/export-docx'" target="_blank" class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded text-xs transition-all shadow-xs flex items-center gap-1.5" title="Baixar arquivo editado preservando todas as marcações em amarelo">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Baixar Versão Editada</span>
                            </a>
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

                <!-- CANVAS PDF.JS PLUGIN (PÁGINAS CORRIDAS COM SCROLL VERTICAL) -->
                <template x-if="currentFile && currentFile.file_type === 'pdf' && viewerMode === 'native' && !pdfEditMode">
                    <div class="w-full flex flex-col items-center relative py-4">
                        <div x-show="renderingPdf" class="fixed top-24 z-20 bg-slate-900/90 text-white px-4 py-2 rounded-full font-bold text-xs shadow-xl flex items-center gap-2">
                            <svg class="animate-spin w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span>Renderizando Páginas do PDF...</span>
                        </div>
                        <div id="pdf-continuous-container" class="w-full max-w-5xl flex flex-col items-center space-y-6"></div>
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
                        <p class="font-bold text-slate-600">Nenhuma dúvida no momento</p>
                        <p class="text-[11px] text-slate-400 max-w-xs mx-auto">Altere a categoria de um apontamento para "Dúvida" para abrir um tópico de conversa com o Autor!</p>
                    </div>
                </template>
            </div>

        </div>
    </div>

    <!-- PAINEL FLUTUANTE LATERAL MÓVEL DE ANÁLISE ORTOGRÁFICA (LANGUAGETOOL) -->
    <div x-show="openLanguageToolModal"
         x-cloak
         class="fixed z-[99999] w-96 max-h-[82vh] bg-white border border-purple-300 rounded-2xl shadow-2xl flex flex-col overflow-hidden select-none"
         :style="'left: ' + langToolPos.x + 'px; top: ' + langToolPos.y + 'px;'">
        
        <div @mousedown="startLangToolDrag($event)" class="p-3.5 border-b border-purple-200 bg-purple-900 text-white flex items-center justify-between shrink-0 cursor-move" title="Clique e arraste para mover esta janela na tela">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-full bg-purple-800 flex items-center justify-center font-bold text-xs">
                    🔍
                </div>
                <div>
                    <h3 class="font-outfit font-black text-xs uppercase tracking-tight flex items-center gap-1.5">
                        <span>Análise Ortográfica</span>
                        <span class="text-[9px] bg-purple-800 text-purple-200 px-1.5 py-0.5 rounded font-mono">Arraste ✋</span>
                    </h3>
                    <p class="text-[10px] text-purple-200" x-text="loadingLanguageTool ? 'Analisando documento...' : languageToolMatches.length + ' sugestões encontradas'"></p>
                </div>
            </div>
            <button type="button" @click="openLanguageToolModal = false" class="w-7 h-7 rounded-full bg-purple-800 hover:bg-purple-700 text-purple-200 hover:text-white flex items-center justify-center font-bold text-xs">✕</button>
        </div>

        <!-- Seletor de Nível de Análise (Padrão vs Acadêmico/Exigente) -->
        <div class="px-3.5 py-2 bg-purple-950 border-b border-purple-800 flex items-center justify-between text-[11px] text-purple-200 shrink-0">
            <span class="font-bold uppercase tracking-wider text-[9px] text-purple-300">Nível de Análise:</span>
            <div class="flex items-center gap-1 bg-purple-900 p-1 rounded-md border border-purple-800">
                <button type="button" 
                        @click="checkLanguageTool('default')" 
                        class="px-2.5 py-1 rounded text-[11px] font-black transition-colors" 
                        :class="langToolLevel === 'default' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'text-purple-300 hover:text-white font-medium'">
                    ✓ Padrão
                </button>
                <button type="button" 
                        @click="checkLanguageTool('picky')" 
                        class="px-2.5 py-1 rounded text-[11px] font-black transition-colors" 
                        :class="langToolLevel === 'picky' ? 'bg-emerald-500 text-slate-950 shadow-sm' : 'text-purple-300 hover:text-white font-medium'">
                    ⚡ Acadêmico / Estilo
                </button>
            </div>
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
                    <p class="text-xs text-emerald-700">Seu texto está de acordo com as regras de ortografia em Português neste nível.</p>
                </div>
            </template>

            <template x-for="(match, idx) in languageToolMatches" :key="idx">
                <div @click="highlightAndScrollToMatch(match)" 
                     class="bg-white border border-purple-200 hover:border-purple-400 rounded-xl p-3.5 shadow-xs space-y-2 text-xs cursor-pointer">
                    
                    <div class="flex items-center justify-between border-b border-purple-100 pb-1.5">
                        <span class="font-black text-purple-900 uppercase text-[9px] bg-purple-100 px-2 py-0.5 rounded-full" x-text="match.rule ? match.rule.category.name : 'Ortografia'"></span>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] text-purple-600 font-bold flex items-center gap-1">
                                <span>Ir para o texto</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                            <button type="button" 
                                    @click.stop="ignoreLanguageToolMatch(match)" 
                                    class="px-2 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold rounded text-[10px] border border-slate-300 cursor-pointer"
                                    title="Ignorar esta sugestão">
                                🚫 Ignorar
                            </button>
                        </div>
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
                            <span class="text-[9px] font-bold text-slate-400 uppercase w-full block mb-0.5">Sugestões de Correção:</span>
                            <template x-for="(rep, rIdx) in match.replacements.slice(0, 4)" :key="rIdx">
                                <button type="button" 
                                        @click.stop="applyLanguageToolCorrection(match, rep.value)" 
                                        class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-md text-xs border border-emerald-500 shadow-xs flex items-center gap-1 cursor-pointer"
                                        title="Clique para aplicar esta correção no texto e salvar no banco">
                                    <span>✓</span>
                                    <span x-text="rep.value"></span>
                                </button>
                            </template>
                        </div>
                    </template>

                </div>
            </template>
        </div>

    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO DE APONTAMENTO -->
    <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-xs select-none">
        <div @click.away="showDeleteModal = false" class="bg-white border border-slate-200 text-slate-800 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center gap-3 text-rose-600 border-b border-slate-100 pb-3">
                <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                <div>
                    <h3 class="font-outfit font-black text-slate-800 text-base uppercase tracking-tight">Excluir Apontamento</h3>
                    <p class="text-xs text-slate-500 font-medium">Esta ação não poderá ser desfeita.</p>
                </div>
            </div>

            <p class="text-xs text-slate-600 leading-relaxed font-medium">
                Deseja realmente excluir este apontamento? O trecho no documento será restaurado para o texto original limpo.
            </p>
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showDeleteModal = false; correctionToDelete = null" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors cursor-pointer">
                    Cancelar
                </button>
                <button type="button" @click="executeDeleteCorrection()" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors shadow-xs flex items-center gap-1 cursor-pointer">
                    <span>Sim, Excluir</span>
                </button>
            </div>
        </div>
    </div>

</body>
</html>
