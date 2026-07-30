@extends('layouts.app')

@section('title', 'Revisão Editorial - ' . $editorialRevision->title . ' - Gestor de Freelas')
@section('page_title', 'Revisão Editorial')

@section('content')
<style>
    @keyframes pulse-glow-purple {
        0%, 100% { box-shadow: 0 0 5px rgba(147, 51, 234, 0.15), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(147, 51, 234, 0.35); }
        50% { box-shadow: 0 0 15px rgba(147, 51, 234, 0.5), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(147, 51, 234, 0.65); }
    }
    .pulse-glow-purple {
        animation: pulse-glow-purple 2s infinite ease-in-out;
    }
</style>

<div x-data="editorialShowWorkspace()" class="space-y-8">
    
    <!-- Link de Retorno -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes-editoriais.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 uppercase tracking-wider flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Listagem de Revisões Editoriais
        </a>
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

    <!-- Ficha Técnica & Links do Projeto -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto de Revisão Editorial</span>
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-xl leading-tight mt-1">
                    {{ $editorialRevision->title }}
                </h3>
                @if($editorialRevision->description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $editorialRevision->description }}</p>
                @endif
            </div>

            <!-- Ficha & Alterar Revisor -->
            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/60 px-4 py-3 rounded-[5px] border border-slate-150 dark:border-slate-700 shrink-0 self-start md:self-center">
                <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 font-bold flex items-center justify-center text-sm border border-purple-200 dark:border-purple-800">
                    ✍️
                </div>
                <div>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">Revisor Atribuído</span>
                    <form action="{{ route('revisoes-editoriais.revisor.change', $editorialRevision->id) }}" method="POST" class="mt-0.5">
                        @csrf
                        @method('PATCH')
                        <select name="revisor_id" onchange="this.form.submit()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-xs font-bold rounded px-2.5 py-1 text-slate-800 dark:text-slate-200 cursor-pointer focus:ring-1 focus:ring-purple-500">
                            <option value="">Nenhum Revisor Atribuído</option>
                            @foreach($revisores as $rev)
                                <option value="{{ $rev->id }}" {{ $editorialRevision->revisor_id == $rev->id ? 'selected' : '' }}>
                                    {{ $rev->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        <!-- Links Públicos do Projeto (Apenas Ícones) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Link do Revisor -->
            <div class="space-y-2 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-[5px] border border-slate-200 dark:border-slate-700">
                <label class="text-[10px] font-black text-purple-700 dark:text-purple-400 uppercase tracking-wider block">🔗 Workspace do Revisor</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('public.editorial.revisor.show', $editorialRevision->share_token) }}" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs px-3 py-2 rounded-[5px] select-all font-mono">
                    <a href="{{ route('public.editorial.revisor.show', $editorialRevision->share_token) }}" target="_blank" class="w-9 h-9 bg-purple-600 hover:bg-purple-700 text-white rounded-[5px] flex items-center justify-center text-sm transition-all shadow-xs" title="Abrir Workspace do Revisor">
                        ↗️
                    </a>
                </div>
            </div>

            <!-- Link do Autor -->
            <div class="space-y-2 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-[5px] border border-slate-200 dark:border-slate-700">
                <label class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider block">🔗 Link do Autor (Esclarecer Dúvidas)</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('public.editorial.show', $editorialRevision->share_token) }}" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs px-3 py-2 rounded-[5px] select-all font-mono">
                    <button type="button" @click="copyShareLink('{{ route('public.editorial.show', $editorialRevision->share_token) }}')" class="w-9 h-9 bg-slate-900 hover:bg-slate-800 text-white rounded-[5px] flex items-center justify-center text-sm transition-all shadow-xs" title="Copiar Link do Autor">
                        📋
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid: Arquivos (2/3) vs Apontamentos e Métricas (1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Coluna Esquerda: Lista de Arquivos do Projeto (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-lg uppercase tracking-tight">Arquivos do Material</h4>
                    <span class="text-xs text-slate-400 font-bold block mt-0.5">{{ $editorialRevision->files->count() }} Arquivos Cadastrados</span>
                </div>
                
                <button type="button" @click="openUploadModal = true" class="w-9 h-9 bg-blue-600 hover:bg-blue-700 text-white rounded-[5px] flex items-center justify-center text-base transition-all shadow-xs" title="Upload de Novos Arquivos">
                    📤
                </button>
            </div>

            <div class="space-y-3">
                @forelse($editorialRevision->files as $file)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px] shadow-xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-2xl shrink-0">
                                @if($file->file_type === 'pdf') 📄 @elseif($file->file_type === 'word') 📝 @else 🖼️ @endif
                            </span>
                            <div class="min-w-0">
                                <h5 class="font-bold text-xs text-slate-800 dark:text-slate-200 truncate" title="{{ $file->filename }}">{{ $file->filename }}</h5>
                                <p class="text-[10px] text-slate-400 font-medium">Versão {{ $file->version }} • {{ strtoupper($file->file_type) }} • {{ number_format($file->file_size / 1024, 1) }} KB</p>
                            </div>
                        </div>

                        <!-- BOTOES COM ICONES APENAS (SEM TEXTO) -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ route('public.editorial.file.download', ['token' => $editorialRevision->share_token, 'fileId' => $file->id]) }}" target="_blank" class="w-8 h-8 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 rounded-[5px] flex items-center justify-center text-sm transition-colors" title="Baixar Arquivo">
                                ⬇️
                            </a>

                            <form action="{{ route('revisoes-editoriais.files.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir o arquivo {{ $file->filename }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-300 rounded-[5px] flex items-center justify-center text-sm transition-colors" title="Excluir Arquivo">
                                    🗑️
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 border border-dashed border-slate-200 dark:border-slate-800 text-center text-slate-400 rounded-[5px] text-xs font-bold">
                        Nenhum arquivo associado a este projeto de revisão.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Coluna Direita: Métricas e Resumo de Apontamentos (1/3) -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-[5px] shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight">Resumo de Apontamentos</h4>
                    <span class="px-2.5 py-0.5 bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300 font-bold text-[10px] rounded-full">
                        {{ $editorialRevision->corrections->count() }} Total
                    </span>
                </div>
                
                <div class="grid grid-cols-2 gap-3 text-center text-xs font-bold">
                    <div class="p-3 bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 rounded-[5px]">
                        <span class="text-xl font-black text-slate-800 dark:text-slate-100 block">{{ $editorialRevision->corrections->count() }}</span>
                        <span class="text-[10px] text-slate-400 uppercase">Apontamentos</span>
                    </div>

                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-[5px]">
                        <span class="text-xl font-black text-amber-700 dark:text-amber-400 block">{{ $editorialRevision->corrections->whereIn('status', ['pendente', 'em_analise'])->count() }}</span>
                        <span class="text-[10px] text-amber-600 dark:text-amber-300 uppercase">Pendentes</span>
                    </div>

                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-rose-700 dark:text-rose-400 block">{{ $editorialRevision->corrections->where('category', 'ortografia')->count() }}</span>
                        <span class="text-[10px] text-rose-600 dark:text-rose-300 uppercase">Ortografia</span>
                    </div>

                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-blue-700 dark:text-blue-400 block">{{ $editorialRevision->corrections->where('category', 'gramatica')->count() }}</span>
                        <span class="text-[10px] text-blue-600 dark:text-blue-300 uppercase">Gramática</span>
                    </div>

                    <div class="p-3 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-emerald-700 dark:text-emerald-400 block">{{ $editorialRevision->corrections->where('category', 'duvida')->count() }}</span>
                        <span class="text-[10px] text-emerald-600 dark:text-emerald-300 uppercase">Dúvidas</span>
                    </div>

                    <div class="p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-purple-700 dark:text-purple-400 block">{{ $editorialRevision->corrections->where('category', 'padronizacao')->count() }}</span>
                        <span class="text-[10px] text-purple-600 dark:text-purple-300 uppercase">Padronização</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- MODAL MODERNO DRAG & DROP DE ARQUIVOS COM BARRA DE PROGRESSO EM TEMPO REAL -->
    <div x-show="openUploadModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openUploadModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-outfit font-black text-md uppercase">📤 Upload Moderno de Arquivos</h3>
                <button type="button" @click="openUploadModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>

            <!-- ZONA DE DRAG & DROP -->
            <div @dragover.prevent="isDragging = true"
                 @dragleave.prevent="isDragging = false"
                 @drop.prevent="handleFileDrop($event)"
                 @click="$refs.fileInput.click()"
                 class="border-2 border-dashed rounded-xl p-8 text-center cursor-pointer transition-all space-y-3"
                 :class="isDragging ? 'border-blue-500 bg-blue-50/50 dark:bg-blue-950/30' : 'border-slate-300 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/40 hover:bg-slate-100 dark:hover:bg-slate-800'">
                
                <input type="file" x-ref="fileInput" @change="handleFileSelect($event)" multiple class="hidden">

                <span class="text-4xl block">📁</span>
                <p class="text-xs font-bold text-slate-700 dark:text-slate-200">
                    Arraste & Solte seus arquivos aqui ou <span class="text-blue-600 underline">clique para buscar</span>
                </p>
                <p class="text-[10px] text-slate-400">PDF, Word (.docx) ou Imagens (Máx: 100MB)</p>
            </div>

            <!-- LISTA DE ARQUIVOS SELECIONADOS -->
            <template x-if="selectedFiles.length > 0">
                <div class="space-y-2 max-h-32 overflow-y-auto">
                    <template x-for="(file, index) in selectedFiles" :key="index">
                        <div class="flex items-center justify-between p-2.5 bg-slate-100 dark:bg-slate-800 rounded text-xs font-medium">
                            <span class="truncate max-w-[240px]" x-text="file.name"></span>
                            <span class="text-[10px] text-slate-400" x-text="(file.size / 1024 / 1024).toFixed(2) + ' MB'"></span>
                        </div>
                    </template>
                </div>
            </template>

            <!-- BARRA DE PROGRESSO EM TEMPO REAL COM PORCENTAGEM -->
            <div x-show="uploading" class="space-y-2 pt-2">
                <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-200">
                    <span>Enviando arquivos...</span>
                    <span x-text="uploadProgress + '%'"></span>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-3 overflow-hidden">
                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-150" :style="'width: ' + uploadProgress + '%'"></div>
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="openUploadModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-[5px]">Cancelar</button>
                <button type="button" @click="submitUploadWithProgress()" :disabled="selectedFiles.length === 0 || uploading" class="w-9 h-9 bg-blue-600 hover:bg-blue-700 text-white rounded-[5px] flex items-center justify-center text-sm transition-all disabled:opacity-50" title="Iniciar Upload">
                    🚀
                </button>
            </div>

        </div>
    </div>

