@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="editorialCreateForm()">

    <!-- Banner Superior com Navegação -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-[5px] shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="text-2xl">✍️</span>
                <h2 class="text-xl font-black font-outfit text-slate-800 dark:text-slate-100 uppercase tracking-tight">Nova Revisão Editorial</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Envie os arquivos brutos do autor (Word, PDF, Imagens) e atribua o revisor responsável.
            </p>
        </div>

        <a href="{{ route('revisoes-editoriais.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-[5px] transition-colors text-center uppercase tracking-wider">
            ← Voltar às Revisões
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Coluna Esquerda / Central: Formulário -->
        <div class="lg:col-span-2 space-y-6">
            <form action="{{ route('revisoes-editoriais.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-6">
                @csrf

                <!-- Título do Projeto -->
                <div class="space-y-1.5">
                    <label for="title" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Título da Revisão Editorial <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" id="title" required x-model="title" placeholder="Ex: Revisão Textual - Livro Motricidade Orofacial" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-medium">
                    @error('title')
                        <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Atribuição de Revisor (Com Opção de Cadastro Rápido) -->
                <div class="space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <div class="flex items-center justify-between">
                        <label for="revisor_id" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Revisor Responsável</label>
                        <button type="button" @click="openNewRevisorModal = true" class="text-xs font-bold text-primary-600 dark:text-primary-400 hover:underline flex items-center gap-1 cursor-pointer">
                            ➕ Cadastrar Novo Revisor
                        </button>
                    </div>
                    
                    <select name="revisor_id" id="revisor_id" x-model="selectedRevisorId" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-medium">
                        <option value="">-- Selecione o Revisor (ou deixe para atribuir depois) --</option>
                        @foreach($revisores as $rev)
                            <option value="{{ $rev->id }}">{{ $rev->name }} ({{ $rev->email }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Seleção de Arquivos (Drag and Drop) -->
                <div class="space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Arquivos do Autor (Word, PDF, Imagens) <span class="text-rose-500">*</span></label>

                    <div class="border-2 border-dashed rounded-[5px] p-8 text-center transition-all cursor-pointer relative"
                         :class="isDragging ? 'border-primary-500 bg-primary-50/20' : 'border-slate-250 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/30 hover:bg-slate-100 dark:hover:bg-slate-800/50'"
                         @dragover.prevent="isDragging = true"
                         @dragleave.prevent="isDragging = false"
                         @drop.prevent="handleDrop">
                        
                        <input type="file" name="files[]" multiple x-ref="fileInput" @change="handleFileChange" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept=".doc,.docx,.pdf,.jpg,.jpeg,.png,.txt,.rtf,.odt">
                        
                        <div class="space-y-2 pointer-events-none">
                            <span class="text-4xl block">📂</span>
                            <p class="text-sm font-bold text-slate-700 dark:text-slate-200">Arraste seus arquivos Word, PDF ou Imagens aqui</p>
                            <p class="text-xs text-slate-400">ou clique para navegar no seu computador</p>
                        </div>
                    </div>

                    <!-- Lista de Arquivos Selecionados -->
                    <template x-if="filesList.length > 0">
                        <div class="space-y-2 pt-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Arquivos Adicionados (<span x-text="filesList.length"></span>):</span>
                            <div class="space-y-1.5 max-h-48 overflow-y-auto">
                                <template x-for="(file, index) in filesList" :key="index">
                                    <div class="flex items-center justify-between p-2.5 bg-slate-50 dark:bg-slate-800 rounded-[5px] border border-slate-200 dark:border-slate-700 text-xs">
                                        <div class="flex items-center gap-2 truncate pr-2">
                                            <span>📄</span>
                                            <span class="font-bold text-slate-700 dark:text-slate-200 truncate" x-text="file.name"></span>
                                            <span class="text-[10px] text-slate-400 font-semibold shrink-0" x-text="file.formattedSize"></span>
                                        </div>
                                        <button type="button" @click="removeFile(index)" class="text-rose-500 hover:text-rose-700 font-bold p-1">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Seleção do Local de Armazenamento -->
                <div class="space-y-2 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <label class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">Onde Salvar os Arquivos</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Opção 1: Google Drive (5 TB) -->
                        <label class="relative flex items-center p-3 rounded-[5px] border cursor-pointer transition-all {{ $isGoogleConnected ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/50 opacity-60' }}">
                            <input type="radio" name="storage_disk" value="google" {{ $isGoogleConnected ? 'checked' : 'disabled' }} class="text-emerald-600 focus:ring-emerald-500 shrink-0">
                            <div class="ml-3 flex items-center gap-2">
                                <span class="text-lg">☁️</span>
                                <div>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Google Drive (5 TB)</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">
                                        {{ $isGoogleConnected ? 'Recomendado - 0 MB usados no servidor' : 'Não conectado' }}
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Opção 2: Servidor Local (Hostinger) -->
                        <label class="relative flex items-center p-3 rounded-[5px] border border-slate-200 dark:border-slate-800 cursor-pointer transition-all bg-white dark:bg-slate-900 hover:bg-slate-50">
                            <input type="radio" name="storage_disk" value="public" {{ !$isGoogleConnected ? 'checked' : '' }} class="text-slate-600 focus:ring-slate-500 shrink-0">
                            <div class="ml-3 flex items-center gap-2">
                                <span class="text-lg">💾</span>
                                <div>
                                    <span class="text-xs font-extrabold text-slate-800 dark:text-slate-100 block">Servidor Local (Hostinger)</span>
                                    <span class="text-[10px] text-slate-500 dark:text-slate-400">Armazena no disco local do servidor</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Prazo de Entrega -->
                <div class="space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <label for="deadline_at" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prazo Final para Entrega da Revisão (Opcional)</label>
                    <input type="date" name="deadline_at" id="deadline_at" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-medium">
                </div>

                <!-- Descrição / Orientações -->
                <div class="space-y-1.5 border-t border-slate-100 dark:border-slate-800 pt-4">
                    <label for="description" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Instruções / Notas Gerais para o Revisor (Opcional)</label>
                    <textarea name="description" id="description" rows="3" placeholder="Ex: Verificar padronização dos termos científicos do Capítulo 2 e atenção especial à bibliografia..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 font-medium"></textarea>
                </div>

                <!-- Botão de Envio -->
                <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-end gap-3">
                    <a href="{{ route('revisoes-editoriais.index') }}" class="px-6 py-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors text-center">
                        Cancelar
                    </a>
                    <button type="submit" class="px-8 py-3 bg-slate-900 dark:bg-slate-100 hover:bg-slate-800 dark:hover:bg-white text-white dark:text-slate-900 text-xs font-bold rounded-[5px] uppercase tracking-wider transition-all shadow-sm flex items-center gap-1.5 cursor-pointer">
                        ⚡ Criar Projeto de Revisão
                    </button>
                </div>

            </form>
        </div>

        <!-- Coluna Direita: Dicas / Instruções -->
        <div class="space-y-6">
            <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-lg uppercase tracking-tight">Fluxo da Revisão Editorial</h4>

            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                <div class="space-y-3">
                    <div class="flex items-start gap-2.5">
                        <span class="text-base shrink-0">1️⃣</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800 dark:text-slate-100">Upload dos Brutos</h5>
                            <p class="mt-0.5">Envie os documentos Word, PDFs originais ou imagens dos manuscritos.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-base shrink-0">2️⃣</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800 dark:text-slate-100">Trabalho do Revisor</h5>
                            <p class="mt-0.5">O revisor analisa o conteúdo, cria apontamentos por categoria e envia dúvidas para o autor.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100 dark:border-slate-800">
                        <span class="text-base shrink-0">3️⃣</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800 dark:text-slate-100">Exportação & Diagramação</h5>
                            <p class="mt-0.5">Após sanar as dúvidas, exporte o texto revisado e inicie a diagramação do livro!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal: Cadastro Rápido de Revisor -->
    <div x-show="openNewRevisorModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openNewRevisorModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase tracking-tight">➕ Cadastrar Novo Revisor</h3>
                <button type="button" @click="openNewRevisorModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <div class="space-y-3 text-xs">
                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nome do Revisor</label>
                    <input type="text" x-model="newRevisorName" placeholder="Ex: Maria Silva (Revisora)" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">E-mail de Acesso</label>
                    <input type="email" x-model="newRevisorEmail" placeholder="revisora@exemplo.com" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Senha de Acesso</label>
                    <input type="text" x-model="newRevisorPassword" placeholder="Defina uma senha..." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                <button type="button" @click="openNewRevisorModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-[5px]">Cancelar</button>
                <button type="button" @click="confirmCreateRevisor" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Salvar Revisor</button>
            </div>
        </div>
    </div>

    <!-- Modal de Alerta / Erro Customizado -->
    <div x-show="errorMessage" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="errorMessage = ''" class="bg-white border border-slate-200 text-slate-800 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    ⚠️
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Atenção no Envio</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Ocorreu um imprevisto ao processar o formulário</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-[5px] border border-slate-100" x-text="errorMessage"></p>
            <div class="flex justify-end pt-1">
                <button type="button" @click="errorMessage = ''" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-[5px]">Entendido</button>
            </div>
        </div>
    </div>

</div>

<script>
    function editorialCreateForm() {
        return {
            isDragging: false,
            originalFiles: [],
            filesList: [],
            title: '',
            selectedRevisorId: '',
            errorMessage: '',
            
            // New Revisor Modal State
            openNewRevisorModal: false,
            newRevisorName: '',
            newRevisorEmail: '',
            newRevisorPassword: 'revisao' + Math.floor(1000 + Math.random() * 9000),

            confirmCreateRevisor() {
                if (!this.newRevisorName || !this.newRevisorEmail || !this.newRevisorPassword) {
                    alert('Por favor, preencha nome, e-mail e senha para o novo revisor.');
                    return;
                }
                this.openNewRevisorModal = false;
                alert('O novo revisor será cadastrado e associado assim que você enviar o formulário!');
            },

            handleFileChange(e) {
                const files = e.target.files;
                this.addFiles(files);
            },

            handleDrop(e) {
                this.isDragging = false;
                const files = e.dataTransfer.files;
                this.addFiles(files);
            },

            addFiles(files) {
                for (let i = 0; i < files.length; i++) {
                    const file = files[i];
                    if (!this.originalFiles.some(f => f.name === file.name && f.size === file.size)) {
                        this.originalFiles.push(file);
                        this.filesList.push({
                            name: file.name,
                            size: file.size,
                            formattedSize: this.formatBytes(file.size)
                        });
                    }
                }
                this.syncFileInput();
            },

            removeFile(index) {
                this.originalFiles.splice(index, 1);
                this.filesList.splice(index, 1);
                this.syncFileInput();
            },

            syncFileInput() {
                const dt = new DataTransfer();
                this.originalFiles.forEach(file => {
                    dt.items.add(file);
                });
                this.$refs.fileInput.files = dt.files;
            },

            submitForm(e) {
                if (this.originalFiles.length === 0) {
                    this.errorMessage = 'Por favor, selecione pelo menos um arquivo (Word, PDF ou Imagem) antes de salvar.';
                    return;
                }

                const form = e.target;
                const formData = new FormData(form);

                formData.delete('files[]');
                this.originalFiles.forEach(file => {
                    formData.append('files[]', file);
                });

                if (this.newRevisorName && this.newRevisorEmail) {
                    formData.append('create_new_revisor', '1');
                    formData.append('new_revisor_name', this.newRevisorName);
                    formData.append('new_revisor_email', this.newRevisorEmail);
                    formData.append('new_revisor_password', this.newRevisorPassword);
                }

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            window.location.href = res.redirect_url || "{{ route('revisoes-editoriais.index') }}";
                        } catch (e) {
                            window.location.href = "{{ route('revisoes-editoriais.index') }}";
                        }
                    } else {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.errors) {
                                this.errorMessage = Object.values(res.errors).flat().join(' ');
                            } else {
                                this.errorMessage = res.message || 'Falha ao criar o projeto de revisão.';
                            }
                        } catch (e) {
                            this.errorMessage = 'Ocorreu um erro no servidor ao processar o upload.';
                        }
                    }
                };

                xhr.onerror = () => {
                    this.errorMessage = 'Erro de conexão ao enviar os arquivos.';
                };

                xhr.send(formData);
            },

            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
            }
        }
    }
</script>
@endsection
