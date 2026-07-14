@extends('layouts.app')

@section('title', 'Notificações - Gestor de Freelas')
@section('page_title', 'Notificações')

@section('content')
<div id="pjax-container" class="space-y-6" x-data="{ showClearModal: false }">

    <!-- Topo da página: Título e Ações -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Histórico de Notificações</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Veja todas as atualizações de propostas, compartilhamentos de arquivos, financeiros e lembretes.</p>
        </div>
        
        <div class="flex items-center gap-2 w-full md:w-auto">
            @if($notifications->count() > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" class="flex-1 md:flex-initial">
                    @csrf
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-[10px] sm:text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider cursor-pointer">
                        ✔️ Marcar Lidas
                    </button>
                </form>

                <button type="button" @click="showClearModal = true" class="flex-1 md:flex-initial w-full inline-flex items-center justify-center gap-1.5 px-3 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 text-[10px] sm:text-xs font-bold rounded-[5px] transition-colors border border-red-200 shadow-sm uppercase tracking-wider cursor-pointer">
                    🗑️ Limpar Histórico
                </button>
            @endif
        </div>
    </div>

    <!-- Lista de Notificações -->
    <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden">
        @if($notifications->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($notifications as $n)
                    @php
                        $isUnread = is_null($n->read_at);
                        
                        // Badge colors
                        $badgeClasses = 'bg-slate-100 text-slate-700 border-slate-200';
                        $badgeText = 'Notificação';
                        if ($n->type === 'proposal') {
                            $badgeClasses = 'bg-blue-50 text-blue-800 border-blue-150';
                            $badgeText = '💼 Orçamento';
                        } elseif ($n->type === 'share') {
                            $badgeClasses = 'bg-purple-50 text-purple-800 border-purple-150';
                            $badgeText = '📂 Compartilhamento';
                        } elseif ($n->type === 'bill') {
                            $badgeClasses = 'bg-amber-50 text-amber-800 border-amber-150';
                            $badgeText = '💸 Financeiro';
                        } elseif ($n->type === 'reminder') {
                            $badgeClasses = 'bg-emerald-50 text-emerald-800 border-emerald-150';
                            $badgeText = '⏰ Lembrete';
                        }
                    @endphp
                    <div class="p-3 sm:p-5 flex items-start justify-between gap-3 sm:gap-4 transition-colors {{ $isUnread ? 'bg-slate-50/50' : 'bg-white' }}">
                        <div class="flex items-start gap-2 sm:gap-3.5 min-w-0 flex-1">
                            <!-- Indicador de Não Lido -->
                            @if($isUnread)
                                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 rounded-full bg-primary-500 shrink-0 mt-1.5" title="Não lida"></span>
                            @else
                                <span class="w-2 h-2 sm:w-2.5 sm:h-2.5 shrink-0 mt-1.5 opacity-0"></span>
                            @endif

                            <div class="space-y-1 min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <span class="px-1.5 py-0.5 border text-[8px] sm:text-[9px] font-black uppercase tracking-wider rounded-[3px] {{ $badgeClasses }}">
                                        {{ $badgeText }}
                                    </span>
                                    <span class="text-[9px] sm:text-[10px] font-medium text-slate-400 shrink-0">
                                        {{ $n->created_at->format('d/m/Y H:i') }} ({{ $n->created_at->diffForHumans() }})
                                    </span>
                                </div>
                                <h3 class="text-xs sm:text-sm font-black text-slate-800 leading-tight break-words">
                                    {{ $n->title }}
                                </h3>
                                <p class="text-[11px] sm:text-xs text-slate-500 leading-relaxed max-w-2xl break-words">
                                    {{ $n->content }}
                                </p>
                            </div>
                        </div>

                        <!-- Botão Deletar -->
                        <form action="{{ route('notifications.destroy', $n->id) }}" method="POST" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-600 transition-colors p-1 rounded-[5px] hover:bg-slate-50 cursor-pointer" title="Excluir notificação">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>

            <!-- Paginação -->
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
                {{ $notifications->links() }}
            </div>
        @else
            <!-- Estado Vazio -->
            <div class="p-12 text-center text-slate-400 space-y-3">
                <div class="text-4xl">🔔</div>
                <p class="text-sm font-semibold">Tudo limpo por aqui!</p>
                <p class="text-xs text-slate-400">Nenhuma notificação registrada no seu histórico no momento.</p>
            </div>
        @endif
    </div>

    <!-- Modal de Confirmação para Limpeza do Histórico -->
    <div x-show="showClearModal" 
         class="fixed inset-0 z-[999999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         style="display: none;">
        
        <div @click.away="showClearModal = false" 
             class="bg-white border border-slate-200 rounded-[5px] max-w-sm w-full p-6 shadow-2xl space-y-4 transform transition-all"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-red-50 text-red-600 flex items-center justify-center text-lg shrink-0">
                    ⚠️
                </div>
                <div>
                    <h3 class="text-sm font-black text-slate-800">Limpar Histórico?</h3>
                    <p class="text-[10px] text-slate-400 font-medium">Esta ação é permanente.</p>
                </div>
            </div>

            <p class="text-xs text-slate-500 leading-relaxed">
                Você tem certeza de que deseja apagar permanentemente todas as notificações registradas no seu histórico?
            </p>

            <div class="flex items-center justify-end gap-2 pt-2">
                <button type="button" 
                        @click="showClearModal = false" 
                        class="px-4 py-2 border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-[5px] transition-colors uppercase tracking-wider cursor-pointer">
                    Cancelar
                </button>
                <form action="{{ route('notifications.destroy-all') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-650 hover:bg-red-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider cursor-pointer">
                        Sim, Limpar tudo
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
