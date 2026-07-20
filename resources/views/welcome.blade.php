<!DOCTYPE html>
<html lang="pt-BR" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $settings->site_title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon_site.png') }}">

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
        .bg-slate-950\/80 {
            background-color: rgba(255, 255, 255, 0.9) !important;
        }
        .text-slate-200 {
            color: #1e293b !important;
        }
        .placeholder-slate-650::placeholder {
            color: #94a3b8 !important;
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
        }
        .hamburger-bar {
            background-color: #1e293b !important;
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
        .hamburger-bar {
            background-color: #ffffff !important;
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
        
        @keyframes float-blob-1 {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.95); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        @keyframes float-blob-2 {
            0% { transform: translate(0px, 0px) scale(1); }
            50% { transform: translate(-30px, 40px) scale(1.05); }
            100% { transform: translate(0px, 0px) scale(1); }
        }
        .animate-float-1 {
            animation: float-blob-1 12s ease-in-out infinite;
        }
        .animate-float-2 {
            animation: float-blob-2 15s ease-in-out infinite;
        }

        /* Custom themed scrollbar for portfolio site */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #070a13;
        }
        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, {{ $settings->primary_color ?? '#3b82f6' }} 0%, {{ $settings->secondary_color ?? '#1d4ed8' }} 100%);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: {{ $settings->primary_color ?? '#3b82f6' }};
        }
        .footer-social-link {
            transition: all 0.3s ease;
        }
        .footer-social-link:hover {
            color: {{ $settings->primary_color ?? '#3b82f6' }} !important;
            border-color: {{ ($settings->primary_color ?? '#3b82f6') }}40 !important;
            background-color: {{ ($settings->primary_color ?? '#3b82f6') }}15 !important;
        }
    </style>
