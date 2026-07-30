<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workspace do Revisor - {{ $revision->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script>
        function revisorWorkspace() {
            const filesData = @json($revision->files);
            const textsData = @json($extractedTexts);

            return {
                openCorrectionModal: false,
                categoryFilter: 'todas',
                toastMessage: '',
                selectedFileId: filesData.length > 0 ? filesData[0].id : null,
                languageToolMatches: [],
                
                modalCategory: 'ortografia',
                modalOriginalText: '',
                modalSuggestedText: '',
                modalJustification: '',

                get currentFile() {
                    return filesData.find(f => f.id == this.selectedFileId) || null;
                },

                get extractedText() {
                    return textsData[this.selectedFileId] || 'Conteúdo disponível para download em formato original.';
                },

                captureSelectedText() {
                    const sel = window.getSelection().toString().trim();
                    if (sel) {
                        this.modalOriginalText = sel;
                    }
                },

                copyAuthorLink(url) {
                    navigator.clipboard.writeText(url);
                    this.toastMessage = 'Link do Autor copiado com sucesso!';
                    setTimeout(() => { this.toastMessage = ''; }, 4000);
                },

                checkLanguageTool() {
                    const text = this.extractedText;
                    if (!text || text.length < 5) {
                        this.toastMessage = 'Não há texto suficiente para análise ortográfica.';
                        setTimeout(() => { this.toastMessage = ''; }, 4000);
                        return;
                    }

                    this.toastMessage = 'Analisando texto com LanguageTool...';

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
                        this.toastMessage = 'Análise concluída! Encontradas ' + this.languageToolMatches.length + ' sugestões.';
                        setTimeout(() => { this.toastMessage = ''; }, 4000);
                    })
                    .catch(err => {
                        this.toastMessage = 'Falha ao conectar ao serviço de ortografia.';
                        setTimeout(() => { this.toastMessage = ''; }, 4000);
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

    <!-- Toast Notification Banner (Sem alerts nativos) -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition
         class="fixed bottom-24 right-6 z-[99999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- Botão Flutuante de Adicionar Novo Apontamento (Canto Inferior Direito) -->
    <div class="fixed bottom-6 right-6 z-40 flex flex-col items-end gap-3">
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

    <!-- Topo / Cabeçalho do Workspace do Revisor -->
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
                        @click="copyAuthorLink('{{ route('public.editorial.show', $revision->share_token) }}')" 
                        class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>🔗</span> Copiar Link para o Autor
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

        <!-- Grid Principal: Leitor de Arquivo (Esquerda) vs Apontamentos (Direita) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Coluna Esquerda: Leitor e Renderizador de Arquivos (7 Colunas) -->
            <div class="lg:col-span-7 space-y-4">
                
                <!-- Seletor de Arquivos -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex items-center justify-between gap-3">
                    <div class="space-y-1">
                        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Arquivo em Análise</span>
                        <select x-model="selectedFileId" class="px-3 py-1.5 border border-slate-200 rounded-[5px] text-xs font-bold bg-slate-50 text-slate-800 focus:outline-none">
                            @foreach($revision->files as $file)
                                <option value="{{ $file->id }}">{{ $file->filename }} ({{ strtoupper($file->file_type) }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Botão de Download do Original -->
                    <template x-if="currentFile">
                        <a :href="'{{ asset('storage') }}/' + currentFile.file_path" target="_blank" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-[5px] transition-colors flex items-center gap-1.5 border border-slate-200">
                            <span>⬇️ Baixar Original</span>
                        </a>
                    </template>
                </div>

                <!-- Visualizador de Conteúdo / Extração de Texto -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4 min-h-[500px]">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <h3 class="font-outfit font-black text-xs uppercase tracking-wider text-slate-400">Visualização de Texto Extraído</h3>
                        <span class="text-[10px] text-slate-400 italic">Selecione trechos com o mouse para criar sugestões rapidamente</span>
                    </div>

                    <!-- Renderização do Texto Bruto ou formatado -->
                    <div class="prose max-w-none text-xs leading-relaxed font-serif text-slate-800 bg-slate-50/50 p-5 rounded-[5px] border border-slate-150 max-h-[600px] overflow-y-auto whitespace-pre-wrap select-text"
                         @mouseup="captureSelectedText">
                        <span x-text="extractedText"></span>
                    </div>
                </div>

                <!-- Painel de Sugestões Automáticas do LanguageTool -->
                <div x-show="languageToolMatches.length > 0" x-cloak class="bg-purple-50/60 border border-purple-200 rounded-[5px] p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <h4 class="font-outfit font-black text-xs uppercase tracking-wider text-purple-800 flex items-center gap-2">
                            <span>🔍</span> Sugestões da Verificação Ortográfica (<span x-text="languageToolMatches.length"></span>)
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
                                    <span class="text-[10px] text-slate-400 font-bold">{{ ucfirst($cor->status) }}</span>
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
                        <input type="number" name="page_number" placeholder="Ex: 5" class="w-full px-3 py-2 border rounded-[5px]">
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

</body>
</html>
