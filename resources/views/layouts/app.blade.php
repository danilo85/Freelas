<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Gestor de Freelas')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $userTheme = auth()->check() ? auth()->user()->theme_color : 'green';
        $faviconColor = match($userTheme) {
            'blue' => '#2563eb',
            'purple' => '#9333ea',
            'indigo' => '#4f46e5',
            'orange' => '#ea580c',
            default => '#16a34a',
        };
        $svgContent = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><path fill="' . $faviconColor . '" d="M865.113,449.522c10.877,386.934-382.478,526.992-710.13,483.392l-20.316-813.713C451.19,2.759,854.661,28.682,865.113,449.522z M609.715,489.39c5.268-86.891-48.151-175.483-143.27-174.42l-24.921,386.211C542.249,687.04,611.843,587.711,609.715,489.39z"/></svg>';
        $unreadNotificationsCount = auth()->check() ? \App\Models\Notification::where('user_id', auth()->id())
            ->whereNull('read_at')
            ->whereNotIn('type', ['bill_dismissed', 'reminder_dismissed'])
            ->count() : 0;
    @endphp
    <link id="app-favicon" rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,{{ rawurlencode($svgContent) }}">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Check theme before rendering to avoid flashing
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            @if(auth()->check() && auth()->user()->theme_color === 'blue')
                                50: '#eff6ff',
                                100: '#dbeafe',
                                200: '#bfdbfe',
                                500: '#3b82f6',
                                600: '#2563eb',
                                700: '#1d4ed8',
                            @elseif(auth()->check() && auth()->user()->theme_color === 'purple')
                                50: '#faf5ff',
                                100: '#f3e8ff',
                                200: '#e9d5ff',
                                500: '#a855f7',
                                600: '#9333ea',
                                700: '#7e22ce',
                            @elseif(auth()->check() && auth()->user()->theme_color === 'indigo')
                                50: '#eef2ff',
                                100: '#e0e7ff',
                                200: '#c7d2fe',
                                500: '#6366f1',
                                600: '#4f46e5',
                                700: '#4338ca',
                            @elseif(auth()->check() && auth()->user()->theme_color === 'orange')
                                50: '#fff7ed',
                                100: '#ffedd5',
                                200: '#fed7aa',
                                500: '#f97316',
                                600: '#ea580c',
                                700: '#c2410c',
                            @else
                                50: '#f0fdf4',
                                100: '#dcfce7',
                                200: '#bbf7d0',
                                500: '#22c55e',
                                600: '#16a34a',
                                700: '#15803d',
                            @endif
                        },
                        slate: {
                            50: '#f8fafc',
                            100: '#f1f5f9',
                            800: '#1e293b',
                            900: '#0f172a',
                        }
                    },
                    fontFamily: {
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <script>
        function layoutState() {
            return {
                sidebarOpen: false,
                touchStartX: 0,
                touchEndX: 0,
                scrolled: false,
                
                // Dark mode state
                darkMode: localStorage.getItem('theme') === 'dark',
                toggleTheme() {
                    this.darkMode = !this.darkMode;
                    if (this.darkMode) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('theme', 'light');
                    }
                },
                
                // Modal de exclusão global
                globalDeleteOpen: false,
                globalDeleteTitle: 'Confirmar Exclusão',
                globalDeleteMessage: '',
                globalDeleteAction: '',
                globalDeleteHighSecurity: false,
                globalDeleteConfirmInput: '',
                globalDeleteBackupUrl: '',
                globalDeleteConfirmBackup: false,

                // Visualizador Premium Global (Assets Lightbox)
                previewModalOpen: false,
                previewAsset: {},
                imageDimensions: 'Calculando...',
                imageZoom: 1,

                initGlobalPreviewListener(event) {
                    this.previewAsset = event.detail.asset;
                    this.imageDimensions = 'Calculando...';
                    this.imageZoom = 1;
                    this.previewModalOpen = true;

                    if (this.previewAsset.type === 'codigo') {
                        this.$nextTick(() => {
                            if (window.hljs) {
                                const codeBlock = this.$refs.previewCodeBlock;
                                if (codeBlock) {
                                    codeBlock.className = 'hljs bg-transparent p-0 select-text';
                                    const ext = this.previewAsset.file_path 
                                        ? this.previewAsset.file_path.split('.').pop().toLowerCase() 
                                        : (this.previewAsset.title.includes('.') ? this.previewAsset.title.split('.').pop().toLowerCase() : '');
                                    if (ext) {
                                        codeBlock.classList.add('language-' + ext);
                                    }
                                    hljs.highlightElement(codeBlock);
                                }
                            }
                        });
                    }
                },

                getImageDetails(e) {
                    this.imageDimensions = e.target.naturalWidth + ' × ' + e.target.naturalHeight + ' px';
                },

                isVideoAsset(asset) {
                    if (!asset || !asset.file_path) return false;
                    const ext = asset.file_path.split('.').pop().toLowerCase();
                    const videoExtensions = ['mp4', 'webm', 'ogg', 'mov'];
                    return videoExtensions.includes(ext) || (asset.mime_type && asset.mime_type.startsWith('video/'));
                },

                formatBytes(bytes) {
                    if (!bytes) return '0 B';
                    const k = 1024;
                    const sizes = ['B', 'KB', 'MB', 'GB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('pt-BR') + ' às ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
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
                    
                    if (diff > swipeThreshold && this.touchStartX < 50) {
                        this.sidebarOpen = true;
                    } 
                    else if (diff < -swipeThreshold && this.sidebarOpen) {
                        this.sidebarOpen = false;
                    }
                },
                
                initGlobalDeleteListener(event) {
                    const data = event.detail;
                    this.globalDeleteTitle = data.title || 'Confirmar Exclusão';
                    this.globalDeleteMessage = data.message || '';
                    this.globalDeleteAction = data.action || '';
                    this.globalDeleteHighSecurity = !!data.highSecurity;
                    this.globalDeleteConfirmInput = '';
                    this.globalDeleteBackupUrl = data.backupUrl || '';
                    this.globalDeleteConfirmBackup = false;
                    this.globalDeleteOpen = true;
                },
                
                closeGlobalDelete() {
                    this.globalDeleteOpen = false;
                    this.globalDeleteTitle = 'Confirmar Exclusão';
                    this.globalDeleteMessage = '';
                    this.globalDeleteAction = '';
                    this.globalDeleteHighSecurity = false;
                    this.globalDeleteConfirmInput = '';
                    this.globalDeleteBackupUrl = '';
                    this.globalDeleteConfirmBackup = false;
                }
            }
        }
    </script>

    <!-- Alpine.js Mask Plugin -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/mask@3.x.x/dist/cdn.min.js"></script>

    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SlimSelect CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.min.css" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        
        /* Custom Premium Select Arrow styling */
        select:not([multiple]) {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3E%3Cpath stroke='%23475569' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.75' d='m6 8 4 4 4-4'/%3E%3C/svg%3E") !important;
            background-position: right 0.6rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.15rem 1.15rem !important;
            padding-right: 2.25rem !important;
            cursor: pointer;
        }

        /* SlimSelect Custom Premium Theme Styling */
        .ss-main {
            padding: 0.6rem 1rem !important;
            border-radius: 5px !important;
            border: 1px solid #e2e8f0 !important;
            font-size: 0.875rem !important;
            font-weight: 600 !important;
            color: #334155 !important;
            background-color: #ffffff !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            height: auto !important;
            transition: all 0.2s ease !important;
        }
        .ss-main:focus-within {
            border-color: #3b82f6 !important;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2) !important;
        }
        .ss-content {
            border-radius: 5px !important;
            border: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
            z-index: 50 !important;
            transition: none !important; /* Prevents slide-in layout transition conflicts on first load */
        }
        .ss-list .ss-option {
            padding: 8px 12px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            color: #334155 !important;
            transition: background-color 0.15s ease !important;
        }
        .ss-list .ss-option:hover, .ss-list .ss-option.ss-highlighted {
            background-color: #f1f5f9 !important;
            color: #1e293b !important;
        }
        .ss-list .ss-option.ss-disabled {
            color: #94a3b8 !important;
        }
        .ss-main .ss-arrow path {
            stroke: #475569 !important;
            stroke-width: 2.5 !important;
        }

        /* Premium global dark theme styles & CSS overrides */
        .dark body {
            background-color: #0b0f19 !important; /* Premium dark background */
            color: #f1f5f9 !important;
        }
        
        /* Prevent general text selectors from overwriting status colored text */
        .dark .text-slate-900:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]),
        .dark .text-slate-800:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]),
        .dark .text-slate-750,
        .dark .text-slate-700:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]),
        .dark .text-gray-900:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]),
        .dark .text-gray-800:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]),
        .dark .text-gray-700:not([class*="text-yellow-"]):not([class*="text-amber-"]):not([class*="text-emerald-"]):not([class*="text-green-"]):not([class*="text-rose-"]):not([class*="text-red-"]):not([class*="text-blue-"]):not([class*="text-indigo-"]) {
            color: #f8fafc !important;
        }

        .dark .text-slate-655 {
            color: #cbd5e1 !important;
        }

        .dark .text-slate-600, .dark .text-slate-500, .dark .text-gray-600, .dark .text-gray-500 {
            color: #94a3b8 !important;
        }
        
        /* White Card background override, but preserving custom status cards */
        .dark .bg-white:not([class*="bg-yellow-"]):not([class*="bg-amber-"]):not([class*="bg-emerald-"]):not([class*="bg-green-"]):not([class*="bg-rose-"]):not([class*="bg-red-"]):not([class*="bg-blue-"]):not([class*="bg-indigo-"]) {
            background-color: #111827 !important; 
        }

        .dark .border-slate-200, .dark .border-slate-100, .dark .border-gray-200, .dark .border-gray-100 {
            border-color: #1f2937 !important;
        }
        
        /* Inputs */
        .dark input, .dark select, .dark textarea {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
            color: #f9fafb !important;
        }

        .dark .bg-slate-50, .dark .bg-slate-100, .dark .bg-gray-50, .dark .bg-gray-100 {
            background-color: #111827 !important;
        }

        .dark .bg-slate-50\/80, .dark .bg-slate-50\/50 {
            background-color: rgba(17, 24, 39, 0.8) !important;
        }

        /* Buttons background overrides if they are white/slate-100 */
        .dark button.bg-slate-100, .dark a.bg-slate-100, .dark .bg-slate-100 button {
            background-color: #1f2937 !important;
            color: #e5e7eb !important;
        }
        
        /* Tables */
        .dark table th {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
            border-bottom-color: #374151 !important;
        }
        .dark table td {
            color: #e5e7eb !important;
            border-bottom-color: #1f2937 !important;
        }

        .dark .shadow-sm, .dark .shadow-md, .dark .shadow-lg {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.4), 0 2px 4px -1px rgba(0, 0, 0, 0.24) !important;
        }

        /* SlimSelect */
        .dark .ss-main {
            background-color: #1f2937 !important;
            color: #f9fafb !important;
            border-color: #374151 !important;
        }
        .dark .ss-content {
            background-color: #1f2937 !important;
            border-color: #374151 !important;
        }
        .dark .ss-list .ss-option {
            color: #f9fafb !important;
        }
        .dark .ss-list .ss-option:hover, .dark .ss-list .ss-option.ss-highlighted {
            background-color: #111827 !important;
            color: #ffffff !important;
        }

        .dark .divide-y > :not([hidden]) ~ :not([hidden]) {
            border-color: #1f2937 !important;
        }

        /* Theme adjustments for status colored cards and badges */
        /* 1. Yellow/Amber/Warning - e.g. Pending Payments / Proposals */
        .dark [class*="bg-yellow-"], .dark [class*="bg-amber-"] {
            background-color: rgba(245, 158, 11, 0.12) !important;
            border-color: rgba(245, 158, 11, 0.25) !important;
        }
        .dark [class*="text-yellow-"], .dark [class*="text-amber-"] {
            color: #fbbf24 !important;
        }

        /* 2. Green/Emerald/Success - e.g. Paid Payments / Approved Proposals */
        .dark [class*="bg-green-"], .dark [class*="bg-emerald-"] {
            background-color: rgba(16, 185, 129, 0.12) !important;
            border-color: rgba(16, 185, 129, 0.25) !important;
        }
        .dark [class*="text-green-"], .dark [class*="text-emerald-"] {
            color: #34d399 !important;
        }

        /* 3. Red/Rose/Danger - e.g. Canceled Payments / Late Tasks */
        .dark [class*="bg-red-"], .dark [class*="bg-rose-"] {
            background-color: rgba(244, 63, 94, 0.12) !important;
            border-color: rgba(244, 63, 94, 0.25) !important;
        }
        .dark [class*="text-red-"], .dark [class*="text-rose-"] {
            color: #fb7185 !important;
        }

        /* 4. Blue/Indigo/Info - e.g. In Progress / Active */
        .dark [class*="bg-blue-"], .dark [class*="bg-indigo-"] {
            background-color: rgba(59, 130, 246, 0.12) !important;
            border-color: rgba(59, 130, 246, 0.25) !important;
        }
        .dark [class*="text-blue-"], .dark [class*="text-indigo-"] {
            color: #60a5fa !important;
        }
        /* Custom scrollbar for the system */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.35);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.55);
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.45);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(75, 85, 99, 0.65);
        }
    </style>
