<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Danilo Miguel | Designer e Ilustrador</title>

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
                Transformando ideias em <span class="text-gradient">experiências visuais</span> marcantes
            </h1>

            <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-normal leading-relaxed">
                Cada traço, cor e forma é pensado de maneira estratégica para contar histórias envolventes em livros infantis, materiais pedagógicos e jogos educativos.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 pt-4">
                <a href="#portfolio" class="w-full sm:w-auto px-8 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-full transition-all shadow-lg shadow-blue-600/25 text-center">
                    Ver Portfólio
                </a>
                <a href="https://wa.me/5514991436268" target="_blank" class="w-full sm:w-auto px-8 py-3.5 bg-slate-900 border border-slate-800 hover:bg-slate-850 text-white font-bold rounded-full transition-all text-center flex items-center justify-center gap-2">
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
                    Filtre e conheça alguns dos trabalhos mais recentes e marcantes produzidos para editoras e autores.
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

            <!-- Grid de Trabalhos -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                @forelse($items as $item)
                    <div x-show="activeCategory === 'all' || activeCategory === '{{ $item->portfolio_category_id }}'"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         class="group bg-slate-900/50 border border-slate-850 hover:border-slate-700/80 rounded-xl overflow-hidden shadow-sm flex flex-col justify-between hover:shadow-xl transition-all duration-300 relative">
                        
                        <!-- Thumbnail aspect-video -->
                        <div class="relative aspect-video bg-slate-950 overflow-hidden">
                            @if($item->thumb_path)
                                <img src="{{ asset('storage/' . $item->thumb_path) }}" 
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" 
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-slate-600">
                                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                </div>
                            @endif

                            @if($item->is_featured)
                                <span class="absolute top-3 left-3 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded bg-yellow-500 text-slate-950 flex items-center gap-1 shadow-md">
                                    ★ Destaque
                                </span>
                            @endif
                        </div>

                        <!-- Info do Card -->
                        <div class="p-6 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                <span class="text-[9px] font-extrabold uppercase tracking-widest text-blue-400">
                                    {{ $item->category->name }}
                                </span>
                                <h4 class="font-extrabold text-white text-base leading-tight group-hover:text-blue-400 transition-colors">
                                    {{ $item->title }}
                                </h4>
                                <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                                    {{ strip_tags($item->description) }}
                                </p>
                            </div>

                            @if($item->technologies)
                                <div class="flex flex-wrap gap-1 pt-1">
                                    @foreach(explode(',', $item->technologies) as $tech)
                                        <span class="bg-slate-950 text-slate-450 border border-slate-800 text-[9px] font-bold px-2 py-0.5 rounded uppercase tracking-wide">
                                            {{ trim($tech) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            <div class="flex items-center justify-between pt-3 border-t border-slate-850">
                                <!-- Likes & Views -->
                                <div class="flex items-center gap-3 text-slate-500 text-xs font-bold">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                        </svg>
                                        <span>{{ $item->views }}</span>
                                    </span>
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5 text-rose-500 fill-rose-500" viewBox="0 0 24 24">
                                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                                        </svg>
                                        <span>{{ $item->likes }}</span>
                                    </span>
                                </div>

                                <button type="button" 
                                        @click="openModal({{ json_encode($item) }})" 
                                        class="px-3.5 py-1.5 bg-blue-600/10 hover:bg-blue-600 text-blue-400 hover:text-white text-xs font-semibold rounded transition-colors uppercase tracking-wider">
                                    Detalhes
                                </button>
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
                    Prazer, sou Danilo Miguel
                </h3>
                <p class="text-slate-350 leading-relaxed text-sm sm:text-base">
                    Com anos de experiência focados em design editorial e ilustração, crio soluções sob medida que integram beleza artística e inteligência estrutural. Desenvolvo livros de literatura infantil, materiais didáticos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas e identidades visuais corporativas.
                </p>
                <p class="text-slate-350 leading-relaxed text-sm sm:text-base">
                    Meu trabalho visa transformar ideias abstratas e materiais textuais densos em composições leves, dinâmicas e altamente interativas. Acompanho autores e editoras desde o conceito original até a entrega do arquivo final preparado para as gráficas.
                </p>

                <!-- Skills Grid -->
                <div class="space-y-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide block">Especialidades</span>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">Ilustração Infantil</span>
                        <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">Diagramação Editorial</span>
                        <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">Design de Jogos Pedagógicos</span>
                        <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">Identidade Visual</span>
                        <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">Criação de Personagens</span>
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
                <!-- Q1 -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                            @click="activeFaq = activeFaq === 1 ? null : 1">
                        <span>Que tipo de materiais você desenvolve?</span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                             :class="activeFaq === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal" x-show="activeFaq === 1" x-collapse x-cloak>
                        Desenvolvo livros infantis, materiais didáticos/pedagógicos de estimulação cognitiva, jogos personalizados de tabuleiro ou cartas, diagramação de catálogos, capas de livros, logotipos corporativos e peças gráficas gerais.
                    </div>
                </div>

                <!-- Q2 -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                            @click="activeFaq = activeFaq === 2 ? null : 2">
                        <span>Qual é o prazo de entrega dos projetos?</span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                             :class="activeFaq === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal" x-show="activeFaq === 2" x-collapse x-cloak>
                        O prazo varia conforme a complexidade de cada demanda. Projetos simples e pontuais levam em média de 10 a 20 dias úteis. Projetos editoriais maiores com alto volume de ilustrações autorais podem requerer prazos mais amplos, definidos em orçamento.
                    </div>
                </div>

                <!-- Q3 -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                            @click="activeFaq = activeFaq === 3 ? null : 3">
                        <span>Como posso solicitar um orçamento?</span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                             :class="activeFaq === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal" x-show="activeFaq === 3" x-collapse x-cloak>
                        Basta clicar no botão do WhatsApp disponível em nosso site e enviar uma mensagem com os detalhes básicos do seu projeto. Retorno o contato no mesmo dia para alinhar mais informações.
                    </div>
                </div>

                <!-- Q4 -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                            @click="activeFaq = activeFaq === 4 ? null : 4">
                        <span>Você atende clientes de fora do seu estado?</span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                             :class="activeFaq === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal" x-show="activeFaq === 4" x-collapse x-cloak>
                        Sim! Atendo clientes e editoras de todo o Brasil e do exterior de forma 100% remota. O processo é simples: compartilhamento de referências e arquivos por e-mail/nuvem e reuniões por WhatsApp ou videoconferência.
                    </div>
                </div>

                <!-- Q5 -->
                <div class="bg-slate-900/40 border border-slate-850 rounded-xl overflow-hidden">
                    <button class="w-full px-6 py-5 text-left flex items-center justify-between gap-4 font-bold text-white hover:bg-slate-900/80 transition-colors"
                            @click="activeFaq = activeFaq === 5 ? null : 5">
                        <span>Em quais formatos você entrega os arquivos finais?</span>
                        <svg class="w-5 h-5 text-slate-400 transition-transform duration-200" 
                             :class="activeFaq === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div class="px-6 pb-5 text-sm text-slate-350 leading-relaxed font-normal" x-show="activeFaq === 5" x-collapse x-cloak>
                        Entrego os arquivos finais fechados prontos para impressão (geralmente PDF em padrão X1a ou similar) e, caso acordado no contrato, posso fornecer os arquivos editáveis fontes (Adobe InDesign, Illustrator ou Photoshop).
                    </div>
                </div>
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
                    <a href="mailto:danilo.a.miguel@hotmail.com" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>danilo.a.miguel@hotmail.com</span>
                    </a>
                    
                    <a href="https://wa.me/5514991436268" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-emerald-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                        <span>(14) 99143-6268</span>
                    </a>

                    <a href="https://behance.net/danilomiguel" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-sky-500 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M22 11.085h-3.414v.933H22v-.933zm.006-2.585h-3.42v.91h3.42v-.91zM24 12c0 6.627-5.373 12-12 12S0 18.627 0 12 5.373 0 12 0s12 5.373 12 12zm-12.822-1.954c0-1.125-.568-1.503-1.478-1.503H6.844v3.006h2.72c1.026-.001 1.614-.383 1.614-1.503zm.215 3.528c0-1.17-.611-1.545-1.579-1.545H6.844v3.09h2.951c1.077 0 1.599-.375 1.599-1.545zm8.932-1.28c0-2.302-1.325-3.08-3.056-3.08-1.848 0-3.078 1.139-3.078 3.099 0 2.012 1.341 3.061 3.256 3.061 1.677 0 2.766-.757 2.99-2.036h-1.411c-.198.543-.701.815-1.507.815-.99 0-1.543-.538-1.63-1.442h4.63c.036-.129.046-.264.046-.417zm-1.636-.931h-3.21c.125-.79.624-1.218 1.543-1.218.89 0 1.488.428 1.667 1.218z"/>
                        </svg>
                        <span>behance.net/danilomiguel</span>
                    </a>
                </div>
            </div>

            <!-- Formulário Moderno -->
            <div class="bg-slate-900/50 border border-slate-850 p-8 rounded-2xl shadow-xl glassmorphism space-y-4">
                <h4 class="font-outfit font-extrabold text-white text-lg border-b border-slate-850 pb-3">Fale Conosco</h4>
                
                <form action="mailto:danilo.a.miguel@hotmail.com" method="GET" enctype="text/plain" class="space-y-4">
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

    <!-- Overlay Modal de Detalhes do Trabalho -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-show="modalOpen"
         x-transition
         x-cloak>
        
        <div class="bg-[#0b1120] border border-slate-800 rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto shadow-2xl flex flex-col lg:flex-row"
             @click.away="closeModal()">
            
            <!-- Esquerda: Imagem e Galeria Carousel -->
            <div class="w-full lg:w-1/2 bg-slate-950 p-6 flex flex-col justify-center border-b lg:border-b-0 lg:border-r border-slate-850 relative">
                <!-- Botão fechar (Mobile) -->
                <button type="button" 
                        @click="closeModal()" 
                        class="absolute top-4 right-4 lg:hidden w-8 h-8 rounded-full bg-slate-900/80 text-slate-400 hover:text-white flex items-center justify-center border border-slate-800 z-10">
                    &times;
                </button>

                <!-- Imagem Principal Ativa -->
                <div class="aspect-video w-full rounded-lg overflow-hidden bg-slate-900 border border-slate-800">
                    <img :src="activeImage" class="w-full h-full object-cover" x-show="activeImage">
                    <div class="w-full h-full flex items-center justify-center text-slate-700" x-show="!activeImage">
                        Sem Imagem
                    </div>
                </div>

                <!-- Carousel de Miniaturas -->
                <div class="flex gap-2 mt-4 overflow-x-auto py-1" x-show="modalItem && modalItem.images && modalItem.images.length > 0">
                    <!-- Thumbnail principal como miniatura -->
                    <button type="button" 
                            @click="activeImage = '{{ asset('storage') }}/' + modalItem.thumb_path" 
                            class="w-16 h-12 rounded border overflow-hidden shrink-0 transition-all duration-200"
                            :class="activeImage === '{{ asset('storage') }}/' + modalItem.thumb_path ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-800 hover:border-slate-600'">
                        <img :src="'{{ asset('storage') }}/' + modalItem.thumb_path" class="w-full h-full object-cover">
                    </button>
                    <!-- Extra Images -->
                    <template x-for="img in (modalItem ? modalItem.images : [])" :key="img.id">
                        <button type="button" 
                                @click="activeImage = '{{ asset('storage') }}/' + img.image_path" 
                                class="w-16 h-12 rounded border overflow-hidden shrink-0 transition-all duration-200"
                                :class="activeImage === '{{ asset('storage') }}/' + img.image_path ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-slate-800 hover:border-slate-600'">
                            <img :src="'{{ asset('storage') }}/' + img.image_path" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
            </div>

            <!-- Direita: Informações Detalhadas -->
            <div class="w-full lg:w-1/2 p-8 flex flex-col justify-between space-y-6 relative">
                <!-- Botão fechar (Desktop) -->
                <button type="button" 
                        @click="closeModal()" 
                        class="hidden lg:flex absolute top-6 right-6 w-8 h-8 rounded-full bg-slate-900 border border-slate-850 text-slate-400 hover:text-white items-center justify-center transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>

                <div class="space-y-6 pr-0 lg:pr-6">
                    <div class="space-y-2">
                        <span class="text-[10px] font-black uppercase tracking-widest text-blue-500 block" x-text="modalItem ? modalItem.category.name : ''"></span>
                        <h3 class="text-xl sm:text-2xl font-outfit font-black text-white leading-tight" x-text="modalItem ? modalItem.title : ''"></h3>
                    </div>

                    <!-- Métricas interativas -->
                    <div class="flex items-center gap-6 text-slate-400 text-xs font-bold py-2 border-y border-slate-850">
                        <span class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                            <span x-text="(modalItem ? modalItem.views : 0) + ' Visualizações'"></span>
                        </span>
                        
                        <button type="button" 
                                @click="likeItem()" 
                                class="flex items-center gap-2 text-slate-400 hover:text-rose-500 transition-colors"
                                :disabled="liked">
                            <svg class="w-4 h-4 text-rose-500 transition-transform" 
                                 :class="liked ? 'scale-110' : 'hover:scale-120'"
                                 :fill="liked ? 'currentColor' : 'none'"
                                 stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span x-text="(modalItem ? modalItem.likes : 0) + ' Curtidas'"></span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <!-- Descrição -->
                        <div class="space-y-1">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Descrição</span>
                            <div class="text-xs text-slate-350 leading-relaxed font-normal text-justify select-text whitespace-pre-line" x-text="modalItem ? stripTags(modalItem.description) : ''"></div>
                        </div>

                        <!-- Cliente -->
                        <div class="space-y-1" x-show="modalItem && modalItem.client">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Cliente</span>
                            <div class="text-xs text-white font-semibold" x-text="modalItem && modalItem.client ? modalItem.client.name : ''"></div>
                        </div>

                        <!-- Tecnologias -->
                        <div class="space-y-1.5" x-show="modalItem && modalItem.technologies">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Tecnologias Utilizadas</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="tech in (modalItem && modalItem.technologies ? modalItem.technologies.split(',') : [])" :key="tech">
                                    <span class="bg-slate-900 text-slate-300 border border-slate-800 text-[10px] font-medium px-2.5 py-0.5 rounded" x-text="tech.trim()"></span>
                                </template>
                            </div>
                        </div>

                        <!-- Autores -->
                        <div class="space-y-1.5" x-show="modalItem && modalItem.authors && modalItem.authors.length > 0">
                            <span class="text-[9px] font-bold text-slate-500 uppercase tracking-wider block">Equipe / Autores</span>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="author in (modalItem ? modalItem.authors : [])" :key="author.id">
                                    <span class="bg-slate-900 text-slate-300 border border-slate-800 text-[10px] font-medium px-2.5 py-0.5 rounded" x-text="author.name"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer do Modal -->
                <div class="pt-6 border-t border-slate-850 flex items-center justify-end gap-2 pr-0 lg:pr-6">
                    <button type="button" 
                            @click="closeModal()" 
                            class="px-4 py-2 border border-slate-800 text-slate-400 hover:text-white text-xs font-semibold rounded transition-colors uppercase tracking-wider">
                        Fechar
                    </button>
                    <a :href="modalItem && modalItem.redirect_url ? modalItem.redirect_url : '#'" 
                       target="_blank" 
                       x-show="modalItem && modalItem.redirect_url" 
                       class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded transition-colors uppercase tracking-wider">
                        Acessar Link
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- Script Inline da Página -->
    <script>
        function publicPortfolio() {
            return {
                scrolled: false,
                activeCategory: 'all',
                
                // Modal
                modalOpen: false,
                modalItem: null,
                activeImage: '',
                liked: false,

                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 50;
                    });
                },

                openModal(item) {
                    this.modalItem = item;
                    this.activeImage = '{{ asset('storage') }}/' + item.thumb_path;
                    this.liked = false;
                    this.modalOpen = true;

                    // Incrementa visualização via fetch
                    fetch(`/portfolio/${item.id}/views`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            item.views = data.views;
                        }
                    });
                },

                closeModal() {
                    this.modalOpen = false;
                    this.modalItem = null;
                    this.activeImage = '';
                },

                likeItem() {
                    if (this.liked || !this.modalItem) return;
                    this.liked = true;

                    fetch(`/portfolio/${this.modalItem.id}/likes`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.modalItem.likes = data.likes;
                        }
                    });
                },

                stripTags(html) {
                    let doc = new DOMParser().parseFromString(html, 'text/html');
                    return doc.body.textContent || "";
                }
            }
        }
    </script>
</body>
</html>
