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

            <!-- Customização de Cores de Tema & Sidebar -->
            <div class="md:col-span-2 space-y-6">
                <!-- Tema do Dashboard -->
                <div class="space-y-3">
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

                <!-- Cor da Sidebar -->
                <div class="space-y-3">
                    <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Cor de Fundo da Sidebar</span>
                    <div class="grid grid-cols-5 gap-3">
                        <!-- Slate (Default Dark) -->
                        <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-slate-800 border-slate-800': selectedSidebar === 'dark'}">
                            <input type="radio" name="sidebar_color" value="dark" x-model="selectedSidebar" class="hidden">
                            <span class="w-6 h-6 rounded-full bg-slate-500 border border-slate-400 shadow-sm"></span>
                            <span class="text-[10px] font-bold text-slate-600">Slate</span>
                        </label>

                        <!-- Zinc -->
                        <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-zinc-700 border-zinc-700': selectedSidebar === 'zinc'}">
                            <input type="radio" name="sidebar_color" value="zinc" x-model="selectedSidebar" class="hidden">
                            <span class="w-6 h-6 rounded-full bg-zinc-700 border border-zinc-650 shadow-sm"></span>
                            <span class="text-[10px] font-bold text-slate-600">Zinc</span>
                        </label>

                        <!-- Brand Teal -->
                        <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-emerald-600 border-emerald-600': selectedSidebar === 'teal'}">
                            <input type="radio" name="sidebar_color" value="teal" x-model="selectedSidebar" class="hidden">
                            <span class="w-6 h-6 rounded-full bg-[#01a87e] border border-[#01906c] shadow-sm"></span>
                            <span class="text-[10px] font-bold text-slate-600">Teal</span>
                        </label>

                        <!-- Deep Navy -->
                        <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-blue-600 border-blue-600': selectedSidebar === 'navy'}">
                            <input type="radio" name="sidebar_color" value="navy" x-model="selectedSidebar" class="hidden">
                            <span class="w-6 h-6 rounded-full bg-blue-600 border border-blue-500 shadow-sm"></span>
                            <span class="text-[10px] font-bold text-slate-600">Navy</span>
                        </label>

                        <!-- Dark Purple -->
                        <label class="cursor-pointer border border-slate-200 rounded-[5px] p-3 flex flex-col items-center gap-2 hover:bg-slate-50 transition-colors" :class="{'ring-2 ring-purple-600 border-purple-600': selectedSidebar === 'purple'}">
                            <input type="radio" name="sidebar_color" value="purple" x-model="selectedSidebar" class="hidden">
                            <span class="w-6 h-6 rounded-full bg-purple-500 border border-purple-400 shadow-sm"></span>
                            <span class="text-[10px] font-bold text-slate-600">Púrpura</span>
                        </label>
                    </div>
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
                <div class="space-y-1.5 relative">
                    <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nova Senha</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" 
                               name="password" 
                               id="password" 
                               placeholder="Preencha apenas se quiser alterar" 
                               readonly 
                               onfocus="this.removeAttribute('readonly');" 
                               autocomplete="new-password"
                               x-model="passwordInput"
                               @input="checkPasswordStrength()"
                               class="w-full pl-4 pr-10 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                        
                        <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer select-none">
                            <svg x-show="!showPassword" class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-2.228-2.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Password Strength Meter -->
                    <div x-show="passwordInput.length > 0" class="space-y-1 pt-1.5 transition-all">
                        <div class="flex items-center justify-between text-[10px] font-bold">
                            <span class="text-slate-400 uppercase tracking-wide">Força da Senha</span>
                            <span :class="strengthColor" x-text="strengthText"></span>
                        </div>
                        <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full transition-all duration-300" :class="strengthBarColor" :style="{ width: strengthWidth }"></div>
                        </div>
                    </div>

                    @error('password')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Nova Senha -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Confirmar Nova Senha</label>
                    <div class="relative">
                        <input :type="showConfirmPassword ? 'text' : 'password'" 
                               name="password_confirmation" 
                               id="password_confirmation" 
                               placeholder="Repita a nova senha" 
                               readonly 
                               onfocus="this.removeAttribute('readonly');" 
                               autocomplete="new-password"
                               class="w-full pl-4 pr-10 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800 transition-all text-sm">
                        
                        <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer select-none">
                            <svg x-show="!showConfirmPassword" class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showConfirmPassword" class="w-4 h-4 text-slate-400 hover:text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.822 7.822L21 21m-2.228-2.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
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
            selectedSidebar: '{{ $user->sidebar_color ?? "dark" }}',
            avatarUrl: '{{ $user->avatar ? asset("storage/" . $user->avatar) : "" }}',
            logoUrl: '{{ $user->logo ? asset("storage/" . $user->logo) : "" }}',
            dragOver: false,
            dragOverLogo: false,

            init() {
                this.$watch('selectedTheme', (color) => {
                    this.updateFaviconColor(color);
                });
            },

            updateFaviconColor(color) {
                const colorMap = {
                    'blue': '#2563eb',
                    'purple': '#9333ea',
                    'indigo': '#4f46e5',
                    'orange': '#ea580c',
                    'green': '#16a34a'
                };
                const hex = colorMap[color] || '#16a34a';
                const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><path d="M520.438,340.64l-19.196,297.476c77.583-10.892,131.188-87.4,129.548-163.13,4.058-66.927-37.088-135.165-110.353-134.346Z" fill="${hex}"/><path d="M52.241,59.197l23.939,895.181,871.241-46.593V59.197H52.241ZM280.537,816.606l-15.648-626.755c243.799-89.688,554.57-69.721,562.62,254.427,8.378,298.033-294.6,405.912-546.972,372.329Z" fill="${hex}"/></svg>`;
                const faviconLink = document.getElementById('app-favicon');
                if (faviconLink) {
                    faviconLink.href = 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
                }
            },
            
            passwordInput: '',
            showPassword: false,
            showConfirmPassword: false,
            strengthScore: 0,
            strengthText: 'Fraca',
            strengthColor: 'text-red-500',
            strengthBarColor: 'bg-red-500',
            strengthWidth: '0%',

            checkPasswordStrength() {
                let score = 0;
                const pwd = this.passwordInput;
                if (pwd.length === 0) {
                    this.strengthScore = 0;
                    this.strengthWidth = '0%';
                    return;
                }
                
                if (pwd.length >= 8) score += 25;
                if (/[A-Z]/.test(pwd)) score += 25;
                if (/[0-9]/.test(pwd)) score += 25;
                if (/[^A-Za-z0-9]/.test(pwd)) score += 25;
                
                this.strengthScore = score;
                this.strengthWidth = score + '%';
                
                if (score <= 25) {
                    this.strengthText = 'Fraca';
                    this.strengthColor = 'text-red-500';
                    this.strengthBarColor = 'bg-red-500';
                } else if (score <= 50) {
                    this.strengthText = 'Razoável';
                    this.strengthColor = 'text-orange-500';
                    this.strengthBarColor = 'bg-orange-500';
                } else if (score <= 75) {
                    this.strengthText = 'Forte';
                    this.strengthColor = 'text-emerald-500';
                    this.strengthBarColor = 'bg-emerald-500';
                } else {
                    this.strengthText = 'Muito Forte';
                    this.strengthColor = 'text-blue-500';
                    this.strengthBarColor = 'bg-blue-600';
                }
            },

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
