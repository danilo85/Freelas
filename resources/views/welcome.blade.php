<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->site_title }}</title>

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
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
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
            background-color: #070a13;
        }
        .text-gradient {
            background: linear-gradient(135deg, #60a5fa 0%, #3b82f6 50%, #1d4ed8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .glassmorphism {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px rgba(255, 255, 255, 0.08) solid;
        }
    </style>
</head>
<body class="text-slate-100 antialiased min-h-screen selection:bg-blue-500 selection:text-white" x-data="publicPortfolio()">

    <!-- Header / Navbar -->
    <header class="fixed top-0 inset-x-0 z-50 glassmorphism transition-all duration-300" 
            :class="scrolled ? 'py-3 shadow-lg' : 'py-5'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <a href="#home" class="flex items-center gap-2">
                <span class="font-outfit font-black text-xl tracking-tight text-white">
                    DANILO<span class="text-blue-500">MIGUEL</span>
                </span>
            </a>

            <!-- Navegação Desktop -->
            <nav class="hidden md:flex items-center gap-8">
                <a href="#home" class="text-sm font-medium text-slate-350 hover:text-white transition-colors">Início</a>
                <a href="#portfolio" class="text-sm font-medium text-slate-350 hover:text-white transition-colors">Portfólio</a>
                <a href="#about" class="text-sm font-medium text-slate-350 hover:text-white transition-colors">Sobre</a>
                <a href="#faq" class="text-sm font-medium text-slate-350 hover:text-white transition-colors">FAQ</a>
                <a href="#contact" class="text-sm font-medium text-slate-350 hover:text-white transition-colors">Contato</a>
            </nav>

            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-full border border-blue-500/30 hover:border-blue-500 text-blue-400 hover:text-white text-xs font-semibold uppercase tracking-wider transition-all flex items-center gap-2 shadow-sm shadow-blue-500/10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Área Restrita</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden bg-radial-gradient">
        <!-- Detalhes de luz de fundo -->
        <div class="absolute top-1/4 left-1/4 -translate-x-1/2 -translate-y-1/2 w-[350px] h-[350px] bg-blue-600/15 rounded-full blur-[100px] pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 translate-x-1/2 translate-y-1/2 w-[450px] h-[450px] bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 relative z-10">
            <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-semibold uppercase tracking-wider shadow-sm animate-pulse">
                🚀 Ilustrador & Designer Editorial
            </span>
            
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-outfit font-black tracking-tight leading-none text-white max-w-4xl mx-auto">
                {{ $settings->site_subtitle }}
            </h1>

            <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-normal leading-relaxed">
                {{ $settings->site_description }}
            </p>

            @php
                $cleanPhone = preg_replace('/\D/', '', $settings->contact_phone);
                $whatsappNumber = str_starts_with($cleanPhone, '55') ? $cleanPhone : '55' . $cleanPhone;
            @endphp
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="#portfolio" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-full transition-all shadow-lg shadow-blue-600/25 text-center">
                    Ver Portfólio
                </a>
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-white font-bold rounded-full transition-all text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-emerald-500 fill-emerald-500" viewBox="0 0 24 24">
                        <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.724-1.458L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.859-4.42 9.863-9.864.002-2.634-1.02-5.11-2.884-6.978C16.59 1.897 14.113 1.83 12.012 1.83c-5.435 0-9.856 4.419-9.86 9.864-.001 1.944.521 3.823 1.512 5.473L2.658 21.35l4.279-1.124.71.428z"/>
                    </svg>
                    Falar no WhatsApp
                </a>
            </div>
        </div>

        <!-- Indicador de Scroll -->
        <div class="absolute bottom-8 inset-x-0 flex justify-center animate-bounce pointer-events-none">
            <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
            </svg>
        </div>
    </section>

    <!-- Portfolio Gallery Section -->
    <section id="portfolio" class="py-24 border-t border-slate-900 bg-dark-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center space-y-4">
                <h2 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white">Meu Portfólio</h2>
                <p class="text-slate-400 max-w-xl mx-auto text-sm sm:text-base font-normal">
                    Passe o mouse (ou toque) nas artes para revelar as informações e clique para ver mais detalhes.
                </p>
            </div>

            <!-- Filtros de Categorias -->
            <div class="flex flex-wrap items-center justify-center gap-2">
                <button type="button" 
                        @click="activeCategory = 'all'"
                        class="px-4 py-2 rounded-full text-xs font-semibold tracking-wider uppercase transition-all duration-200"
                        :class="activeCategory === 'all' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'">
                    Todos
                </button>
                @foreach($categories as $cat)
                    <button type="button" 
                            @click="activeCategory = '{{ $cat->id }}'"
                            class="px-4 py-2 rounded-full text-xs font-semibold tracking-wider uppercase transition-all duration-200"
                            :class="activeCategory === '{{ $cat->id }}' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/20' : 'bg-slate-900 border border-slate-800 text-slate-400 hover:text-white'">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Grid de Trabalhos (Pinterest Masonry) -->
            <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-0">
                @forelse($items as $item)
                    <div x-show="activeCategory === 'all' || activeCategory === '{{ $item->portfolio_category_id }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         @click="window.location.href = '{{ route('public.portfolio.show', $item->slug) }}'"
                         class="break-inside-avoid w-full mb-6 cursor-pointer group bg-slate-900 border border-slate-850 hover:border-slate-750 rounded-[3px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 relative inline-block">
                        
                        <!-- Thumbnail (Pinterest Adaptável) -->
                        <div class="relative w-full overflow-hidden bg-slate-950">
                            @if($item->thumb_path)
                                <img src="{{ asset('storage/' . $item->thumb_path) }}" 
                                     class="w-full h-auto object-cover transition-transform duration-500 group-hover:scale-[1.02]" 
                                     loading="lazy">
                            @else
                                <div class="aspect-video w-full flex items-center justify-center text-slate-600 bg-slate-900">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif

                            <!-- Destaque Badge -->
                            @if($item->is_featured)
                                <span class="absolute top-2 left-2 z-20 px-2 py-0.5 text-[8px] font-black uppercase tracking-wider rounded-[3px] bg-yellow-500 text-slate-950 flex items-center gap-1 shadow-md">
                                    ★ Destaque
                                </span>
                            @endif

                            <!-- Tags de Curtidas & Views (Sempre Visíveis) -->
                            <div class="absolute top-2 right-2 z-20 flex items-center gap-1.5 select-none bg-slate-950/70 backdrop-blur-sm px-2 py-0.5 rounded-[3px] border border-white/5 text-[9px] font-bold text-slate-350">
                                <span class="flex items-center gap-1" title="{{ $item->views }} visualizações">
                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                    <span>{{ $item->views }}</span>
                                </span>
                                <span class="w-px h-3 bg-white/10"></span>
                                <span class="flex items-center gap-1" title="{{ $item->likes }} curtidas">
                                    <svg class="w-3 h-3 text-rose-500 fill-rose-500" viewBox="0 0 24 24">
                                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                    </svg>
                                    <span>{{ $item->likes }}</span>
                                </span>
                            </div>

                            <!-- Overlay de Informações (Somente no Hover) -->
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/70 to-slate-950/15 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col justify-end p-5 space-y-2.5 z-10">
                                <div class="space-y-0.5">
                                    <span class="text-[8px] font-extrabold uppercase tracking-widest text-blue-400 block">
                                        {{ $item->category->name }}
                                    </span>
                                    <h4 class="font-extrabold text-white text-sm sm:text-base leading-tight">
                                        {{ $item->title }}
                                    </h4>
                                </div>

                                @if($item->technologies)
                                    <div class="flex flex-wrap gap-1">
                                        @foreach(explode(',', $item->technologies) as $tech)
                                            <span class="bg-blue-600/10 text-blue-400 border border-blue-500/20 text-[8px] font-bold px-1.5 py-0.5 rounded-[3px] uppercase tracking-wide">
                                                {{ trim($tech) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                                
                                <div class="text-[10px] font-bold text-blue-400 flex items-center gap-1">
                                    <span>Ver detalhes</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="col-span-full border-2 border-dashed border-slate-800 p-12 text-center text-slate-500 rounded-xl text-sm italic">
                        Nenhum trabalho de portfólio publicado ainda.
                    </div>
                @endforelse
            </div>

        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-dark-900 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Sobre Mim</span>
                <h3 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white leading-tight">
                    {{ $settings->about_title ?? 'Prazer, sou Danilo Miguel' }}
                </h3>
                <div class="text-slate-350 leading-relaxed text-sm sm:text-base space-y-4 whitespace-pre-line">
                    {{ $settings->about_text }}
                </div>

                <!-- Skills Grid -->
                <div class="space-y-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide block">Especialidades</span>
                    <div class="flex flex-wrap gap-2">
                        @foreach(explode(',', $settings->skills) as $skill)
                            <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">{{ trim($skill) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Ilustração / Imagem decorativa de destaque -->
            <div class="relative flex justify-center">
                <div class="absolute inset-0 bg-blue-600/10 rounded-full blur-[80px] pointer-events-none"></div>
                <div class="w-full max-w-md aspect-square rounded-2xl bg-gradient-to-tr from-blue-700/20 to-indigo-700/20 border border-slate-800/80 p-8 flex items-center justify-center relative overflow-hidden shadow-2xl">
                    <div class="absolute top-4 left-4 w-3.5 h-3.5 rounded-full bg-red-500/60"></div>
                    <div class="absolute top-4 left-10 w-3.5 h-3.5 rounded-full bg-yellow-500/60"></div>
                    <div class="absolute top-4 left-16 w-3.5 h-3.5 rounded-full bg-emerald-500/60"></div>
                    
                    <div class="text-center space-y-4">
                        <span class="text-6xl">🎨</span>
                        <h4 class="font-outfit font-black text-xl text-white">Criatividade & Estrutura</h4>
                        <p class="text-xs text-slate-400 max-w-xs mx-auto">
                            A união entre design técnico apurado e arte ilustrativa autoral que gera valor real para o seu livro ou produto educativo.
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-dark-800 border-t border-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-12">
            
            <div class="text-center space-y-4">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Dúvidas Frequentes</span>
                <h3 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white">Perguntas & Respostas</h3>
                <p class="text-slate-400 max-w-xl mx-auto text-sm">
                    Encontre respostas rápidas para as principais dúvidas sobre os serviços de ilustração e diagramação.
                </p>
            </div>

            <!-- Accordion FAQ -->
            <div class="space-y-4" x-data="{ activeFaq: null }">
                @foreach($settings->faq_items as $index => $faq)
                    <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                        <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                                @click="activeFaq = activeFaq === {{ $index }} ? null : {{ $index }}">
                            <span>{{ $faq['question'] }}</span>
                            <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                                 :class="activeFaq === {{ $index }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>
                        <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal whitespace-pre-line" x-show="activeFaq === {{ $index }}" x-collapse x-cloak>
                            {{ $faq['answer'] }}
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-24 bg-dark-900 border-t border-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <div class="space-y-6">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Contato</span>
                <h3 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white leading-tight">
                    Vamos conversar sobre seu projeto?
                </h3>
                <p class="text-slate-350 text-sm sm:text-base leading-relaxed">
                    Precisa ilustrar um livro infantil, diagramar material pedagógico ou desenvolver um jogo educativo? Mande uma mensagem agora mesmo! Estou sempre aberto a novas parcerias e colaborações.
                </p>

                <!-- Infos -->
                <div class="space-y-4 pt-4">
                    <a href="mailto:{{ $settings->contact_email }}" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $settings->contact_email }}</span>
                    </a>
                    
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-emerald-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                        <span>{{ $settings->contact_phone }}</span>
                    </a>

                    <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->behance_url) }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-sky-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 11.085h-3.414v.933H22v-.933zm.006-2.585h-3.42v.91h3.42v-.91zM24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-12.822-1.954c0-1.125-.568-1.503-1.478-1.503H6.844v3.006h2.72c1.026-.001 1.614-.383 1.614-1.503zm.215 3.528c0-1.17-.611-1.545-1.579-1.545H6.844v3.09h2.951c1.077 0 1.599-.375 1.599-1.545zm8.932-1.28c0-2.302-1.325-3.08-3.056-3.08-1.848 0-3.078 1.139-3.078 3.099 0 2.012 1.341 3.061 3.256 3.061 1.677 0 2.766-.757 2.99-2.036h-1.411c-.198.543-.701.815-1.507.815-.99 0-1.543-.538-1.63-1.442h4.63c.036-.129.046-.264.046-.417zm-1.636-.931h-3.21c.125-.79.624-1.218 1.543-1.218.89 0 1.488.428 1.667 1.218z"/>
                        </svg>
                        <span>{{ $settings->behance_url }}</span>
                    </a>
                </div>
            </div>

            <!-- Formulário Moderno -->
            <div class="bg-slate-900/50 border border-slate-850 p-8 rounded-2xl shadow-xl glassmorphism space-y-4">
                <h4 class="font-outfit font-extrabold text-white text-lg border-b border-slate-850 pb-3">Fale Conosco</h4>
                
                <form action="mailto:{{ $settings->contact_email }}" method="GET" enctype="text/plain" class="space-y-4">
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Seu Nome</label>
                        <input type="text" name="subject" required placeholder="Ex: João Silva" class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650">
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mensagem</label>
                        <textarea name="body" required rows="4" placeholder="Descreva brevemente sua necessidade..." class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650"></textarea>
                    </div>

                    <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors text-center text-sm shadow-md shadow-blue-600/10">
                        Enviar E-mail
                    </button>
                </form>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 bg-dark-900 border-t border-slate-950 text-slate-500 text-xs">
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

    <!-- Script Inline da Página -->
    <script>
        function publicPortfolio() {
            return {
                scrolled: false,
                activeCategory: 'all',

                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 50;
                    });
                }
            }
        }
    </script>
</body>
</html>
