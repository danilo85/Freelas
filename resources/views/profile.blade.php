@extends('layouts.app')

@section('title', 'Meu Perfil - Gestor de Freelas')
@section('page_title', 'Configurações de Perfil')

@section('content')
<div class="max-w-3xl bg-white rounded-[5px] border border-slate-200 shadow-sm overflow-hidden">
    
    <div class="p-6 md:p-8 border-b border-slate-100 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Personalizar Perfil</h2>
            <p class="text-sm text-slate-400 mt-0.5">Altere suas informações de conta, senha e preferências visuais do dashboard.</p>
        </div>
    </div>

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8" x-data="profileForm()">
        @csrf
        @method('PUT')

        <!-- Foto de Perfil & Preferência de Tema -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 items-start">
            
            <!-- Avatar Upload com Alpine.js + Drag and Drop -->
            <div class="flex flex-col items-center space-y-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Foto de Perfil</span>
                
                <div class="relative group cursor-pointer select-none" 
                     @click="triggerUpload()"
                     @dragover.prevent="dragOver = true"
                     @dragleave.prevent="dragOver = false"
                     @drop.prevent="handleDrop($event)">
                    <!-- Círculo de Imagem do Avatar (permanece redondo por ser avatar circular tradicional) -->
                    <div class="w-32 h-32 rounded-full border-4 transition-colors duration-200 bg-slate-100 overflow-hidden shadow-inner flex items-center justify-center relative"
                         :class="dragOver ? 'border-primary-500 scale-105' : 'border-slate-100'">
                        <template x-if="avatarUrl">
                            <img :src="avatarUrl" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!avatarUrl">
                            <svg class="w-16 h-16 text-slate-300 group-hover:text-slate-400 transition-colors" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                            </svg>
                        </template>

                        <!-- Overlay Hover / Dragover -->
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-xs font-semibold rounded-full p-2 text-center"
                             :class="dragOver ? 'opacity-100 bg-slate-900/60' : ''">
                            <span x-show="!dragOver">Alterar Foto</span>
                            <span x-show="dragOver" class="text-white animate-pulse">Solte aqui</span>
                        </div>
                    </div>
                </div>

                <!-- Input File Oculto -->
                <input type="file" name="avatar" x-ref="avatarInput" @change="previewAvatar" class="hidden" accept="image/*">
                <p class="text-[10px] text-slate-400 text-center">Arraste e solte ou clique para enviar.<br>JPG, PNG, WEBP. Máx: 2MB</p>
                @error('avatar')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Logo Upload com Alpine.js + Drag and Drop -->
            <div class="flex flex-col items-center space-y-3">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Logotipo Pessoal</span>
                
                <div class="relative group cursor-pointer select-none w-full" 
                     @click="triggerLogoUpload()"
                     @dragover.prevent="dragOverLogo = true"
                     @dragleave.prevent="dragOverLogo = false"
                     @drop.prevent="handleLogoDrop($event)">
                    <!-- Retângulo de Imagem da Logo (mais apropriado para logos horizontais/quadrados) -->
                    <div class="w-full h-32 rounded-[5px] border-2 border-dashed transition-colors duration-200 bg-slate-50 flex items-center justify-center relative p-2"
                         :class="dragOverLogo ? 'border-primary-500 bg-slate-100 scale-102' : 'border-slate-200 hover:border-slate-350 bg-slate-50/50'">
                        <template x-if="logoUrl">
                            <img :src="logoUrl" class="max-w-full max-h-full object-contain">
                        </template>
                        <template x-if="!logoUrl">
                            <div class="flex flex-col items-center gap-1 text-center">
                                <span class="text-2xl text-slate-300">🖼️</span>
                                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Upload Logotipo</span>
                            </div>
                        </template>

                        <!-- Overlay Hover / Dragover -->
                        <div class="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center text-white text-xs font-semibold rounded-[5px] p-2 text-center"
                             :class="dragOverLogo ? 'opacity-100 bg-slate-900/60' : ''">
                            <span x-show="!dragOverLogo">Alterar Logo</span>
                            <span x-show="dragOverLogo" class="text-white animate-pulse">Solte aqui</span>
                        </div>
                    </div>
                </div>

                <!-- Input File Oculto -->
                <input type="file" name="logo" x-ref="logoInput" @change="previewLogo" class="hidden" accept="image/*">
                <p class="text-[10px] text-slate-400 text-center">Arraste e solte ou clique para enviar.<br>JPG, PNG, WEBP. Máx: 2MB</p>
                @error('logo')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Customização de Cores de Tema -->
            <div class="md:col-span-2 space-y-4">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tema Visual do Dashboard</span>
                
                <div class="grid grid-cols-5 gap-3">
                    <!-- Tema Verde -->
                    <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-green-500 border-green-500': selectedTheme === 'green'}">
                        <input type="radio" name="theme_color" value="green" x-model="selectedTheme" class="hidden">
                        <span class="w-6 h-6 rounded-full bg-green-500 shadow-sm"></span>
                        <span class="text-xs font-medium text-slate-600">Verde</span>
                    </label>

                    <!-- Tema Azul -->
                    <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-blue-500 border-blue-500': selectedTheme === 'blue'}">
                        <input type="radio" name="theme_color" value="blue" x-model="selectedTheme" class="hidden">
                        <span class="w-6 h-6 rounded-full bg-blue-500 shadow-sm"></span>
                        <span class="text-xs font-medium text-slate-600">Azul</span>
                    </label>

                    <!-- Tema Roxo -->
                    <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-purple-500 border-purple-500': selectedTheme === 'purple'}">
                        <input type="radio" name="theme_color" value="purple" x-model="selectedTheme" class="hidden">
                        <span class="w-6 h-6 rounded-full bg-purple-500 shadow-sm"></span>
                        <span class="text-xs font-medium text-slate-600">Roxo</span>
                    </label>

                    <!-- Tema Indigo -->
                    <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-indigo-500 border-indigo-500': selectedTheme === 'indigo'}">
                        <input type="radio" name="theme_color" value="indigo" x-model="selectedTheme" class="hidden">
                        <span class="w-6 h-6 rounded-full bg-indigo-500 shadow-sm"></span>
                        <span class="text-xs font-medium text-slate-600">Índigo</span>
                    </label>

                    <!-- Tema Laranja -->
                    <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-orange-500 border-orange-500': selectedTheme === 'orange'}">
                        <input type="radio" name="theme_color" value="orange" x-model="selectedTheme" class="hidden">
                        <span class="w-6 h-6 rounded-full bg-orange-500 shadow-sm"></span>
                        <span class="text-xs font-medium text-slate-600">Laranja</span>
                    </label>
                </div>
            </div>

        </div>

        <hr class="border-slate-100">

        <!-- Informações Pessoais -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Nome -->
            <div class="space-y-1.5">
                <label for="name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome Completo</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                @error('name')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- E-mail -->
            <div class="space-y-1.5">
                <label for="email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Endereço de E-mail</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                @error('email')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Telefone -->
            <div class="space-y-1.5">
                <label for="phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                @error('phone')
                    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                @enderror
            </div>

            <!-- Nível de Acesso (Apenas Leitura) -->
            <div class="space-y-1.5">
                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nível de Acesso</label>
                <input type="text" value="{{ $user->role === 'master' ? 'Master (Administrador)' : 'Comum (Usuário)' }}" disabled class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-slate-50 text-slate-500 transition-all text-sm font-medium cursor-not-allowed">
            </div>
        </div>

        <hr class="border-slate-100">

        <!-- Alterar Senha (Opcional) -->
        <div>
            <h3 class="text-sm font-bold text-slate-900 mb-4 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                </svg>
                Alterar Senha
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nova Senha -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nova Senha</label>
                    <input type="password" name="password" id="password" placeholder="Preencha apenas se quiser alterar" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                    @error('password')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Nova Senha -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Confirmar Nova Senha</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repita a nova senha" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                </div>
            </div>
        </div>

        <div class="pt-6 border-t border-slate-100 flex justify-end">
            <button type="submit" class="px-6 py-3 bg-slate-900 hover:bg-slate-800 text-white font-semibold rounded-[5px] text-sm transition-colors shadow-sm">
                Salvar Alterações
            </button>
        </div>
    </form>
</div>

<script>
    function profileForm() {
        return {
            selectedTheme: '{{ $user->theme_color }}',
            avatarUrl: '{{ $user->avatar ? asset("storage/" . $user->avatar) : "" }}',
            logoUrl: '{{ $user->logo ? asset("storage/" . $user->logo) : "" }}',
            dragOver: false,
            dragOverLogo: false,

            triggerUpload() {
                this.$refs.avatarInput.click();
            },

            previewAvatar(event) {
                const file = event.target.files[0];
                if (file) {
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
                        this.avatarUrl = URL.createObjectURL(file);
                    }
                }
            },

            triggerLogoUpload() {
                this.$refs.logoInput.click();
            },

            previewLogo(event) {
                const file = event.target.files[0];
                if (file) {
                    this.logoUrl = URL.createObjectURL(file);
                }
            },

            handleLogoDrop(event) {
                this.dragOverLogo = false;
                const files = event.dataTransfer.files;
                if (files && files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        const dataTransfer = new DataTransfer();
                        dataTransfer.items.add(file);
                        this.$refs.logoInput.files = dataTransfer.files;
                        this.logoUrl = URL.createObjectURL(file);
                    }
                }
            }
        }
    }
</script>
@endsection
