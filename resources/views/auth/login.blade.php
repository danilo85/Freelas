<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar - Gestor de Freelas</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
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
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
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
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
            <span class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Gestor<span class="text-green-600">Freelas</span>
            </span>
        </div>

        <div class="bg-white rounded-[5px] border border-slate-200 shadow-xl p-8 space-y-6">
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

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <!-- E-mail -->
                <div class="space-y-1.5">
                    <label for="email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required placeholder="seuemail@exemplo.com" class="w-full px-4 py-3 rounded-[5px] border @error('email') border-red-300 bg-red-50 @else border-slate-200 bg-white @enderror text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition-all text-sm">
                    @error('email')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Senha -->
                <div class="space-y-1.5">
                    <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Senha</label>
                    <input type="password" name="password" id="password" required placeholder="••••••••" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 bg-white text-slate-900 focus:outline-none focus:ring-2 focus:ring-green-500/20 focus:border-green-600 transition-all text-sm">
                </div>

                <!-- Lembrar de mim -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded-[5px] text-green-600 border-slate-300 focus:ring-green-500/20 focus:ring-offset-0">
                        <span class="text-xs text-slate-500 font-medium">Lembrar de mim</span>
                    </label>
                </div>

                <!-- Botão de Ação -->
                <button type="submit" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-[5px] text-sm shadow-lg shadow-green-600/20 hover:shadow-green-700/30 transition-all text-center">
                    Entrar
                </button>
            </form>
        </div>

        <!-- Rodapé de Auth -->
        <p class="text-center text-xs text-slate-400 mt-6">
            Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-green-600 hover:text-green-800 transition-colors">Cadastre-se aqui</a>
        </p>
    </div>

</body>
</html>
