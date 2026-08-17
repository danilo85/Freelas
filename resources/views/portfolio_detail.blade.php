<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $item->title }} | Danilo Miguel</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('storage/assets/logo_DM.svg') }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        dark: {
                            900: '#070a13',
                            800: '#0b1120',
                            700: '#151e33',
                            600: '#1e294b',
                        },
                        blue: {
                            50: '{{ ($settings->primary_color ?? "#3b82f6") }}10',
                            100: '{{ ($settings->primary_color ?? "#3b82f6") }}20',
                            200: '{{ ($settings->primary_color ?? "#3b82f6") }}40',
                            300: '{{ ($settings->primary_color ?? "#3b82f6") }}80',
                            400: '{{ ($settings->primary_color ?? "#3b82f6") }}c0',
                            500: '{{ $settings->primary_color ?? "#3b82f6" }}',
                            600: '{{ $settings->primary_color ?? "#2563eb" }}',
                            700: '{{ $settings->secondary_color ?? "#1d4ed8" }}',
                        },
                        primary: {
                            50: '{{ ($settings->primary_color ?? "#3b82f6") }}10',
                            100: '{{ ($settings->primary_color ?? "#3b82f6") }}20',
                            500: '{{ $settings->primary_color ?? "#3b82f6" }}',
                            600: '{{ $settings->primary_color ?? "#2563eb" }}',
                            700: '{{ $settings->secondary_color ?? "#1d4ed8" }}',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Alpine.js via CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            transition: background-color 0.3s, color 0.3s;
        }
        @if(($settings->theme_mode ?? 'escuro') === 'claro')
        body {
            background-color: #f8fafc;
            color: #1e293b;
        }
        .text-white, .text-slate-100 {
            color: #0f172a !important;
        }
        .text-slate-350, .text-slate-400, .text-slate-450 {
            color: #475569 !important;
        }
        .text-slate-300 {
            color: #334155 !important;
        }
        .bg-dark-900, .bg-slate-950 {
            background-color: #f8fafc !important;
        }
        .bg-dark-800 {
            background-color: #f1f5f9 !important;
        }
        .bg-slate-900 {
            background-color: #ffffff !important;
        }
        .bg-slate-900\/50, .bg-slate-900\/40 {
            background-color: rgba(255, 255, 255, 0.8) !important;
        }
        .bg-slate-950\/70 {
            background-color: rgba(255, 255, 255, 0.9) !important;
        }
        .border-slate-900, .border-slate-800, .border-slate-800\/80 {
            border-color: #e2e8f0 !important;
        }
        .border-t {
            border-color: #e2e8f0 !important;
        }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.75) !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border: 1px rgba(0, 0, 0, 0.08) solid !important;
        }
        .border-white\/\[0\.08\] {
            border-color: rgba(0, 0, 0, 0.08) !important;
        }
        .border-white\/5 {
            border-color: rgba(0, 0, 0, 0.05) !important;
        }
        .bg-slate-900.border-slate-800 {
            background-color: #ffffff !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
        .bg-slate-900.border-slate-800:hover {
            background-color: #f1f5f9 !important;
        }
        .hover\:bg-slate-900\/80:hover {
            background-color: rgba(0, 0, 0, 0.03) !important;
        .project-description-text,
        .project-description-text *,
        .project-description-text p,
        .project-description-text div,
        .project-description-text span,
        .project-description-text strong {
            color: #334155 !important;
        }
        @else
        body {
            background-color: #070a13;
            color: #f1f5f9;
        }
        .glassmorphism {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.08) solid;
        }
        .project-description-text,
        .project-description-text *,
        .project-description-text p,
        .project-description-text div,
        .project-description-text span,
        .project-description-text strong {
            color: #cbd5e1 !important;
        }
        @endif
        
        .text-gradient {
            background: linear-gradient(135deg, {{ $settings->primary_color ?? '#3b82f6' }}e0 0%, {{ $settings->primary_color ?? '#3b82f6' }} 50%, {{ $settings->secondary_color ?? '#1d4ed8' }} 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        @keyframes gradient-shift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animated-gradient-border {
            position: relative;
            border-radius: inherit;
            z-index: 0;
        }
        .animated-gradient-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: inherit;
            padding: 1.5px;
            background: linear-gradient(90deg, {{ $settings->primary_color ?? '#3b82f6' }}, {{ $settings->secondary_color ?? '#1d4ed8' }}, {{ $settings->primary_color ?? '#3b82f6' }});
            background-size: 200% 200%;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask-composite: exclude;
            z-index: 10;
            pointer-events: none;
            animation: gradient-shift 3s ease infinite;
        }
        .heart-beat {
            animation: heartbeat 0.4s ease-in-out;
        }
        @keyframes heartbeat {
            0% { transform: scale(1); }
            50% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body class="text-slate-100 antialiased min-h-screen pb-16 selection:bg-blue-500 selection:text-white" x-data="portfolioDetail()">
    <!-- Custom Cursor Elements -->
    <div id="custom-cursor" class="pointer-events-none fixed top-0 left-0 w-8 h-8 rounded-full border border-blue-500/40 mix-blend-difference z-[9999] transition-all duration-200 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>
    <div id="custom-cursor-dot" class="pointer-events-none fixed top-0 left-0 w-1.5 h-1.5 bg-blue-500 rounded-full z-[9999] transition-all duration-200 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>

    <!-- Header / Navbar -->
    <header class="sticky top-0 z-50 glassmorphism py-4 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <a href="{{ route('public.home') }}" class="flex items-center gap-2">
                <span class="font-outfit font-black text-lg tracking-tight text-white">
                    DANILO<span class="text-blue-500">MIGUEL</span>
                </span>
            </a>

            <a href="{{ route('public.home') }}#portfolio" class="flex items-center gap-2 text-sm text-slate-450 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
                </svg>
                <span>Voltar ao Portfólio</span>
            </a>
        </div>
    </header>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-10">
        
        <!-- Grid principal tipo Behance -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
            
            <!-- Coluna Esquerda: Showcase do Trabalho (8 colunas) -->
            <div class="lg:col-span-8 space-y-8">
                
                <!-- Imagens do Trabalho (Estilo Pinterest / Behance) -->
                <div class="flex flex-col space-y-0 rounded-none overflow-hidden shadow-2xl border border-white/[0.08]">
                    <!-- Showcase de Imagens do Trabalho -->
                    @if($item->images->count() > 0)
                        <div class="flex flex-col" style="gap: {{ intval($item->gallery_spacing ?? 0) }}px;">
                            @foreach($item->images->sortBy('order') as $image)
                                <img src="{{ asset('storage/' . $image->image_path) }}" 
                                     alt="Trabalho - {{ $item->title }}"
                                     class="w-full h-auto object-cover rounded-none block m-0 p-0 border-0 outline-none">
                            @endforeach
                        </div>
                    @elseif($item->thumb_path)
                        <!-- Fallback apenas se não houver imagens de galeria cadastradas -->
                        <img src="{{ asset('storage/' . $item->thumb_path) }}" 
                             alt="{{ $item->title }}"
                             class="w-full h-auto object-cover rounded-none block m-0 p-0">
                    @endif
                </div>

                <!-- Caso seja Font / Tipografia: Testador Interativo -->
                @if(str_contains(strtolower($item->category->name), 'fonte') || str_contains(strtolower($item->category->name), 'tipografia'))
                    <div class="p-8 rounded-2xl shadow-none space-y-4 glassmorphism">
                        <div class="flex items-center justify-between border-b border-white/[0.08] pb-3">
                            <h4 class="font-outfit font-extrabold text-white text-base">Teste a Fonte Online</h4>
                            <span class="text-[10px] bg-blue-500/10 text-blue-400 border border-blue-500/20 px-2 py-0.5 rounded font-bold uppercase tracking-wider">Amostra</span>
                        </div>
                        
                        <div class="space-y-4" x-data="{ sampleText: 'Digite aqui para testar o alinhamento da fonte...', fontSize: 32 }">
                            <div class="flex items-center gap-4 text-xs text-slate-400">
                                <span class="shrink-0">Tamanho da Letra:</span>
                                <input type="range" min="16" max="72" x-model="fontSize" class="w-full h-1 bg-slate-800 rounded-lg appearance-none cursor-pointer accent-blue-500">
                                <span class="w-12 text-right font-mono" x-text="fontSize + 'px'"></span>
                            </div>
                            
                            <input type="text" x-model="sampleText" class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-300 focus:outline-none focus:border-blue-500 transition-colors">
                            
                            <!-- Caixa de Amostra Estilizada -->
                            <div class="p-6 bg-slate-950 border border-white/[0.08] rounded-lg min-h-[120px] flex items-center justify-center text-center text-white break-all leading-normal"
                                 :style="'font-size: ' + fontSize + 'px; font-family: Outfit, sans-serif; font-weight: 800;'"
                                 x-text="sampleText">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Link Externo para Visualizar / Acessar -->
                @if($item->redirect_url)
                    <div class="p-6 rounded-2xl text-center space-y-4 shadow-none glassmorphism">
                        <p class="text-sm text-slate-350">Este trabalho possui um link interativo ou demonstração online disponível.</p>
                        <a href="{{ $item->redirect_url }}" target="_blank" class="inline-flex items-center justify-center gap-2.5 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-full transition-all shadow-md shadow-blue-600/25">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            <span>Acessar Projeto Completo</span>
                        </a>
                    </div>
                @endif

            </div>

            <!-- Coluna Direita: Detalhes, Ficha Técnica e Likes (4 colunas) -->
            <div class="lg:col-span-4 lg:sticky lg:top-24 space-y-6">
                
                <!-- Ficha Técnica -->
                <div class="p-8 rounded-2xl shadow-none space-y-6 glassmorphism">
                    <div class="space-y-3">
                        <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500 block">
                            {{ $item->category->name }}
                        </span>
                        <h2 class="text-2xl font-outfit font-black text-white leading-tight">
                            {{ $item->title }}
                        </h2>
                    </div>

                    <!-- Métricas de Likes & Views -->
                    <div class="flex items-center gap-4 py-3 border-y border-white/[0.08]">
                        <!-- Views -->
                        <div class="flex items-center gap-2 text-slate-400 text-xs font-bold">
                            <svg class="w-4.5 h-4.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span><strong class="text-white">{{ $item->views }}</strong> visualizações</span>
                        </div>
                        
                        <!-- Curtidas -->
                        <button type="button" 
                                @click="likeItem($event)" 
                                class="like-btn flex items-center gap-2 text-slate-400 hover:text-rose-500 text-xs font-bold transition-colors select-none"
                                :disabled="liked">
                            <svg class="w-4.5 h-4.5 text-rose-500 transition-transform" 
                                 :class="liked ? 'scale-110 heart-beat' : 'hover:scale-110'"
                                 :fill="liked ? 'currentColor' : 'none'"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span><strong class="text-white" x-text="likesCount"></strong> curtidas</span>
                        </button>
                    </div>

                    <!-- Descrição -->
                    <div class="space-y-2">
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Descrição do Projeto</span>
                        <div class="project-description-text text-xs text-slate-300 dark:text-slate-300 leading-relaxed font-normal text-justify whitespace-pre-line select-text">
                            {!! str_replace(['&lt;div&gt;', '&lt;/div&gt;', '&lt;p&gt;', '&lt;/p&gt;', '&lt;br&gt;', '&lt;br/&gt;'], ["\n", "\n", "\n", "\n", "\n", "\n"], $item->description) !!}
                        </div>
                    </div>

                    <!-- Cliente -->
                    @if($item->client)
                        <div class="space-y-1.5 pt-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Cliente Atendido</span>
                            <div class="text-xs font-bold text-white uppercase tracking-wide">
                                {{ $item->client->name }}
                            </div>
                        </div>
                    @endif

                    <!-- Tecnologias -->
                    @if($item->technologies)
                        <div class="space-y-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Tecnologias</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach(explode(',', $item->technologies) as $tech)
                                    <span class="bg-slate-950 text-slate-300 border border-slate-800 text-[10px] font-medium px-2.5 py-0.5 rounded">
                                        {{ trim($tech) }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Autores / Equipe -->
                    @if($item->authors->count() > 0)
                        <div class="space-y-2">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Equipe / Autores</span>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach($item->authors as $author)
                                    <span class="bg-slate-950 text-slate-300 border border-slate-800 text-[10px] font-medium px-2.5 py-0.5 rounded">
                                        {{ $author->name }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                @php
                    $cleanPhone = preg_replace('/\D/', '', $settings->contact_phone);
                    $whatsappNumber = str_starts_with($cleanPhone, '55') ? $cleanPhone : '55' . $cleanPhone;
                @endphp
                <!-- Botão de Contato Rápido -->
                <div class="p-6 rounded-2xl text-center space-y-3 glassmorphism">
                    <p class="text-xs text-slate-400">Gostou deste trabalho? Vamos desenvolver o seu projeto juntos!</p>
                    <a href="https://wa.me/{{ $whatsappNumber }}?text=Olá Danilo, gostei muito do seu trabalho '{{ rawurlencode($item->title) }}' e gostaria de bater um papo!" 
                       target="_blank" 
                       class="w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors flex items-center justify-center gap-2 shadow-sm shadow-emerald-600/10">
                        Falar com Danilo no WhatsApp
                    </a>
                </div>

            </div>

        </div>

        <!-- Rodapé: Trabalhos Relacionados -->
        @if($relatedItems->count() > 0)
            <div class="pt-24 border-t border-slate-900 mt-24 space-y-8">
                <div class="text-center md:text-left space-y-2">
                    <span class="text-[10px] font-extrabold uppercase tracking-widest text-blue-500 block">Outros Projetos</span>
                    <h3 class="text-2xl font-outfit font-black text-white">Veja Também</h3>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach($relatedItems as $rel)
                        <a href="{{ route('public.portfolio.show', $rel->slug) }}" class="group bg-slate-900/40 border border-white/[0.08] hover:border-blue-500/50 p-4 rounded-xl shadow-none transition-all duration-300 block">
                            <div class="aspect-video w-full rounded-lg overflow-hidden bg-slate-950 relative">
                                @if($rel->thumb_path)
                                    <img src="{{ asset('storage/' . $rel->thumb_path) }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105">
                                @endif
                            </div>
                            <div class="pt-4 space-y-1">
                                <span class="text-[9px] font-bold uppercase tracking-wider text-blue-400">{{ $rel->category->name }}</span>
                                <h4 class="font-extrabold text-white text-sm truncate group-hover:text-blue-400 transition-colors">{{ $rel->title }}</h4>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    <!-- Footer -->
    <footer class="py-12 bg-dark-900 border-t border-slate-950 text-slate-500 text-xs mt-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <span class="font-outfit font-black text-sm tracking-tight text-slate-400">
                DANILO<span class="text-blue-500">MIGUEL</span>
            </span>

            <p class="text-center md:text-left leading-relaxed">
                &copy; 2026 Danilo Miguel - Designer e Ilustrador. Todos os direitos reservados.
            </p>

            <a href="{{ route('dashboard') }}" class="hover:text-white transition-colors flex items-center gap-1.5">
                <span>Área Administrativa</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </a>
        </div>
    </footer>

    <!-- Script de Curtidas -->
    <script>
        function portfolioDetail() {
            return {
                liked: false,
                likesCount: {{ $item->likes }},
                
                init() {
                    // Cursor Follower Logic
                    const cursor = document.getElementById('custom-cursor');
                    const cursorDot = document.getElementById('custom-cursor-dot');
                    if (cursor && cursorDot) {
                        window.addEventListener('mousemove', (e) => {
                            cursor.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                            cursorDot.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                        });

                        const clickables = document.querySelectorAll('a, button, input, select, textarea, [role="button"], .group');
                        clickables.forEach(el => {
                            el.addEventListener('mouseenter', () => {
                                cursor.classList.add('scale-[2.5]', 'bg-white');
                                cursor.classList.remove('border-blue-500/40');
                                cursorDot.classList.add('opacity-0');
                            });
                            el.addEventListener('mouseleave', () => {
                                cursor.classList.remove('scale-[2.5]', 'bg-white');
                                cursor.classList.add('border-blue-500/40');
                                cursorDot.classList.remove('opacity-0');
                            });
                        });
                    }

                    // Click particle emoji effect
                    window.addEventListener('click', (e) => {
                        if (e.target.closest('select') || e.target.closest('input[type="file"]') || e.target.closest('button.like-btn')) return;

                        const particle = document.createElement('div');
                        particle.className = 'pointer-events-none fixed z-[9999] text-2xl transition-all duration-1000 ease-out transform -translate-x-1/2 -translate-y-1/2';
                        
                        const icons = ['👍', '✨', '🎨', '🔥', '🚀'];
                        particle.innerHTML = icons[Math.floor(Math.random() * icons.length)];
                        
                        particle.style.left = `${e.clientX}px`;
                        particle.style.top = `${e.clientY}px`;
                        particle.style.opacity = '1';
                        particle.style.transform = 'translate3d(-50%, -50%, 0) scale(0.5)';
                        document.body.appendChild(particle);
                        
                        setTimeout(() => {
                            particle.style.transform = `translate3d(-50%, -120px, 0) scale(1.5) rotate(${Math.random() > 0.5 ? 20 : -20}deg)`;
                            particle.style.opacity = '0';
                        }, 50);
                        
                        setTimeout(() => {
                            particle.remove();
                        }, 1050);
                    });
                },

                likeItem(event) {
                    if (this.liked) return;
                    this.liked = true;

                    // Hearts burst animation
                    const btn = event.currentTarget;
                    const rect = btn.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;

                    for (let i = 0; i < 12; i++) {
                        const heart = document.createElement('div');
                        heart.className = 'pointer-events-none fixed z-[9999] text-base transition-all duration-1000 ease-out transform -translate-x-1/2 -translate-y-1/2';
                        heart.innerHTML = '❤️';
                        heart.style.left = `${centerX}px`;
                        heart.style.top = `${centerY}px`;
                        heart.style.opacity = '1';
                        heart.style.transform = 'translate3d(-50%, -50%, 0) scale(0.5)';
                        document.body.appendChild(heart);

                        const angle = Math.random() * Math.PI * 2;
                        const velocity = 40 + Math.random() * 70;
                        const targetX = Math.cos(angle) * velocity;
                        const targetY = Math.sin(angle) * velocity - 20;

                        setTimeout(() => {
                            heart.style.transform = `translate3d(calc(-50% + ${targetX}px), calc(-50% + ${targetY}px), 0) scale(${1.2 + Math.random() * 0.8}) rotate(${Math.random() * 360}deg)`;
                            heart.style.opacity = '0';
                        }, 50);

                        setTimeout(() => {
                            heart.remove();
                        }, 1050);
                    }

                    fetch("{{ route('public.portfolio.likes', $item->id) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.likesCount = data.likes;
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>
