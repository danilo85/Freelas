<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Gestor de Freelas')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col md:flex-row overflow-x-hidden relative"
      x-data="layoutState()"
      @trigger-global-delete.window="initGlobalDeleteListener($event)"
      @touchstart.window="handleTouchStart($event)"
      @touchend.window="handleTouchEnd($event)">

    <!-- Backdrop móvel para quando a Sidebar estiver aberta -->
    <div x-show="sidebarOpen" @click="sidebarOpen = false" class="fixed inset-0 bg-slate-900/40 z-30 md:hidden" x-transition x-cloak></div>

    <!-- Sidebar (Drawer no mobile, estática no desktop) -->
    <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-100 flex flex-col border-r border-slate-800 transform -translate-x-full transition-transform duration-300 ease-in-out md:sticky md:top-0 md:h-screen md:translate-x-0 md:flex"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <!-- Logo -->
        <div class="h-16 flex items-center px-6 border-b border-slate-800 justify-between">
            <span class="text-xl font-bold tracking-tight text-white flex items-center gap-2">
                <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Gestor<span class="text-primary-500">Freelas</span>
            </span>
            <!-- Botão de Fechar Sidebar (Apenas Mobile) -->
            <button @click="sidebarOpen = false" class="text-slate-400 hover:text-white md:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Menu de Navegação -->
        <nav class="flex-1 py-6 px-4 space-y-1">
            <!-- Link: Dashboard -->
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('dashboard') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z"></path>
                </svg>
                Dashboard
            </a>

            <!-- Link: Usuários (Apenas Master) -->
            @if(auth()->check() && auth()->user()->role === 'master')
                <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                    {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('users.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    Usuários
                </a>
            @endif

            <!-- Link: Projetos -->
            <a href="{{ route('projects.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('projects.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('projects.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                </svg>
                Projetos
            </a>

            <!-- Link: Pagamentos -->
            <a href="{{ route('payments.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('payments.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('payments.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Pagamentos
            </a>

            <!-- Link: Financeiro -->
            <a href="{{ route('finances.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ (request()->routeIs('finances.*') || request()->routeIs('categories.*')) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ (request()->routeIs('finances.*') || request()->routeIs('categories.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
                Controle Financeiro
            </a>

            <!-- Link: Carteira -->
            <a href="{{ route('bank-accounts.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ (request()->routeIs('bank-accounts.*') || request()->routeIs('credit-cards.*')) ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ (request()->routeIs('bank-accounts.*') || request()->routeIs('credit-cards.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
                Carteira
            </a>

            <!-- Link: Clientes -->
            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('clients.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('clients.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                Clientes
            </a>

            <!-- Link: Autores -->
            <a href="{{ route('authors.index') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('authors.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('authors.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Autores
            </a>

            <!-- Dropdown: Portfólio -->
            <div x-data="{ open: {{ (request()->routeIs('portfolio.*') || request()->routeIs('portfolio-categories.*')) ? 'true' : 'false' }} }" class="space-y-1">
                <button @click="open = !open" class="flex items-center justify-between w-full px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors text-slate-400 hover:text-white hover:bg-slate-800 focus:outline-none" :class="open ? 'text-white bg-slate-800/50' : ''">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 {{ (request()->routeIs('portfolio.*') || request()->routeIs('portfolio-categories.*')) ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                </div>
            </div>

            <!-- Link: Configurações -->
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-2.5 rounded-[5px] text-sm font-medium transition-colors 
                {{ request()->routeIs('profile.*') ? 'bg-slate-800 text-white' : 'text-slate-400 hover:text-white hover:bg-slate-800' }}">
                <svg class="w-5 h-5 {{ request()->routeIs('profile.*') ? 'text-primary-500' : 'text-slate-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Configurações
            </a>
        </nav>

        <!-- Footer da Sidebar: Perfil & Logout -->
        <div class="p-4 border-t border-slate-800 flex items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 text-white font-bold flex items-center justify-center text-sm shadow-inner shrink-0 overflow-hidden">
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
    <div class="flex-1 flex flex-col overflow-hidden min-h-screen">
        
        <!-- Header -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 md:px-8 shrink-0">
            <!-- Botão Hambúrguer Mobile -->
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-800 p-2 md:hidden">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <h1 class="text-lg font-bold text-slate-900">@yield('page_title', 'Painel de Controle')</h1>
            </div>
            
            <div class="flex items-center gap-4">
                <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-[5px] flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-primary-500 animate-pulse"></span>
                    Servidor Local
                </span>
            </div>
        </header>

        <!-- Conteúdo Principal -->
        <main class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8">
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
            <form :action="globalDeleteAction" method="POST" class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                @csrf
                @method('DELETE')
                <button type="button" @click="closeGlobalDelete()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        :disabled="globalDeleteHighSecurity && globalDeleteConfirmInput.trim().toUpperCase() !== 'EXCLUIR'" 
                        class="px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-[5px] hover:bg-red-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed shadow-sm shadow-red-600/10">
                    Confirmar Exclusão
                </button>
            </form>
        </div>
    </div>

    <!-- Script de Estado do Layout Base (Drawer + Gestos) -->
    <script>
        function layoutState() {
            return {
                sidebarOpen: false,
                touchStartX: 0,
                touchEndX: 0,
                
                // Modal de exclusão global
                globalDeleteOpen: false,
                globalDeleteTitle: 'Confirmar Exclusão',
                globalDeleteMessage: '',
                globalDeleteAction: '',
                globalDeleteHighSecurity: false,
                globalDeleteConfirmInput: '',

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
                    this.globalDeleteOpen = true;
                },
                
                closeGlobalDelete() {
                    this.globalDeleteOpen = false;
                    this.globalDeleteTitle = 'Confirmar Exclusão';
                    this.globalDeleteMessage = '';
                    this.globalDeleteAction = '';
                    this.globalDeleteHighSecurity = false;
                    this.globalDeleteConfirmInput = '';
                }
            }
        }
    </script>

    <!-- SlimSelect JavaScript CDN and Auto-Initialization -->
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
        });
    </script>
</body>
</html>
