@extends('layouts.app')

@section('title', 'Novo Compartilhamento - Gestor de Freelas')
@section('page_title', 'Novo Compartilhamento')

@section('content')
<div x-data="shareCreateForm()" class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes.shares.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Formulário de Upload e Detalhes (Esquerda) -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 lg:col-span-2">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Enviar e Compartilhar Arquivos</h2>
                <p class="text-xs text-slate-400 mt-1">Selecione ou arraste múltiplos arquivos de qualquer formato. O limite total acumulado é de até 1GB.</p>
            </div>

            <form action="{{ route('revisoes.shares.store') }}" method="POST" enctype="multipart/form-data" @submit.prevent="submitForm($event)" class="space-y-6">
                @csrf
                
                <!-- Input de arquivos oculto -->
                <input type="file" name="files[]" multiple x-ref="fileInput" @change="handleFileChange($event)" class="hidden" />

                <!-- Zona de Drag and Drop -->
                <div 
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="handleDrop($event)"
                    @click="$refs.fileInput.click()"
                    class="border-2 border-dashed rounded-[5px] p-8 text-center cursor-pointer transition-all duration-200 select-none flex flex-col items-center justify-center min-h-[220px]"
                    :class="isDragging ? 'border-primary-500 bg-primary-50/10 dark:bg-primary-950/20 ring-4 ring-primary-500/10 dark:ring-primary-400/20 scale-[1.01]' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50/50 dark:hover:bg-slate-800/40'"
                >
                    <span class="text-4xl mb-3">📁</span>
                    <h3 class="font-extrabold text-sm text-slate-700">Arraste seus arquivos para cá</h3>
                    <p class="text-xs text-slate-400 mt-1.5 max-w-sm leading-relaxed">Ou clique para navegar pelo seu computador. Formatos aceitos: imagens, PDFs, vídeos, ZIPs, etc (máx. 1GB).</p>
                    
                    <!-- Indicador de progresso se necessário -->
                    <div class="mt-4 text-xs font-bold text-primary-600 bg-primary-50 px-3 py-1 rounded-full uppercase tracking-wider" x-show="originalFiles.length > 0">
                        <span x-text="originalFiles.length"></span> arquivo(s) selecionado(s)
                    </div>
                </div>

                @error('files')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror

                <!-- Lista de arquivos selecionados -->
                <template x-if="filesList.length > 0">
                    <div class="space-y-2 border border-slate-150 dark:border-slate-800 rounded-[5px] p-4 bg-slate-50/50 dark:bg-slate-800/40">
                        <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-700 pb-2 mb-2">
                            <span class="text-xs font-black text-slate-500 uppercase tracking-wider">Lista de Arquivos</span>
                            <span class="text-xs font-bold text-slate-650 dark:text-slate-400" :class="totalSize > 1024*1024*1024 ? 'text-red-600 font-black' : ''">
                                Tamanho Total: <span x-text="formatBytes(totalSize)"></span> / 1 GB
                            </span>
                        </div>
                        <div class="max-h-[200px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800 pr-1.5">
                            <template x-for="(file, index) in filesList" :key="index">
                                <div class="py-2.5 flex items-center justify-between text-xs gap-3">
                                    <div class="min-w-0 flex items-center gap-2">
                                        <span class="shrink-0 text-lg">📄</span>
                                        <span class="font-bold text-slate-700 dark:text-slate-200 truncate" :title="file.name" x-text="file.name"></span>
                                    </div>
                                    <div class="flex items-center gap-3 shrink-0">
                                        <span class="text-slate-400 font-semibold" x-text="file.formattedSize"></span>
                                        <button type="button" @click.stop="removeFile(index)" class="text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/30 p-1 rounded-[5px] transition-colors" title="Remover">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Configurações do Lançamento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    
                    <!-- Nome do Compartilhamento -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="title" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Nome da Transferência / Título do Link</label>
                        <input type="text" name="title" id="title" x-model="title" placeholder="Ex: Campanha Publicitária de Natal" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        <p class="text-[10px] text-slate-400">Pegará automaticamente o nome do primeiro arquivo caso não seja informado.</p>
                        @error('title')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Slider Dias de Expiração (1 a 30 dias) -->
                    <div class="space-y-2">
                        <div class="flex justify-between items-center">
                            <label for="expires_days" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prazo de Expiração</label>
                            <span class="text-xs font-black text-primary-600 dark:text-primary-400 uppercase" x-text="expiresDays + (expiresDays == 1 ? ' Dia' : ' Dias')">7 Dias</span>
                        </div>
                        <input type="range" name="expires_days" id="expires_days" min="1" max="30" x-model="expiresDays" class="w-full h-2 bg-slate-100 dark:bg-slate-800 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                        <p class="text-[10px] text-slate-400">O link deixará de funcionar automaticamente após o período selecionado.</p>
                        @error('expires_days')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Limite de Downloads -->
                    <div class="space-y-1.5">
                        <label for="download_limit" class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Limite de Downloads</label>
                        <input type="number" name="download_limit" id="download_limit" placeholder="Ex: 5 (vazio para ilimitado)" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                        <p class="text-[10px] text-slate-400">Deixe em branco se quiser permitir downloads ilimitados.</p>
                        @error('download_limit')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Senha de Acesso (Segurança) -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <div class="flex items-center gap-2 mb-1">
                            <input type="checkbox" name="has_password" value="1" id="has_password" x-model="hasPassword" class="rounded text-primary-600 border-slate-350 dark:border-slate-700 focus:ring-primary-500/20 w-4 h-4 cursor-pointer" />
                            <label for="has_password" class="text-xs font-semibold text-slate-700 dark:text-slate-300 cursor-pointer select-none">Proteger link com Senha de Segurança</label>
                        </div>
                        <div x-show="hasPassword" x-transition>
                            <input type="password" name="password" id="password" placeholder="Defina a senha de segurança..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
                            <p class="text-[10px] text-slate-400 mt-1">Apenas as pessoas que possuírem essa senha conseguirão acessar e baixar os arquivos.</p>
                        </div>
                        @error('password')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Descrição da Transferência -->
                    <div class="space-y-1.5 sm:col-span-2">
                        <label for="description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Descrição / Nota Interna (Opcional)</label>
                        <textarea name="description" id="description" rows="3" placeholder="Insira alguma instrução, descrição do que contêm os arquivos ou notas adicionais..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400"></textarea>
                        @error('description')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Seleção do Local de Armazenamento -->
                    @php
                        $isGoogleConnected = !empty(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
                    @endphp

                    <div class="space-y-2 sm:col-span-2 border-t border-slate-100 pt-3">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Onde Salvar os Arquivos</label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Opção 1: Google Drive (5 TB) -->
                            <label class="relative flex items-center p-3 rounded-[5px] border cursor-pointer transition-all {{ $isGoogleConnected ? 'border-emerald-300 bg-emerald-50/20' : 'border-slate-200 bg-slate-50/50 opacity-60' }}">
                                <input type="radio" name="storage_disk" value="google" {{ $isGoogleConnected ? 'checked' : 'disabled' }} class="text-emerald-600 focus:ring-emerald-500 shrink-0">
                                <div class="ml-3 flex items-center gap-2">
                                    <span class="text-lg">☁️</span>
                                    <div>
                                        <span class="text-xs font-extrabold text-slate-800 block">Google Drive (5 TB)</span>
                                        <span class="text-[10px] text-slate-500">
                                            {{ $isGoogleConnected ? 'Recomendado - 0 MB usados na hospedagem' : 'Não conectado (conecte no painel)' }}
                                        </span>
                                    </div>
                                </div>
                            </label>

                            <!-- Opção 2: Servidor Local (Hostinger) -->
                            <label class="relative flex items-center p-3 rounded-[5px] border border-slate-200 cursor-pointer transition-all bg-white hover:bg-slate-50">
                                <input type="radio" name="storage_disk" value="public" {{ !$isGoogleConnected ? 'checked' : '' }} class="text-slate-600 focus:ring-slate-500 shrink-0">
                                <div class="ml-3 flex items-center gap-2">
                                    <span class="text-lg">💾</span>
                                    <div>
                                        <span class="text-xs font-extrabold text-slate-800 block">Servidor Local (Hostinger)</span>
                                        <span class="text-[10px] text-slate-500">Armazena no disco local do servidor</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Botão de Ação -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('revisoes.shares.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors text-center">
                        Cancelar
                    </a>
                    <button type="submit" class="px-8 py-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] uppercase tracking-wider transition-all shadow-sm flex items-center gap-1.5">
                        ⚡ Gerar Link de Envio
                    </button>
                </div>

            </form>
        </div>

        <!-- Coluna Direita: Instruções / Dicas de Compartilhamento -->
        <div class="space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Regras de Compartilhamento</h4>
            
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4 text-xs leading-relaxed text-slate-500">
                <div class="space-y-3">
                    <div class="flex items-start gap-2.5">
                        <span class="text-base shrink-0">📦</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800">Tamanho Limite</h5>
                            <p class="mt-0.5">O tamanho total somado de todos os arquivos em um único link não pode passar de 1GB (1024 MB).</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100">
                        <span class="text-base shrink-0">⏱️</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800">Expiração Automática</h5>
                            <p class="mt-0.5">Os prazos de validade do link contam dias corridos. Ao expirar, os arquivos continuam armazenados mas o acesso público fica bloqueado.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100">
                        <span class="text-base shrink-0">🔒</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800">Criptografia e Senha</h5>
                            <p class="mt-0.5">Ao definir uma senha, os downloads serão protegidos. Ideal para enviar documentos e materiais confidenciais de marcas.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-2.5 pt-2 border-t border-slate-100">
                        <span class="text-base shrink-0">📥</span>
                        <div>
                            <h5 class="font-extrabold text-slate-800">Download em ZIP</h5>
                            <p class="mt-0.5">A página pública oferece automaticamente um botão para que o cliente baixe um único arquivo empacotado compactado contendo tudo.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Modal de Progresso de Upload Premium -->
    <div x-show="isUploading" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999; margin: 0 !important;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-sm w-full p-8 text-center space-y-6 select-none relative overflow-hidden">
            <!-- Animated Background Glow -->
            <div class="absolute -right-16 -top-16 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl"></div>
            <div class="absolute -left-16 -bottom-16 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl"></div>

            <div class="space-y-2">
                <span class="text-4xl animate-bounce inline-block">⚡</span>
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-lg uppercase tracking-tight">Enviando Arquivos...</h3>
                <p class="text-xs text-slate-400">Por favor, não feche ou recarregue esta página.</p>
            </div>

            <!-- Ring or Bar Progress -->
            <div class="relative pt-1">
                <div class="flex mb-2 items-center justify-between">
                    <div>
                        <span class="text-[10px] font-black inline-block py-1 px-2.5 uppercase rounded-full text-blue-600 bg-blue-50 dark:bg-blue-950 dark:text-blue-300 tracking-wider">
                            Progresso
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-sm font-black text-blue-600 dark:text-blue-400" x-text="uploadProgress + '%'"></span>
                    </div>
                </div>
                <div class="overflow-hidden h-2.5 text-xs flex rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-150 dark:border-slate-700">
                    <div :style="'width: ' + uploadProgress + '%'" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-150 rounded-full"></div>
                </div>
            </div>

            <!-- Upload Statistics -->
            <div class="grid grid-cols-2 gap-4 text-left border-t border-slate-100 pt-4">
                <div class="space-y-0.5">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Velocidade</span>
                    <span class="text-xs font-black text-slate-700" x-text="uploadSpeedFormatted">0 B/s</span>
                </div>
                <div class="space-y-0.5">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Restante</span>
                    <span class="text-xs font-black text-slate-750" x-text="timeRemainingFormatted || 'Calculando...'">Calculando...</span>
                </div>
                <div class="space-y-0.5 col-span-2">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Enviado</span>
                    <span class="text-xs font-black text-slate-700">
                        <span x-text="uploadedBytesFormatted"></span> / <span x-text="totalBytesFormatted"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Alerta / Erro Customizado -->
    <div x-show="errorMessage" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="errorMessage = ''" class="bg-white border border-slate-200 text-slate-800 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4 animate-in fade-in zoom-in-95 duration-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-rose-50 border border-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Atenção no Upload</h3>
                    <p class="text-xs text-slate-500 font-medium mt-0.5">Ocorreu um imprevisto ao enviar os arquivos</p>
                </div>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-[5px] border border-slate-100" x-text="errorMessage"></p>
            <div class="flex justify-end pt-1">
                <button type="button" @click="errorMessage = ''" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors cursor-pointer">
                    Entendido
                </button>
            </div>
        </div>
    </div>

</div>

<script>
    function shareCreateForm() {
        return {
            isDragging: false,
            originalFiles: [],
            filesList: [],
            totalSize: 0,
            title: '',
            expiresDays: 7,
            hasPassword: false,
            errorMessage: '',
            
            // Upload progress states
            isUploading: false,
            uploadProgress: 0,
            uploadedBytesFormatted: '0 Bytes',
            totalBytesFormatted: '0 Bytes',
            uploadSpeedFormatted: '0 B/s',
            timeRemainingFormatted: '',

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
                        this.totalSize += file.size;
                    }
                }
                this.syncFileInput();
                this.autoGuessTitle();
            },

            removeFile(index) {
                this.totalSize -= this.originalFiles[index].size;
                this.originalFiles.splice(index, 1);
                this.filesList.splice(index, 1);
                this.syncFileInput();
                this.autoGuessTitle();
            },

            syncFileInput() {
                const dt = new DataTransfer();
                this.originalFiles.forEach(file => {
                    dt.items.add(file);
                });
                this.$refs.fileInput.files = dt.files;
            },

            autoGuessTitle() {
                if (!this.title || this.title.startsWith('Transferência de') || this.originalFiles.length <= 1) {
                    if (this.originalFiles.length === 1) {
                        const name = this.originalFiles[0].name;
                        const nameWithoutExt = name.substring(0, name.lastIndexOf('.')) || name;
                        this.title = nameWithoutExt;
                    } else if (this.originalFiles.length > 1) {
                        this.title = 'Transferência de ' + this.originalFiles.length + ' arquivos';
                    } else {
                        this.title = '';
                    }
                }
            },

            submitForm(e) {
                if (this.originalFiles.length === 0) {
                    this.errorMessage = 'Por favor, selecione pelo menos um arquivo antes de compartilhar.';
                    return;
                }
                if (this.totalSize > 1024 * 1024 * 1024) {
                    this.errorMessage = 'O tamanho total dos arquivos excede o limite permitido de 1GB.';
                    return;
                }

                this.isUploading = true;
                this.uploadProgress = 0;

                const form = e.target;
                const formData = new FormData(form);

                // Garante que se o checkbox de senha não estiver ativo, a senha não seja enviada
                if (!this.hasPassword) {
                    formData.delete('password');
                    formData.delete('has_password');
                }

                // Garante sincronização de arquivos no FormData
                formData.delete('files[]');
                this.originalFiles.forEach(file => {
                    formData.append('files[]', file);
                });

                const xhr = new XMLHttpRequest();
                xhr.open('POST', form.action, true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                const startTime = Date.now();

                // Monitor de progresso do upload
                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        const loaded = event.loaded;
                        const total = event.total;
                        this.uploadProgress = Math.round((loaded / total) * 100);

                        this.uploadedBytesFormatted = this.formatBytes(loaded);
                        this.totalBytesFormatted = this.formatBytes(total);

                        // Calcula velocidade de upload
                        const elapsedSeconds = (Date.now() - startTime) / 1000;
                        if (elapsedSeconds > 0) {
                            const speed = loaded / elapsedSeconds;
                            this.uploadSpeedFormatted = this.formatBytes(speed) + '/s';

                            // Calcula tempo restante estimado
                            const remainingBytes = total - loaded;
                            const remainingSeconds = remainingBytes / speed;
                            if (remainingSeconds > 60) {
                                this.timeRemainingFormatted = Math.ceil(remainingSeconds / 60) + ' min restante(s)';
                            } else {
                                this.timeRemainingFormatted = Math.ceil(remainingSeconds) + ' s restante(s)';
                            }
                        }
                    }
                });

                xhr.onload = () => {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        try {
                            const res = JSON.parse(xhr.responseText);
                            window.location.href = res.redirect_url || "{{ route('revisoes.shares.index') }}";
                        } catch (e) {
                            window.location.href = "{{ route('revisoes.shares.index') }}";
                        }
                    } else {
                        this.isUploading = false;
                        try {
                            const res = JSON.parse(xhr.responseText);
                            if (res.errors) {
                                const msgs = Object.values(res.errors).flat().join(' ');
                                this.errorMessage = msgs || 'Falha no processamento dos arquivos.';
                            } else if (res.message) {
                                this.errorMessage = res.message;
                            } else {
                                this.errorMessage = 'Ocorreu uma falha no upload (código HTTP ' + xhr.status + ').';
                            }
                        } catch (e) {
                            this.errorMessage = 'O tamanho acumulado dos uploads pode ter excedido os limites do PHP no servidor. Tente enviar arquivos menores ou em pacotes.';
                        }
                    }
                };

                xhr.onerror = () => {
                    this.isUploading = false;
                    this.errorMessage = 'Falha de conexão com o servidor ao enviar os arquivos.';
                };

                xhr.send(formData);
            },

            formatBytes(bytes, decimals = 2) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const dm = decimals < 0 ? 0 : decimals;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
            }
        }
    }
</script>
@endsection
