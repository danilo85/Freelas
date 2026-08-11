<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Mensal de Desempenho - Instagram @{{ $account->username }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: black !important; }
            .page-break { page-break-before: always; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 font-sans p-8 max-w-5xl mx-auto">

    <!-- Barra Superior de Controle de Impressão -->
    <div class="no-print bg-purple-950 border border-purple-800 text-purple-200 p-4 rounded-xl mb-8 flex items-center justify-between shadow-lg">
        <div class="flex items-center gap-3">
            <span class="text-2xl">📊</span>
            <div>
                <h4 class="font-extrabold text-white text-sm">Relatório Mensal de Instagram Prontinho!</h4>
                <p class="text-xs text-purple-300">Clique em "Imprimir / Salvar PDF" para gerar o documento limpo para enviar ao seu cliente.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs rounded-lg shadow-md cursor-pointer transition-all">
                🖨️ Imprimir / Salvar PDF
            </button>
            <a href="{{ route('instagram.index') }}" class="px-4 py-2 bg-slate-800 text-slate-300 font-bold text-xs rounded-lg border border-slate-700 hover:text-white">
                Voltar
            </a>
        </div>
    </div>

    <!-- CABEÇALHO DO RELATÓRIO DO CLIENTE -->
    <div class="bg-gradient-to-r from-purple-950 via-slate-900 to-slate-900 border border-purple-500/30 rounded-2xl p-8 shadow-xl space-y-6">
        <div class="flex items-center justify-between border-b border-purple-900/50 pb-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 p-0.5 shadow-md">
                    <img src="{{ $account->profile_picture_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($account->username) }}" class="w-full h-full object-cover rounded-full">
                </div>
                <div>
                    <h1 class="text-2xl font-black text-white tracking-tight">Relatório de Mídia Social & Instagram</h1>
                    <p class="text-sm font-bold text-purple-400 mt-0.5">Perfil: @{{ $account->username }}</p>
                </div>
            </div>
            <div class="text-right">
                <span class="px-3 py-1 bg-purple-500/20 text-purple-300 text-xs font-black rounded-full border border-purple-500/30 uppercase">
                    Documento Oficial
                </span>
                <p class="text-xs text-slate-400 mt-2 font-mono">Gerado em: {{ date('d/m/Y H:i') }}</p>
            </div>
        </div>

        <!-- METRICAS KPI DE DESEMPENHO -->
        <div class="grid grid-cols-4 gap-4 pt-2">
            <div class="bg-slate-900/80 border border-purple-900/40 p-4 rounded-xl text-center space-y-1">
                <span class="text-xs font-extrabold uppercase text-slate-400 block">Total de Posts</span>
                <span class="text-2xl font-black text-purple-300">{{ $totalPostsCount }}</span>
            </div>
            <div class="bg-slate-900/80 border border-purple-900/40 p-4 rounded-xl text-center space-y-1">
                <span class="text-xs font-extrabold uppercase text-slate-400 block">Curtidas Acumuladas</span>
                <span class="text-2xl font-black text-rose-400">❤️ {{ number_format($totalLikes, 0, ',', '.') }}</span>
            </div>
            <div class="bg-slate-900/80 border border-purple-900/40 p-4 rounded-xl text-center space-y-1">
                <span class="text-xs font-extrabold uppercase text-slate-400 block">Comentários</span>
                <span class="text-2xl font-black text-sky-400">💬 {{ number_format($totalComments, 0, ',', '.') }}</span>
            </div>
            <div class="bg-slate-900/80 border border-purple-900/40 p-4 rounded-xl text-center space-y-1">
                <span class="text-xs font-extrabold uppercase text-slate-400 block">Média de Engajamento</span>
                <span class="text-2xl font-black text-emerald-400">🔥 {{ $avgEngagement }}</span>
            </div>
        </div>
    </div>

    <!-- LISTA DE POSTS PUBLICADOS E PERFORMANCE -->
    <div class="mt-8 space-y-4">
        <h3 class="text-base font-extrabold text-white uppercase tracking-wider">Detalhamento das Publicações Recentes</h3>
        
        <div class="grid grid-cols-2 gap-4">
            @foreach(array_slice($liveInstagramPosts, 0, 10) as $p)
                <div class="bg-slate-800/80 border border-slate-700/80 p-4 rounded-xl flex gap-4 items-start shadow-sm">
                    @if(!empty($p['media_url']))
                        <img src="{{ $p['media_url'] }}" class="w-20 h-20 object-cover rounded-lg shrink-0">
                    @else
                        <div class="w-20 h-20 bg-slate-900 rounded-lg flex items-center justify-center text-slate-600 text-2xl shrink-0">📸</div>
                    @endif
                    <div class="space-y-1.5 min-w-0 flex-1">
                        <span class="px-2 py-0.5 bg-purple-600 text-white text-[9px] font-black uppercase rounded">
                            {{ $p['media_type'] ?? 'FEED' }}
                        </span>
                        <p class="text-xs text-slate-200 line-clamp-2 leading-snug">
                            {{ $p['caption'] ?? 'Sem legenda' }}
                        </p>
                        <div class="flex items-center gap-3 text-xs font-bold text-slate-400 pt-1">
                            <span class="text-rose-400">❤️ {{ $p['like_count'] ?? 0 }}</span>
                            <span class="text-sky-400">💬 {{ $p['comments_count'] ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- RODAPÉ DO DOCUMENTO -->
    <div class="mt-12 pt-6 border-t border-slate-800 text-center text-xs text-slate-500">
        <p>Relatório gerado automaticamente pela plataforma <strong>Gestor de Freelas & Mídia Social</strong>.</p>
    </div>
</body>
</html>
