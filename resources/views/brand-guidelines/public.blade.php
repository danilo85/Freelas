<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manual de Identidade Visual - {{ $guideline->brand_name }}</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/freela/freela-03.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        outfit: ['Outfit', 'sans-serif'],
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .bg-pattern {
            background-color: #fafbfc;
            background-image: radial-gradient(#e2e8f0 1.2px, transparent 1.2px);
            background-size: 24px 24px;
        }
        html {
            scroll-behavior: smooth;
        }
    </style>
    
    <!-- Dynamically loaded custom fonts via @font-face -->
    <style>
        @if($guideline->typography)
            @foreach($guideline->typography as $f)
                @if(!empty($f['font_file']))
                    @font-face {
                        font-family: '{{ $f['font_family'] }}';
                        src: url('/storage/{{ $f['font_file'] }}');
                    }
                @endif
            @endforeach
        @endif
    </style>
</head>
<body class="bg-pattern text-slate-800 font-sans min-h-screen flex flex-col justify-between" x-data="brandPresenter()" x-init="init()">

    <!-- Header bar -->
    <header class="bg-white/70 backdrop-blur-md border-b border-slate-100 sticky top-0 z-50 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <span class="text-xl">🎨</span>
            <div>
                <h1 class="font-outfit font-black text-slate-900 text-sm md:text-base leading-tight">{{ $guideline->brand_name }}</h1>
                <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Manual de Identidade Visual</p>
            </div>
        </div>
        
        <div class="flex items-center gap-4">
            <!-- Global download ZIP package -->
            @if($guideline->final_package)
                <a href="{{ asset('storage/' . $guideline->final_package) }}" download class="px-3 py-1.5 sm:px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-[11px] uppercase tracking-wider rounded-[5px] shadow-sm flex items-center gap-1 transition-colors" title="Baixar Pacote Completo">
                    <span>📥</span><span class="hidden sm:inline"> Baixar Pacote Completo</span>
                </a>
            @else
                <a href="{{ route('revisoes.brand-guidelines.zip', $guideline->id) }}" class="px-3 py-1.5 sm:px-3.5 bg-blue-600 hover:bg-blue-700 text-white font-black text-[11px] uppercase tracking-wider rounded-[5px] shadow-sm flex items-center gap-1 transition-colors" title="Gerar ZIP da Identidade">
                    <span>📦</span><span class="hidden sm:inline"> Gerar ZIP da Identidade</span>
                </a>
            @endif
        </div>
    </header>

    <!-- Floating Vertical Timeline Navigation (Desktop) -->
    <div class="hidden lg:flex flex-col fixed left-8 top-1/2 -translate-y-1/2 z-40 items-start" x-cloak>
        <div class="relative pl-6 border-l border-slate-200 py-3 space-y-7">
            <template x-for="(sec, idx) in sections" :key="idx">
                <div class="relative group flex items-center">
                    <!-- Nav dot marker -->
                    <button @click="scrollToSection(sec.id)" 
                            class="absolute -left-[30px] top-1/2 -translate-y-1/2 w-3.5 h-3.5 rounded-full border-2 bg-white transition-all duration-300"
                            :class="activeSection === sec.id ? 'border-blue-600 scale-125 bg-blue-600' : 'border-slate-300 hover:border-slate-500 hover:scale-110'">
                    </button>
                    <!-- Label -->
                    <button @click="scrollToSection(sec.id)" 
                            class="text-left text-[10px] font-black tracking-wider uppercase transition-all duration-300"
                            :class="activeSection === sec.id ? 'text-slate-900 translate-x-1' : 'text-slate-400 hover:text-slate-600'">
                        <span x-text="sec.label"></span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Main Container -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 lg:pl-56 lg:pr-10 py-12 space-y-24">

        <!-- SECTION 1: CAPA -->
        <section id="capa" class="min-h-[75vh] flex flex-col items-center justify-center text-center space-y-6 bg-white border border-slate-200/80 rounded-[5px] p-8 md:p-16 shadow-sm relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-blue-50/10 via-transparent to-emerald-50/5 pointer-events-none"></div>
            
            @if($guideline->logo_primary)
                <div class="w-48 h-48 md:w-56 md:h-56 rounded-[5px] bg-slate-50 border border-slate-100 flex items-center justify-center p-6 shadow-inner mx-auto mb-4">
                    <img src="{{ asset('storage/' . $guideline->logo_primary) }}" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <span class="text-6xl mx-auto block mb-4">✨</span>
            @endif

            <h2 class="font-outfit font-black text-4xl md:text-5xl text-slate-900 tracking-tight leading-tight uppercase">{{ $guideline->brand_name }}</h2>
            <div class="w-12 h-1 bg-slate-900 mx-auto rounded"></div>
            <p class="text-xs md:text-sm text-slate-500 max-w-md mx-auto leading-relaxed font-medium">
                Guia oficial de assinatura visual, cores institucionais, tipografias aprovadas e assets de papelaria de nossa marca.
            </p>

            <button @click="scrollToSection('logos')" class="px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white text-[10px] font-black uppercase tracking-wider rounded-[5px] shadow-sm transition-all cursor-pointer">
                Explorar Identidade Visual
            </button>
        </section>

        <!-- SECTION 2: LOGOTIPOS -->
        <section id="logos" class="scroll-mt-24 space-y-12">
            <div class="border-l-4 border-slate-900 pl-4">
                <h3 class="font-outfit font-black text-xl md:text-2xl text-slate-900 uppercase">Assinatura Visual</h3>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">Versões oficiais dos logotipos e regras</p>
            </div>

            <div class="space-y-10">
                <!-- Variação 1: Principal / Horizontal -->
                @if($guideline->logo_primary)
                    <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 md:p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                        <!-- Left Side: Image & Modes -->
                        <div class="md:col-span-5 space-y-4">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Logo Principal / Horizontal</span>
                            
                            @php
                                $isPrimarySvg = str_ends_with(strtolower($guideline->logo_primary), '.svg');
                            @endphp
                            
                            <div @click="openPreview('{{ asset('storage/' . $guideline->logo_primary) }}')"
                                 class="w-full h-44 border border-slate-100 flex items-center justify-center p-4 rounded transition-colors cursor-zoom-in"
                                 :class="logoModePrimary === 'light' ? 'bg-slate-900' : 'bg-slate-50'"
                                 title="Clique para ampliar">
                                <img src="{{ asset('storage/' . $guideline->logo_primary) }}" 
                                     class="max-w-full max-h-full object-contain transition-all"
                                     :style="logoModePrimary === 'dark' ? 'filter: brightness(0)' : (logoModePrimary === 'light' ? 'filter: brightness(0) invert(1)' : '')">
                            </div>

                            @if($isPrimarySvg)
                                <div class="flex gap-1 justify-center">
                                    <button @click="logoModePrimary = 'original'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModePrimary === 'original' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Original</button>
                                    <button @click="logoModePrimary = 'dark'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModePrimary === 'dark' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Preto</button>
                                    <button @click="logoModePrimary = 'light'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModePrimary === 'light' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Branco</button>
                                </div>
                            @endif
                        </div>

                        <!-- Right Side: Text & Actions -->
                        <div class="md:col-span-7 space-y-4 flex flex-col justify-between h-full">
                            <div class="space-y-2">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Manual de Aplicação</h4>
                                <div class="text-xs text-slate-500 leading-relaxed prose prose-slate max-w-none">
                                    @if($guideline->logo_horizontal_desc)
                                        {!! $guideline->logo_horizontal_desc !!}
                                    @else
                                        <p class="italic text-slate-400">Nenhum conceito explicativo cadastrado para esta variação.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ asset('storage/' . $guideline->logo_primary) }}" download class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] uppercase tracking-wider rounded transition-colors">
                                    📥 Baixar Logo Horizontal ({{ $isPrimarySvg ? 'SVG' : 'PNG' }})
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Variação 2: Logo Secundário / Vertical -->
                @if($guideline->logo_secondary)
                    <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 md:p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                        <!-- Left Side: Image & Modes -->
                        <div class="md:col-span-5 space-y-4">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Logo Secundário / Vertical</span>
                            
                            @php
                                $isSecondarySvg = str_ends_with(strtolower($guideline->logo_secondary), '.svg');
                            @endphp
                            
                            <div @click="openPreview('{{ asset('storage/' . $guideline->logo_secondary) }}')"
                                 class="w-full h-44 border border-slate-100 flex items-center justify-center p-4 rounded transition-colors cursor-zoom-in"
                                 :class="logoModeSecondary === 'light' ? 'bg-slate-900' : 'bg-slate-50'"
                                 title="Clique para ampliar">
                                <img src="{{ asset('storage/' . $guideline->logo_secondary) }}" 
                                     class="max-w-full max-h-full object-contain transition-all"
                                     :style="logoModeSecondary === 'dark' ? 'filter: brightness(0)' : (logoModeSecondary === 'light' ? 'filter: brightness(0) invert(1)' : '')">
                            </div>

                            @if($isSecondarySvg)
                                <div class="flex gap-1 justify-center">
                                    <button @click="logoModeSecondary = 'original'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSecondary === 'original' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Original</button>
                                    <button @click="logoModeSecondary = 'dark'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSecondary === 'dark' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Preto</button>
                                    <button @click="logoModeSecondary = 'light'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSecondary === 'light' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Branco</button>
                                </div>
                            @endif
                        </div>

                        <!-- Right Side: Text & Actions -->
                        <div class="md:col-span-7 space-y-4 flex flex-col justify-between h-full">
                            <div class="space-y-2">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Manual de Aplicação</h4>
                                <div class="text-xs text-slate-500 leading-relaxed prose prose-slate max-w-none">
                                    @if($guideline->logo_vertical_desc)
                                        {!! $guideline->logo_vertical_desc !!}
                                    @else
                                        <p class="italic text-slate-400">Nenhum conceito explicativo cadastrado para esta variação.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ asset('storage/' . $guideline->logo_secondary) }}" download class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] uppercase tracking-wider rounded transition-colors">
                                    📥 Baixar Logo Vertical ({{ $isSecondarySvg ? 'SVG' : 'PNG' }})
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Variação 3: Símbolo / Ícone -->
                @if($guideline->logo_symbol)
                    <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 md:p-8 shadow-sm grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                        <!-- Left Side: Image & Modes -->
                        <div class="md:col-span-5 space-y-4">
                            <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest block">Símbolo / Ícone</span>
                            
                            @php
                                $isSymbolSvg = str_ends_with(strtolower($guideline->logo_symbol), '.svg');
                            @endphp
                            
                            <div @click="openPreview('{{ asset('storage/' . $guideline->logo_symbol) }}')"
                                 class="w-full h-44 border border-slate-100 flex items-center justify-center p-4 rounded transition-colors cursor-zoom-in"
                                 :class="logoModeSymbol === 'light' ? 'bg-slate-900' : 'bg-slate-50'"
                                 title="Clique para ampliar">
                                <img src="{{ asset('storage/' . $guideline->logo_symbol) }}" 
                                     class="max-w-full max-h-full object-contain transition-all"
                                     :style="logoModeSymbol === 'dark' ? 'filter: brightness(0)' : (logoModeSymbol === 'light' ? 'filter: brightness(0) invert(1)' : '')">
                            </div>

                            @if($isSymbolSvg)
                                <div class="flex gap-1 justify-center">
                                    <button @click="logoModeSymbol = 'original'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSymbol === 'original' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Original</button>
                                    <button @click="logoModeSymbol = 'dark'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSymbol === 'dark' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Preto</button>
                                    <button @click="logoModeSymbol = 'light'" class="px-2.5 py-1 rounded text-[10px] font-black tracking-wider uppercase transition-all cursor-pointer" :class="logoModeSymbol === 'light' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-400 hover:bg-slate-200'">Branco</button>
                                </div>
                            @endif
                        </div>

                        <!-- Right Side: Text & Actions -->
                        <div class="md:col-span-7 space-y-4 flex flex-col justify-between h-full">
                            <div class="space-y-2">
                                <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">Manual de Aplicação</h4>
                                <div class="text-xs text-slate-500 leading-relaxed prose prose-slate max-w-none">
                                    @if($guideline->logo_symbol_desc)
                                        {!! $guideline->logo_symbol_desc !!}
                                    @else
                                        <p class="italic text-slate-400">Nenhum conceito explicativo cadastrado para esta variação.</p>
                                    @endif
                                </div>
                            </div>
                            <div class="pt-4 border-t border-slate-100">
                                <a href="{{ asset('storage/' . $guideline->logo_symbol) }}" download class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] uppercase tracking-wider rounded transition-colors">
                                    📥 Baixar Símbolo / Ícone ({{ $isSymbolSvg ? 'SVG' : 'PNG' }})
                                </a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if($guideline->logo_description)
                <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 shadow-sm">
                    <h5 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Instruções de Uso da Marca</h5>
                    <p class="text-xs text-slate-600 leading-relaxed whitespace-pre-line">{{ $guideline->logo_description }}</p>
                </div>
            @endif
        </section>

        <!-- SECTION 3: CORES -->
        <section id="cores" class="scroll-mt-24 space-y-6">
            <div class="border-l-4 border-slate-900 pl-4">
                <h3 class="font-outfit font-black text-xl md:text-2xl text-slate-900 uppercase">Paleta de Cores</h3>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">As referências cromáticas da marca</p>
            </div>

            @if($guideline->color_palette && count($guideline->color_palette) > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($guideline->color_palette as $color)
                        <div class="bg-white border border-slate-200/80 rounded-[5px] p-4 shadow-sm flex flex-col justify-between space-y-4">
                            <!-- Color box -->
                            <div class="w-full h-24 rounded-[5px] shadow-inner border border-slate-100" style="background-color: {{ $color['hex'] }}"></div>
                            
                            <div>
                                <span class="text-xs font-extrabold text-slate-800 uppercase block truncate max-w-[180px]">{{ $color['name'] }}</span>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">{{ $color['type'] ?? 'Auxiliar' }}</span>
                            </div>

                            <div class="text-[10px] font-mono text-slate-500 space-y-0.5 border-t pt-2">
                                <div class="flex justify-between"><span>HEX:</span><span>{{ $color['hex'] }}</span></div>
                                <div class="flex justify-between"><span>RGB:</span><span>{{ $color['rgb'] ?? '' }}</span></div>
                                <div class="flex justify-between"><span>CMYK:</span><span>{{ $color['cmyk'] ?? '' }}</span></div>
                            </div>

                            @if(!empty($color['note']))
                                <p class="text-[10px] text-slate-500 italic bg-slate-50 p-2 rounded">{{ $color['note'] }}</p>
                            @endif

                            <button @click="copyHex('{{ $color['hex'] }}', $event)" class="px-3 py-2 bg-slate-900 hover:bg-slate-800 text-white font-mono text-[10px] font-bold uppercase tracking-wider rounded transition-colors w-full cursor-pointer">
                                Copiar HEX
                            </button>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-slate-200/80 rounded-[5px] p-8 text-center text-slate-400 text-xs">
                    Nenhuma paleta de cores configurada.
                </div>
            @endif
        </section>

        <!-- SECTION 4: TIPOGRAFIA -->
        <section id="fontes" class="scroll-mt-24 space-y-6">
            <div class="border-l-4 border-slate-900 pl-4">
                <h3 class="font-outfit font-black text-xl md:text-2xl text-slate-900 uppercase">Tipografia</h3>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">Fontes, pesos e demonstrativo tipográfico</p>
            </div>

            @if($guideline->typography && count($guideline->typography) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($guideline->typography as $typo)
                        <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                                <span class="text-sm font-extrabold text-slate-850">{{ $typo['font_family'] }}</span>
                                <span class="bg-slate-100 text-slate-700 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[5px]">{{ $typo['usage'] }}</span>
                            </div>
                            
                            <!-- Specimen -->
                            <div class="py-1 font-mono text-slate-400 text-[9px] uppercase tracking-wider block">Demonstração da Fonte</div>
                            <div class="text-slate-850 leading-tight tracking-tight break-all" style="font-family: '{{ $typo['font_family'] }}', sans-serif;">
                                <p class="text-2xl md:text-3xl font-light">AaBbCcDdEeFfGgHh</p>
                                <p class="text-2xl md:text-3xl font-extrabold mt-1">AaBbCcDdEeFfGgHh</p>
                                <p class="text-xs md:text-sm mt-4 text-slate-500 font-medium">{{ $typo['specimen_text'] }}</p>
                            </div>

                            @if(!empty($typo['font_file']))
                                <div class="pt-3 border-t">
                                    <a href="{{ asset('storage/' . $typo['font_file']) }}" download class="inline-flex items-center justify-center gap-1.5 px-3.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[10px] font-black uppercase tracking-wider rounded">
                                        📥 Baixar Fonte
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="bg-white border border-slate-200/80 rounded-[5px] p-8 text-center text-slate-400 text-xs">
                    Nenhuma tipografia institucional configurada.
                </div>
            @endif
        </section>

        <!-- SECTION 5: PAPELARIA & REDES SOCIAIS -->
        <section id="assets" class="scroll-mt-24 space-y-10">
            <div class="border-l-4 border-slate-900 pl-4">
                <h3 class="font-outfit font-black text-xl md:text-2xl text-slate-900 uppercase">Assets & Papelaria</h3>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">Mockups e arquivos de produção</p>
            </div>

            <!-- Redes Sociais se existirem -->
            @php
                $hasSocial = false;
                if ($guideline->social_media) {
                    foreach ($guideline->social_media as $net) {
                        if (!empty($net['avatar']) || !empty($net['cover'])) $hasSocial = true;
                    }
                }
            @endphp

            @if($hasSocial)
                <div class="bg-white border border-slate-200/80 rounded-[5px] p-6 shadow-sm space-y-4">
                    <h4 class="text-xs font-black text-slate-850 uppercase tracking-wider border-b border-slate-100 pb-3">📱 Mockups de Redes Sociais</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach(['instagram', 'facebook', 'linkedin', 'youtube', 'tiktok', 'whatsapp'] as $net)
                            @if(!empty($guideline->social_media[$net]['avatar']) || !empty($guideline->social_media[$net]['cover']))
                                <div class="p-4 bg-slate-50 border border-slate-200 rounded-[5px] space-y-4">
                                    <span class="text-xs font-black text-slate-800 uppercase tracking-wider" x-text="'{{ $net }}'"></span>
                                    
                                    <!-- Interactive Mockup Preview -->
                                    <div class="w-full h-32 bg-slate-350 rounded relative overflow-hidden flex flex-col justify-end p-2.5 border border-slate-200 shadow-sm">
                                        @if(!empty($guideline->social_media[$net]['cover']))
                                            <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('storage/' . $guideline->social_media[$net]['cover']) }}')"></div>
                                        @endif
                                        @if(!empty($guideline->social_media[$net]['avatar']))
                                            <div class="w-12 h-12 rounded-full border-2 border-white bg-slate-200 relative z-10 overflow-hidden bg-cover bg-center shadow-md" style="background-image: url('{{ asset('storage/' . $guideline->social_media[$net]['avatar']) }}')"></div>
                                        @endif
                                    </div>

                                    <div class="flex items-center gap-2 justify-end">
                                        @if(!empty($guideline->social_media[$net]['avatar']))
                                            <a href="{{ asset('storage/' . $guideline->social_media[$net]['avatar']) }}" download class="px-2.5 py-1 bg-white hover:bg-slate-100 border text-slate-700 text-[9px] font-black uppercase tracking-wider rounded">Avatar</a>
                                        @endif
                                        @if(!empty($guideline->social_media[$net]['cover']))
                                            <a href="{{ asset('storage/' . $guideline->social_media[$net]['cover']) }}" download class="px-2.5 py-1 bg-white hover:bg-slate-100 border text-slate-700 text-[9px] font-black uppercase tracking-wider rounded">Banner</a>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Separação de Papelaria/Mockup -->
            @php
                $mockupItems = [];
                $productionItems = [];
                if ($guideline->stationery) {
                    foreach ($guideline->stationery as $item) {
                        $ext = pathinfo($item['path'], PATHINFO_EXTENSION);
                        $isImg = in_array(strtolower($ext), ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif']);
                        if ($isImg) {
                            $mockupItems[] = $item;
                        } else {
                            $productionItems[] = $item;
                        }
                    }
                }
            @endphp

            <!-- 1. Mockups de Apresentação Visual (Imagens em Telas de Navegador) -->
            @if(count($mockupItems) > 0)
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">🖼️ Mockups de Apresentação Visual (Telas de Navegador)</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        @foreach($mockupItems as $mockup)
                            <!-- Browser Window Mockup Frame -->
                            <div class="bg-white rounded-[8px] border border-slate-200/80 shadow-sm overflow-hidden flex flex-col transition-all duration-300 hover:shadow-md">
                                <!-- Browser Header Bar -->
                                <div class="bg-slate-50 px-4 py-3 flex items-center justify-between border-b border-slate-150 shrink-0">
                                    <div class="flex gap-1.5 shrink-0">
                                        <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <div class="bg-white border border-slate-200/60 rounded-[4px] text-[9px] font-bold text-slate-400 py-1 px-8 mx-auto truncate max-w-xs text-center select-all">
                                        {{ $mockup['name'] }}
                                    </div>
                                    <a href="{{ asset('storage/' . $mockup['path']) }}" download class="text-slate-400 hover:text-slate-700 font-bold text-[9px] uppercase tracking-wider flex items-center gap-1">
                                        📥 Salvar
                                    </a>
                                </div>
                                <!-- Browser Content Area (Image Preview) -->
                                <div @click="openPreview('{{ asset('storage/' . $mockup['path']) }}')"
                                     class="bg-slate-50 p-4 flex items-center justify-center overflow-hidden h-72 cursor-zoom-in"
                                     title="Clique para ampliar">
                                    <img src="{{ asset('storage/' . $mockup['path']) }}" class="max-w-full max-h-full object-contain rounded border border-slate-100 shadow-sm hover:scale-[1.01] transition-transform duration-300">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- 2. Arquivos e Peças de Papelaria (Downloads de Produção) -->
            @if(count($productionItems) > 0)
                <div class="space-y-4">
                    <h4 class="text-xs font-black text-slate-800 uppercase tracking-wider">💼 Peças & Arquivos de Produção</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                        @foreach($productionItems as $item)
                            <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col justify-between space-y-3">
                                <div class="w-full h-24 bg-slate-50 border rounded flex items-center justify-center p-2 text-3xl">
                                    📄
                                </div>
                                <div class="min-w-0">
                                    <span class="font-bold text-xs text-slate-800 block truncate">{{ $item['name'] }}</span>
                                    <span class="text-[9px] text-slate-400 block truncate">{{ $item['original_name'] }}</span>
                                </div>
                                <a href="{{ asset('storage/' . $item['path']) }}" download class="w-full py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-[10px] uppercase tracking-wider rounded transition-colors text-center">
                                    Baixar Arquivo
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <!-- SECTION 6: ASSINATURA DE E-MAIL -->
        <section id="assinatura" class="scroll-mt-24 space-y-6">
            <div class="border-l-4 border-slate-900 pl-4">
                <h3 class="font-outfit font-black text-xl md:text-2xl text-slate-900 uppercase">Assinatura de E-mail</h3>
                <p class="text-[9px] text-slate-400 uppercase tracking-widest font-bold mt-0.5">Gerador interativo de assinatura profissional</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                
                <!-- Formulário (Inputs) -->
                <div class="lg:col-span-5 bg-white border border-slate-200/80 rounded-[5px] p-6 shadow-sm space-y-4">
                    <h4 class="text-xs font-black text-slate-850 uppercase tracking-wider border-b border-slate-100 pb-3">Informações da Assinatura</h4>
                    
                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Nome</label>
                        <input type="text" x-model="sig.name" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Cargo / Registro</label>
                        <input type="text" x-model="sig.role" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">WhatsApp / Celular</label>
                        <input type="text" x-model="sig.phone1" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Telefone Secundário</label>
                        <input type="text" x-model="sig.phone2" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Endereço</label>
                        <input type="text" x-model="sig.address" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Logo da Assinatura</label>
                        <select x-model="sig.logoUrl" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none cursor-pointer">
                            @if($guideline->logo_primary)
                                <option value="{{ asset('storage/' . $guideline->logo_primary) }}">Logo Principal</option>
                            @endif
                            @if($guideline->logo_secondary)
                                <option value="{{ asset('storage/' . $guideline->logo_secondary) }}">Logo Alternativo</option>
                            @endif
                            @if($guideline->logo_symbol)
                                <option value="{{ asset('storage/' . $guideline->logo_symbol) }}">Símbolo</option>
                            @endif
                        </select>
                    </div>

                    @if($guideline->color_palette && count($guideline->color_palette) > 0)
                        <div class="space-y-1">
                            <label class="text-[10px] font-bold text-slate-450 uppercase block">Cor da Linha Divider</label>
                            <select x-model="sig.lineColor" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none cursor-pointer">
                                @foreach($guideline->color_palette as $color)
                                    <option value="{{ $color['hex'] }}">{{ $color['name'] }} ({{ strtoupper($color['hex']) }})</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <!-- Preview e Copiar -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="bg-white border border-slate-200/80 rounded-[5px] p-8 shadow-sm space-y-4">
                        <h4 class="text-xs font-black text-slate-450 uppercase tracking-wider block border-b border-slate-100 pb-2.5">👁️ Visualização em Tempo Real</h4>
                        
                        <div class="border border-slate-200 rounded-[5px] p-6 bg-white overflow-x-auto min-h-[140px] flex items-center justify-start select-all" id="emailSignaturePreview">
                            <table cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; border-collapse: collapse;">
                                <tr>
                                    <td style="vertical-align: middle; padding-right: 20px;" x-show="sig.logoUrl">
                                        <img :src="sig.logoUrl" alt="Logo" style="max-height: 80px; max-width: 95px; display: block; object-contain: contain;">
                                    </td>
                                    <td :style="'width: 2px; vertical-align: stretch; background-color: ' + sig.lineColor + '; padding: 0;'" style="width: 2px; vertical-align: stretch; background-color: #0070c0; padding: 0;"></td>
                                    <td style="vertical-align: middle; padding-left: 20px; text-align: left;">
                                        <div style="font-size: 15px; font-weight: bold; color: #0284c7; margin-bottom: 2px; line-height: 1.2;" :style="'color: ' + sig.lineColor" x-text="sig.name || 'Nome Completo'"></div>
                                        <div style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px; line-height: 1.2;" x-text="sig.role || 'Cargo'"></div>
                                        
                                        <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 11px; color: #475569; line-height: 1.4;">
                                            <template x-if="sig.phone1">
                                                <tr>
                                                    <td style="padding-right: 4px;">🟢</td>
                                                    <td x-text="sig.phone1"></td>
                                                </tr>
                                            </template>
                                            <template x-if="sig.phone2">
                                                <tr>
                                                    <td style="padding-right: 4px;">📞</td>
                                                    <td x-text="sig.phone2"></td>
                                                </tr>
                                            </template>
                                            <template x-if="sig.address">
                                                <tr>
                                                    <td style="padding-right: 4px;">📍</td>
                                                    <td x-text="sig.address" style="word-break: break-word; max-width: 320px;"></td>
                                                </tr>
                                            </template>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <button type="button" @click="copyRichText()" class="w-full sm:flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow transition-colors flex items-center justify-center gap-2 cursor-pointer">
                            📋 Copiar Assinatura
                        </button>
                        <button type="button" @click="copyHtmlCode()" class="w-full sm:w-auto px-6 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-sm transition-colors cursor-pointer">
                            Copiar Código HTML
                        </button>
                    </div>
                </div>

            </div>
        </section>

    </main>

    <footer class="bg-white border-t border-slate-100 py-6 text-center text-xs text-slate-400">
        Desenvolvido por <strong class="text-slate-600 font-semibold">{{ $guideline->user->name }}</strong> - Manual de Marca. Todos os direitos reservados.
    </footer>

    <!-- Toast Notifications -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         class="fixed bottom-6 left-6 z-50 bg-slate-900 text-white px-4 py-3 rounded shadow-lg text-xs font-bold uppercase tracking-wider flex items-center gap-2"
         x-cloak>
        <span x-show="toast.type === 'success'">✅</span>
        <span x-show="toast.type === 'error'">❌</span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Image Preview Modal -->
    <div x-show="previewModal.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/90 backdrop-blur-sm p-4 md:p-10"
         @click="closePreview()"
         x-cloak>
        
        <!-- Close Button -->
        <button class="absolute top-6 right-6 text-white hover:text-slate-350 text-2xl font-bold cursor-pointer bg-slate-900/60 w-10 h-10 rounded-full flex items-center justify-center transition-colors">
            ✕
        </button>

        <!-- Image Container -->
        <div class="max-w-full max-h-full flex items-center justify-center" @click.stop>
            <img :src="previewModal.imageUrl" class="max-w-full max-h-[85vh] object-contain rounded shadow-2xl">
        </div>
    </div>

    <script>
        function brandPresenter() {
            return {
                activeSection: 'capa',
                logoModePrimary: 'original',
                logoModeSecondary: 'original',
                logoModeSymbol: 'original',
                toast: {
                    show: false,
                    message: '',
                    type: 'success'
                },
                previewModal: {
                    show: false,
                    imageUrl: ''
                },
                openPreview(url) {
                    this.previewModal.imageUrl = url;
                    this.previewModal.show = true;
                },
                closePreview() {
                    this.previewModal.show = false;
                    this.previewModal.imageUrl = '';
                },
                sections: [
                    { id: 'capa', label: 'Capa' },
                    { id: 'logos', label: 'Assinatura Visual' },
                    { id: 'cores', label: 'Cores' },
                    { id: 'fontes', label: 'Tipografia' },
                    { id: 'assets', label: 'Assets & Papelaria' },
                    { id: 'assinatura', label: 'Assinatura de E-mail' }
                ],
                sig: {
                    name: 'Silvana Rodrigues Mota',
                    role: 'Psicóloga - CRP:20/06594',
                    phone1: '(95) 99125-9059',
                    phone2: '(95) 3623-0348',
                    address: 'Rua da Jaqueira, 78, 2º andar — Caçari - Boa Vista - RR',
                    logoUrl: '{{ $guideline->logo_primary ? asset('storage/' . $guideline->logo_primary) : '' }}',
                    lineColor: '{{ ($guideline->color_palette && count($guideline->color_palette) > 0) ? $guideline->color_palette[0]['hex'] : '#0284c7' }}'
                },

                init() {
                    window.addEventListener('keydown', (e) => {
                        if (e.key === 'Escape') this.closePreview();
                    });
                    window.addEventListener('scroll', () => {
                        let scrollPosition = window.scrollY + 250;
                        for (let sec of this.sections) {
                            let el = document.getElementById(sec.id);
                            if (el) {
                                let top = el.offsetTop;
                                let height = el.offsetHeight;
                                if (scrollPosition >= top && scrollPosition < top + height) {
                                    this.activeSection = sec.id;
                                }
                            }
                        }
                    });
                },

                scrollToSection(id) {
                    let el = document.getElementById(id);
                    if (el) {
                        window.scrollTo({
                            top: el.offsetTop - 90,
                            behavior: 'smooth'
                        });
                        this.activeSection = id;
                    }
                },

                showToast(msg, type = 'success') {
                    this.toast.message = msg;
                    this.toast.type = type;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },

                copyHex(hex, event) {
                    navigator.clipboard.writeText(hex).then(() => {
                        this.showToast('Código HEX copiado!');
                    });
                },

                copyRichText() {
                    let container = document.getElementById('emailSignaturePreview');
                    let range = document.createRange();
                    range.selectNode(container);
                    window.getSelection().removeAllRanges();
                    window.getSelection().addRange(range);
                    try {
                        document.execCommand('copy');
                        this.showToast('Assinatura copiada! Basta colar no Gmail/Outlook.');
                    } catch (err) {
                        this.showToast('Erro ao copiar automaticamente.', 'error');
                    }
                    window.getSelection().removeAllRanges();
                },

                copyHtmlCode() {
                    let container = document.getElementById('emailSignaturePreview');
                    navigator.clipboard.writeText(container.innerHTML).then(() => {
                        this.showToast('Código HTML copiado!');
                    });
                }
            }
        }
    </script>
</body>
</html>