</head>
<body class="bg-slate-50 dark:bg-slate-950 dark:text-slate-100 font-sans text-slate-800 antialiased min-h-screen flex flex-col md:flex-row overflow-x-hidden relative"
      x-data="layoutState()"
      @scroll.window="scrolled = window.scrollY > 10"
      @trigger-global-delete.window="initGlobalDeleteListener($event)"
      @trigger-global-preview.window="initGlobalPreviewListener($event)"
      @touchstart.window="handleTouchStart($event)"
      @touchend.window="handleTouchEnd($event)">

    <!-- Tela de Loading de Alta Performance Global -->
    <div id="page-loader" class="fixed inset-0 bg-white dark:bg-slate-950 z-[9999] flex flex-col items-center justify-center transition-opacity duration-300">
        <div class="flex flex-col items-center space-y-6">
            <!-- Preloader Animated Logo (logo_sidebar.svg com Construção de Linhas e Preenchimento no Tema) -->
            <div class="relative flex items-center justify-center">
                <svg id="loader-logo-svg" class="w-24 h-auto text-primary-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 890.361 478.897" fill="currentColor">
                    <style>
                        @keyframes lineDrawFillPath {
                            0% {
                                stroke-dashoffset: 2500;
                                fill-opacity: 0;
                                stroke-opacity: 1;
                            }
                            60% {
                                stroke-dashoffset: 0;
                                fill-opacity: 0;
                                stroke-opacity: 1;
                            }
                            90%, 100% {
                                stroke-dashoffset: 0;
                                fill-opacity: 1;
                                stroke-opacity: 0;
                            }
                        }
                        .draw-path-1 {
                            stroke: currentColor;
                            stroke-width: 14px;
                            stroke-dasharray: 2500;
                            stroke-dashoffset: 2500;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                            animation: lineDrawFillPath 1.6s cubic-bezier(0.4, 0, 0.2, 1) infinite alternate;
                        }
                        .draw-path-2 {
                            stroke: currentColor;
                            stroke-width: 14px;
                            stroke-dasharray: 2500;
                            stroke-dashoffset: 2500;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                            animation: lineDrawFillPath 1.6s cubic-bezier(0.4, 0, 0.2, 1) 0.15s infinite alternate;
                        }
                    </style>
                    <path class="draw-path-1" d="M307.679,64.69c-18.667-17.379-40.874-30.68-66.621-39.907-25.747-9.223-53.963-13.84-84.643-13.84-24.676,0-49.513,1.56-74.507,4.668-24.997,3.112-49.297,8.423-72.896,15.931L0,463.449c10.083,1.075,19.954,1.88,29.609,2.413,9.656.538,19.522.805,29.61.805,27.034,0,53.37-2.092,79.011-6.276,25.636-4.183,49.83-10.62,72.574-19.31,22.74-8.689,43.659-19.793,62.758-33.31,19.095-13.518,35.563-29.66,49.402-48.437,13.839-18.772,24.621-40.23,32.346-64.368,7.724-24.138,11.586-51.222,11.586-81.265,0-31.751-5.205-60.183-15.609-85.287-10.41-25.103-24.942-46.344-43.609-63.724ZM234.942,234.299v4.506c-.432,12.446-2.736,24.515-6.919,36.206-4.184,11.698-10.088,22.318-17.702,31.862-7.618,9.55-16.685,17.595-27.195,24.138-10.515,6.548-22.207,10.782-35.08,12.713l12.874-199.54c13.084.432,24.349,3.168,33.792,8.207,9.439,5.043,17.219,11.697,23.334,19.953,6.115,8.263,10.51,17.757,13.195,28.483,2.681,10.732,3.913,21.885,3.701,33.472Z"/>
                    <polygon class="draw-path-2" points="717.213 0 629.672 225.932 564.016 12.875 398.591 19.311 392.154 464.092 540.843 459.586 541.488 251.035 592.337 430.621 648.981 430.621 715.28 229.794 713.994 478.897 860.753 472.461 890.361 0 717.213 0"/>
                </svg>
            </div>
            
            <!-- Progress bar -->
            <div class="w-48 h-1 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden relative">
                <div id="loader-progress" class="h-full bg-primary-500 w-0 transition-all duration-150 ease-out shadow-[0_0_8px_rgba(34,197,94,0.3)]"></div>
            </div>
            
            <span class="text-slate-400 dark:text-slate-500 text-[10px] font-bold uppercase tracking-widest animate-pulse">Carregando...</span>
        </div>
    </div>

    <script>
        (function() {
            const loader = document.getElementById('page-loader');
            const progress = document.getElementById('loader-progress');
            
            let currentProgress = 0;
            const interval = setInterval(() => {
                if (currentProgress < 85) {
                    currentProgress += Math.floor(Math.random() * 15) + 5;
                    if (progress) progress.style.width = currentProgress + '%';
                }
            }, 50);

            window.addEventListener('DOMContentLoaded', () => {
                clearInterval(interval);
                if (progress) progress.style.width = '100%';
                
                setTimeout(() => {
                    if (loader) {
                        loader.classList.add('opacity-0');
                        loader.style.pointerEvents = 'none';
                        setTimeout(() => {
                            loader.remove();
                        }, 400);
                    }
                }, 200);
            });
        })();
    </script>
    <!-- Backdrop móvel para quando a Sidebar estiver aberta -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-30 md:hidden" x-transition x-cloak></div>

    <!-- Sidebar (Drawer no mobile, estática no desktop) -->
    @php
        $sidebarBg = 'bg-slate-900';
        if(auth()->check()) {
            switch(auth()->user()->sidebar_color) {
                case 'zinc': $sidebarBg = 'bg-zinc-950'; break;
                case 'teal': $sidebarBg = 'bg-[#012d2b]'; break;
                case 'navy': $sidebarBg = 'bg-[#0b1329]'; break;
                case 'purple': $sidebarBg = 'bg-[#1e152a]'; break;
                default: $sidebarBg = 'bg-slate-900'; break;
            }
        }
    @endphp
    <aside class="fixed inset-y-0 left-0 z-40 w-64 {{ $sidebarBg }} dark:bg-slate-950 text-slate-100 flex flex-col border-r border-slate-800 dark:border-slate-900 transform -translate-x-full transition-transform duration-300 ease-in-out md:sticky md:top-0 md:h-screen md:translate-x-0 md:flex"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <!-- Logo do Sidebar com Badge de Notificações -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 dark:border-slate-900 justify-between">
            <div class="relative inline-flex items-center">
                <a href="{{ route('dashboard') }}" class="inline-flex items-center" title="Gestor Freela">
                    <!-- SVG Logo Sidebar (Acompanha a cor do Tema Visual do Dashboard) -->
                    <svg id="sidebar-logo-svg" class="h-7 w-auto text-primary-500 transition-colors duration-300" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 890.361 478.897" fill="currentColor">
                        <path d="M307.679,64.69c-18.667-17.379-40.874-30.68-66.621-39.907-25.747-9.223-53.963-13.84-84.643-13.84-24.676,0-49.513,1.56-74.507,4.668-24.997,3.112-49.297,8.423-72.896,15.931L0,463.449c10.083,1.075,19.954,1.88,29.609,2.413,9.656.538,19.522.805,29.61.805,27.034,0,53.37-2.092,79.011-6.276,25.636-4.183,49.83-10.62,72.574-19.31,22.74-8.689,43.659-19.793,62.758-33.31,19.095-13.518,35.563-29.66,49.402-48.437,13.839-18.772,24.621-40.23,32.346-64.368,7.724-24.138,11.586-51.222,11.586-81.265,0-31.751-5.205-60.183-15.609-85.287-10.41-25.103-24.942-46.344-43.609-63.724ZM234.942,234.299v4.506c-.432,12.446-2.736,24.515-6.919,36.206-4.184,11.698-10.088,22.318-17.702,31.862-7.618,9.55-16.685,17.595-27.195,24.138-10.515,6.548-22.207,10.782-35.08,12.713l12.874-199.54c13.084.432,24.349,3.168,33.792,8.207,9.439,5.043,17.219,11.697,23.334,19.953,6.115,8.263,10.51,17.757,13.195,28.483,2.681,10.732,3.913,21.885,3.701,33.472Z"/>
                        <polygon points="717.213 0 629.672 225.932 564.016 12.875 398.591 19.311 392.154 464.092 540.843 459.586 541.488 251.035 592.337 430.621 648.981 430.621 715.28 229.794 713.994 478.897 860.753 472.461 890.361 0 717.213 0"/>
                    </svg>
                </a>

                <!-- Badge de Notificações Não Lidas Posicionado no Canto da Logo DM (Atualização em Tempo Real) -->
                <template x-if="window.Alpine && $store.notifications && $store.notifications.unreadCount > 0">
                    <a href="{{ route('notifications.index') }}" 
                       class="absolute -top-1.5 -right-3 min-w-[18px] h-4.5 px-1 bg-rose-600 hover:bg-rose-700 text-white text-[10px] font-black rounded-full flex items-center justify-center border-2 border-slate-900 shadow-md transition-transform transform hover:scale-110 select-none cursor-pointer z-10" 
                       :title="$store.notifications.unreadCount + ($store.notifications.unreadCount == 1 ? ' notificação não lida' : ' notificações não lidas')">
                        <span x-text="$store.notifications.unreadCount > 99 ? '99+' : $store.notifications.unreadCount"></span>
                    </a>
                </template>
            </div>

            <!-- Botão de Fechar Sidebar (Apenas Mobile) -->
            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white md:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Menu de Navegação -->
        <nav class="flex-1 py-6 px-4 space-y-1 overflow-y-auto">
            <!-- Link: Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                </svg>
                Dashboard
            </a>

            <!-- Link/Aba de Administração (Apenas Master) -->
            <!-- Link de Administração (Apenas Master) -->
            @if(auth()->check() && auth()->user()->isMaster())
                <a href="{{ route('admin.settings.index') }}" 
                   class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors {{ (request()->routeIs('admin.settings.*') || request()->routeIs('users.*')) ? 'text-white bg-slate-800' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <svg class="w-5 h-5 {{ (request()->routeIs('admin.settings.*') || request()->routeIs('users.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                    </svg>
                    Administração
                </a>
            @endif

            <!-- Pasta: Projetos e Contatos -->
            <div x-data="{ open: {{ (request()->routeIs('projects.*') || request()->routeIs('clients.*') || request()->routeIs('authors.*')) ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" :class="open ? 'text-white bg-slate-800/50' : ''">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ (request()->routeIs('projects.*') || request()->routeIs('clients.*') || request()->routeIs('authors.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                        Projetos
                    </div>
                    <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" class="pl-9 space-y-1" style="display: none;" :style="open ? 'display: block;' : 'display: none;'">
                    <a href="{{ route('projects.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('projects.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                        Orçamentos
                    </a>
                    @if(auth()->check() && auth()->user()->isMaster())
                        <a href="{{ route('clients.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('clients.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Clientes
                        </a>
                        <a href="{{ route('authors.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('authors.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Autores
                        </a>
                    @endif
                </div>
            </div>

            <!-- Pasta: Controle Financeiro -->
            <div x-data="{ open: {{ (request()->routeIs('finances.*') || request()->routeIs('categories.*') || request()->routeIs('payments.*') || request()->routeIs('bank-accounts.*') || request()->routeIs('credit-cards.*')) ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" :class="open ? 'text-white bg-slate-800/50' : ''">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ (request()->routeIs('finances.*') || request()->routeIs('categories.*') || request()->routeIs('payments.*') || request()->routeIs('bank-accounts.*') || request()->routeIs('credit-cards.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Financeiro
                    </div>
                    <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" class="pl-9 space-y-1" style="display: none;" :style="open ? 'display: block;' : 'display: none;'">
                    <a href="{{ route('finances.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ (request()->routeIs('finances.*') && !request()->routeIs('finances.mei')) || request()->routeIs('categories.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                        Controle Financeiro
                    </a>
                    <a href="{{ route('finances.mei') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('finances.mei') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                        Faturamento & Impostos
                    </a>
                    <a href="{{ route('payments.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('payments.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                        Pagamentos
                    </a>
                    <a href="{{ route('bank-accounts.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('bank-accounts.*') || request()->routeIs('credit-cards.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                        Carteira
                    </a>
                </div>
            </div>

            @if(auth()->check() && auth()->user()->isMaster())
                <!-- Dropdown: Portfólio -->
                <div x-data="{ open: {{ (request()->routeIs('portfolio.*') || request()->routeIs('portfolio-categories.*') || request()->routeIs('portfolio.settings')) ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" :class="open ? 'text-white bg-slate-800/50' : ''">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ (request()->routeIs('portfolio.*') || request()->routeIs('portfolio-categories.*') || request()->routeIs('portfolio.settings')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Portfólio
                        </div>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="pl-9 space-y-1" style="display: none;" :style="open ? 'display: block;' : 'display: none;'">
                        <a href="{{ route('portfolio.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('portfolio.index') || request()->routeIs('portfolio.show') || request()->routeIs('portfolio.edit') || request()->routeIs('portfolio.create') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Trabalhos
                        </a>
                        <a href="{{ route('portfolio-categories.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('portfolio-categories.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Categorias
                        </a>
                        <a href="{{ route('portfolio.pipeline') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('portfolio.pipeline') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Pipeline
                        </a>
                        <a href="{{ route('portfolio.settings') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('portfolio.settings') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Configurações do Portfólio
                        </a>
                    </div>
                </div>

                <!-- Dropdown: Utilidades -->
                <div x-data="{ open: {{ (request()->routeIs('revisoes.*') || request()->routeIs('instagram.*') || request()->routeIs('revisoes-editoriais.*') || request()->routeIs('lembretes.*')) ? 'true' : 'false' }} }" class="space-y-1">
                    <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" :class="open ? 'text-white bg-slate-800/50' : ''">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 {{ (request()->routeIs('revisoes.*') || request()->routeIs('instagram.*') || request()->routeIs('revisoes-editoriais.*') || request()->routeIs('lembretes.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Utilidades
                        </div>
                        <svg class="w-3 h-3 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="open" class="pl-9 space-y-1" style="display: none;" :style="open ? 'display: block;' : 'display: none;'">
                        <a href="{{ route('instagram.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('instagram.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            📸 Instagram & Mídia Social
                        </a>
                        <a href="{{ route('revisoes.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('revisoes.index') || request()->routeIs('revisoes.show') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Revisão de Trabalhos
                        </a>
                        <a href="{{ route('revisoes-editoriais.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('revisoes-editoriais.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            ✍️ Revisão Editorial
                        </a>
                        <a href="{{ route('revisoes.shares.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('revisoes.shares.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Compartilhar Arquivos
                        </a>
                        <a href="{{ route('revisoes.assets.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('revisoes.assets.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Banco de Assets
                        </a>
                        <a href="{{ route('revisoes.brand-guidelines.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('revisoes.brand-guidelines.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Identidades Visuais
                        </a>
                        <a href="{{ route('lembretes.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('lembretes.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Lembretes e Notas
                        </a>

                        <a href="{{ route('notifications.index') }}" class="block py-2 px-3 text-xs font-semibold rounded-[5px] transition-colors {{ request()->routeIs('notifications.*') ? 'text-white bg-slate-850' : 'text-slate-400 hover:text-white' }}">
                            Notificações
                        </a>
                    </div>
                </div>
            @endif

            <!-- Link: Minha Conta -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Minha Conta
            </a>
        </nav>

        <!-- Footer da Sidebar: Perfil & Logout -->
        <div class="p-4 border-t border-slate-800 dark:border-slate-900 flex items-center justify-between gap-2 shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 dark:bg-slate-900 border border-slate-700 dark:border-slate-800 text-white font-bold flex items-center justify-center text-sm shadow-inner shrink-0 overflow-hidden">
                    @if(auth()->check() && auth()->user()->avatar)
                        <img src="{{ asset('storage/' . auth()->user()->avatar) }}" class="w-full h-full object-cover">
                    @elseif(auth()->check())
                        {{ collect(explode(' ', auth()->user()->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                    @endif
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-white truncate">{{ auth()->check() ? auth()->user()->name : 'Convidado' }}</p>
                    <p class="text-[9px] text-slate-400 font-medium uppercase tracking-wider">
                        {{ auth()->check() && auth()->user()->role === 'master' ? 'Administrador' : 'Membro' }}
                    </p>
                </div>
            </div>

            <!-- Botão Logout -->
            @if(auth()->check())
                <form action="{{ route('logout') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="text-slate-400 hover:text-red-400 p-1.5 rounded-[5px] transition-colors" title="Sair da Conta">
                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </button>
                </form>
            @endif
        </div>
    </aside>

    <!-- Área Principal -->
    <div class="flex-1 flex flex-col overflow-hidden min-h-screen pt-16 dark:bg-slate-950">
        
        <!-- Header -->
        <header class="h-16 flex items-center justify-between px-4 sm:px-6 md:px-8 shrink-0 fixed top-0 left-0 right-0 md:left-64 z-30 transition-all duration-300 backdrop-blur-md border-b"
                :class="scrolled ? 'bg-white/95 dark:bg-slate-900/95 shadow-md border-slate-200/80 dark:border-slate-800/80' : 'bg-white/70 dark:bg-slate-900/70 border-transparent'">
            <!-- Botão Hambúrguer Mobile -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-800 p-2 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-lg font-bold text-slate-900 dark:text-white">@yield('page_title', 'Painel de Controle')</h1>
            </div>
            
            <div class="flex items-center gap-2">
                <!-- Botão de Alternância de Tema -->
                <button @click="toggleTheme()" class="text-slate-500 hover:text-primary-500 dark:text-slate-400 dark:hover:text-primary-400 p-2 rounded-[5px] transition-colors" title="Alternar Tema">
                    <!-- Ícone do Sol (Mostrado no modo escuro) -->
                    <svg x-show="darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.364 17.636l-.707.707M6.364 5.636l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z"></path>
                    </svg>
                    <!-- Ícone da Lua (Mostrado no modo claro) -->
                    <svg x-show="!darkMode" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>

                @if(auth()->check())
                    <form action="{{ route('logout') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-455 p-2 rounded-[5px] transition-colors" title="Sair da Conta">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main class="flex-1 overflow-y-auto p-4 pb-28 sm:p-6 sm:pb-20 md:p-8 md:pb-8">
            <div class="max-w-[1200px] mx-auto w-full">
                
                <!-- Alerta Flash: Sucesso -->
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-[5px] p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('success') }}</span>
                        </div>
                        <button @click="show = false" class="text-green-600 hover:text-green-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                <!-- Alerta Flash: Erro -->
                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-[5px] p-4 flex items-center justify-between shadow-sm">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">{{ session('error') }}</span>
                        </div>
                        <button @click="show = false" class="text-red-600 hover:text-red-800">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Modal de Exclusão Global (Overlay de Tela Inteira) -->
    <div x-show="globalDeleteOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6 text-left" 
             @click.away="closeGlobalDelete()">
            
            <!-- Cabeçalho do Modal -->
            <div class="flex items-center gap-3 text-red-600">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-lg font-bold text-slate-900" x-text="globalDeleteTitle">Confirmar Exclusão</h3>
            </div>
            
            <!-- Mensagem e Detalhes -->
            <div class="space-y-3">
                <p class="text-sm text-slate-500" x-html="globalDeleteMessage"></p>
                
                <!-- Campo de Segurança para Alta Segurança (ex: Exclusão de Usuário) -->
                <template x-if="globalDeleteHighSecurity">
                    <div class="space-y-2">
                        <p class="text-xs text-red-600 font-medium">
                            Para confirmar, digite a palavra <strong class="uppercase font-extrabold select-all">EXCLUIR</strong> no campo abaixo:
                        </p>
                        <input type="text" x-model="globalDeleteConfirmInput" placeholder="Digite EXCLUIR para confirmar" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 placeholder-slate-400">
                    </div>
                </template>
            </div>

            <!-- Ações -->
            <form :action="globalDeleteAction" method="POST" class="pt-4 flex flex-col gap-3 border-t border-slate-100">
                @csrf
                @method('DELETE')

                <!-- Opção de Backup antes de excluir -->
                <template x-if="globalDeleteBackupUrl">
                    <label class="flex items-start gap-2.5 p-3 rounded-[5px] bg-slate-50 border border-slate-150 hover:bg-slate-100/50 cursor-pointer select-none">
                        <input type="checkbox" name="backup" value="1" x-model="globalDeleteConfirmBackup" class="mt-1 rounded border-slate-300 text-red-600 focus:ring-red-500/20">
                        <div class="space-y-0.5">
                            <span class="text-xs font-bold text-slate-800">Fazer backup dos arquivos antes de excluir</span>
                            <p class="text-[10px] text-slate-550 leading-relaxed">
                                Baixa um ZIP com os arquivos originais e relatórios de anotações organizados por rodadas para backup local.
                            </p>
                        </div>
                    </label>
                </template>

                <div class="flex items-center justify-end gap-2 w-full">
                    <button type="button" @click="closeGlobalDelete()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <button type="submit" 
                            :disabled="globalDeleteHighSecurity && globalDeleteConfirmInput.trim().toUpperCase() !== 'EXCLUIR'" 
                            class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-[5px] hover:bg-red-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm shadow-red-600/10">
                        Confirmar Exclusão
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Visualização Premium Global (Preview Lightbox) -->
    <div x-show="previewModalOpen" 
         class="fixed inset-0 z-[99999] overflow-y-auto bg-slate-900/60 backdrop-blur-sm p-4 flex justify-center items-start md:items-center animate-fade-in"
         x-transition.opacity
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-4xl w-full p-6 space-y-4 text-left select-none relative animate-scale-up" @click.away="previewModalOpen = false">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-extrabold uppercase px-1.5 py-0.5 rounded-[3px]"
                          :class="previewAsset.type === 'imagem' ? 'bg-emerald-50 text-emerald-600' : (previewAsset.type === 'fonte' ? 'bg-purple-50 text-purple-600' : (previewAsset.type === 'codigo' ? 'bg-blue-50 text-blue-600' : 'bg-amber-50 text-amber-600'))"
                          x-text="previewAsset.type"></span>
                    <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight text-slate-850" x-text="previewAsset.title"></h3>
                </div>
                <button @click="previewModalOpen = false" class="text-slate-400 hover:text-slate-650 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content Area -->
            <div class="overflow-y-auto max-h-[70vh] space-y-4 pr-1">
                
                <!-- PREVIEW: IMAGEM -->
                <div x-show="previewAsset.type === 'imagem'" class="space-y-4">
                    <div class="bg-slate-150 border border-slate-200 rounded-[5px] p-4 flex items-center justify-center max-h-[45vh] overflow-auto select-none cursor-grab active:cursor-grabbing">
                        <img :src="previewAsset.file_path ? '{{ asset('storage') }}/' + previewAsset.file_path : ''" 
                             class="max-h-[40vh] object-contain rounded shadow-sm"
                             :style="`transform: scale(${imageZoom}); transition: transform 0.15s ease-out; transform-origin: center center;`"
                             @load="getImageDetails($event); imageZoom = 1;" />
                    </div>

                    <!-- Zoom Control Bar -->
                    <div class="flex items-center justify-center gap-4 bg-slate-50 border border-slate-200 rounded-[5px] py-2 px-4 text-xs font-bold text-slate-500 max-w-xs mx-auto">
                        <span>🔍 Zoom: <span x-text="Math.round(imageZoom * 100) + '%'" class="text-slate-700 font-extrabold"></span></span>
                        <input type="range" min="0.5" max="3" step="0.1" x-model="imageZoom" class="w-28 accent-primary-600 cursor-pointer" />
                        <button type="button" @click="imageZoom = 1" class="text-slate-400 hover:text-slate-700 transition-colors uppercase font-black text-[9px]">Reset</button>
                    </div>

                    <!-- Metadata Info -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center border-t border-slate-100 pt-4">
                        <div class="bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-455 uppercase tracking-wider block">Dimensões</span>
                            <span class="text-xs font-black text-slate-700 mt-1 block" x-text="imageDimensions">Carregando...</span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-455 uppercase tracking-wider block">Tamanho</span>
                            <span class="text-xs font-black text-slate-700 mt-1 block" x-text="formatBytes(previewAsset.file_size)"></span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-455 uppercase tracking-wider block">Formato</span>
                            <span class="text-xs font-black text-slate-700 mt-1 block uppercase" x-text="previewAsset.mime_type ? previewAsset.mime_type.split('/').pop() : ''"></span>
                        </div>
                        <div class="bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                            <span class="text-[9px] font-bold text-slate-455 uppercase tracking-wider block">Enviado em</span>
                            <span class="text-xs font-black text-slate-700 mt-1 block" x-text="formatDate(previewAsset.created_at)"></span>
                        </div>
                    </div>
                </div>

                <!-- PREVIEW: FONTE (Google Fonts-like Tester) -->
                <div x-show="previewAsset.type === 'fonte'" class="space-y-4" x-data="{ fontText: 'O freela do seu jeito. Organizado e prático.', fontSize: 32 }">
                    <div class="bg-slate-50 border border-slate-200 p-4 rounded-[5px] space-y-4">
                        <!-- Controls -->
                        <div class="flex flex-col sm:flex-row gap-4 items-center justify-between border-b border-slate-150 pb-3">
                            <!-- Custom Text Input -->
                            <div class="w-full sm:flex-1">
                                <input type="text" x-model="fontText" class="w-full border-0 bg-transparent text-sm font-bold text-slate-700 placeholder-slate-400 focus:outline-none" placeholder="Digite um texto customizado para testar..." />
                            </div>
                            <!-- Size Slider -->
                            <div class="flex items-center gap-3 w-full sm:w-auto shrink-0">
                                <span class="text-[10px] font-bold text-slate-405 uppercase tracking-wider">Tamanho: <span x-text="fontSize + 'px'" class="text-slate-700 font-extrabold"></span></span>
                                <input type="range" min="12" max="72" x-model="fontSize" class="w-24 accent-primary-600 cursor-pointer" />
                            </div>
                        </div>
                        
                        <!-- Font Sample Display -->
                        <div class="py-8 px-4 overflow-x-hidden min-h-[120px] bg-white border border-slate-100 rounded-[5px] flex items-center justify-start">
                            <p :style="`font-family: font_preview_${previewAsset.id}; font-size: ${fontSize}px;`" 
                               class="text-slate-800 break-words w-full" 
                               x-text="fontText"></p>
                        </div>
                    </div>
                </div>

                <!-- PREVIEW: CODIGO (Code Editor view) -->
                <div x-show="previewAsset.type === 'codigo'" class="space-y-3" x-data="{ copyStatus: false }">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-455 font-bold uppercase tracking-wider font-mono bg-slate-100 px-2 py-1 rounded border border-slate-200" x-text="previewAsset.file_path ? previewAsset.file_path.split('.').pop().toUpperCase() : 'SNIPPET'"></span>
                        <button type="button" 
                                @click="navigator.clipboard.writeText(previewAsset.code_snippet); copyStatus = true; setTimeout(() => copyStatus = false, 2000)"
                                class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-3.5 py-1.5 rounded-[5px] transition-colors cursor-pointer"
                                x-text="copyStatus ? '✓ Copiado!' : '📋 Copiar Código'"></button>
                    </div>
                    
                    <div class="flex bg-[#282c34] border border-[#181a1f] rounded-[5px] font-mono text-xs max-h-[50vh] overflow-y-auto select-text">
                        <!-- Line Numbers Gutter -->
                        <div class="bg-[#21252b] text-slate-500 px-3 py-4 text-right border-r border-[#181a1f] select-none min-w-[3rem] leading-5">
                            <template x-for="(line, idx) in (previewAsset.code_snippet || '').split('\n')">
                                <div x-text="idx + 1" class="h-5 text-[10px]"></div>
                            </template>
                        </div>
                        <!-- Code container -->
                        <pre class="p-4 overflow-x-auto flex-1 leading-5 bg-transparent m-0"><code class="hljs bg-transparent p-0 select-text" x-text="previewAsset.code_snippet" x-ref="previewCodeBlock"></code></pre>
                    </div>
                </div>

                <!-- PREVIEW: VIDEO / PLAYER -->
                <template x-if="isVideoAsset(previewAsset)">
                    <div class="space-y-4">
                        <div class="bg-slate-950 border border-slate-900 rounded-[5px] overflow-hidden flex items-center justify-center max-h-[50vh] relative select-none">
                            <video :src="previewAsset.file_path ? '{{ asset('storage') }}/' + previewAsset.file_path : ''" 
                                   controls 
                                   autoplay
                                   class="max-h-[48vh] w-full object-contain"></video>
                        </div>
                    </div>
                </template>

                <!-- PREVIEW: OUTROS ARQUIVOS -->
                <div x-show="previewAsset.type === 'arquivo' && !isVideoAsset(previewAsset)" class="p-8 text-center bg-slate-50 border border-slate-200 rounded-[5px] space-y-4">
                    <span class="text-6xl block">📄</span>
                    <h4 class="font-outfit font-black text-slate-800 text-base text-slate-850" x-text="previewAsset.title"></h4>
                    <div class="text-xs text-slate-455 font-medium space-y-1">
                        <p>Tipo de Arquivo: <span class="font-bold text-slate-600 uppercase" x-text="previewAsset.mime_type || 'Desconhecido'"></span></p>
                        <p>Tamanho: <span class="font-bold text-slate-600" x-text="formatBytes(previewAsset.file_size)"></span></p>
                    </div>
                    <a :href="'{{ url('admin/utilidades/assets') }}/' + previewAsset.id + '/download'" class="inline-flex items-center gap-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-6 py-3 rounded-[5px] transition-colors">
                        📥 Baixar Arquivo
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- PJAX and SlimSelect JavaScript CDN and Auto-Initialization -->
    <script src="https://cdn.jsdelivr.net/npm/slim-select@2.8.2/dist/slimselect.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Function to initialize SlimSelect on all select elements, ignoring already bound ones
            const initSlimSelect = () => {
                document.querySelectorAll('select:not([data-slimselect-initialized])').forEach(el => {
                    el.setAttribute('data-slimselect-initialized', 'true');
                    new SlimSelect({
                        select: el,
                        settings: {
                            showSearch: false,
                            placeholderText: el.getAttribute('placeholder') || 'Selecione uma opção...',
                        }
                    });
                });
            };

            // Run on load
            initSlimSelect();

            // Observe dynamic DOM changes to apply SlimSelect on newly added elements
            const observer = new MutationObserver(() => {
                initSlimSelect();
            });
            observer.observe(document.body, { childList: true, subtree: true });

            // --- PJAX Container Swapper ---
            document.body.addEventListener('click', async (e) => {
                const link = e.target.closest('#pjax-container a:not([target]):not([href^="#"])');
                if (!link) return;
                
                if (link.href.includes('/download') || link.href.includes('/export') || link.classList.contains('no-pjax')) return;
                
                e.preventDefault();
                await loadPjaxPage(link.href);
            });

            document.body.addEventListener('submit', async (e) => {
                const form = e.target.closest('#pjax-container form');
                if (!form) return;
                if (form.method.toLowerCase() === 'post' && !form.classList.contains('pjax-form')) return;
                
                e.preventDefault();
                const url = new URL(form.action || window.location.href);
                const formData = new FormData(form);
                
                if (form.method.toLowerCase() === 'get') {
                    for (const [key, val] of formData.entries()) {
                        if (val !== '') {
                            url.searchParams.set(key, val);
                        } else {
                            url.searchParams.delete(key);
                        }
                    }
                    await loadPjaxPage(url.toString());
                } else {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    });
                    if (response.ok) {
                        await loadPjaxPage(window.location.href);
                    }
                }
            });

            async function loadPjaxPage(url) {
                try {
                    const container = document.getElementById('pjax-container');
                    if (container) {
                        container.style.opacity = '0.4';
                        container.style.pointerEvents = 'none';
                        container.style.transition = 'opacity 0.1s ease-out';
                    }
                    
                    const res = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const html = await res.text();
                    
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newContent = doc.getElementById('pjax-container');
                    
                    if (newContent && container) {
                        container.outerHTML = newContent.outerHTML;
                        window.history.pushState(null, '', url);
                        
                        const freshContainer = document.getElementById('pjax-container');
                        if (window.Alpine && freshContainer) {
                            window.Alpine.initTree(freshContainer);
                        }
                        
                        initSlimSelect();
                    } else {
                        window.location.href = url;
                    }
                } catch (err) {
                    console.error('PJAX Nav Error:', err);
                    window.location.href = url;
                }
            }

            window.addEventListener('popstate', () => {
                if (document.getElementById('pjax-container')) {
                    loadPjaxPage(window.location.href);
                }
            });
        });

        document.addEventListener('alpine:init', () => {
            if (typeof Alpine !== 'undefined') {
                Alpine.store('notifications', {
                    unreadCount: {{ $unreadNotificationsCount }},
                    setCount(count) {
                        this.unreadCount = count;
                    },
                    decrement() {
                        if (this.unreadCount > 0) this.unreadCount--;
                    }
                });
            }
        });
    </script>

    <!-- Global Notifications Stack -->
    <div x-data="globalNotificationStackManager()" class="fixed top-4 right-4 z-[999999] space-y-2.5 w-80 sm:w-88 select-none pointer-events-none">
        <template x-for="alert in activeNotifications" :key="alert.id">
            <div class="bg-slate-900/95 backdrop-blur-md border border-slate-750 text-white rounded-xl p-4 shadow-2xl flex flex-col gap-2.5 pointer-events-auto transition-all duration-300">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-[10px] font-black text-amber-400 uppercase tracking-widest" x-text="alert.badge"></span>
                    <button type="button" @click="dismissNotification(alert.id)" class="text-slate-400 hover:text-white font-bold text-xs shrink-0 cursor-pointer p-0.5" title="Fechar">✕</button>
                </div>

                <a :href="'{{ route('notifications.index') }}'" class="block min-w-0 select-none hover:opacity-90 transition-opacity">
                    <h4 class="text-xs font-black text-white truncate" x-text="alert.title"></h4>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-3 leading-relaxed" x-text="alert.content"></p>
                </a>

                <div class="flex items-center justify-between pt-1 border-t border-slate-800/80 mt-0.5">
                    <span class="text-[9px] font-medium text-slate-400">Notificação do Sistema</span>
                    <button type="button" 
                            @click="dismissNotification(alert.id)" 
                            class="px-2.5 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/30 rounded-[5px] text-[10px] font-bold transition-all flex items-center gap-1 cursor-pointer"
                            title="Marcar como lida e não mostrar mais">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Marcar como lida
                    </button>
                </div>
            </div>
        </template>
    </div>

    <script>
        function globalNotificationStackManager() {
            return {
                activeNotifications: [],
                checkInterval: null,
                
                init() {
                    // Fetch notifications immediately, then every 12 seconds
                    this.fetchNotifications();
                    this.checkInterval = setInterval(() => {
                        this.fetchNotifications();
                    }, 12000);
                },

                async fetchNotifications() {
                    try {
                        const res = await fetch('{{ route('lembretes.notifications') }}');
                        if (res.ok) {
                            const data = await res.json();
                            let hasNew = false;

                            // Add new notifications returned by server
                            data.forEach(item => {
                                const exists = this.activeNotifications.some(an => an.id === item.id);
                                if (!exists) {
                                    this.activeNotifications.push(item);
                                    hasNew = true;
                                }
                            });

                            // Atualiza a contagem do badge de notificações em tempo real
                            if (window.Alpine && Alpine.store('notifications')) {
                                Alpine.store('notifications').setCount(data.length);
                            }

                            if (hasNew) {
                                this.playChime();
                            }
                        }
                    } catch (e) {
                        console.warn('Error fetching system notifications:', e);
                    }
                },

                async dismissNotification(id) {
                    this.activeNotifications = this.activeNotifications.filter(an => an.id !== id);
                    if (window.Alpine && Alpine.store('notifications')) {
                        Alpine.store('notifications').decrement();
                    }
                    try {
                        const readUrl = "{{ route('lembretes.notifications.read', ':id') }}".replace(':id', id);
                        await fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });
                    } catch (e) {
                        console.warn('Error marking notification as read:', e);
                    }
                },

                playChime() {
                    try {
                        const ctx = new (window.AudioContext || window.webkitAudioContext)();
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                        gain.gain.setValueAtTime(0.08, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + 0.3);
                    } catch (e) {
                        // Browser prevents audio before user interaction
                    }
                }
            };
        }
    </script>

    <!-- 💬 WIDGET FLUTUANTE DE CHAT & INBOX LATERAL DO INSTAGRAM -->
    @if(auth()->check())
        <div x-data="{ chatOpen: false, selectedPost: null, replyMsg: '', isReplying: false, replyAlert: '', commentsList: [] }" class="relative z-50">
            <!-- Botão Flutuante no Canto Inferior Direito -->
            <button @click="chatOpen = !chatOpen" 
                    type="button" 
                    title="Instagram Live Inbox"
                    class="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-tr from-purple-600 via-rose-500 to-amber-500 text-white rounded-full shadow-2xl hover:scale-110 transition-all flex items-center justify-center cursor-pointer border-2 border-white focus:outline-none z-50 group">
                <svg class="w-7 h-7 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                <span class="absolute -top-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white animate-pulse"></span>
            </button>

            <!-- Back-drop Escuro -->
            <div x-show="chatOpen" 
                 x-transition.opacity 
                 @click="chatOpen = false" 
                 class="fixed inset-0 bg-black/60 backdrop-blur-xs z-40"></div>

            <!-- Drawer Lateral Estilo Feed / Chat do Instagram -->
            <div x-show="chatOpen" 
                 x-transition:enter="transition ease-out duration-300 transform"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in duration-200 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed top-0 right-0 h-full w-full sm:w-[420px] bg-slate-900 text-white shadow-2xl z-50 flex flex-col justify-between border-l border-slate-800">
                
                <!-- Cabeçalho do Drawer -->
                <div class="p-4 border-b border-slate-800 flex items-center justify-between bg-slate-950/80">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-purple-500 to-rose-500 p-0.5">
                            <div class="w-full h-full bg-slate-900 rounded-full flex items-center justify-center text-xs">📸</div>
                        </div>
                        <div>
                            <h4 class="text-xs font-black text-white uppercase tracking-wider">Instagram Live Inbox</h4>
                            <span class="text-[10px] text-emerald-400 font-extrabold flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Conectado ao Feed
                            </span>
                        </div>
                    </div>
                    <button type="button" @click="chatOpen = false" class="text-slate-400 hover:text-white p-1 rounded-lg text-sm font-bold">✕</button>
                </div>

                <!-- Feed Vertical de Comentários / Interações Reais -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    @php
                        $postsWithComments = collect($liveInstagramPosts ?? [])->filter(function($p) {
                            return ($p['comments_count'] ?? 0) > 0;
                        });
                    @endphp

                    @forelse($postsWithComments as $item)
                        <div class="bg-slate-800/90 border border-slate-700/80 rounded-2xl p-4 space-y-3 shadow-md hover:border-purple-500/50 transition-all"
                             x-data="{ 
                                comments: [], 
                                loaded: false, 
                                loading: false,
                                fetchComments() {
                                    if(this.loaded) return;
                                    this.loading = true;
                                    fetch('/freelas/utilidades/instagram/media/{{ $item['id'] }}/comments')
                                        .then(r => r.json())
                                        .then(d => {
                                            this.comments = d.comments || [];
                                            this.loaded = true;
                                            this.loading = false;
                                        })
                                        .catch(() => { this.loading = false; });
                                }
                             }"
                             x-init="fetchComments()">

                            <!-- Header da Publicação -->
                            <div class="flex items-center gap-3">
                                @if(!empty($item['media_url']))
                                    <img src="{{ $item['media_url'] }}" class="w-12 h-12 rounded-xl object-cover border border-slate-700 shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-xl bg-slate-900 border border-slate-700 flex items-center justify-center text-lg shrink-0">🖼️</div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-slate-200 line-clamp-1 font-semibold">{{ $item['caption'] ?? 'Publicação do Instagram' }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-slate-400 font-mono mt-0.5">
                                        <span>❤️ {{ number_format($item['like_count'] ?? 0, 0, ',', '.') }}</span>
                                        <span>💬 {{ $item['comments_count'] ?? 0 }} comentários</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Estado de Carregamento dos Comentários da Meta -->
                            <template x-if="loading">
                                <div class="p-3 bg-slate-900/60 rounded-xl text-center text-[10px] text-purple-300 animate-pulse font-bold">
                                    ⏳ Carregando comentários do Instagram...
                                </div>
                            </template>

                            <!-- Lista dos Comentários Reais buscados da Meta Graph API -->
                            <template x-if="loaded && comments.length > 0">
                                <div class="space-y-2">
                                    <template x-for="c in comments" :key="c.id">
                                        <div class="bg-slate-900/90 rounded-xl p-3 space-y-2 border border-slate-700/50 text-left" x-data="{ replyMsg: '', isReplying: false, replyAlert: '' }">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex items-center gap-2">
                                                    <div class="w-6 h-6 rounded-full bg-purple-600 flex items-center justify-center text-[10px] font-black text-white shrink-0" x-text="(c.author_name || c.username || 'S').substring(0, 1).toUpperCase()">
                                                    </div>
                                                    <span class="text-xs font-bold text-purple-300" x-text="'@' + (c.author_name || c.username || 'seguidor')"></span>
                                                </div>
                                                <span class="text-[9px] text-slate-500 font-mono">Meta Live</span>
                                            </div>
                                            <p class="text-xs text-slate-200 leading-relaxed font-medium pl-8" x-text="c.text"></p>

                                            <!-- Caixa de Resposta por Comentário Real -->
                                            <div class="space-y-2 pt-2 border-t border-slate-800/80">
                                                <div class="flex flex-wrap gap-1">
                                                    <button type="button" @click="replyMsg = 'Muito obrigado pelo carinho! ❤️'" class="px-2 py-0.5 bg-slate-800 hover:bg-purple-900/60 text-[9px] text-purple-200 rounded border border-slate-700">❤️ Agradecer</button>
                                                    <button type="button" @click="replyMsg = 'Te chamamos no Direct com os detalhes! 📩'" class="px-2 py-0.5 bg-slate-800 hover:bg-purple-900/60 text-[9px] text-purple-200 rounded border border-slate-700">📩 Direct</button>
                                                </div>

                                                <div class="flex gap-2">
                                                    <input type="text" x-model="replyMsg" :placeholder="'Responder a @' + (c.username || 'seguidor') + '...'" class="flex-1 bg-slate-950 text-white border border-slate-700 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-purple-400">
                                                    <button type="button" 
                                                            @click="
                                                                if(!replyMsg.trim()) return;
                                                                isReplying = true;
                                                                fetch('{{ route('instagram.comments.reply') }}', {
                                                                    method: 'POST',
                                                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                                    body: JSON.stringify({ comment_id: c.id, message: replyMsg })
                                                                })
                                                                .then(r => r.json())
                                                                .then(d => {
                                                                    isReplying = false;
                                                                    replyAlert = d.message || 'Enviado!';
                                                                    if(d.success) setTimeout(() => { replyMsg = ''; replyAlert = ''; }, 2000);
                                                                });
                                                            "
                                                            :disabled="isReplying"
                                                            class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl cursor-pointer shrink-0">
                                                        <span x-text="isReplying ? '...' : 'Responder'"></span>
                                                    </button>
                                                </div>
                                                <span x-show="replyAlert" class="text-[10px] text-emerald-400 font-bold block" x-text="replyAlert"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Se não retornou array estendido mas possui contador -->
                            <template x-if="loaded && comments.length === 0">
                                <div class="bg-slate-900/90 rounded-xl p-3 space-y-2 border border-slate-700/50" x-data="{ replyMsg: '', isReplying: false, replyAlert: '' }">
                                    <p class="text-xs text-slate-400 italic">Esta publicação possui comentários no Instagram. Responda diretamente abaixo:</p>
                                    <div class="flex gap-2">
                                        <input type="text" x-model="replyMsg" placeholder="Responder comentário no post..." class="flex-1 bg-slate-950 text-white border border-slate-700 rounded-xl px-3 py-1.5 text-xs outline-none focus:border-purple-400">
                                        <button type="button" 
                                                @click="
                                                    if(!replyMsg.trim()) return;
                                                    isReplying = true;
                                                    fetch('{{ route('instagram.comments.reply') }}', {
                                                        method: 'POST',
                                                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                                        body: JSON.stringify({ comment_id: '{{ $item['id'] }}', message: replyMsg })
                                                    })
                                                    .then(r => r.json())
                                                    .then(d => {
                                                        isReplying = false;
                                                        replyAlert = d.message || 'Enviado!';
                                                        if(d.success) setTimeout(() => { replyMsg = ''; replyAlert = ''; }, 2000);
                                                    });
                                                "
                                                :disabled="isReplying"
                                                class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl cursor-pointer shrink-0">
                                            <span x-text="isReplying ? '...' : 'Enviar'"></span>
                                        </button>
                                    </div>
                                    <span x-show="replyAlert" class="text-[10px] text-emerald-400 font-bold block" x-text="replyAlert"></span>
                                </div>
                            </template>
                        </div>
                    @empty
                        <div class="text-center p-8 bg-slate-800/50 rounded-2xl border border-slate-800 text-slate-400 space-y-2">
                            <span class="text-3xl block">💬</span>
                            <p class="text-xs font-bold text-slate-300">Nenhum comentário pendente no momento.</p>
                            <p class="text-[11px] text-slate-500">As publicações com comentários recebidos aparecerão aqui automaticamente estilo chat!</p>
                        </div>
                    @endforelse
                </div>

                <!-- Rodapé do Drawer -->
                <div class="p-3 bg-slate-950 border-t border-slate-800 text-center text-[10px] text-slate-500">
                    <span>Meta Graph API Live Chat Sync</span>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
