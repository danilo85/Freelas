<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta Comercial - {{ $proposal->project->title }}</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                        }
                    },
                    fontFamily: {
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        .wysiwyg-content {
            font-family: 'Geist', sans-serif !important;
        }
        .wysiwyg-content ul {
            list-style-type: disc !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .wysiwyg-content ol {
            list-style-type: decimal !important;
            padding-left: 1.5rem !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        .wysiwyg-content a {
            color: #2563eb !important;
            text-decoration: underline !important;
        }
        .wysiwyg-content u {
            text-decoration: underline !important;
        }

        /* Estilos de Impressão */
        @media print {
            body {
                background-color: transparent !important;
                background-image: none !important;
                color: #000000 !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            .no-print {
                display: none !important;
            }
            .print-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            /* Garantir que as cores de fundo sejam impressas */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased min-h-screen flex flex-col items-center justify-start p-4 sm:p-8" x-data="{ rejectModalOpen: false }">

    <!-- Top Action Bar (Escondida na impressão) -->
    <div class="w-full max-w-4xl no-print mb-6 bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            @if($proposal->project->client->user->logo)
                <img src="{{ asset('storage/' . $proposal->project->client->user->logo) }}" class="max-h-8 w-auto object-contain shrink-0" alt="Logo">
                <span class="h-5 w-px bg-slate-200"></span>
            @endif
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full 
                    {{ $proposal->project->status === 'aprovado' || $proposal->project->status === 'quitado' || $proposal->project->status === 'finalizado' ? 'bg-emerald-500' : '' }}
                    {{ $proposal->project->status === 'rejeitado' ? 'bg-red-500' : '' }}
                    {{ $proposal->project->status === 'rascunho' || $proposal->project->status === 'analisando' ? 'bg-amber-500' : '' }}"></span>
                <span class="text-sm font-bold text-slate-700">Status: {{ ucfirst($proposal->project->status) }}</span>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <!-- Imprimir -->
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-[5px] text-sm shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                Imprimir
            </button>
            <!-- Salvar PDF -->
            <button type="button" onclick="window.print()" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-[5px] text-sm shadow-sm transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Salvar PDF
            </button>
        </div>
    </div>

    <!-- Mensagens de Sucesso ou Erro (Escondidas na impressão) -->
    @if(session('success'))
        <div class="w-full max-w-4xl no-print mb-6 bg-green-50 border border-green-200 text-green-800 rounded-[5px] p-4 flex items-center gap-3 shadow-sm">
            <svg class="w-5 h-5 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="text-sm font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Documento Comercial / Proposta (Área que será impressa) -->
    <div class="w-full max-w-4xl bg-white border border-slate-200 rounded-[5px] p-6 sm:p-8 space-y-6 shadow-md relative min-h-[500px] print-card">
        <!-- Cabeçalho da Proposta -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-4">
                @if($proposal->project->client->user->logo)
                    <img src="{{ asset('storage/' . $proposal->project->client->user->logo) }}" class="max-h-12 sm:max-h-16 w-auto object-contain shrink-0" alt="Logo">
                    <span class="h-8 w-px bg-slate-200 hidden sm:block"></span>
                @endif
                <h1 class="text-3xl sm:text-4xl font-bold text-slate-900 tracking-tight">PROPOSTA</h1>
            </div>
            
            <!-- Badge Circular com número da proposta -->
            <div class="relative shrink-0 self-end sm:self-auto">
                <div class="w-11 h-11 rounded-full bg-[#1e293b] flex items-center justify-center text-white font-bold text-sm shadow-md">
                    <span>{{ $proposal->project->id }}</span>
                </div>
                <!-- Círculo de Status -->
                <span class="absolute -top-0.5 -right-0.5 w-3 h-3 border-2 border-white rounded-full
                    {{ $proposal->project->status === 'aprovado' || $proposal->project->status === 'quitado' || $proposal->project->status === 'finalizado' ? 'bg-emerald-500' : '' }}
                    {{ $proposal->project->status === 'rejeitado' ? 'bg-red-500' : '' }}
                    {{ $proposal->project->status === 'rascunho' || $proposal->project->status === 'analisando' ? 'bg-amber-500' : '' }}"></span>
            </div>
        </div>

        <!-- Datas e Cliente -->
        <div class="text-sm text-slate-400 font-semibold space-y-1.5 border-b border-slate-100 pb-4">
            <div>Válido de <span class="text-slate-500 font-bold">{{ \Carbon\Carbon::parse($proposal->project->budget_date)->format('d/m/Y') }}</span> a <span class="text-slate-500 font-bold">{{ \Carbon\Carbon::parse($proposal->project->expiration_date)->format('d/m/Y') }}</span></div>
            <div>Para <span class="font-semibold text-slate-700">{{ $proposal->project->client->name }}</span></div>
        </div>

        <!-- Seção: Orçamento -->
        <div class="space-y-3 mt-6">
            <h5 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Orçamento:</h5>
            <p class="text-sm text-slate-650 font-bold leading-relaxed">{{ $proposal->project->title }}</p>
            
            <!-- Conteúdo HTML do Editor WYSIWYG -->
            <div class="text-sm text-slate-600 leading-relaxed space-y-2 wysiwyg-content pt-2">
                {!! $proposal->project->description !!}
            </div>
        </div>

        <!-- Seção: Prazo -->
        <div class="space-y-1.5 mt-6 border-t border-slate-100 pt-4">
            <h5 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Prazo:</h5>
            <p class="text-sm text-slate-660 font-medium">Prazo estimado é de {{ $proposal->project->term }} dias</p>
        </div>

        <!-- Bloco Financeiro e Forma de Pagamento -->
        <div class="border border-slate-200 rounded-[5px] overflow-hidden mt-8 shadow-sm">
            <!-- Topo do bloco -->
            <div class="bg-slate-50/50 p-5 space-y-1">
                <span class="text-sm font-bold text-slate-400 tracking-wider uppercase block">Total</span>
                <h3 class="text-4xl sm:text-5xl font-bold text-[#0f172a] tracking-tight">R$ {{ number_format($proposal->project->total_value, 2, ',', '.') }}</h3>
                <span class="text-sm text-slate-400 block pt-1">Forma de pagamento:</span>
            </div>
            <!-- Rodapé do bloco (Divisão das parcelas) -->
            <div class="flex text-sm">
                <!-- Sinal -->
                <div class="bg-[#1e293b] text-white p-4 flex-1">
                    <span class="text-sm font-bold text-slate-300 tracking-wider block uppercase">{{ $proposal->project->initial_payment_percent }}% Para Iniciar</span>
                    <span class="text-base font-bold block mt-1">1º R$ {{ number_format($proposal->project->total_value * ($proposal->project->initial_payment_percent / 100), 2, ',', '.') }}</span>
                </div>
                <!-- Restante -->
                <div class="bg-[#334155] text-white p-4 flex-1 border-l border-slate-700">
                    <span class="text-sm font-bold text-slate-300 tracking-wider block uppercase">{{ 100 - $proposal->project->initial_payment_percent }}% Ao Término</span>
                    <span class="text-base font-bold block mt-1">2º R$ {{ number_format($proposal->project->total_value * ((100 - $proposal->project->initial_payment_percent) / 100), 2, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Rodapé informativo -->
        <div class="pt-4 flex items-center justify-between text-sm font-bold uppercase tracking-wider text-slate-400 border-t border-slate-100">
            <span>Status do Orçamento</span>
            <span class="px-2.5 py-0.5 rounded-[5px] border flex items-center gap-1.5
                {{ $proposal->project->status === 'rascunho' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
                {{ $proposal->project->status === 'analisando' ? 'bg-amber-100 text-amber-900 border-amber-300' : '' }}
                {{ $proposal->project->status === 'aprovado' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : '' }}
                {{ $proposal->project->status === 'rejeitado' ? 'bg-red-100 text-red-900 border-red-300' : '' }}
                {{ $proposal->project->status === 'quitado' ? 'bg-purple-100 text-purple-900 border-purple-300' : '' }}
                {{ $proposal->project->status === 'finalizado' ? 'bg-blue-100 text-blue-900 border-blue-300' : '' }}">
                <span class="w-2 h-2 rounded-full inline-block
                    {{ $proposal->project->status === 'rascunho' ? 'bg-slate-400' : '' }}
                    {{ $proposal->project->status === 'analisando' ? 'bg-amber-500' : '' }}
                    {{ $proposal->project->status === 'aprovado' ? 'bg-emerald-500' : '' }}
                    {{ $proposal->project->status === 'rejeitado' ? 'bg-red-500' : '' }}
                    {{ $proposal->project->status === 'quitado' ? 'bg-purple-500' : '' }}
                    {{ $proposal->project->status === 'finalizado' ? 'bg-blue-500' : '' }}"></span>
                <span>{{ $proposal->project->status }}</span>
            </span>
        </div>
    </div>

    <!-- Client Actions Footer (Escondido na impressão) -->
    @if($proposal->project->status === 'rascunho' || $proposal->project->status === 'analisando')
        <div class="w-full max-w-4xl no-print mt-6 bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm flex flex-col sm:flex-row items-center justify-end gap-3">
            <!-- Recusar -->
            <button type="button" @click="rejectModalOpen = true" class="w-full sm:w-auto px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-[5px] text-sm shadow-sm transition-colors text-center">
                Rejeitar Orçamento
            </button>

            <!-- Aprovar -->
            <form action="{{ route('proposal.approve', $proposal->hash) }}" method="POST" class="w-full sm:w-auto">
                @csrf
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-[5px] text-sm shadow-sm transition-colors text-center">
                    Aprovar Orçamento
                </button>
            </form>
        </div>
    @else
        <!-- Proposta já respondida -->
        <div class="w-full max-w-4xl no-print mt-6 bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm text-center">
            @if($proposal->project->status === 'aprovado' || $proposal->project->status === 'quitado' || $proposal->project->status === 'finalizado')
                <div class="inline-flex items-center gap-2 text-emerald-900 font-semibold bg-emerald-100 border border-emerald-300 px-4 py-2.5 rounded-[5px] text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Esta proposta foi aprovada. Obrigado!
                </div>
            @elseif($proposal->project->status === 'rejeitado')
                <div class="inline-flex items-center gap-2 text-red-900 font-semibold bg-red-100 border border-red-300 px-4 py-2.5 rounded-[5px] text-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Esta proposta foi rejeitada.
                </div>
            @endif
        </div>
    @endif

    <!-- Modal de Confirmação de Rejeição (Overlay) -->
    <div x-show="rejectModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6 text-left" @click.away="rejectModalOpen = false">
            <div class="flex items-center gap-3 text-red-600">
                <svg class="w-6 h-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <h3 class="text-lg font-bold text-slate-900">Rejeitar Orçamento</h3>
            </div>
            
            <p class="text-sm text-slate-500">
                Tem certeza de que deseja rejeitar esta proposta comercial? Você poderá entrar em contato com o prestador para renegociar se necessário.
            </p>

            <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-slate-200 text-slate-500 text-sm font-semibold rounded-[5px] hover:bg-slate-50 transition-colors">
                    Cancelar
                </button>
                <form action="{{ route('proposal.reject', $proposal->hash) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-[5px] hover:bg-red-700 transition-colors shadow-sm shadow-red-600/10">
                        Confirmar Rejeição
                    </button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>
