@extends('layouts.app')

@section('title', 'Pipeline de Portfólio - Gestor de Freelas')
@section('page_title', 'Pipeline de Portfólio')

@section('content')
<div class="space-y-6">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para Trabalhos
        </a>
    </div>

    <!-- Informações explicativas -->
    <div class="bg-blue-50 border border-blue-150 p-4 rounded-[5px] text-sm text-blue-800 leading-relaxed flex items-start gap-3">
        <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <strong class="font-bold">O que é o Pipeline de Portfólio?</strong>
            <p class="mt-1 font-normal text-blue-700">Esta tela reúne todos os seus orçamentos que foram marcados com o status <span class="bg-blue-100 text-blue-900 px-1 py-0.5 rounded text-xs font-bold uppercase">finalizado</span>. Você pode importá-los rapidamente para o seu portfólio clicando no botão de importação, o qual preencherá previamente o título, descrição, clientes e autores associados.</p>
        </div>
    </div>

    <!-- Listagem de Projetos Concluídos -->
    <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Orçamentos Finalizados</h3>
            <span class="text-xs text-slate-400 font-semibold">{{ $projects->count() }} disponíveis</span>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse($projects as $proj)
                @php
                    $isAlreadyImported = in_array($proj->id, $importedProjectIds);
                @endphp
                <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <h4 class="font-extrabold text-slate-800 text-sm">{{ $proj->title }}</h4>
                            @if($isAlreadyImported)
                                <span class="bg-emerald-100 text-emerald-800 border border-emerald-200 text-[9px] font-black uppercase tracking-wider px-1.5 py-0.5 rounded-[4px]">
                                    Importado
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-400 flex flex-wrap items-center gap-1.5">
                            <span>Cliente: <strong>{{ $proj->client->name }}</strong></span>
                            <span>•</span>
                            <span>Valor: <strong class="text-slate-650">R$ {{ number_format($proj->total_value, 2, ',', '.') }}</strong></span>
                            <span>•</span>
                            <span>Concluído em: <strong>{{ $proj->updated_at->format('d/m/Y') }}</strong></span>
                        </p>

                        <!-- Autores vinculados -->
                        @if($proj->authors->count() > 0)
                            <div class="pt-1.5 flex flex-wrap gap-1 items-center">
                                <span class="text-[10px] text-slate-400 font-semibold mr-1">Autores do Trabalho:</span>
                                @foreach($proj->authors as $author)
                                    <span class="bg-slate-100 text-slate-650 text-[9px] font-bold px-1.5 py-0.5 rounded-[4px] border border-slate-150">{{ $author->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="shrink-0 flex items-center gap-2">
                        @if($isAlreadyImported)
                            <button disabled 
                                    class="py-2 px-3 bg-slate-100 text-slate-400 text-xs font-bold rounded-[5px] border border-slate-200 cursor-not-allowed">
                                Já Importado
                            </button>
                        @else
                            <a href="{{ route('portfolio.create', ['project_id' => $proj->id]) }}" 
                               class="py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar para Portfólio
                            </a>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center text-slate-400 text-sm italic">
                    Nenhum orçamento finalizado disponível para importar no momento.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