</div>

<script>
    function editorialShowWorkspace() {
        return {
            openUploadModal: false,
            toastMessage: '',
            isDragging: false,
            selectedFiles: [],
            uploading: false,
            uploadProgress: 0,

            copyShareLink(url) {
                navigator.clipboard.writeText(url);
                this.toastMessage = 'Link copiado com sucesso!';
                setTimeout(() => { this.toastMessage = ''; }, 4000);
            },

            handleFileSelect(event) {
                this.selectedFiles = Array.from(event.target.files);
            },

            handleFileDrop(event) {
                this.isDragging = false;
                if (event.dataTransfer.files.length > 0) {
                    this.selectedFiles = Array.from(event.dataTransfer.files);
                }
            },

            submitUploadWithProgress() {
                if (this.selectedFiles.length === 0) return;

                this.uploading = true;
                this.uploadProgress = 0;

                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                
                this.selectedFiles.forEach(file => {
                    formData.append('files[]', file);
                });

                const xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("revisoes-editoriais.files.upload", $editorialRevision->id) }}', true);

                xhr.upload.onprogress = (e) => {
                    if (e.lengthComputable) {
                        this.uploadProgress = Math.round((e.loaded / e.total) * 100);
                    }
                };

                xhr.onload = () => {
                    this.uploading = false;
                    if (xhr.status === 200 || xhr.status === 302) {
                        this.toastMessage = 'Arquivos enviados com sucesso!';
                        setTimeout(() => { window.location.reload(); }, 800);
                    } else {
                        alert('Falha ao realizar upload.');
                    }
                };

                xhr.onerror = () => {
                    this.uploading = false;
                    alert('Erro de rede ao enviar os arquivos.');
                };

                xhr.send(formData);
            }
        }
    }
</script>
@endsection
