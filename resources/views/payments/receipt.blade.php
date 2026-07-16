<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo #{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }} - Danilo Miguel</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        outfit: ['Outfit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: white !important;
            }
            .receipt-card {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-50 font-sans text-slate-700 min-h-screen flex items-center justify-center p-4 sm:p-8">

    <div class="bg-white border border-slate-200 shadow-lg rounded-lg max-w-2xl w-full p-8 sm:p-12 space-y-8 receipt-card relative">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-6">
            <h1 class="text-4xl font-outfit font-black text-slate-800 tracking-tight">RECIBO</h1>
            
            <!-- User Logo -->
            @if($payment->project->client->user && $payment->project->client->user->logo)
                <img src="{{ asset('storage/' . $payment->project->client->user->logo) }}" class="max-h-12 w-auto object-contain shrink-0" alt="Logo">
            @else
                <div class="bg-slate-900 text-white font-outfit font-black px-4.5 py-2 text-2xl rounded-[4px] tracking-tighter select-none">
                    DM
                </div>
            @endif
        </div>

        <!-- Info Fields -->
        <div class="space-y-3.5 text-sm sm:text-base">
            <div class="flex">
                <span class="font-bold text-slate-500 w-36 uppercase tracking-wider text-xs sm:text-sm pt-0.5">Número:</span>
                <span class="font-semibold text-slate-800 font-mono">{{ str_pad($payment->id, 4, '0', STR_PAD_LEFT) }}</span>
            </div>
            <div class="flex">
                <span class="font-bold text-slate-500 w-36 uppercase tracking-wider text-xs sm:text-sm pt-0.5">Data de Emissão:</span>
                <span class="font-semibold text-slate-800">{{ $formattedDateString }}</span>
            </div>
        </div>

        <!-- Declaration Text -->
        <div class="text-base sm:text-lg text-slate-700 leading-relaxed font-normal pt-2">
            Eu, <strong class="text-slate-850 font-bold">Danilo Miguel</strong>, inscrito(a) no CPF/CNPJ sob o nº <strong class="text-slate-850 font-bold">35102826808</strong>, declaro para os devidos fins que recebi de <strong class="text-slate-850 font-bold">{{ $payment->project->client->name }}{{ $payment->project->client->company ? ' (' . $payment->project->client->company . ')' : '' }}</strong>, a importância de <strong class="text-slate-850 font-bold">R$ {{ $amountFormatted }}</strong> (<span class="italic text-slate-550">{{ $amountInWords }}</span>), referente ao pagamento de <strong class="text-slate-850 font-bold uppercase">{{ $payment->payment_method }}</strong> do projeto <strong class="text-slate-850 font-bold">"{{ $payment->project->title }}"</strong>.
        </div>

        <!-- Signature Section -->
        <div class="text-center pt-12">
            <div class="w-72 border-t border-slate-400 mx-auto mb-2"></div>
            <span class="font-outfit font-bold text-slate-800 text-base">Danilo Miguel</span>
        </div>

        <!-- Actions -->
        <div class="flex justify-center pt-4 no-print">
            <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm uppercase tracking-wider px-8 py-3 rounded-[5px] transition-colors shadow-md shadow-blue-500/10 cursor-pointer">
                Imprimir / Salvar PDF
            </button>
        </div>

    </div>

</body>
</html>
