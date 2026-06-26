@extends('layouts.app')

@section('title', 'Editar Autor - Gestor de Freelas')
@section('page_title', 'Editar Autor')

@section('content')
<div x-data="authorEditForm()" class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('authors.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Grid Principal: Formulário + Preview (Mobile First) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Formulário de Edição (Esquerda) -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 lg:col-span-2">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Editar Autor</h2>
                <p class="text-xs text-slate-400 mt-1">Altere as informações abaixo para atualizar os dados cadastrais do autor.</p>
            </div>

            <form action="{{ route('authors.update', $author->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Nome, Email, Phone, Documento e Bio -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Nome Completo -->
                    <div class="space-y-1.5 col-span-1 sm:col-span-2">
                        <label for="name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome Completo</label>
                        <input type="text" name="name" id="name" required x-model="name" placeholder="Ex: Machado de Assis" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        @error('name')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- E-mail -->
                    <div class="space-y-1.5">
                        <label for="email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                        <input type="email" name="email" id="email" required x-model="email" placeholder="autor@exemplo.com" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        @error('email')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Telefone ou WhatsApp -->
                    <div class="space-y-1.5">
                        <label for="phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone ou WhatsApp</label>
                        <input type="text" name="phone" id="phone" x-model="phone" x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'" placeholder="Ex: (11) 99999-9999" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        @error('phone')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- CPF / CNPJ / Documento -->
                    <div class="space-y-1.5">
                        <label for="document" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Documento (CPF/CNPJ)</label>
                        <input type="text" name="document" id="document" x-model="document" x-mask:dynamic="$input.replace(/\D/g, '').length > 11 ? '99.999.999/9999-99' : '999.999.999-99'" placeholder="Ex: 000.000.000-00" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        @error('document')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Biografia -->
                    <div class="space-y-1.5 col-span-1 sm:col-span-2">
                        <label for="bio" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Biografia</label>
                        <textarea name="bio" id="bio" rows="4" x-model="bio" placeholder="Escreva a biografia ou descrição do autor..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-none"></textarea>
                        @error('bio')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Foto do Autor (Upload com Drag-and-Drop) -->
                <div class="space-y-3">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Foto do Autor</span>
                    <div class="flex flex-col sm:flex-row items-center gap-6 p-4 border border-dashed border-slate-200 rounded-[5px] bg-slate-50/50 hover:bg-slate-50 transition-colors">
                        
                        <!-- Drag-and-Drop circular zone -->
                        <div class="relative w-24 h-24 rounded-full border-2 transition-all duration-200 bg-white shadow-inner flex items-center justify-center shrink-0 cursor-pointer overflow-hidden group select-none"
                             @click="triggerUpload()"
                             @dragover.prevent="dragOver = true"
                             @dragleave.prevent="dragOver = false"
                             @drop.prevent="handleDrop($event)"
                             :class="dragOver ? 'border-primary-500 scale-105 ring-4 ring-primary-500/10' : 'border-slate-200'">
                            
                            <template x-if="avatarUrl">
                                <img :src="avatarUrl" class="w-full h-full object-cover">
                            </template>
                            
                            <template x-if="!avatarUrl">
                                <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                            </template>

                            <!-- Hover Overlay -->
                            <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-[10px] font-semibold text-center p-1"
                                 :class="dragOver ? 'opacity-100 bg-slate-900/60' : ''">
                                <span x-show="!dragOver">Alterar Foto</span>
                                <span x-show="dragOver" class="text-white animate-pulse">Solte aqui</span>
                            </div>
                        </div>

                        <!-- Texto Instrucional & Botão Tradicional -->
                        <div class="space-y-1.5 text-center sm:text-left flex-1 w-full">
                            <button type="button" @click="triggerUpload()" class="px-3.5 py-1.5 border border-slate-200 text-slate-700 text-xs font-semibold rounded-[5px] hover:bg-white bg-slate-50 transition-colors shadow-sm inline-block">
                                Selecionar Imagem
                            </button>
                            <p class="text-xs text-slate-500" x-text="avatarName || 'Arraste e solte o arquivo de imagem aqui (PNG, JPG, WEBP). Máx: 2MB'"></p>
                        </div>
                        
                        <input type="file" name="avatar" x-ref="avatarInput" @change="previewAvatar" class="hidden" accept="image/*">
                    </div>
                    @error('avatar')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('authors.index') }}" class="px-6 py-3 border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 transition-colors font-semibold rounded-[5px] text-sm">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-[5px] text-sm transition-colors shadow-sm">
                        Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Card (Direita - Sticky no Desktop - Modo Claro) -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-6 space-y-6 lg:sticky lg:top-6 lg:col-span-1 w-full shadow-sm">
            <div>
                <span class="text-[10px] font-bold tracking-wider text-primary-600 uppercase">Visualização em Tempo Real</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Preview de Edição</h4>
            </div>

            <!-- Card de Visualização do Autor (Modo Claro) -->
            <div class="bg-slate-50 border border-slate-200 rounded-[5px] p-5 space-y-5">
                <div class="flex items-center gap-4">
                    <!-- Avatar Preview -->
                    <div class="w-16 h-16 rounded-full bg-white border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-sm">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarUrl">
                            <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </template>
                    </div>

                    <div class="min-w-0">
                        <h5 class="font-bold text-slate-900 text-base truncate" x-text="name || 'Nome do Autor'"></h5>
                        <p class="text-xs text-primary-600 truncate" x-text="email || 'email@exemplo.com'"></p>
                    </div>
                </div>

                <div class="space-y-2.5 pt-4 border-t border-slate-200 text-xs">
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">WhatsApp / Telefone</span>
                        <span class="text-slate-800 font-semibold truncate" x-text="phone || 'Não informado'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500 font-medium">Documento</span>
                        <span class="text-slate-800 font-semibold truncate" x-text="document || 'Não informado'"></span>
                    </div>
                    <!-- Biografia -->
                    <div class="pt-2">
                        <span class="text-slate-500 font-medium block mb-1">Biografia</span>
                        <p class="text-slate-700 bg-white p-2.5 rounded-[5px] border border-slate-100 font-normal leading-relaxed text-justify min-h-[64px] whitespace-pre-wrap break-words" x-text="bio || 'Nenhuma biografia informada.'"></p>
                    </div>
                </div>
            </div>
            
            <p class="text-[10px] text-slate-400 text-center">Este card simula o visual do perfil comercial do autor na listagem.</p>
        </div>

    </div>

</div>

<script>
    function authorEditForm() {
        return {
            name: @json($author->name),
            email: @json($author->email),
            phone: @json($author->phone),
            document: @json($author->document),
            bio: @json($author->bio ?? ''),
            avatarUrl: @json($author->avatar ? asset("storage/" . $author->avatar) : ""),
            avatarName: @json($author->avatar ? basename($author->avatar) : ""),
            dragOver: false,

            triggerUpload() {
                this.$refs.avatarInput.click();
            },

            previewAvatar(event) {
                const file = event.target.files[0];
                if (file) {
                    this.avatarName = file.name;
                    this.avatarUrl = URL.createObjectURL(file);
                }
            },

            handleDrop(event) {
                this.dragOver = false;
                const files = event.dataTransfer.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        this.$refs.avatarInput.files = dataTransfer.files;
                        this.avatarName = file.name;
                        this.avatarUrl = URL.createObjectURL(file);
                    }
                }
            }
        }
    }
</script>
@endsection
