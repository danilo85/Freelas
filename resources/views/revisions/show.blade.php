@extends('layouts.app')

@section('title', 'Linha do Tempo - ' . $revision->title . ' - Gestor de Freelas')
@section('page_title', 'Linha do Tempo de Revisão')

@section('content')
<style>
    @keyframes pulse-glow-rose {
        0%, 100% { box-shadow: 0 0 5px rgba(244, 63, 94, 0.15), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(244, 63, 94, 0.35); }
        50% { box-shadow: 0 0 15px rgba(244, 63, 94, 0.5), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(244, 63, 94, 0.65); }
    }
    .pulse-glow-rose {
        animation: pulse-glow-rose 2s infinite ease-in-out;
    }
</style>
<div x-data="timelineManager()" class="space-y-8">
    
    <!-- Link de Retorno -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase tracking-wider flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Listagem
        </a>
    </div>

    <!-- Ficha Técnica & Link de Compartilhamento -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto de Provas</span>
                <h3 class="font-outfit font-black text-slate-800 text-xl leading-tight mt-1">
                    {{ $revision->title }}
                </h3>
                @if($revision->subtitle)
                    <p class="text-sm text-slate-500 mt-1">{{ $revision->subtitle }}</p>
                @endif
            </div>

            <!-- Ficha Autor -->
            <div class="flex items-center gap-3 bg-slate-50 px-4 py-2.5 rounded-[5px] border border-slate-150 shrink-0 self-start md:self-center">
                <div class="w-9 h-9 rounded-full bg-slate-200 border border-slate-300 text-slate-600 font-bold flex items-center justify-center text-sm overflow-hidden">
                    @if($revision->author->avatar)
                        <img src="{{ asset('storage/' . $revision->author->avatar) }}" class="w-full h-full object-cover">
                    @else
                        {{ collect(explode(' ', $revision->author->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                    @endif
                </div>
                <div>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">Autor Creditado</span>
                    <p class="text-xs font-bold text-slate-700 leading-tight mt-0.5">{{ $revision->author->name }}</p>
                </div>
            </div>
        </div>

        <!-- Link Público para o Cliente -->
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Link de Revisão Pública do Cliente</label>
            <div class="flex flex-col sm:flex-row gap-2">
                <input type="text" 
                       id="publicLinkInput"
                       readonly
                       value="{{ route('public.revisao.show', $revision->share_token) }}"
                       class="flex-1 bg-slate-50 border border-slate-200 text-slate-500 text-xs px-4 py-2.5 rounded-[5px] focus:outline-none select-all">
                
                <div class="flex gap-2">
                    <!-- Botão Copiar Link -->
                    <button @click="copyLink()"
                            class="bg-slate-900 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] hover:bg-slate-800 transition-all shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 00-2 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                        </svg>
                        <span x-text="copiedText">Copiar Link</span>
                    </button>

                    <!-- Abrir Link -->
                    <a href="{{ route('public.revisao.show', $revision->share_token) }}" 
                       target="_blank"
                       class="border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] transition-all flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        Abrir Prova
                    </a>
                </div>
            </div>
            <p class="text-[10px] text-slate-400 italic">Compartilhe o link acima com o seu cliente para que ele faça anotações, desenhos e aprovações diretas nos arquivos.</p>
        </div>
    </div>

    <!-- Timeline Vertical & Botão de Nova Rodada -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Timeline Column (2/3 de largura) -->
        <div class="lg:col-span-2 space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Histórico de Rodadas</h4>
            
            @if($revision->rounds->isEmpty())
                <div class="border border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] bg-white text-sm">
                    Nenhuma rodada de ajustes registrada.
                </div>
            @else
                <div class="relative pl-6 border-l-2 border-slate-200 space-y-8 py-2">
                    @foreach($revision->rounds as $round)
                        @php
                            $pendingCount = 0;
                            $resolvedCount = 0;
                            foreach ($round->files as $file) {
                                $pendingCount += $file->annotations->where('status', 'aberto')->count();
                                $resolvedCount += $file->annotations->where('status', 'resolvido')->count();
                            }
                        @endphp
                        
                        <!-- Timeline Node Item -->
                        <div class="relative">
                            <!-- Timeline Dot Icon -->
                            <span class="absolute -left-[33px] top-1.5 w-4 h-4 rounded-full border-2 border-white flex items-center justify-center shadow-sm 
                                {{ $round->status === 'aprovado' ? 'bg-emerald-500' : ($round->status === 'em_ajuste' ? 'bg-amber-400' : 'bg-blue-500') }}">
                            </span>

                            @php
                                $isRoundAllOk = ($resolvedCount > 0 && $pendingCount === 0);
                                $isRoundApproved = ($round->status === 'aprovado');
                                $roundHasAdjustments = ($pendingCount > 0);

                                $roundCardClass = 'bg-white border-slate-200';
                                if ($isRoundAllOk) {
                                    $roundCardClass = 'bg-white border-slate-200';
                                } elseif ($roundHasAdjustments) {
                                    $roundCardClass = 'bg-rose-50/25 border-rose-200 pulse-glow-rose';
                                } elseif ($isRoundApproved) {
                                    $roundCardClass = 'bg-emerald-50/25 border-emerald-200';
                                }
                            @endphp

                            <!-- Round Card -->
                            <div class="{{ $roundCardClass }} rounded-[5px] p-6 shadow-sm space-y-4 hover:shadow-md transition-shadow relative overflow-hidden">
                                <!-- Stamp Carimbo Tudo Ok -->
                                @if($isRoundAllOk)
                                    <div class="absolute right-4 top-[55%] -translate-y-1/2 -rotate-12 pointer-events-none select-none z-10 opacity-30 transform scale-110">
                                        <div class="border-4 border-emerald-600/75 text-emerald-600/75 font-black text-lg px-3 py-1.5 rounded uppercase tracking-widest flex items-center gap-1">
                                            <span>✓</span> <span>TUDO OK</span>
                                        </div>
                                    </div>
                                @endif
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-100">
                                    <div>
                                        <h5 class="font-outfit font-black text-slate-800 text-md">
                                            Rodada de Ajustes #{{ $round->round_number }}
                                        </h5>
                                        <span class="text-[10px] text-slate-400 font-medium">
                                            Enviado em: {{ $round->created_at->format('d/m/Y \à\s H:i') }}
                                        </span>
                                    </div>

                                    <!-- Status Badge dropdown/form -->
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('revisoes.rounds.status', $round->id) }}" method="POST" class="flex items-center gap-1.5 bg-slate-50 border border-slate-200 px-2.5 py-1 rounded-[5px]">
                                            @csrf
                                            @method('PATCH')
                                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wide">Status:</span>
                                            <select name="status" 
                                                    onchange="this.form.submit()"
                                                    class="bg-transparent border-none text-[10px] font-extrabold uppercase focus:outline-none py-0.5 cursor-pointer 
                                                        {{ $round->status === 'aprovado' ? 'text-emerald-600' : ($round->status === 'em_ajuste' ? 'text-amber-600' : 'text-blue-600') }}">
                                                <option value="pendente" {{ $round->status === 'pendente' ? 'selected' : '' }}>Pendente</option>
                                                <option value="em_ajuste" {{ $round->status === 'em_ajuste' ? 'selected' : '' }}>Em Ajuste</option>
                                                <option value="aprovado" {{ $round->status === 'aprovado' ? 'selected' : '' }}>Aprovado</option>
                                            </select>
                                        </form>
                                    </div>
                                </div>

                                <!-- Descrição da Rodada -->
                                <div class="text-xs text-slate-600 leading-relaxed whitespace-pre-line bg-slate-50/50 p-3 rounded-[5px] border border-slate-100">
                                    <span class="text-[8px] font-bold text-slate-400 uppercase block mb-1">Notas / Instruções</span>
                                    {{ $round->description }}
                                </div>

                                <!-- Stats de Arquivos e Ajustes -->
                                <div class="flex flex-wrap items-center gap-3 text-xs">
                                    <div class="bg-slate-100 text-slate-700 px-2.5 py-1 rounded-[5px] font-semibold border border-slate-150 flex items-center gap-1">
                                        📁 <span>{{ $round->files->count() }} arquivo(s)</span>
                                    </div>
                                    <div class="bg-rose-50 text-rose-700 px-2.5 py-1 rounded-[5px] font-semibold border border-rose-100 flex items-center gap-1">
                                        ⚠️ <span>{{ $pendingCount }} ajustes abertos</span>
                                    </div>
                                    <div class="bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-[5px] font-semibold border border-emerald-100 flex items-center gap-1">
                                        ✅ <span>{{ $resolvedCount }} resolvidos</span>
                                    </div>
                                </div>

                                <!-- Ações da Rodada -->
                                <div class="flex flex-wrap items-center gap-2 pt-3 border-t border-slate-100">
                                    <!-- Gerenciar Arquivos -->
                                    <a href="{{ route('revisoes.rounds.files', $round->id) }}" 
                                       class="flex-1 sm:flex-none text-center bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] transition-all shadow-sm shadow-blue-500/10 flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V4a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                        </svg>
                                        Gerenciar Arquivos
                                    </a>

                                    <!-- Download ZIP unificado -->
                                    <a href="{{ route('public.revisao.download.all', $round->id) }}" 
                                       class="flex-1 sm:flex-none text-center border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider px-5 py-2.5 rounded-[5px] transition-all flex items-center justify-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                        Baixar ZIP
                                    </a>

                                    <!-- Excluir Rodada (se não for a primeira) -->
                                    @if($round->round_number > 1)
                                        <form action="{{ route('revisoes.rounds.destroy', $round->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir esta rodada de ajustes e todos os seus arquivos?')" class="inline ml-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="border border-slate-200 text-rose-600 hover:bg-rose-50 p-2.5 rounded-[5px] transition-all flex items-center gap-1" title="Excluir Rodada">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </div>

        <!-- Nova Rodada Card Column (1/3 de largura) -->
        <div class="space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Criar Nova Rodada</h4>
            
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Rodada de Ajustes Avançada</span>
                <p class="text-xs text-slate-500 leading-relaxed">Se o cliente solicitou novas alterações após o envio anterior, crie a próxima rodada de ajustes para separar cronologicamente as revisões.</p>
                
                <form action="{{ route('revisoes.rounds.store', $revision->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">O que precisa ser mudado?</label>
                        <textarea name="description" 
                                  required
                                  rows="4" 
                                  placeholder="Digite as instruções repassadas pelo cliente ou as anotações do feedback..."
                                  class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400"></textarea>
                    </div>

                    <button type="submit" class="w-full bg-slate-900 text-white font-bold text-xs uppercase tracking-wider py-3 rounded-[5px] hover:bg-slate-800 transition-all shadow-sm">
                        Criar Próxima Rodada
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

<script>
    function timelineManager() {
        return {
            copiedText: 'Copiar Link',

            copyLink() {
                const input = document.getElementById('publicLinkInput');
                input.select();
                input.setSelectionRange(0, 99999); // for mobile devices
                navigator.clipboard.writeText(input.value);

                this.copiedText = 'Copiado!';
                setTimeout(() => {
                    this.copiedText = 'Copiar Link';
                }, 2000);
            }
        }
    }
</script>
@endsection
