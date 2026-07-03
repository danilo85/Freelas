<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Restrito (403)</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        .glow-effect {
            box-shadow: 0 0 80px 10px rgba(239, 68, 68, 0.15);
        }
    </style>
</head>
<body class="bg-[#0b0f19] font-sans text-slate-300 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative background elements -->
    <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] rounded-full bg-rose-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] rounded-full bg-orange-500/10 blur-[120px] pointer-events-none"></div>

    <div class="max-w-lg w-full text-center space-y-8 relative z-10">
        
        <!-- Icon -->
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-rose-500/10 border border-rose-500/25 glow-effect relative animate-pulse">
            <span class="text-4xl">🔒</span>
        </div>

        <div class="space-y-3">
            <span class="text-xs font-black uppercase tracking-widest text-rose-400 font-outfit">Erro 403 - Acesso Limitado</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight font-outfit">
                {{ $exception->getMessage() ?: 'Acesso Restrito' }}
            </h1>
            <p class="text-sm text-slate-400 leading-relaxed max-w-md mx-auto">
                Desculpe, você não tem autorização para acessar esta página, ou o limite máximo de downloads/visualizações para este compartilhamento foi atingido.
            </p>
        </div>

        <!-- Glass card information -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md max-w-sm mx-auto space-y-4 text-left">
            <div class="flex items-center gap-3">
                <span class="text-2xl">🛡️</span>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Segurança do Sistema</h4>
                    <p class="text-[11px] text-slate-400">Verifique se você possui o link e as credenciais de acesso corretas.</p>
                </div>
            </div>
        </div>

        <!-- Back link -->
        <div class="pt-6">
            <a href="javascript:history.back()" class="text-xs text-slate-500 hover:text-rose-400 transition-colors font-bold uppercase tracking-wider flex items-center justify-center gap-1.5">
                <svg class="w-3.5 h-3.5 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
                <span>Voltar para a página anterior</span>
            </a>
        </div>
    </div>

</body>
</html>
