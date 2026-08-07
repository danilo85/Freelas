<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Gestor de Freelas</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/assets/logo_DM.svg') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4" x-data="{ showPassword: false, rememberMe: false }">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <a href="/" title="Gestor Freelas">
                <svg class="h-10 w-auto text-primary-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 890.361 478.897" fill="currentColor">
                    <path d="M307.679,64.69c-18.667-17.379-40.874-30.68-66.621-39.907-25.747-9.223-53.963-13.84-84.643-13.84-24.676,0-49.513,1.56-74.507,4.668-24.997,3.112-49.297,8.423-72.896,15.931L0,463.449c10.083,1.075,19.954,1.88,29.609,2.413,9.656.538,19.522.805,29.61.805,27.034,0,53.37-2.092,79.011-6.276,25.636-4.183,49.83-10.62,72.574-19.31,22.74-8.689,43.659-19.793,62.758-33.31,19.095-13.518,35.563-29.66,49.402-48.437,13.839-18.772,24.621-40.23,32.346-64.368,7.724-24.138,11.586-51.222,11.586-81.265,0-31.751-5.205-60.183-15.609-85.287-10.41-25.103-24.942-46.344-43.609-63.724ZM234.942,234.299v4.506c-.432,12.446-2.736,24.515-6.919,36.206-4.184,11.698-10.088,22.318-17.702,31.862-7.618,9.55-16.685,17.595-27.195,24.138-10.515,6.548-22.207,10.782-35.08,12.713l12.874-199.54c13.084.432,24.349,3.168,33.792,8.207,9.439,5.043,17.219,11.697,23.334,19.953,6.115,8.263,10.51,17.757,13.195,28.483,2.681,10.732,3.913,21.885,3.701,33.472Z"/>
                    <polygon points="717.213 0 629.672 225.932 564.016 12.875 398.591 19.311 392.154 464.092 540.843 459.586 541.488 251.035 592.337 430.621 648.981 430.621 715.28 229.794 713.994 478.897 860.753 472.461 890.361 0 717.213 0"/>
                </svg>
            </a>
        </div>

        <div class="bg-white rounded-[5px] border border-slate-200 shadow-sm p-8 space-y-6">
            <div class="text-center">
                <h2 class="text-xl font-bold text-slate-900">Acesse sua conta</h2>
                <p class="text-sm text-slate-400 mt-1">Preencha suas credenciais para entrar no sistema.</p>
            </div>

            <!-- Alertas -->
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-[5px] p-3 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-[5px] p-3 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- E-mail -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="seuemail@exemplo.com" class="w-full px-4 py-3 rounded-[5px] border @error('email') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                    @error('email')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Senha</label>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required placeholder="••••••••" class="w-full pl-4 pr-10 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-600 transition-all text-sm">
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 focus:outline-none cursor-pointer flex items-center justify-center">
                            <template x-if="!showPassword">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </template>
                            <template x-if="showPassword">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"></path></svg>
                            </template>
                        </button>
                    </div>
                </div>

                <!-- Lembrar de mim -->
                <div class="flex items-center justify-between py-1">
                    <label class="flex items-center gap-3 cursor-pointer select-none group">
                        <div class="relative w-9 h-5 rounded-full transition-colors duration-300 focus-within:ring-2 focus-within:ring-primary-500/20"
                             :class="rememberMe ? 'bg-primary-600' : 'bg-slate-200'">
                            <input type="checkbox" name="remember" x-model="rememberMe" class="sr-only">
                            <!-- Sliding Dot -->
                            <div class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full transition-transform duration-300 shadow-sm"
                                 :class="rememberMe ? 'translate-x-4' : 'translate-x-0'">
                            </div>
                        </div>
                        <span class="text-xs text-slate-500 font-semibold group-hover:text-slate-700 transition-colors">Lembrar de mim</span>
                    </label>
                </div>

                <!-- Botão de Ação -->
                <button type="submit" class="w-full px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-[5px] text-sm shadow-md shadow-primary-600/10 hover:shadow-primary-700/20 transition-all duration-300 text-center cursor-pointer relative overflow-hidden group">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/15 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-out"></span>
                    <span class="relative z-10">Entrar</span>
                </button>
            </form>
        </div>

        <!-- Rodapé de Auth -->
        @php
            $systemSetting = \App\Models\SystemSetting::first();
            $allowReg = $systemSetting ? $systemSetting->allow_registration : true;
        @endphp
        @if($allowReg)
            <p class="text-center text-xs text-slate-400 mt-6">
                Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-primary-600 hover:text-primary-500 transition-colors">Cadastre-se aqui</a>
            </p>
        @endif
    </div>

</body>
</html>
