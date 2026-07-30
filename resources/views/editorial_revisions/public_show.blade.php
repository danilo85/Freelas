<!DOCTYPE html>
<html lang="pt-BR" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal de Revisão Editorial - {{ $revision->title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@600;700;800;900&display=swap" rel="stylesheet">
</head>
<body class="font-sans antialiased bg-slate-50 text-slate-800 min-h-full flex flex-col justify-between">

    <!-- Topo Limpo sem Sidebar -->
    <header class="bg-white border-b border-slate-200 shadow-xs py-4 px-6 sticky top-0 z-50">
        <div class="max-w-5xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-2xl">✍️</span>
                <div>
                    <h1 class="text-base font-black font-outfit uppercase tracking-tight text-slate-900">Portal de Revisão Editorial</h1>
                    <p class="text-[11px] text-slate-400 font-medium">Área de acompanhamento e resposta a dúvidas do autor</p>
                </div>
            </div>
            <span class="px-3 py-1 bg-primary-50 text-primary-700 text-xs font-bold rounded-full border border-primary-100">
                Acesso do Autor(a)
            </span>
        </div>
    </header>

    <!-- Conteúdo Principal -->
    <main class="max-w-5xl mx-auto px-4 py-8 w-full flex-1 space-y-6">

        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-[5px] text-xs font-bold shadow-xs">
                ✅ {{ session('success') }}
            </div>
        @endif

        <!-- Card de Informações da Revisão -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-3">
            <h2 class="text-xl font-black font-outfit text-slate-900">{{ $revision->title }}</h2>
            @if($revision->description)
                <p class="text-xs text-slate-600 leading-relaxed font-medium bg-slate-50 p-3 rounded-[5px] border border-slate-100">
                    <strong>Instruções do Projeto:</strong> {{ $revision->description }}
                </p>
            @endif

            <div class="flex items-center gap-4 text-xs text-slate-500 font-medium pt-2 border-t border-slate-100">
                <span>📂 Arquivos: <strong class="text-slate-800">{{ $revision->files->count() }}</strong></span>
                <span>•</span>
                <span>❓ Dúvidas Pendentes: <strong class="text-rose-600">{{ $duvidasCount }}</strong></span>
            </div>
        </div>

        <!-- Seção 1: Dúvidas para o Autor -->
        <div class="space-y-4">
            <h3 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight flex items-center gap-2">
                <span>💬</span> Dúvidas e Questionamentos do Revisor ({{ $revision->corrections->where('category', 'duvida')->count() }})
            </h3>

            @forelse($revision->corrections->where('category', 'duvida') as $duvida)
                <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
                    
                    <div class="flex items-center justify-between text-xs">
                        <span class="px-2.5 py-0.5 rounded-[5px] bg-blue-100 text-blue-800 text-[10px] font-black uppercase">
                            Dúvida para Autor
                        </span>
                        @if($duvida->page_number)
                            <span class="text-[10px] text-slate-400 font-bold">Página {{ $duvida->page_number }}</span>
                        @endif
                    </div>

                    @if($duvida->original_text)
                        <div class="bg-slate-50 p-3 rounded-[5px] border border-slate-100 text-xs">
                            <span class="text-[9px] font-bold text-slate-400 uppercase block mb-1">Trecho Citado</span>
                            <p class="font-mono text-slate-800">"{{ $duvida->original_text }}"</p>
                        </div>
                    @endif

                    @if($duvida->justification)
                        <div class="text-xs text-slate-700 font-medium">
                            ❓ <strong>Pergunta do Revisor:</strong> {{ $duvida->justification }}
                        </div>
                    @endif

                    <!-- Comentários e Respostas -->
                    <div class="border-t border-slate-100 pt-3 space-y-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase block">Respostas Registradas:</span>
                        
                        @foreach($duvida->comments as $com)
                            <div class="p-3 rounded-[5px] bg-slate-50 text-xs text-slate-800 border border-slate-150">
                                <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 mb-1">
                                    <span>👤 {{ $com->author_name ?: 'Autor(a)' }}</span>
                                    <span>{{ $com->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="font-medium">{{ $com->message }}</p>
                            </div>
                        @endforeach

                        <!-- Formulário de Resposta do Autor -->
                        <form action="{{ route('public.editorial.reply', ['token' => $revision->share_token, 'correctionId' => $duvida->id]) }}" method="POST" class="space-y-2 pt-2">
                            @csrf
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <input type="text" name="author_name" placeholder="Seu Nome (ex: Maria)" class="px-3 py-2 border rounded-[5px] text-xs">
                                <input type="text" name="message" required placeholder="Digite sua resposta ou esclarecimento..." class="sm:col-span-2 px-3 py-2 border rounded-[5px] text-xs">
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold text-xs rounded-[5px] hover:bg-primary-700 transition-colors">
                                    Responder ao Revisor
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            @empty
                <div class="bg-white border border-dashed border-slate-200 rounded-[5px] p-8 text-center text-slate-400 text-xs font-semibold">
                    Nenhuma dúvida cadastrada para o autor até o momento.
                </div>
            @endforelse
        </div>

        <!-- Seção 2: Glossário do Projeto -->
        @if($revision->glossaries->count() > 0)
            <div class="space-y-4 pt-4 border-t border-slate-200">
                <h3 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight flex items-center gap-2">
                    <span>📖</span> Glossário e Termos do Projeto
                </h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($revision->glossaries as $glo)
                        <div class="bg-white p-4 rounded-[5px] border border-slate-200 shadow-xs text-xs space-y-1.5">
                            <span class="text-[9px] font-bold text-emerald-600 uppercase block">Termo Padronizado</span>
                            <h4 class="font-bold text-sm text-slate-900">{{ $glo->correct_term }}</h4>
                            @if($glo->description)
                                <p class="text-slate-500 font-medium">{{ $glo->description }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </main>

    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-400 font-medium">
        Revisão Editorial • Todos os direitos reservados
    </footer>

</body>
</html>
