<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar-se - Gestor de Freelas</title>
    <link class="favicon" rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Alpine.js Mask Plugin & Core CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#01a87e',
                            600: '#024e4b',
                            700: '#023c3a',
                        }
                    },
                    fontFamily: {
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4" x-data="{ 
    showPass: false, 
    showConfirmPass: false, 
    password: '', 
    getStrength() {
        let score = 0;
        if (!this.password) return 0;
        if (this.password.length >= 8) score++;
        if (/[A-Z]/.test(this.password)) score++;
        if (/[0-9]/.test(this.password)) score++;
        if (/[^A-Za-z0-9]/.test(this.password)) score++;
        return score;
    }
}">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <img src="{{ asset('storage/freela/freela_1.svg') }}" class="h-10 w-auto object-contain" alt="Gestor Freelas">
        </div>

        <div class="bg-white rounded-[5px] border border-slate-200 shadow-sm p-8 space-y-6">
            <div class="text-center">
                <h2 class="text-xl font-bold text-slate-900">Crie sua conta</h2>
                <p class="text-sm text-slate-400 mt-1">Inscreva-se gratuitamente para gerenciar seus projetos.</p>
            </div>

            <form action="{{ route('register') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Nome Completo -->
                <div class="space-y-1.5">
                    <label for="name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome Completo</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required placeholder="Ex: Danilo Silva" class="w-full px-4 py-3 rounded-[5px] border @error('name') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                    @error('name')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- E-mail -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="seuemail@exemplo.com" class="w-full px-4 py-3 rounded-[5px] border @error('email') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                    @error('email')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Telefone -->
                <div class="space-y-1.5">
                    <label for="phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone') }}" x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'" placeholder="Ex: (11) 99999-9999" class="w-full px-4 py-3 rounded-[5px] border @error('phone') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                    @error('phone')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Senha</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" name="password" id="password" x-model="password" required placeholder="Mínimo 8 caracteres" class="w-full pl-4 pr-10 py-3 rounded-[5px] border @error('password') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                        <button type="button" @click="showPass = !showPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer flex items-center justify-center">
                            <template x-if="!showPass">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </template>
                            <template x-if="showPass">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </template>
                        </button>
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="space-y-1.5 mt-1.5" x-show="password" x-cloak>
                        <div class="flex justify-between items-center text-[10px] font-bold uppercase tracking-wider">
                            <span class="text-slate-400">Força da Senha</span>
                            <span :class="{
                                'text-red-500': getStrength() <= 1,
                                'text-amber-500': getStrength() === 2,
                                'text-emerald-500': getStrength() === 3,
                                'text-blue-500': getStrength() === 4
                            }" x-text="['Muito Fraca', 'Fraca', 'Média', 'Forte', 'Excelente'][getStrength()] || ''"></span>
                        </div>
                        <div class="flex gap-1 h-1 w-full bg-slate-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500" 
                                 :class="{
                                     'bg-red-500': getStrength() <= 1,
                                     'bg-amber-500': getStrength() === 2,
                                     'bg-emerald-500': getStrength() === 3,
                                     'bg-blue-500': getStrength() === 4
                                 }" 
                                 :style="'width: ' + (getStrength() === 0 ? 10 : getStrength() * 25) + '%'"></div>
                        </div>
                    </div>

                    @error('password')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmar Senha -->
                <div class="space-y-1.5">
                    <label for="password_confirmation" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Confirmar Senha</label>
                    <div class="relative">
                        <input :type="showConfirmPass ? 'text' : 'password'" name="password_confirmation" id="password_confirmation" required placeholder="Repita a senha" class="w-full pl-4 pr-10 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                        <button type="button" @click="showConfirmPass = !showConfirmPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer flex items-center justify-center">
                            <template x-if="!showConfirmPass">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </template>
                            <template x-if="showConfirmPass">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Botão de Ação -->
                <button type="submit" class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-[5px] text-sm shadow-md shadow-primary-600/10 hover:shadow-primary-700/20 transition-all duration-300 text-center cursor-pointer relative overflow-hidden group">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/15 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"></span>
                    <span class="relative z-10">Criar Conta</span>
                </button>
            </form>
        </div>

        <!-- Rodapé de Auth -->
        <p class="text-center text-xs text-slate-400 mt-6">
            Já tem uma conta? <a href="{{ route('login') }}" class="font-semibold text-primary-600 hover:text-primary-500 transition-colors">Faça login</a>
        </p>
    </div>

</body>
</html>
