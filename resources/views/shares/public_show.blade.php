<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Baixar Arquivos - {{ $share->title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Outfit:wght@700;800;900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS (via CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
        .dark .glassmorphism {
            background: rgba(15, 23, 42, 0.85);
        }

        .pulse-glow-rose {
            box-shadow: 0 0 15px rgba(244, 63, 94, 0.1);
            animation: pulse-rose 2s infinite alternate;
        }
        @keyframes pulse-rose {
            0% { box-shadow: 0 0 5px rgba(244, 63, 94, 0.1); }
            100% { box-shadow: 0 0 20px rgba(244, 63, 94, 0.25); }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col justify-between selection:bg-blue-500 selection:text-white dark:bg-slate-950 dark:text-slate-100 transition-colors duration-300"
      x-data="{ darkMode: localStorage.getItem('public_dark_mode') === 'true' }"
      :class="darkMode ? 'dark' : ''">

    <!-- Header / Navbar -->
    <header class="py-5 px-6 max-w-6xl mx-auto w-full flex items-center justify-between border-b border-slate-200/50 dark:border-slate-800/40">
        <span class="font-outfit font-black text-lg tracking-tight text-slate-900 dark:text-white">
            DANILO<span class="text-blue-500">MIGUEL</span> <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest ml-1">Transfer</span>
        </span>

        <!-- Darkmode Toggle Button -->
        <button @click="darkMode = !darkMode; localStorage.setItem('public_dark_mode', darkMode)" class="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors" title="Alternar Tema">
            <!-- Sun -->
            <svg x-show="darkMode" class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 9H3m15.364-3.364l-.707.707M6.343 17.657l-.707.707M16.243 17.657l.707-.707M7.757 6.343l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
            </svg>
            <!-- Moon -->
            <svg x-show="!darkMode" class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
            </svg>
        </button>
    </header>

    <!-- Main Container -->
    <main class="flex-1 flex items-center justify-center py-12 px-4 sm:px-6">
        <div class="max-w-xl w-full">

            <!-- CASO 1: LINK INATIVO -->
            @if($isInactive)
                <div class="bg-white border border-slate-200 dark:bg-slate-900 dark:border-slate-800 rounded-lg p-8 text-center space-y-4 shadow-xl">
                    <span class="text-5xl block">⚠️</span>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">Este link foi desativado</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">O proprietário desativou o acesso a esta transferência temporariamente. Por favor, entre em contato para solicitar um novo link.</p>
                </div>

            <!-- CASO 2: LINK EXPIRADO -->
            @elseif($isExpired)
                <div class="bg-white border border-slate-200 dark:bg-slate-900 dark:border-slate-800 rounded-lg p-8 text-center space-y-4 shadow-xl">
                    <span class="text-5xl block">⏳</span>
                    <h2 class="text-xl font-bold text-slate-800 dark:text-white">A transferência expirou</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed max-w-sm mx-auto">Esta transferência passou da data limite de expiração ({{ $share->expires_at->format('d/m/Y') }}) e não está mais disponível para download.</p>
                </div>

            <!-- CASO 3: REQUER SENHA -->
            @elseif($requiresPassword)
                <div class="bg-white border border-slate-200 dark:bg-slate-900 dark:border-slate-800 rounded-lg p-8 shadow-xl space-y-6">
                    <div class="text-center space-y-2">
                        <span class="text-4xl block">🔐</span>
                        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Este link é protegido por senha</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Insira a senha de segurança fornecida para acessar os arquivos.</p>
                    </div>

                    <form action="{{ route('public.share.verify', $share->share_token) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="space-y-1.5">
                            <label for="password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Senha de Acesso</label>
                            <input type="password" name="password" id="password" required placeholder="Digite a senha..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 dark:border-slate-700 bg-transparent text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-center">
                            @error('password')
                                <p class="text-xs text-red-600 font-medium text-center mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider py-3 rounded-[5px] transition-colors shadow-md shadow-blue-500/10">
                            Acessar Transferência
                        </button>
                    </form>
                </div>

            <!-- CASO 4: ACESSO PERMITIDO -->
            @else
                <div class="bg-white border border-slate-200 dark:bg-slate-900 dark:border-slate-800 rounded-lg shadow-xl overflow-hidden flex flex-col">
                    <!-- Topo do Compartilhamento -->
                    <div class="p-6 border-b border-slate-100 dark:border-slate-800/80 space-y-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <span class="text-[9px] font-black uppercase tracking-widest text-blue-500">Transferência de Arquivos</span>
                                <h1 class="font-outfit font-black text-xl text-slate-900 dark:text-white mt-1 leading-tight truncate" title="{{ $share->title }}">
                                    {{ $share->title }}
                                </h1>
                            </div>
                            <span class="text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 px-3 py-1 rounded-full shrink-0">
                                {{ $share->items->count() }} {{ $share->items->count() == 1 ? 'arquivo' : 'arquivos' }}
                            </span>
                        </div>

                        @if($share->description)
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed bg-slate-50 dark:bg-slate-950 p-3 rounded-[5px] border border-slate-100 dark:border-slate-800 font-medium">{{ $share->description }}</p>
                        @endif

                        <!-- Cronômetro / Detalhes de Expiração -->
                        <div class="flex items-center justify-between text-[11px] font-bold text-slate-400 dark:text-slate-500">
                            <span class="flex items-center gap-1">
                                ⏱️ Expira em: <span class="text-slate-650 dark:text-slate-350 font-black">{{ $share->expires_at->format('d/m/Y') }}</span>
                            </span>
                            <span>
                                Total: <span class="text-slate-650 dark:text-slate-350 font-black">{{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($share->items->sum('file_size')) }}</span>
                            </span>
                        </div>
                    </div>

                    <!-- Lista de Arquivos -->
                    <div class="divide-y divide-slate-100 dark:divide-slate-800 max-h-[300px] overflow-y-auto px-6">
                        @foreach($share->items as $item)
                            <div class="py-3 flex items-center justify-between text-xs gap-3">
                                <div class="min-w-0 flex items-center gap-2.5">
                                    <span class="text-xl shrink-0">📄</span>
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-slate-700 dark:text-slate-300 truncate" title="{{ $item->filename }}">{{ $item->filename }}</h4>
                                        <span class="text-[10px] text-slate-400 font-medium block mt-0.5">{{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($item->file_size) }}</span>
                                    </div>
                                </div>
                                <a href="{{ route('public.share.download', [$share->share_token, $item->id]) }}" 
                                   class="w-8 h-8 rounded-full border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                                   title="Baixar arquivo">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <!-- Rodapé do Card com Ações -->
                    <div class="p-6 bg-slate-50 dark:bg-slate-900/60 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center gap-3">
                        <!-- Baixar Todos unificado (.ZIP) -->
                        <a href="{{ route('public.share.zip', $share->share_token) }}" 
                           class="w-full text-center py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow-md shadow-blue-500/10 flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Baixar Tudo (.ZIP)
                        </a>
                    </div>
                </div>
            @endif

        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 px-6 max-w-6xl mx-auto w-full text-center border-t border-slate-200/50 dark:border-slate-800/40 text-slate-450 dark:text-slate-550 text-xs">
        <p>&copy; 2026 Danilo Miguel - Designer e Ilustrador. Todos os direitos reservados.</p>
    </footer>

</body>
</html>
