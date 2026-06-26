<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Extrato de Pagamentos - {{ $client->name }}</title>
    
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
                    fontFamily: {
                        sans: ['Geist', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Geist', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen pb-32">

    <!-- Container Central -->
    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        <!-- Logo/Header da Marca -->
        <div class="flex items-center justify-between border-b border-slate-200 pb-4">
            <span class="text-lg font-bold tracking-tight text-slate-800 flex items-center gap-1.5">
                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Gestor<span class="text-indigo-600">Freelas</span>
            </span>
            <div class="text-right text-xs text-slate-500 font-semibold uppercase tracking-wider">
                Extrato atualizado em {{ \Carbon\Carbon::now()->format('d/m/Y \à\s H:i') }}
            </div>
        </div>

        <!-- Cartão de Identificação do Cliente (Avatar + Dados) -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-xs flex flex-col md:flex-row items-center gap-6">
            <!-- Avatar -->
            <div class="w-24 h-24 rounded-full border-2 border-slate-100 bg-slate-50 overflow-hidden shadow-inner flex items-center justify-center shrink-0">
                @if($client->avatar)
                    <img src="{{ asset('storage/' . $client->avatar) }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-3xl">
                        {{ strtoupper(substr($client->name, 0, 2)) }}
                    </div>
                @endif
            </div>

            <!-- Informações cadastrais -->
            <div class="flex-1 text-center md:text-left space-y-2">
                <span class="text-xs font-bold uppercase tracking-wider px-2 py-0.5 rounded bg-indigo-50 text-indigo-700 border border-indigo-100">
                    Área do Cliente
                </span>
                <h2 class="text-3xl font-bold text-slate-800 leading-tight mt-1">{{ $client->name }}</h2>
                
                <div class="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1 text-sm font-medium text-slate-500">
                    @if($client->document)
                        <span class="font-mono">CPF/CNPJ: {{ $client->document }}</span>
                    @endif
                    @if($client->phone)
                        <span>Tel: {{ $client->phone }}</span>
                    @endif
                    <span>Email: {{ $client->email }}</span>
                </div>
            </div>
        </div>

        <!-- Alerta de Extrato -->
        <div class="bg-indigo-50 border border-indigo-100 text-indigo-800 text-sm font-medium p-4 rounded-[5px] leading-relaxed flex items-start gap-2">
            <span>ℹ️</span>
            <div>
                Este é o seu canal direto de controle financeiro. Abaixo você encontra a consolidação de todas as suas propostas aprovadas, valores pagos e lançamentos liquidados em nosso sistema.
            </div>
        </div>

        <!-- Seção 1: Orçamentos Aprovados -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Orçamentos Aprovados</h3>
            
            @if($projects->count() > 0)
                <div class="bg-white border border-slate-200 rounded-[5px] shadow-xs overflow-hidden divide-y divide-slate-100">
                    @foreach($projects as $project)
                        @php
                            $paidAmount = (float) $project->payments()->sum('amount');
                            $remaining = max(0.00, (float) $project->total_value - $paidAmount);
                        @endphp
                        <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                            <div class="space-y-1">
                                <h4 class="font-bold text-slate-800 text-lg">{{ $project->title }}</h4>
                                <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed">{{ $project->description ?? 'Sem descrição fornecida.' }}</p>
                                <span class="text-xs text-slate-400 block font-semibold uppercase tracking-wider">Aprovado em: {{ $project->updated_at->format('d/m/Y') }}</span>
                            </div>

                            <div class="flex flex-wrap items-center gap-3 shrink-0">
                                <!-- Valor Total -->
                                <div class="text-center sm:text-right px-3.5 py-1.5 bg-slate-50 border border-slate-200 rounded-[5px] min-w-[110px]">
                                    <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block">Total</span>
                                    <span class="text-sm font-bold text-slate-700">R$ {{ number_format($project->total_value, 2, ',', '.') }}</span>
                                </div>
                                <!-- Valor Pago -->
                                <div class="text-center sm:text-right px-3.5 py-1.5 bg-emerald-50 border border-emerald-100 rounded-[5px] min-w-[110px]">
                                    <span class="text-[10px] font-semibold text-emerald-600 uppercase tracking-wider block">Pago</span>
                                    <span class="text-sm font-bold text-emerald-700">R$ {{ number_format($paidAmount, 2, ',', '.') }}</span>
                                </div>
                                <!-- Restante -->
                                <div class="text-center sm:text-right px-3.5 py-1.5 bg-rose-50 border border-rose-100 rounded-[5px] min-w-[110px]">
                                    <span class="text-[10px] font-semibold text-rose-550 uppercase tracking-wider block">Restante</span>
                                    <span class="text-sm font-bold text-rose-700">R$ {{ number_format($remaining, 2, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-dashed border-slate-200 bg-white p-8 text-center text-slate-400 rounded-[5px] text-sm font-semibold shadow-xs">
                    Nenhum orçamento aprovado registrado.
                </div>
            @endif
        </div>

        <!-- Seção 2: Histórico de Pagamentos -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider block">Histórico de Pagamentos</h3>

            @if($payments->count() > 0)
                <div class="bg-white border border-slate-200 rounded-[5px] shadow-xs overflow-hidden divide-y divide-slate-100">
                    @foreach($payments as $payment)
                        <div class="p-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="w-10 h-10 rounded-[5px] bg-slate-50 border border-slate-200 flex items-center justify-center text-xl shrink-0">
                                    💰
                                </span>
                                <div class="min-w-0">
                                    <h4 class="font-bold text-sm text-slate-800 truncate">{{ $payment->project->title }}</h4>
                                    <div class="flex items-center gap-1.5 text-xs text-slate-400 font-semibold mt-0.5 flex-wrap">
                                        <span>{{ $payment->paid_at->format('d/m/Y') }}</span>
                                        <span>•</span>
                                        <span class="uppercase tracking-wider">Método: {{ $payment->payment_method ?? 'Transferência' }}</span>
                                        @if($payment->observations)
                                            <span>•</span>
                                            <span class="normal-case font-medium italic text-slate-500 truncate max-w-[200px]" title="{{ $payment->observations }}">{{ $payment->observations }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="shrink-0 text-right">
                                <span class="inline-flex items-center gap-1 text-xs font-bold uppercase tracking-wider px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200 shadow-sm">
                                    ＋ R$ {{ number_format($payment->amount, 2, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border border-dashed border-slate-200 bg-white p-8 text-center text-slate-400 rounded-[5px] text-sm font-semibold shadow-xs">
                    Nenhum lançamento de pagamento efetuado ainda.
                </div>
            @endif
        </div>

        <!-- Copyright do Sistema -->
        <div class="text-center text-xs font-semibold text-slate-400 uppercase tracking-widest pt-8">
            © 2026 Danilo Miguel. Todos os direitos reservados.
        </div>

    </div>

    <!-- Rodapé Fixo (Floating/Sticky Summary Bar) -->
    <div class="fixed bottom-0 left-0 right-0 bg-slate-900 text-slate-100 border-t border-slate-800 shadow-[0_-8px_30px_rgb(15,23,42,0.15)] z-40">
        <div class="max-w-4xl mx-auto px-6 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
            
            <!-- Orçamentos e Totais -->
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Orçamentos</span>
                    <span class="text-base font-bold text-white">{{ $estimatesCount }} aprovados</span>
                </div>
                <div class="h-8 w-px bg-slate-800 hidden sm:block"></div>
                <div>
                    <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block">Investimento Contratado</span>
                    <span class="text-base font-bold text-white">R$ {{ number_format($totalEstimates, 2, ',', '.') }}</span>
                </div>
            </div>

            <!-- Valores Pago e Pendente -->
            <div class="flex items-center gap-3">
                <!-- Total Pago -->
                <div class="px-4 py-2 bg-emerald-950/40 border border-emerald-800 rounded-[5px] text-center min-w-[120px]">
                    <span class="text-[10px] font-semibold text-emerald-400 uppercase tracking-wider block">Total Pago</span>
                    <span class="text-sm font-bold text-emerald-400">R$ {{ number_format($totalPaid, 2, ',', '.') }}</span>
                </div>

                <!-- Saldo Devedor -->
                <div class="px-4 py-2 bg-rose-950/40 border border-rose-900 rounded-[5px] text-center min-w-[120px]">
                    <span class="text-[10px] font-semibold text-rose-400 uppercase tracking-wider block">Restante a Pagar</span>
                    <span class="text-sm font-bold text-rose-400">R$ {{ number_format($totalRemaining, 2, ',', '.') }}</span>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
