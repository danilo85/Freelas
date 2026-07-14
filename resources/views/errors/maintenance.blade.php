<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfólio em Manutenção - Danilo Miguel</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">
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
            box-shadow: 0 0 80px 10px rgba(99, 102, 241, 0.15);
        }
    </style>
</head>
<body class="bg-[#0b0f19] font-sans text-slate-300 antialiased min-h-screen flex items-center justify-center p-4 relative overflow-hidden">

    <!-- Decorative background elements -->
    <div class="absolute top-[-20%] left-[-20%] w-[60%] h-[60%] rounded-full bg-blue-500/10 blur-[120px] pointer-events-none"></div>
    <div class="absolute bottom-[-20%] right-[-20%] w-[60%] h-[60%] rounded-full bg-indigo-500/10 blur-[120px] pointer-events-none"></div>

    <div class="max-w-lg w-full text-center space-y-8 relative z-10">
        
        <!-- Icon / Visual representation of maintenance -->
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-indigo-550/10 border border-indigo-500/25 glow-effect relative animate-pulse">
            <span class="text-4xl">🛸</span>
        </div>

        <div class="space-y-3">
            <span class="text-xs font-black uppercase tracking-widest text-indigo-400 font-outfit">Volto em Instantes</span>
            <h1 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight font-outfit">Portfólio em Manutenção</h1>
            <p class="text-sm text-slate-400 leading-relaxed max-w-md mx-auto">
                Olá! Estou organizando novas artes, trabalhos e atualizações no meu site. O portfólio estará de volta muito em breve.
            </p>
        </div>

        <!-- Glass card information -->
        <div class="bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md max-w-sm mx-auto space-y-4">
            <div class="flex items-center gap-3 text-left">
                <span class="text-2xl">☕</span>
                <div>
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Trabalhos em Progresso</h4>
                    <p class="text-[11px] text-slate-400">Arrumando a galeria de ilustrações e layouts.</p>
                </div>
            </div>
        </div>

        <!-- System access button -->
        <div class="pt-6">
            <a href="{{ route('login') }}" class="text-xs text-slate-500 hover:text-indigo-400 transition-colors font-bold uppercase tracking-wider flex items-center justify-center gap-1.5">
                <span>Acesso Administrativo</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                </svg>
            </a>
        </div>
    </div>

</body>
</html>
