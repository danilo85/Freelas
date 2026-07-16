@extends('layouts.app')

@section('title', 'Gerenciar Arquivos - Rodada #' . $round->round_number . ' - Gestor de Freelas')
@section('page_title', 'Gerenciador de Arquivos')

@section('content')
<div x-data="fileManager()" class="space-y-8">
    
    <!-- Link de Retorno -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes.show', $round->projectRevision->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase tracking-wider flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Linha do Tempo
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto: {{ $round->projectRevision->title }}</span>
        <h3 class="font-outfit font-black text-slate-800 text-lg leading-tight mt-1">
            Gerenciamento de Arquivos - Rodada #{{ $round->round_number }}
        </h3>
    </div>

    <!-- Upload & File Tree Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Enviar Arquivos Card (1/3) -->
        <div class="space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Upload de Arquivos</h4>
            
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4" x-data="fileUploadManager()">
                <form id="upload-form" @submit.prevent="submitForm($event)" class="space-y-4">
                    @csrf
                    
                    <!-- Pasta Virtual -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pasta de Destino (Opcional)</label>
                        <input type="text" 
                               name="folder_name" 
                               x-model="folderName"
                               placeholder="Ex: Ilustrações, Páginas Internas, Capa"
                               class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                        <p class="text-[9px] text-slate-400">Deixe em branco para salvar na pasta principal (Raiz).</p>
                    </div>

                    <!-- Input Files Drag & Drop Zone -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block font-outfit">Arquivos a Revisar</label>
                        
                        <div 
                            @dragover.prevent="dragover = true"
                            @dragleave.prevent="dragover = false"
                            @drop.prevent="handleDrop($event)"
                            @click="$refs.fileInput.click()"
                            class="border-2 border-dashed rounded-[5px] p-6 text-center cursor-pointer transition-all relative flex flex-col items-center justify-center min-h-[140px]"
                            :class="dragover ? 'border-blue-500 bg-blue-50/10' : 'border-slate-200 hover:border-slate-350 bg-slate-50/30'"
                        >
                            <input 
                                type="file" 
                                x-ref="fileInput" 
                                name="files[]" 
                                multiple 
                                class="hidden" 
                                @change="handleFileSelect($event)"
                            >
                            
                            <span class="text-3xl block mb-2">📤</span>
                            <span class="text-xs font-bold text-slate-700 block leading-snug">
                                Arraste arquivos aqui ou clique para selecionar
                            </span>
                            <span class="text-[9px] text-slate-400 block mt-1">PDF ou Imagens recomendados</span>
                        </div>
                    </div>

                    <!-- Previews of selected files -->
                    <template x-if="selectedFiles.length > 0">
                        <div class="space-y-2 pt-2 border-t border-slate-100">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Arquivos Selecionados (<span x-text="selectedFiles.length"></span>)</span>
                            <div class="grid grid-cols-1 gap-2 max-h-48 overflow-y-auto">
                                <template x-for="(file, index) in selectedFiles" :key="index">
                                    <div class="flex items-center justify-between p-2 bg-slate-50 border border-slate-200 rounded-[5px]">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <!-- Simple File Preview -->
                                            <template x-if="file.isImage">
                                                <img :src="file.previewUrl" class="w-8 h-8 object-cover rounded-[3px] border border-slate-300">
                                            </template>
                                            <template x-if="!file.isImage">
                                                <div class="w-8 h-8 rounded-[3px] bg-slate-200 text-slate-600 flex items-center justify-center font-bold text-[10px]">PDF</div>
                                            </template>
                                            
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold text-slate-700 truncate leading-tight" x-text="file.name"></p>
                                                <span class="text-[8px] text-slate-400 uppercase" x-text="(file.size / 1024).toFixed(1) + ' KB'"></span>
                                            </div>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-rose-500 hover:text-rose-700 p-1 font-bold text-xs cursor-pointer">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Progress Bar -->
                    <template x-if="isUploading">
                        <div class="space-y-1.5 pt-2">
                            <div class="flex justify-between items-center text-[10px] font-bold text-blue-600">
                                <span class="uppercase tracking-wider">Enviando Arquivos...</span>
                                <span x-text="uploadPercentage + '%'"></span>
                            </div>
                            <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                <div class="bg-blue-600 h-2 rounded-full transition-all duration-150" :style="'width: ' + uploadPercentage + '%'"></div>
                            </div>
                        </div>
                    </template>

                    <button 
                        type="submit" 
                        :disabled="selectedFiles.length === 0 || isUploading"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-slate-300 disabled:cursor-not-allowed text-white font-bold text-xs uppercase tracking-wider py-3 rounded-[5px] transition-all shadow-sm shadow-blue-500/10 cursor-pointer"
                    >
                        Enviar Arquivos
                    </button>
                </form>
            </div>
        </div>

        <!-- Árvore de Pastas e Arquivos (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Arquivos Cadastrados nesta Rodada</h4>

            @if($round->files->isEmpty())
                <div class="border border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] bg-white text-sm">
                    Nenhum arquivo enviado para esta rodada de revisão.
                </div>
            @else
                <div class="space-y-6">
                    @php
                        // Group files by folder_name
                        $groupedFiles = $round->files->groupBy(function($item) {
                            return $item->folder_name ?: 'Diretório Raiz';
                        });
                    @endphp

                    @foreach($groupedFiles as $folder => $files)
                        <!-- Card da Pasta Virtual -->
                        <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden" x-data="{ open: true }">
                            
                            <!-- Cabecalho da Pasta -->
                            <div @click="open = !open" class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between cursor-pointer hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📁</span>
                                    <span class="font-bold text-sm text-slate-800 uppercase tracking-wide">{{ $folder }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-200 px-2 py-0.5 rounded-full">{{ $files->count() }} arquivo(s)</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>

                            <!-- Listagem de Arquivos da Pasta -->
                            <div x-show="open" class="divide-y divide-slate-100" x-cloak>
                                @foreach($files as $file)
                                    <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                                        
                                        <!-- Detalhes do Arquivo -->
                                        <div class="min-w-0 flex items-center gap-3">
                                            @php
                                                $icon = '📎';
                                                if(in_array($file->file_type, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) $icon = '🖼️';
                                                elseif($file->file_type === 'pdf') $icon = '📄';
                                                elseif(in_array($file->file_type, ['doc', 'docx', 'txt', 'rtf'])) $icon = '📝';
                                            @endphp
                                            <span class="text-xl shrink-0">{{ $icon }}</span>
                                            <div class="min-w-0">
                                                <a href="{{ Storage::url($file->file_path) }}" 
                                                   target="_blank" 
                                                   class="text-sm font-semibold text-slate-700 hover:text-blue-600 hover:underline block truncate" 
                                                   title="Abrir arquivo no navegador">
                                                    {{ $file->filename }}
                                                </a>
                                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5 uppercase">
                                                    {{ strtoupper($file->file_type) }} • {{ number_format($file->file_size / 1024, 1) }} KB
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Ações e Contadores -->
                                        <div class="flex items-center gap-3 shrink-0">
                                            
                                            <!-- Ajustes Contador Badge -->
                                            @if($file->annotations->count() > 0 && $file->annotations->where('status', 'aberto')->count() > 0)
                                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-[5px] bg-rose-50 text-rose-600 border border-rose-200 shadow-sm">
                                                    ⚠️ {{ $file->annotations->where('status', 'aberto')->count() }} Ajustes
                                                </span>
                                            @else
                                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-[5px] bg-emerald-50 text-emerald-600 border border-emerald-250/60 shadow-sm flex items-center gap-1">
                                                    ✓ Tudo Ok
                                                </span>
                                            @endif

                                            <!-- Substituir Arquivo -->
                                            <div class="inline-block">
                                                <input type="file" name="file" class="hidden" @change="replaceFile($event, '{{ route('revisoes.files.replace', $file->id) }}', '{{ addslashes($file->filename) }}')" x-ref="replaceInput{{ $file->id }}">
                                                <button type="button" @click="$refs.replaceInput{{ $file->id }}.click()" class="border border-slate-200 text-blue-500 hover:bg-blue-50 p-2 rounded-[5px] transition-all cursor-pointer" title="Substituir Arquivo">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4" />
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Deletar Arquivo -->
                                            <button type="button" @click="confirmDelete('{{ addslashes($file->filename) }}', '{{ route('revisoes.files.destroy', $file->id) }}')" class="border border-slate-200 text-rose-500 hover:bg-rose-50 p-2 rounded-[5px] transition-all cursor-pointer" title="Excluir Arquivo">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>

    <!-- Deletion Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999; margin: 0 !important;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-sm w-full p-6 text-center space-y-4 select-none relative"
             @click.away="showDeleteModal = false">
            <div class="w-12 h-12 rounded-full bg-red-50 dark:bg-red-950/40 text-red-650 flex items-center justify-center text-xl mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="font-outfit font-black text-slate-850 dark:text-slate-100 text-sm uppercase tracking-tight">Excluir Arquivo?</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block truncate max-w-xs mx-auto" x-text="deleteFileName"></p>
            </div>
            <p class="text-xs text-slate-500 leading-relaxed">
                Deseja realmente excluir este arquivo? Esta ação apagará permanentemente todas as anotações e revisões do cliente feitas nele.
            </p>
            <div class="flex justify-center gap-2 pt-2">
                <button type="button" @click="showDeleteModal = false" class="px-4 py-2 border border-slate-200 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold rounded-[5px] uppercase tracking-wider cursor-pointer">
                    Cancelar
                </button>
                <form :action="deleteActionUrl" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-[5px] shadow-sm uppercase tracking-wider cursor-pointer">
                        Sim, Excluir
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Replacement Progress Modal -->
    <div x-show="isReplacing" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999; margin: 0 !important;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-sm w-full p-6 text-center space-y-4 select-none relative">
            <div class="w-12 h-12 rounded-full bg-blue-50 dark:bg-blue-950/40 text-blue-650 flex items-center justify-center text-xl mx-auto animate-bounce">
                🔄
            </div>
            <div class="space-y-1">
                <h3 class="font-outfit font-black text-slate-850 dark:text-slate-100 text-sm uppercase tracking-tight">Substituindo Arquivo...</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block truncate max-w-xs mx-auto" x-text="replaceFileName"></p>
            </div>
            <div class="space-y-2">
                <div class="w-full bg-slate-100 dark:bg-slate-800 h-2 rounded-full overflow-hidden">
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-150" :style="'width: ' + replacePercentage + '%'"></div>
                </div>
                <div class="text-xs font-bold text-blue-600 dark:text-blue-450" x-text="replacePercentage + '%'"></div>
            </div>
        </div>
    </div>

</div>

<script>
    function fileManager() {
        return {
            showDeleteModal: false,
            deleteFileName: '',
            deleteActionUrl: '',
            
            // Replacement progress states
            isReplacing: false,
            replacePercentage: 0,
            replaceFileName: '',
            
            confirmDelete(fileName, actionUrl) {
                this.deleteFileName = fileName;
                this.deleteActionUrl = actionUrl;
                this.showDeleteModal = true;
            },

            replaceFile(e, actionUrl, fileName) {
                const file = e.target.files[0];
                if (!file) return;

                this.replaceFileName = fileName;
                this.isReplacing = true;
                this.replacePercentage = 0;

                const formData = new FormData();
                formData.append('file', file);

                const xhr = new XMLHttpRequest();
                xhr.open('POST', actionUrl, true);

                // CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                // Track upload progress
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        this.replacePercentage = Math.round((event.loaded / event.total) * 100);
                    }
                });

                xhr.onload = () => {
                    this.isReplacing = false;
                    this.replacePercentage = 0;

                    if (xhr.status >= 200 && xhr.status < 300) {
                        window.location.reload();
                    } else {
                        alert('Erro ao substituir arquivo. Por favor, tente novamente.');
                    }
                };

                xhr.onerror = () => {
                    this.isReplacing = false;
                    this.replacePercentage = 0;
                    alert('Houve um erro de conexão ao realizar o upload.');
                };

                xhr.send(formData);
            }
        }
    }

    function fileUploadManager() {
        return {
            dragover: false,
            selectedFiles: [],
            isUploading: false,
            uploadPercentage: 0,
            folderName: '',

            handleFileSelect(e) {
                this.addFiles(e.target.files);
            },

            handleDrop(e) {
                this.dragover = false;
                if (e.dataTransfer.files) {
                    this.addFiles(e.dataTransfer.files);
                }
            },

            addFiles(fileList) {
                Array.from(fileList).forEach(file => {
                    const isImage = file.type.startsWith('image/');
                    const previewUrl = isImage ? URL.createObjectURL(file) : null;
                    this.selectedFiles.push({
                        fileObject: file,
                        name: file.name,
                        size: file.size,
                        isImage: isImage,
                        previewUrl: previewUrl
                    });
                });
            },

            removeFile(index) {
                const file = this.selectedFiles[index];
                if (file.previewUrl) {
                    URL.revokeObjectURL(file.previewUrl);
                }
                this.selectedFiles.splice(index, 1);
            },

            submitForm(e) {
                if (this.selectedFiles.length === 0) return;
                
                this.isUploading = true;
                this.uploadPercentage = 0;

                const formData = new FormData();
                formData.append('folder_name', this.folderName);
                
                // Append files
                this.selectedFiles.forEach(file => {
                    formData.append('files[]', file.fileObject);
                });

                const xhr = new XMLHttpRequest();
                xhr.open('POST', "{{ route('revisoes.rounds.upload', $round->id) }}", true);
                
                // CSRF Token
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                // Progress event listener
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        this.uploadPercentage = Math.round((event.loaded / event.total) * 100);
                    }
                });

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        // Success - reload
                        window.location.reload();
                    } else {
                        alert('Erro ao enviar arquivos. Por favor, tente novamente.');
                        this.isUploading = false;
                    }
                };

                xhr.onerror = () => {
                    alert('Erro de conexão ao enviar arquivos.');
                    this.isUploading = false;
                };

                xhr.send(formData);
            }
        }
    }
</script>
@endsection