</head>
<body class="text-slate-100 antialiased min-h-screen selection:bg-blue-500 selection:text-white" x-data="publicPortfolio()">
    <!-- Custom Cursor Elements -->
    <div id="custom-cursor" class="pointer-events-none fixed top-0 left-0 w-8 h-8 rounded-full border border-blue-500/40 mix-blend-difference z-[9999] transition-all duration-200 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>
    <div id="custom-cursor-dot" class="pointer-events-none fixed top-0 left-0 w-1.5 h-1.5 bg-blue-500 rounded-full z-[9999] transition-all duration-200 ease-out transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>
    @php
        $cleanPhone = preg_replace('/\D/', '', $settings->contact_phone);
        $whatsappNumber = str_starts_with($cleanPhone, '55') ? $cleanPhone : '55' . $cleanPhone;

        $getSvg = function($filename, $class = 'w-5 h-5 fill-current') {
            $path = public_path($filename);
            if (file_exists($path)) {
                $svg = file_get_contents($path);
                $svg = preg_replace('/<\?xml.*?\?>/s', '', $svg);
                
                // Replace hardcoded fills and strokes with currentColor to allow CSS/Tailwind coloring
                $svg = preg_replace('/fill="[^"]*"/', 'fill="currentColor"', $svg);
                $svg = preg_replace('/stroke="[^"]*"/', 'stroke="currentColor"', $svg);
                
                // Inject classes and default fill
                $svg = preg_replace('/<svg/', '<svg fill="currentColor" class="' . $class . '"', $svg, 1);
                return $svg;
            }
            return '';
        };
    @endphp

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
                <a href="{{ route('dashboard') }}" class="hidden md:flex px-4 py-2 rounded-full border border-blue-500/30 hover:border-blue-500 hover:bg-blue-500 text-blue-400 hover:text-white text-xs font-semibold uppercase tracking-wider transition-all items-center gap-2 shadow-sm shadow-blue-500/10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    <span>Área Restrita</span>
                </a>
                
                <!-- Hamburger Menu Button -->
                <button type="button" @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden flex flex-col items-center justify-center w-9 h-9 space-y-1.5 focus:outline-none z-50 relative" aria-label="Menu">
                    <span class="w-6 h-0.5 hamburger-bar transition-all duration-300 transform" :class="mobileMenuOpen ? 'rotate-45 translate-y-2' : ''"></span>
                    <span class="w-6 h-0.5 hamburger-bar transition-all duration-300" :class="mobileMenuOpen ? 'opacity-0' : ''"></span>
                    <span class="w-6 h-0.5 hamburger-bar transition-all duration-300 transform" :class="mobileMenuOpen ? '-rotate-45 -translate-y-2' : ''"></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Menu Lateral Mobile (Drawer) -->
    <div x-show="mobileMenuOpen" 
         class="fixed inset-0 z-[60] md:hidden flex justify-end"
         x-transition:enter="transition-opacity ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @touchstart="handleTouchStart($event)"
         @touchend="handleTouchEnd($event)"
         style="display: none;">
         
         <!-- Backdrop -->
         <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" @click="mobileMenuOpen = false"></div>

         <!-- Drawer Panel -->
         <div class="relative w-80 max-w-full bg-slate-900 border-l border-white/[0.08] h-full flex flex-col justify-between p-6 shadow-2xl z-[70] glassmorphism"
              x-show="mobileMenuOpen"
              x-transition:enter="transition ease-out duration-300 transform"
              x-transition:enter-start="translate-x-full"
              x-transition:enter-end="translate-x-0"
              x-transition:leave="transition ease-in duration-200 transform"
              x-transition:leave-start="translate-x-0"
              x-transition:leave-end="translate-x-full">
              
              <!-- Close Button inside Drawer Panel -->
              <button type="button" @click="mobileMenuOpen = false" class="absolute top-4 right-4 text-slate-450 hover:text-white transition-colors" aria-label="Fechar menu">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                  </svg>
              </button>
              
              <div class="space-y-8 pt-16">
                  <!-- Logo / Título -->
                  <div class="border-b border-white/[0.08] pb-4">
                      <span class="font-outfit font-black text-lg tracking-tight text-white">
                          DANILO<span class="text-blue-500">MIGUEL</span>
                      </span>
                  </div>

                  <!-- Links de Navegação -->
                  <nav class="flex flex-col gap-6">
                      <a href="#home" @click="mobileMenuOpen = false" class="text-lg font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-3">
                          <span>🏠</span> Início
                      </a>
                      <a href="#portfolio" @click="mobileMenuOpen = false" class="text-lg font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-3">
                          <span>📁</span> Portfólio
                      </a>
                      <a href="#about" @click="mobileMenuOpen = false" class="text-lg font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-3">
                          <span>🧑‍🎨</span> Sobre Mim
                      </a>
                      <a href="#faq" @click="mobileMenuOpen = false" class="text-lg font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-3">
                          <span>💬</span> FAQ
                      </a>
                      <a href="#contact" @click="mobileMenuOpen = false" class="text-lg font-semibold text-slate-300 hover:text-white transition-colors flex items-center gap-3">
                          <span>📞</span> Contato
                      </a>
                  </nav>
              </div>

              <!-- Rodapé do Drawer -->
              <div class="border-t border-white/[0.08] pt-6 flex flex-col gap-4">
                  <a href="{{ route('dashboard') }}" class="w-full py-3 rounded-full border border-blue-500/30 hover:border-blue-500 hover:bg-blue-500 text-blue-400 hover:text-white text-xs font-semibold uppercase tracking-wider transition-all text-center flex items-center justify-center gap-2 shadow-sm shadow-blue-500/10">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                      </svg>
                      <span>Área Administrativa</span>
                  </a>
                  <p class="text-[10px] text-slate-450 text-center">Arraste para a direita para fechar 👉</p>
              </div>
         </div>
    </div>

    <!-- Hero Section -->
    <section id="home" class="relative min-h-screen flex items-center justify-center pt-20 overflow-hidden bg-radial-gradient">
        <!-- Detalhes de luz de fundo -->
        <div class="absolute top-[15%] left-[15%] w-[350px] h-[350px] bg-blue-600/15 rounded-full blur-[100px] pointer-events-none animate-float-1"></div>
        <div class="absolute bottom-[15%] right-[15%] w-[450px] h-[450px] bg-indigo-600/15 rounded-full blur-[120px] pointer-events-none animate-float-2"></div>

        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8 relative z-10">
            <div class="flex justify-center">
                <svg id="b" xmlns="http://www.w3.org/2000/svg" version="1.1" viewBox="0 0 3756.6 878.8" class="h-28 sm:h-36 w-auto max-w-full">
                  <defs>
                    <style>
                      .st0 {
                        fill: {{ $settings->primary_color ?? '#3b82f6' }} !important;
                        transition: fill 0.3s ease;
                      }
                    </style>
                  </defs>
                  <g id="c">
                    <g>
                      <g>
                        <path class="st0" d="M304.4,177.3c0,24.9-3.2,47.4-9.6,67.4s-15.4,37.8-26.8,53.4c-11.5,15.6-25.1,29-41,40.2-15.8,11.2-33.2,20.4-52.1,27.6-18.9,7.2-38.9,12.6-60.2,16-21.3,3.5-43.1,5.2-65.6,5.2s-16.6-.2-24.6-.7c-8-.4-16.2-1.1-24.6-2L7.5,26.2c19.6-6.2,39.7-10.6,60.5-13.2,20.7-2.6,41.3-3.9,61.8-3.9s48.9,3.8,70.2,11.5c21.4,7.7,39.8,18.7,55.3,33.1,15.5,14.4,27.5,32,36.2,52.9,8.6,20.8,13,44.4,13,70.8h0ZM194.9,194.4c.2-9.6-.8-18.9-3.1-27.8-2.2-8.9-5.9-16.8-10.9-23.6-5.1-6.9-11.5-12.4-19.4-16.6-7.8-4.2-17.2-6.5-28-6.8l-10.7,165.6c10.7-1.6,20.4-5.1,29.1-10.5,8.7-5.4,16.2-12.1,22.6-20,6.3-7.9,11.2-16.7,14.7-26.4,3.5-9.7,5.4-19.7,5.7-30v-3.7h0Z"/>
                        <path class="st0" d="M649.3,366.4l-132.5,17.1-16-60.4h-60.9l-13.4,60.4-136.2-13.4L396.7,14.4l148.5-7.5,104.1,359.4h0ZM491.2,241.4l-20.3-92.9-19.8,92.9h40.1Z"/>
                        <path class="st0" d="M1023.6,6.4l-19.2,362.1-139.9,12.8-74.8-193.9-11.8,197.6h-120.2l9.6-378.7L800.9,0l92.4,192.3,4.3-181.6,126-4.3h0Z"/>
                        <path class="st0" d="M1194.9,13.4l-29.4,363.7-112.7,5.9V20.8l142.1-7.5Z"/>
                        <path class="st0" d="M1453.5,237.1l-11.2,118.6-224.8,17.6c1.1-62.1,2.3-123.9,3.6-185.3,1.3-61.4,3-123.2,4.9-185.3h138.9c-4.1,39-8,77.9-11.7,116.8-3.7,38.9-6.4,78.1-8,117.6,8.4.4,16.6.5,24.8.5h24.8c10,0,19.8,0,29.4-.3,9.6-.2,19.4-.3,29.4-.3h0Z"/>
                        <path class="st0" d="M1801.6,200.8c0,16.2-1.9,31.8-5.6,46.9-3.7,15-9.1,29.2-16.2,42.3-7,13.2-15.6,25.2-25.6,36.2-10.1,10.9-21.4,20.3-33.9,28.2-12.6,7.8-26.2,13.9-41,18.3-14.8,4.4-30.4,6.5-47,6.5s-31.3-2-45.8-6.1c-14.5-4.1-28.1-9.8-40.7-17.2-12.6-7.4-24.1-16.3-34.3-26.7-10.2-10.4-19-22-26.2-34.7-7.2-12.7-12.8-26.4-16.8-41-4-14.6-6-29.8-6-45.7s1.9-30.7,5.6-45.5c3.7-14.9,9.1-28.9,16.2-42.1,7-13.2,15.5-25.3,25.5-36.3,10-11,21.1-20.6,33.5-28.6,12.4-8,25.8-14.2,40.2-18.7,14.4-4.4,29.6-6.7,45.7-6.7,25.8,0,49.4,4,70.6,12,21.3,8,39.4,19.5,54.5,34.3,15,14.9,26.7,32.8,35,53.9,8.3,21.1,12.4,44.6,12.4,70.6h0ZM1683,207.8c0-7.7-1.1-15.1-3.3-22.3-2.2-7.2-5.5-13.6-9.7-19.2s-9.6-10.1-15.9-13.6c-6.3-3.5-13.6-5.2-21.8-5.2s-15.8,1.5-22.4,4.5c-6.6,3-12.2,7.2-17,12.4-4.7,5.3-8.3,11.4-10.8,18.4-2.5,7-3.7,14.5-3.7,22.3s1.1,15,3.2,22.4,5.3,14.2,9.6,20.3c4.3,6.1,9.6,10.9,15.9,14.7,6.3,3.7,13.7,5.6,22,5.6s15.9-1.6,22.6-4.9c6.7-3.3,12.3-7.7,17-13.4,4.6-5.6,8.2-12.1,10.7-19.4,2.5-7.3,3.7-14.9,3.7-22.7h0Z"/>
                        <path class="st0" d="M414.7,477.2l-24.6,392-121.8,5.3,1.1-206.7-55,166.6h-47l-42.2-149-.5,173-123.4,3.7,5.3-369.1,137.3-5.3,54.5,176.8,72.6-187.5h143.7,0Z"/>
                        <path class="st0" d="M583.9,490.5l-29.4,363.7-112.7,5.9v-362.1l142.1-7.5h0Z"/>
                        <path class="st0" d="M927.4,812c-8.4,10.5-18.7,19.7-31,27.5-12.3,7.8-25.5,14.3-39.5,19.5-14.1,5.2-28.5,9-43.3,11.6-14.8,2.6-28.8,3.9-42.2,3.9-25.8,0-49.6-4.6-71.3-13.9-21.7-9.3-40.5-22-56.2-38.3-15.8-16.3-28.1-35.5-37-57.5-8.9-22.1-13.4-45.8-13.4-71.3s2.2-38.4,6.7-57c4.4-18.6,10.8-36.1,19.1-52.6,8.3-16.5,18.4-31.6,30.3-45.4,11.9-13.8,25.5-25.7,40.6-35.6,15.1-10,31.7-17.7,49.7-23.2,18-5.5,37.2-8.3,57.7-8.3s15.8.3,24.6.9c8.7.6,17.4,1.7,26,3.2,8.6,1.5,17,3.6,25.2,6.3,8.2,2.7,15.7,6,22.4,9.9l-7.5,102c-10-3.7-20.3-6.3-30.8-7.6-10.6-1.3-21-2-31.1-2s-27.6,2.2-39.9,6.5c-12.4,4.4-23.1,10.6-32.3,18.8-9.2,8.2-16.4,18.2-21.8,29.9-5.3,11.8-8,25-8,39.8s1.2,18.2,3.7,26.8c2.5,8.6,6.3,16.3,11.3,23,5.1,6.7,11.4,12.1,19,16.2,7.6,4.1,16.3,6.1,26.3,6.1s13.8-.9,21-2.7c7.2-1.8,13.4-4.8,18.6-9.1l1.1-20.8-64.1-1.1,3.2-80.1c26.5-.9,53-1.6,79.6-2.3,26.5-.6,53.2-1.5,80.1-2.5l3.2,179.5h0Z"/>
                        <path class="st0" d="M1272.6,569.6c0,13.2-.6,27.7-1.9,43.5-1.2,15.8-3.3,32.2-6.1,49.1-2.8,16.9-6.5,34-11.1,51.4-4.5,17.4-10.2,34-17,50.1-6.8,16-14.6,31-23.6,45-9,14-19.3,26.2-31,36.6-11.7,10.4-24.7,18.6-39.1,24.6-14.4,6-30.4,8.9-48.1,8.9s-38.6-3.3-53.8-10c-15.2-6.7-28.3-15.7-39.1-27-10.9-11.3-19.8-24.5-26.7-39.5-6.9-15-12.4-31-16.3-47.8-3.9-16.8-6.6-34-8.1-51.5-1.5-17.5-2.3-34.4-2.3-50.6,0-24.9,1.2-49.8,3.7-74.6,2.5-24.8,6-49.6,10.7-74.4l125,4.8c-4.8,28-8.9,56-12.4,84.3-3.5,28.2-5.2,56.6-5.2,85.1s.1,7.5.4,13.5c.3,6,.8,12.6,1.5,20,.7,7.4,1.7,15,3.1,22.7,1.3,7.7,3.2,14.8,5.5,21.2,2.3,6.4,5.1,11.7,8.4,15.8,3.3,4.1,7.2,6.1,11.6,6.1s9.8-2.4,13.9-7.3c4.1-4.9,7.7-11.4,10.8-19.6,3.1-8.2,5.8-17.6,8.1-28.3,2.3-10.7,4.3-21.8,5.9-33.4,1.6-11.6,2.9-23.1,3.9-34.7,1-11.6,1.8-22.3,2.4-32.3.6-10,1-18.8,1.2-26.4.2-7.7.3-13.4.3-17.1,0-13.9-.2-27.7-.7-41.5-.4-13.8-1.1-27.5-2-41.3h125c2.1,24.6,3.2,49.5,3.2,74.8h0Z"/>
                        <path class="st0" d="M1552.3,481.4c-1.1,16.7-2,33.3-2.9,49.7-.9,16.4-2,32.9-3.5,49.7l-113.2,5.9-2.7,33.6h79.6l-5.9,82.8-80.1,2.7-2.7,39h113.2c-1.1,19.6-2.2,39-3.3,58.3-1.2,19.3-2.2,38.7-3.1,58.1l-230.2,5.3,8.5-385.1h246.2,0Z"/>
                        <path class="st0" d="M1801.6,714.3l-11.2,118.6-224.8,17.6c1.1-62.1,2.3-123.9,3.6-185.3,1.3-61.4,3-123.2,4.9-185.3h138.9c-4.1,39-8,77.9-11.7,116.8-3.7,38.9-6.4,78.1-8,117.6,8.4.4,16.6.5,24.8.5h24.8c10,0,19.8,0,29.4-.3,9.6-.2,19.4-.3,29.4-.3h0Z"/>
                      </g>
                      <g>
                        <g>
                          <path class="st0" d="M2150.6,143.8c19.5-6.8,45.3-10.3,77.5-10.3,58.7,0,98.9,14.5,120.5,43.5,8.4,11.1,12.6,25.2,12.6,42.3.3,2.4.4,4.7.4,7.1,0,26.3-15.4,53.1-46.2,80.2-28.2,25.6-63,46.1-104.3,61.6-27.4,10.5-50.1,15.8-68,15.8s-13.7-1-19-3.2v-246.6h5.5c6.1,0,10.9,1,14.6,3,3.7,2,5.8,4.1,6.3,6.5h0ZM2334.7,222.5c-2.4-23.4-15.4-40.8-39.1-52.2-17.7-8.4-39.8-12.6-66.4-12.6l-7.1.4c-25,.5-46.6,4.5-64.8,11.9v188.5c18.4,0,41.5-6.7,69.2-20.2,47.9-23.7,80.9-51.2,98.8-82.6,6.3-11.1,9.5-22.1,9.5-33.2h0Z"/>
                          <path class="st0" d="M2462.8,358.8c23.2,0,58.1-10.5,104.7-31.6,2.9,0,5.3,1,7.1,3,1.8,2,2.8,4.3,2.8,6.9-10.5,17.9-30.7,31.9-60.5,41.9-29.8,10-57.6,11.7-83.4,5.1-29.2-7.6-47-24.1-53.3-49.4-.5-5-.8-11.7-.8-20.2s2.2-19.6,6.5-33.4c4.3-13.8,11.8-26.9,22.3-39.3,10.5-12.4,22.9-22.1,37.1-29.2,14.2-7.1,29.4-10.7,45.4-10.7s32.4,5.3,51.4,15.8c9,5,16.1,10.7,21.3,17,5.3,6.3,7.9,12.8,7.9,19.4s-.1,3.7-.4,5.5c-.5,16.1-8.3,26.2-23.3,30.4-6.6,1.8-14.2,2.8-22.9,2.8l-56.9-2.4c-19,0-33.5,4.1-43.5,12.3-5.8,4.5-9.9,11.2-12.2,20.2,6.6,24,23.4,36,50.6,36h0ZM2533.3,236.7c-5.9-4.2-12.1-7.2-18.4-9.1-6.3-1.8-11.6-2.8-15.8-2.8s-11.1,1.4-20.5,4.1-17.9,5.9-25.3,9.3c-18.2,8.4-27.3,17.3-27.3,26.5,21.1,1.8,36.9,2.8,47.4,2.8,29.2,0,49-2.5,59.3-7.5,6.3-2.6,9.5-5.9,9.5-9.9s-3-9.2-8.9-13.4h0Z"/>
                          <path class="st0" d="M2736.2,284.5c29,0,43.5,7.4,43.5,22.1s-8,24-24.1,38.7c-24,21.9-56.2,39.4-96.8,52.6-1.8,0-3.9-1.8-6.1-5.5-2.2-3.7-4-6.5-5.1-8.5-1.2-2-2-3.2-2.6-3.8,30.3-10,55.2-22.1,74.7-36.4,13.2-9.5,19.8-17.3,19.8-23.3s-7.8-9.1-23.3-9.1-38.3,3.7-68.4,11.1c-13.7-.5-25.6-5.4-35.6-14.6-8.7-8.2-13-16.4-13-24.7s2.4-15.9,7.1-22.9c4.7-7,11.1-13.5,19-19.6,7.9-6.1,17.1-11.6,27.7-16.6,25.8-12.4,52.6-18.6,80.2-18.6s16.1.8,23.7,2.4c6.3,0,10,2.8,11.1,8.3,1,5.5,1.6,14.6,1.6,27.3-15.5-7.1-33.1-10.7-52.6-10.7s-44.3,4.9-60.1,14.6c-16.3,9.5-24.8,21.9-25.3,37.1,0,7.4,5.4,11.1,16.2,11.1l28.8-3.2c24.8-5.3,44.7-7.9,59.7-7.9h0Z"/>
                          <path class="st0" d="M2803,147c0-9.2,5.4-13.8,16.2-13.8s12.9,1.6,17.4,4.7c.8,2.9,1.2,5.7,1.2,8.5s-1.1,5.5-3.2,8.1c-2.1,2.6-5.3,4-9.5,4s-8.4-1-12.6-3c-4.2-2-7.4-4.8-9.5-8.5h0ZM2839.8,200.8c3.4,49.3,6.3,106.3,8.7,171.1-1.6,8.2-7.9,12.2-19,12.2s-5.9-.6-8.9-1.8c-3-1.2-4.9-2.8-5.7-4.9v-176.6h24.9Z"/>
                          <path class="st0" d="M2979.7,555.6l-5.5-.4c-7.9,0-16.3.5-25.3,1.6h-15c-15.8,0-28.2-3-37.1-9.1-4.7-3.2-7.1-7.9-7.1-14.2l90.9-4.3c27.9-.8,41.9-15.8,41.9-45v-21.7l2-127.2c0-4.5-.1-8.2-.4-11.1-42.7,39.8-83.8,59.7-123.3,59.7s-6.9-.1-10.3-.4c-17.7,0-26.5-7.8-26.5-23.3.5-12.1,4.1-25.5,10.7-40.1,6.6-14.6,15.1-28.7,25.5-42.3,10.4-13.6,22.2-25.7,35.4-36.6,20.3-16.6,39.9-24.9,58.9-24.9s40.3,11.7,54.5,35.2c4.2,62.7,6.3,107.2,6.3,133.6s-.4,47.9-1.2,64.8c-.8,16.9-2.6,32.3-5.3,46.2-2.8,14-6.4,25-10.9,33.2-4.5,8.2-9.8,14.1-15.8,17.8-10,5.8-24.1,8.7-42.3,8.7h0ZM3003.4,245c-3.2-1.1-7.6-1.6-13.2-1.6s-13,2.4-21.9,7.1c-9,4.7-17.5,10.9-25.7,18.4-8.2,7.5-15.8,15.9-22.9,25.1-17.4,22.7-26.1,40.7-26.1,54.1s1.2,7.4,3.6,9.5c1.8,1.1,4.1,1.6,6.9,1.6s6.8-.5,12.1-1.4c5.3-.9,12.4-3.3,21.5-7.1,9.1-3.8,18.5-8.8,28.3-14.8,9.7-6.1,18.6-12.8,26.5-20.2,18.4-16.9,27.7-33.3,27.7-49.4s-5.5-16.9-16.6-21.3h0Z"/>
                          <path class="st0" d="M3228.2,299.9c0-31.3-1.7-54.4-5.1-69.2,2.1-4-1.1-5.9-9.5-5.9s-20.5,2.2-34.8,6.7c-14.2,4.5-25.8,9-34.8,13.4-21.3,10.3-33.2,20.3-35.6,30v107.5h-26.5c-3.4-9-5.1-34.1-5.1-75.5s.7-64,2.2-81.4c1.4-17.4,2.6-27.3,3.4-29.6,6.8-4.5,12.4-6.7,16.6-6.7,6.6,0,9.9,7.8,9.9,23.3s-.3,11.9-1,17.6c-.7,5.7-1,9.6-1,11.7,42.7-31.3,75.7-47,99.2-47s41.8,21.5,45.4,64.4c1.6,15,2.4,35.8,2.4,62.4l-.4,31.2c0,11.6.3,22.8.8,33.6-2.6,1.6-4.7,2.4-6.3,2.4-.8-.3-2.5-.7-5.1-1.2-2.6-.5-6.1-.9-10.3-1.2-2.6-10.5-4.1-21.3-4.3-32.2-.3-10.9-.4-21-.4-30.2l.4-24.1h0Z"/>
                          <path class="st0" d="M3673.6,356.5c0,7.4-5.8,11.1-17.4,11.1s-17.5-3.6-29.6-10.7c-4.5-2.6-9-5.1-13.6-7.3-4.6-2.2-9.7-3.4-15.2-3.4-8.2,4.2-17.1,9-26.9,14.2-33.7,17.9-63.4,26.9-88.9,26.9l-12.6-.8h-2.2c-1.2,0-4-.5-8.5-1.6-11.6-2.9-20.3-7-26.1-12.3-1.8-1.8-2.8-3.6-2.8-5.3s.3-3.1.8-4.1l71.9-114.6c-11.6-20-18.8-34.1-21.7-42.3-4.2-12.9-6.3-28.1-6.3-45.4,2.1-26.9,11.1-49.1,26.9-66.8,12.4-13.4,24.8-20.2,37.1-20.2s16.7,4.6,21.7,13.8c3.4,5.8,5.1,14,5.1,24.5,0,30.8-14.5,74-43.5,129.6.8,10.5,13.2,25.8,37.1,45.8,11.6,9.8,21.5,17,29.6,21.7,8.2,4.7,13.3,7.1,15.4,7.1,15-8.4,25.3-15.4,30.8-20.9s9.6-10,12.2-13.4c2.6-3.4,5.5-7.1,8.7-11.1,7.9-10.3,17.4-20.5,28.5-30.8,2.6,0,5.5,1.3,8.7,3.8,3.2,2.5,4.6,4.1,4.3,4.7-.3.7-1.5,3-3.6,6.9-2.1,4-5.9,9.4-11.5,16.2-14.2,17.4-34.9,36-62,55.7,35.6,17.7,53.3,27.3,53.3,28.8h0ZM3459.4,362c0,4.7,2.2,7.1,6.7,7.1s4.3,0,10.5-.2c6.2-.1,14-.9,23.3-2.4,9.3-1.4,18.4-3.6,27.1-6.5,15-5,32.9-13.7,53.7-26.1l-65.6-65.6c-6.3,6.3-13.7,15.8-22.1,28.5-22.4,32.7-33.6,54.4-33.6,65.2h0ZM3508.8,222.5c12.1-6.8,21.6-22.5,28.5-47,5.3-18.7,7.9-37.5,7.9-56.5l.4-10.3c0-4.7-1.1-7.8-3.4-9.1-2.2-1.3-6.2-2-11.9-2s-11,2.4-16,7.3c-5,4.9-9.1,10.9-12.2,18-5.8,12.9-8.7,28.8-8.7,47.8s5.1,36.2,15.4,51.8h0Z"/>
                        <path class="st0" d="M1988.6,763c0,4.5-3.2,6.7-9.5,6.7s-10.4-2.2-17-6.7c-3.2-7.1-4.7-24.1-4.7-51s.3-49.1.8-66.6c.5-17.5,1.1-33.7,1.6-48.4,1.6-32.1,2.4-49.1,2.4-51,7.6-4.5,14-6.7,19-6.7s7.5,2.2,7.5,6.7v217h0Z"/>
                        <path class="st0" d="M2026.1,616c-.5-14.2-1.1-26.9-1.6-38.1-.5-11.2-.9-19.5-1-24.9-.1-5.4-2.3-18.6-6.5-39.7,2.4-3.2,4.7-6.2,6.9-9.1,2.2-2.9,5.5-4.3,9.7-4.3,9.2,0,16.3,38.9,21.3,116.6,2.6,45,4,90.1,4,135.2s-8.4,18.2-25.3,18.2l-3.2-.4c0-13.7-.4-31.2-1.2-52.6-.8-21.3-1.4-39.9-1.8-55.7-.4-15.8-.9-30.8-1.4-45h0Z"/>
                        <path class="st0" d="M2080.6,716.8c0-4.2-.3-10.1-1-17.6-.7-7.5-1.3-15.7-1.8-24.7-.5-9-.8-18-.8-27.3s.8-17.6,2.4-25.3c3.4-17.1,10.5-25.7,21.3-25.7,7.4,2.1,12.3,4.7,14.8,7.9,2.5,3.2,3.8,7.1,3.8,11.7s-1.2,10.3-3.6,17.2c-8.7,22.9-13,43.6-13,62s.4,12.5,1.2,19.4c4.2,17.7,18.7,26.5,43.5,26.5s25.2-2.2,36.8-6.7c22.1-8.2,37.1-17.8,45.1-28.8,2.4-12.1,3.6-22.7,3.6-31.8s-.3-17.2-1-24.3c-.7-7.1-1.5-14.9-2.4-23.5-.9-8.6-1.5-18-1.8-28.3,3.4-1.6,6.8-2.4,10.1-2.4s6.7,1.7,10.3,5.1c3.6,3.4,6.6,9.9,9.1,19.4,2.5,9.5,4.1,20.6,4.9,33.4.8,12.8,1.2,26,1.4,39.7.1,13.7.2,26.9.2,39.7s.5,23.8,1.6,33c-3.2,2.9-6.2,4.3-9.1,4.3-10.5,0-18.3-10.5-23.3-31.6-3.4,2.9-9.2,6.5-17.4,10.7-17.4,9.5-37.9,15.8-61.6,19-4.5.5-9.5.8-15,.8s-12.4-1-20.7-3.2c-8.3-2.1-16.1-7-23.5-14.6-7.4-7.6-12-19-13.8-34h0Z"/>
                        <path class="st0" d="M2423.2,667.4c29,0,43.5,7.4,43.5,22.1s-8,24-24.1,38.7c-24,21.9-56.2,39.4-96.8,52.6-1.8,0-3.9-1.8-6.1-5.5-2.2-3.7-4-6.5-5.1-8.5-1.2-2-2-3.2-2.6-3.8,30.3-10,55.2-22.1,74.7-36.4,13.2-9.5,19.8-17.3,19.8-23.3s-7.8-9.1-23.3-9.1-38.3,3.7-68.4,11.1c-13.7-.5-25.6-5.4-35.6-14.6-8.7-8.2-13-16.4-13-24.7s2.4-15.9,7.1-22.9c4.7-7,11.1-13.5,19-19.6,7.9-6.1,17.1-11.6,27.7-16.6,25.8-12.4,52.6-18.6,80.2-18.6s16.1.8,23.7,2.4c6.3,0,10,2.8,11.1,8.3,1,5.5,1.6,14.6,1.6,27.3-15.5-7.1-33.1-10.7-52.6-10.7s-44.3,4.9-60.1,14.6c-16.3,9.5-24.8,21.9-25.3,37.1,0,7.4,5.4,11.1,16.2,11.1l28.8-3.2c24.8-5.3,44.7-7.9,59.7-7.9h0Z"/>
                        <path class="st0" d="M2550.9,731.4l.8-63.2c0-10-.9-18.6-2.8-25.7h-61.3l-.8-27.3h59.3v-102c.8-2.4,2.4-4.7,4.7-7.1,3.7-3.7,6.8-5.5,9.3-5.5s4.7.3,6.5.8c1.8.5,3.8,2.4,5.9,5.5,4.2,17.4,6.3,43.6,6.3,78.6s.4,17.3,1.2,25.7c8.4-1,16.9-1.6,25.3-1.6h20.5c5,0,9.6-.3,13.8-.8l-1.2,28.5-57.7-1.6,2,136.3c-4.7,2.1-8.8,3.2-12.3,3.2-13.2,0-19.8-14.6-19.8-43.9h0Z"/>
                        <path class="st0" d="M2848.9,625.1c-11.1-7.9-25.3-11.9-42.7-11.9s-31.5,3-45.4,9.1c-26.3,11.1-44.4,25.3-54.1,42.7-4.2,7.9-6.3,16.3-6.3,25.3v13.2c0,7.2,1.3,17,4,29.2,2.6,12.2,4,24.7,4,37.3-5.5.3-10,.9-13.4,2-3.4,1-6.3,1.6-8.7,1.6s-4.4-1.1-6.1-3.2c-1.7-2.1-3.6-6.2-5.7-12.3-.3-1.6-.8-6.1-1.6-13.4-.8-7.4-1.8-16.3-3-26.9-1.2-10.5-2.5-22.1-4-34.6-1.5-12.5-2.8-24.8-4.1-36.9-2.9-26.3-5.1-45.7-6.7-58.1,0-6.3,4.6-9.5,13.8-9.5s9.9,2.3,13,6.9c3.2,4.6,5.7,9.9,7.5,15.8,1.8,5.9,3.1,11.8,3.8,17.6.7,5.8,1.2,9.9,1.8,12.2,34.8-31.1,78-46.6,129.6-46.6s11.9.1,18.2.4c1.6-.3,3.6-.4,5.9-.4s4.7,1.6,7.1,4.7c2.9,3.7,4.3,8.2,4.3,13.4,0,13.2-3.7,20.5-11.1,22.1h0Z"/>
                        <path class="st0" d="M3033.4,703.7c-19.8,31.1-46.1,50.3-79,57.7-7.6,1.6-14.6,2.4-20.7,2.4s-11.3,0-15.4-.2c-4.1-.1-8.6-.7-13.6-1.8-5-1-9.6-2.8-13.8-5.1-9.7-5.5-14.6-13.3-14.6-23.3-1.1-4.7-1.6-10.1-1.6-16s1.3-13.8,4-23.5c2.6-9.7,7.4-20.3,14.4-31.6,7-11.3,15.5-21.7,25.5-31.2,20.8-20,43.3-32.9,67.6-38.7,7.1-1.6,13.6-2.4,19.6-2.4s11.3.4,16,1.2c15.3,0,25.6,10.4,30.8,31.2,3.4,13.2,5.5,31.6,6.1,55.1.7,23.6,1.3,41.8,2,54.5.7,12.8,2.3,24.4,4.9,35h-23.3c-1.1-7.1-1.8-13.4-2.4-19-.5-5.5-1.1-10.7-1.6-15.4-1.3-11.1-2.9-20.7-4.7-28.8h0ZM3031.8,642.5c0-15.3-7.5-22.9-22.5-22.9-1.8-.3-3.7-.4-5.5-.4-19.2,0-39.5,9.2-60.9,27.7-10,8.4-18.6,17.9-25.7,28.5-10.5,15-15.8,28.5-15.8,40.3s9,24.8,26.9,27.7c23.7-.5,46.4-11.3,68-32.4,9.2-9.2,16.9-19.2,22.9-30,8.4-14.5,12.6-27.3,12.6-38.3h0Z"/>
                        <path class="st0" d="M3243.8,727.6c10.7-4.3,21.1-8.4,31.4-12.1,10.3-3.7,20.1-5.5,29.4-5.5s14,2.9,14,8.7-.3,3.4-1,6.1c-.7,2.8-4.2,6.1-10.7,9.9-6.5,3.8-14.4,7.4-23.7,10.9-9.4,3.4-19.6,6.6-30.6,9.5-11.1,2.9-21.9,5.4-32.4,7.5-21.9,4.5-37.4,6.7-46.6,6.7s-19.2-1-29.8-3.2c-10.7-2.1-20.6-5.5-29.8-10.3-21.3-11.1-32-27.4-32-49v-9.5c0-4.7,2-11.5,5.9-20.4,4-8.8,9.9-17.6,17.8-26.5,7.9-8.8,17.1-16.9,27.7-24.3,22.1-15.8,45.3-26.1,69.6-30.8,8.4-1.8,15.5-2.8,21.1-2.8s10.3,0,14,.2c3.7.1,7.5.4,11.5.8,4,.4,7.4,1.4,10.3,3,6.8,3.4,10.3,10.1,10.3,20.2s-4.5,12.5-13.4,18.6c-2.4,1.6-4.5,3-6.3,4.3-1.3-6.1-3-11.2-5.1-15.4-2.1-4.2-6.7-6.3-13.8-6.3-31.1,6.6-57.8,16.5-80.2,29.6-25.3,15-37.9,30.8-37.9,47.4s.1,3.8.4,5.9c0,16.9,6.5,28.8,19.4,36,9.7,5.3,22.3,7.9,37.7,7.9s28.8-1.8,40.1-5.3c11.3-3.6,22.3-7.5,33-11.9h0ZM3142.1,766.2c0-5.3,1.6-7.9,4.7-7.9s5.9,1.8,8.3,5.3c2.4,3.6,3,5.7,2,6.5,0,7.1.1,13.4.4,19,.3,5.5,2.8,8.3,7.5,8.3,5.8-.5,12.1-.8,18.8-.8s13.6,1.3,20.7,4c10.5,4.2,15.8,11.2,15.8,20.9s-6.2,20-18.6,28.5c-10.8,7.6-23.2,11.5-37.1,11.5s-14.8-1.3-22.9-4c-.8,0-1.2-.7-1.2-2s0-2.9.2-4.7c.1-1.8.3-3.6.4-5.3.1-1.7,0-2.8-.6-3.4,2.9.3,6.5.4,10.7.4s9.7-.6,16.6-1.8c6.8-1.2,13.4-3,19.8-5.5,6.3-2.5,11.1-5.2,14.2-8.1,6.8-6.3,2.9-10.5-11.9-12.6-3.2-.5-7.5-.8-13-.8s-12.5.4-20.9,1.2c-9,0-13.4-14.2-13.4-42.7-.3-2.4-.4-4.3-.4-5.9h0Z"/>
                          <path class="st0" d="M3495.4,703.7c-19.8,31.1-46.1,50.3-79,57.7-7.6,1.6-14.6,2.4-20.7,2.4s-11.3,0-15.4-.2c-4.1-.1-8.6-.7-13.6-1.8-5-1-9.6-2.8-13.8-5.1-9.7-5.5-14.6-13.3-14.6-23.3-1.1-4.7-1.6-10.1-1.6-16s1.3-13.8,4-23.5c2.6-9.7,7.4-20.3,14.4-31.6,7-11.3,15.5-21.7,25.5-31.2,20.8-20,43.3-32.9,67.6-38.7,7.1-1.6,13.6-2.4,19.6-2.4s11.3.4,16,1.2c15.3,0,25.6,10.4,30.8,31.2,3.4,13.2,5.5,31.6,6.1,55.1.7,23.6,1.3,41.8,2,54.5.7,12.8,2.3,24.4,4.9,35h-23.3c-1.1-7.1-1.8-13.4-2.4-19-.5-5.5-1.1-10.7-1.6-15.4-1.3-11.1-2.9-20.7-4.7-28.8h0ZM3399.3,545.3c-8.4,0-19.8,10.7-34,32-1.6,0-4.1-.5-7.7-1.6-3.6-1-5.7-3.3-6.5-6.7,4-12.6,9.9-23.8,17.8-33.6,7.9-9.7,16.2-16.3,24.9-19.8,2.1-.8,4.5-1.2,7.1-1.2s5.9,1.4,9.7,4.1c3.8,2.8,8.1,6.7,12.8,11.7,4.7,5,9.6,9.7,14.6,14.2,5,4.5,10.3,6.7,16,6.7s10.1-.9,13.4-2.8c3.3-1.8,6.3-4.5,9.1-7.9,2.8-3.4,5.8-7.6,9.1-12.6,3.3-5,8-10.7,14-17,1.6,0,3.2,1.4,4.9,4.1,1.7,2.8,2.6,5.1,2.8,7.1.1,2,.2,4.4.2,7.3s-1,6.7-3,11.5c-2,4.7-5.2,9.5-9.7,14.2-11.6,12.4-25.6,18.6-41.9,18.6s-21.9-4.1-29.2-12.2c-5.3-4.7-9.7-8.6-13.4-11.7-3.7-3-7.4-4.5-11.1-4.5h0ZM3493.8,642.5c0-15.3-7.5-22.9-22.5-22.9-1.8-.3-3.7-.4-5.5-.4-19.2,0-39.5,9.2-60.9,27.7-10,8.4-18.6,17.9-25.7,28.5-10.5,15-15.8,28.5-15.8,40.3s9,24.8,26.9,27.7c23.7-.5,46.4-11.3,68-32.4,9.2-9.2,16.9-19.2,22.9-30,8.4-14.5,12.6-27.3,12.6-38.3h0Z"/>
                          <path class="st0" d="M3555.8,694.2c0-31.1,13.4-56.1,40.3-75.1,21.3-15,47-24.1,77.1-27.3h11.1c5.8,0,13.6,1.7,23.3,5.1,21.6,7.4,36.4,20.4,44.3,39.1,3.2,7.4,4.7,13.6,4.7,18.8s-.1,8.8-.4,10.9c0,32.9-12.5,58.9-37.5,77.8-23.2,17.4-52.2,26.1-86.9,26.1s-43.9-5.8-56.7-17.4c-12.8-11.6-19.2-31-19.2-58.1h0ZM3583.1,703.7c0,16.3,4.3,27.4,13,33.2,6.6,4.2,17.6,6.3,33.2,6.3s29.2-1.2,41.1-3.6c11.9-2.4,22.4-6.5,31.6-12.2,19.8-12.6,29.6-32.3,29.6-58.9s-4.2-28.7-12.6-38.7c-9.2-10.8-21.3-16.2-36.4-16.2-10.3,1.8-20.6,3.8-31,5.9-10.4,2.1-20.2,6.2-29.4,12.2-20,12.9-33.1,36.9-39.1,71.9h0Z"/>
                        </g>
                        <polygon class="st0" points="1948.4 114.1 1947.1 393.2 2042.1 253.6 1948.4 114.1"/>
                      </g>
                    </g>
                  </g>
                </svg>
            </div>
            
            <h1 class="text-4xl sm:text-6xl md:text-7xl font-outfit font-black tracking-tight leading-none text-white max-w-4xl mx-auto">
                {{ $settings->site_subtitle }}
            </h1>

            <p class="text-lg sm:text-xl text-slate-400 max-w-2xl mx-auto font-normal leading-relaxed">
                {!! $settings->site_description !!}
            </p>

        </div>

        <!-- Indicador de Scroll -->
        <div class="absolute bottom-8 inset-x-0 flex justify-center animate-bounce">
            <a href="#portfolio" class="text-slate-500 hover:text-white transition-colors duration-200" title="Ver Portfólio">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                </svg>
            </a>
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
            @if(count($items) > 0)
                <div class="columns-1 sm:columns-2 lg:columns-3 gap-6 space-y-0">
                    @foreach($items as $item)
                        <div x-show="activeCategory === 'all' || activeCategory === '{{ $item->portfolio_category_id }}'"
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             @click="window.location.href = '{{ route('public.portfolio.show', $item->slug) }}'"
                             class="break-inside-avoid w-full mb-6 cursor-pointer group bg-slate-900/50 border border-white/[0.08] hover:border-blue-500/50 rounded-[3px] overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 relative inline-block">
                            
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
                    @endforeach
                </div>
            @else
                <div class="border border-dashed border-slate-800 p-12 text-center text-slate-500 rounded-[5px] text-sm italic">
                    Nenhum trabalho de portfólio publicado ainda.
                </div>
            @endif

        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-24 bg-dark-900 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            
            <div class="space-y-6 text-center lg:text-left">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Sobre Mim</span>
                <h3 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white leading-tight">
                    {{ $settings->about_title ?? 'Prazer, sou Danilo Miguel' }}
                </h3>
                <div class="text-slate-350 leading-relaxed text-sm sm:text-base space-y-4 whitespace-pre-line">
                    {!! $settings->about_text !!}
                </div>

                <!-- Skills Grid -->
                <div class="space-y-3">
                    <span class="text-xs font-bold text-slate-400 uppercase tracking-wide block text-center lg:text-left">Especialidades</span>
                    <div class="flex flex-wrap justify-center lg:justify-start gap-2">
                        @foreach(explode(',', $settings->skills) as $skill)
                            <span class="bg-slate-900 border border-slate-800 text-slate-300 text-xs px-3.5 py-1.5 rounded-lg font-medium">{{ trim($skill) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Ilustração / Imagem decorativa de destaque: Caricatura Animada ou Mídia Personalizada -->
            <div class="relative flex justify-center items-center">
                <div class="absolute inset-0 bg-blue-600/15 rounded-full blur-[85px] pointer-events-none"></div>
                <div class="w-full max-w-xs sm:max-w-md aspect-square rounded-full border border-white/[0.08] overflow-hidden shadow-2xl relative bg-slate-950 flex items-center justify-center">
                    @if($settings->media_path)
                        @if(in_array(pathinfo($settings->media_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'ogg']))
                            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                                <source src="{{ asset('storage/' . $settings->media_path) }}">
                                Seu navegador não suporta vídeos.
                            </video>
                        @else
                            <img src="{{ asset('storage/' . $settings->media_path) }}" class="w-full h-full object-cover">
                        @endif
                    @else
                        <video autoplay loop muted playsinline class="w-full h-full object-cover">
                            <source src="{{ asset('storage/caricatura.mp4') }}" type="video/mp4">
                            Seu navegador não suporta vídeos.
                        </video>
                    @endif
                </div>
            </div>

        </div>
    </section>

    <!-- Partners Section -->
    @if($settings->show_partners && !empty($settings->partner_items))
        <section id="partners" class="py-16 bg-dark-900 border-t border-slate-900 overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">
                <div class="text-center space-y-2">
                    <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Parcerias de Sucesso</span>
                    <h3 class="text-2xl sm:text-3xl font-outfit font-extrabold text-white">Empresas e Projetos que Confiam</h3>
                </div>
                
                <!-- Logos Grid -->
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach($settings->partner_items as $partner)
                        @if($partner['logo_path'])
                            <a @if(!empty($partner['url'])) href="{{ str_starts_with($partner['url'], 'http') ? $partner['url'] : 'https://' . $partner['url'] }}" target="_blank" @endif 
                               class="group relative block bg-slate-950/40 border border-white/[0.05] p-6 rounded-2xl shadow-xl hover:shadow-blue-500/5 hover:border-blue-500/25 hover:bg-slate-950/80 transition-all duration-500 overflow-hidden flex items-center justify-center h-28" 
                               title="{{ $partner['name'] }}">
                                <!-- Glow Neon Ambient -->
                                <div class="absolute -inset-10 bg-[radial-gradient(circle_at_center,{{ $settings->primary_color ?? '#3b82f6' }}12_0%,transparent_60%)] opacity-0 group-hover:opacity-100 transition-opacity duration-500 pointer-events-none"></div>
                                
                                <img src="{{ asset('storage/' . $partner['logo_path']) }}" 
                                     alt="{{ $partner['name'] }}" 
                                     class="max-h-12 w-auto max-w-full object-contain filter grayscale opacity-45 brightness-200 group-hover:grayscale-0 group-hover:opacity-100 group-hover:brightness-100 transition-all duration-500 ease-out">
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>
        </section>
    @endif

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
                    <div class="bg-slate-900/40 border border-white/[0.08] rounded-xl overflow-hidden">
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
            
            <div class="space-y-6 text-center lg:text-left">
                <span class="text-xs font-extrabold uppercase tracking-widest text-blue-500">Contato</span>
                <h3 class="text-3xl sm:text-5xl font-outfit font-extrabold text-white leading-tight">
                    Vamos conversar sobre seu projeto?
                </h3>
                <p class="text-slate-350 text-sm sm:text-base leading-relaxed">
                    Precisa ilustrar um livro infantil, diagramar material pedagógico ou desenvolver um jogo educativo? Mande uma mensagem agora mesmo! Estou sempre aberto a novas parcerias e colaborações.
                </p>
                
                <!-- Infos -->
                <div class="space-y-4 pt-4 flex flex-col items-center lg:items-start">
                    <a href="mailto:{{ $settings->contact_email }}" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>{{ $settings->contact_email }}</span>
                    </a>
                    
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-emerald-400 transition-colors w-fit">
                        {!! $getSvg('whatsapp.svg', 'w-5 h-5 text-emerald-500 shrink-0') !!}
                        <span>{{ $settings->contact_phone }}</span>
                    </a>

                    @if(!empty($settings->behance_url))
                    <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->behance_url) }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-450 transition-colors w-fit">
                        {!! $getSvg('behance.svg', 'w-5 h-5 text-sky-500 shrink-0') !!}
                        <span>{{ str_replace(['http://', 'https://'], '', $settings->behance_url) }}</span>
                    </a>
                    @endif

                    @if(!empty($settings->instagram_url))
                    <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->instagram_url) }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-pink-400 transition-colors w-fit">
                        {!! $getSvg('instagram.svg', 'w-5 h-5 text-pink-500 shrink-0') !!}
                        <span>{{ str_replace(['http://', 'https://'], '', $settings->instagram_url) }}</span>
                    </a>
                    @endif

                    @if(!empty($settings->linkedin_url))
                    <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->linkedin_url) }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-400 transition-colors w-fit">
                        {!! $getSvg('linkedin.svg', 'w-5 h-5 text-blue-600 shrink-0') !!}
                        <span>{{ str_replace(['http://', 'https://'], '', $settings->linkedin_url) }}</span>
                    </a>
                    @endif

                    @if(!empty($settings->facebook_url))
                    <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->facebook_url) }}" target="_blank" class="flex items-center gap-3 text-sm text-slate-300 hover:text-blue-500 transition-colors w-fit">
                        {!! $getSvg('facebook.svg', 'w-5 h-5 text-blue-500 shrink-0') !!}
                        <span>{{ str_replace(['http://', 'https://'], '', $settings->facebook_url) }}</span>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Formulário Moderno -->
            <div class="bg-slate-900/50 border border-white/[0.08] p-8 rounded-2xl shadow-xl glassmorphism space-y-4"
                 x-data="{
                     name: '',
                     email: '',
                     phone: '',
                     message: '',
                     website: '',
                     loading: false,
                     successMsg: '',
                     errorMsg: '',
                     errors: {},
                     
                     submitForm() {
                         this.loading = true;
                         this.successMsg = '';
                         this.errorMsg = '';
                         this.errors = {};
                         
                         fetch('{{ route('public.contact.send') }}', {
                             method: 'POST',
                             headers: {
                                 'Content-Type': 'application/json',
                                 'X-CSRF-TOKEN': '{{ csrf_token() }}'
                             },
                             body: JSON.stringify({
                                 name: this.name,
                                 email: this.email,
                                 phone: this.phone,
                                 message: this.message,
                                 website: this.website
                             })
                         })
                         .then(async res => {
                             const data = await res.json();
                             if (res.ok && data.success) {
                                 this.successMsg = data.message;
                                 this.name = '';
                                 this.email = '';
                                 this.phone = '';
                                 this.message = '';
                                 this.website = '';
                             } else {
                                 this.errors = data.errors || {};
                                 this.errorMsg = this.errors.rate_limit ? this.errors.rate_limit[0] : 'Por favor, corrija os erros no formulário.';
                             }
                         })
                         .catch(err => {
                             this.errorMsg = 'Ocorreu um erro ao enviar sua mensagem. Tente novamente.';
                         })
                         .finally(() => {
                             this.loading = false;
                         });
                     }
                 }">
                <h4 class="font-outfit font-extrabold text-white text-lg border-b border-white/[0.08] pb-3">Fale Conosco</h4>
                
                <div x-show="successMsg" class="p-4 bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold rounded-lg" x-cloak>
                    ✨ <span x-text="successMsg"></span>
                </div>
                
                <div x-show="errorMsg" class="p-4 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs font-bold rounded-lg" x-cloak>
                    ⚠️ <span x-text="errorMsg"></span>
                </div>

                <form @submit.prevent="submitForm()" class="space-y-4" x-show="!successMsg">
                    <!-- Honeypot -->
                    <input type="text" name="website" x-model="website" class="hidden" style="display: none !important;" tabindex="-1" autocomplete="off">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Seu Nome</label>
                            <input type="text" x-model="name" required placeholder="Ex: João Silva" class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650">
                            <template x-if="errors.name">
                                <span class="text-[10px] text-rose-500 font-bold" x-text="errors.name[0]"></span>
                            </template>
                        </div>
                        
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Seu E-mail</label>
                            <input type="email" x-model="email" required placeholder="Ex: joao@email.com" class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650">
                            <template x-if="errors.email">
                                <span class="text-[10px] text-rose-500 font-bold" x-text="errors.email[0]"></span>
                            </template>
                        </div>
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Telefone / WhatsApp (Opcional)</label>
                        <input type="text" x-model="phone" placeholder="Ex: (14) 99123-4567" class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650">
                        <template x-if="errors.phone">
                            <span class="text-[10px] text-rose-500 font-bold" x-text="errors.phone[0]"></span>
                        </template>
                    </div>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Mensagem</label>
                        <textarea x-model="message" required rows="4" placeholder="Descreva brevemente sua necessidade..." class="w-full px-4 py-3 bg-slate-950/80 border border-slate-800 rounded-lg text-sm text-slate-200 focus:outline-none focus:border-blue-500 transition-colors placeholder-slate-650"></textarea>
                        <template x-if="errors.message">
                            <span class="text-[10px] text-rose-500 font-bold" x-text="errors.message[0]"></span>
                        </template>
                    </div>

                    <button type="submit" :disabled="loading" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-lg transition-colors text-center text-sm shadow-md shadow-blue-600/10 flex items-center justify-center gap-2">
                        <span x-show="loading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                        <span x-text="loading ? 'Enviando...' : 'Enviar Mensagem'"></span>
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

            <div class="flex items-center gap-3">
                @if(!empty($settings->behance_url))
                <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->behance_url) }}" target="_blank" class="footer-social-link w-8 h-8 rounded-full bg-slate-950 border border-white/5 flex items-center justify-center text-slate-400" title="Behance">
                    {!! $getSvg('behance.svg', 'w-4 h-4 fill-current') !!}
                </a>
                @endif
                @if(!empty($settings->instagram_url))
                <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->instagram_url) }}" target="_blank" class="footer-social-link w-8 h-8 rounded-full bg-slate-950 border border-white/5 flex items-center justify-center text-slate-400" title="Instagram">
                    {!! $getSvg('instagram.svg', 'w-4 h-4 fill-current') !!}
                </a>
                @endif
                @if(!empty($settings->linkedin_url))
                <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->linkedin_url) }}" target="_blank" class="footer-social-link w-8 h-8 rounded-full bg-slate-950 border border-white/5 flex items-center justify-center text-slate-400" title="LinkedIn">
                    {!! $getSvg('linkedin.svg', 'w-4 h-4 fill-current') !!}
                </a>
                @endif
                @if(!empty($settings->facebook_url))
                <a href="https://{{ str_replace(['http://', 'https://'], '', $settings->facebook_url) }}" target="_blank" class="footer-social-link w-8 h-8 rounded-full bg-slate-950 border border-white/5 flex items-center justify-center text-slate-400" title="Facebook">
                    {!! $getSvg('facebook.svg', 'w-4 h-4 fill-current') !!}
                </a>
                @endif
            </div>

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
                mobileMenuOpen: false,
                touchStartX: 0,
                touchEndX: 0,

                init() {
                    window.addEventListener('scroll', () => {
                        this.scrolled = window.scrollY > 50;
                    });

                    // Cursor Follower Logic
                    const cursor = document.getElementById('custom-cursor');
                    const cursorDot = document.getElementById('custom-cursor-dot');
                    if (cursor && cursorDot) {
                        window.addEventListener('mousemove', (e) => {
                            cursor.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                            cursorDot.style.transform = `translate3d(${e.clientX}px, ${e.clientY}px, 0)`;
                        });

                        // Hover scaling for interactive elements (inverted text effect)
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

                    // Click particle thumb effect
                    window.addEventListener('click', (e) => {
                        if (e.target.closest('select') || e.target.closest('input[type="file"]')) return;

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

                handleTouchStart(e) {
                    this.touchStartX = e.changedTouches[0].clientX;
                },

                handleTouchEnd(e) {
                    this.touchEndX = e.changedTouches[0].clientX;
                    this.handleSwipe();
                },

                handleSwipe() {
                    const swipeThreshold = 55;
                    const diff = this.touchEndX - this.touchStartX;
                    // Closing drawer (swiping from left to right -> positive diff)
                    if (diff > swipeThreshold && this.mobileMenuOpen) {
                        this.mobileMenuOpen = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
