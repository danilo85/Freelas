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

    <!-- Listagem de Projetos Concluídos em Cards -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wider">Orçamentos Finalizados</h3>
            <span class="text-xs text-slate-400 font-bold bg-slate-100 px-2 py-0.5 rounded-[4px] border border-slate-150">{{ $projects->count() }} disponíveis</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            @forelse($projects as $proj)
                @php
                    $isAlreadyImported = in_array($proj->id, $importedProjectIds);
                @endphp
                <div class="bg-white border border-slate-200 p-5 rounded-[5px] shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-shadow duration-200 w-full relative overflow-hidden">
                    
                    @if($isAlreadyImported)
                        <!-- Destaque Importado -->
                        <div class="absolute top-0 right-0 bg-emerald-500 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-bl-[5px] flex items-center gap-1 shadow-sm animate-pulse">
                            <span>✓</span> <span>Importado</span>
                        </div>
                    @endif

                    <!-- Topo do Card -->
                    <div class="space-y-1">
                        <h4 class="font-bold text-slate-900 text-sm line-clamp-1" title="{{ $proj->title }}">{{ $proj->title }}</h4>
                        <p class="text-xs text-slate-400">Cliente: <strong class="text-slate-700 font-semibold">{{ $proj->client->name }}</strong></p>
                    </div>

                    <!-- Corpo / Dados -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-500">
                        <div class="flex justify-between">
                            <span class="text-slate-450 font-medium">Valor Gerado:</span>
                            <span class="text-slate-850 font-black">R$ {{ number_format($proj->total_value, 2, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-450 font-medium">Conclusão:</span>
                            <span class="text-slate-800 font-semibold">{{ $proj->updated_at->format('d/m/Y') }}</span>
                        </div>

                        <!-- Autores -->
                        @if($proj->authors->count() > 0)
                            <div class="pt-2">
                                <span class="text-[10px] text-slate-400 font-bold uppercase block mb-1">Equipe/Autores:</span>
                                <div class="flex flex-wrap gap-1">
                                    @foreach($proj->authors as $author)
                                        <span class="bg-slate-50 text-slate-650 text-[9px] font-bold px-1.5 py-0.5 rounded-[4px] border border-slate-150">{{ $author->name }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Rodapé / Ações -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                        @if($isAlreadyImported)
                            <button disabled 
                                    class="w-full py-2 px-3 bg-slate-100 text-slate-400 text-xs font-bold rounded-[5px] border border-slate-200 cursor-not-allowed text-center">
                                Já Importado para Portfólio
                            </button>
                        @else
                            <a href="{{ route('portfolio.create', ['project_id' => $proj->id]) }}" 
                               class="w-full py-2 px-3 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm text-center flex items-center justify-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-slate-350" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                </svg>
                                Importar para Portfólio
                            </a>
                        @endif
                    </div>

                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] text-sm italic">
                    Nenhum orçamento finalizado disponível para importar no momento.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
