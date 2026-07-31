@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    filterStatus: 'all', 
    searchQuery: '',
    toastMessage: '',
    copyLink(url, msg = 'Link copiado com sucesso!') {
        navigator.clipboard.writeText(url);
        this.toastMessage = msg;
        setTimeout(() => { this.toastMessage = ''; }, 3000);
    }
}">

    <!-- Toast Notification Banner -->
    <template x-teleport="body">
        <div x-show="toastMessage" 
             x-cloak 
             x-transition
             class="fixed bottom-10 right-6 z-[999999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700 select-none">
            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span x-text="toastMessage"></span>
            <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
        </div>
    </template>

    <!-- Banner Superior / Título e Ação Principal -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-[5px] shadow-sm">
        <div class="space-y-1 min-w-0">
            <div class="flex items-center gap-2 min-w-0">
                <span class="text-2xl shrink-0">✍️</span>
                <h2 class="text-xl font-black font-outfit text-slate-800 dark:text-slate-100 uppercase tracking-tight truncate">Revisão Editorial</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Gerencie os textos originais, atribua revisores profissionais e responda dúvidas de autores antes da diagramação.
            </p>
        </div>

        <div class="flex items-center gap-3 flex-wrap shrink-0">
            <a href="{{ route('revisoes-editoriais.revisores.index') }}" class="px-4 py-2.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer uppercase tracking-wider">
                <span>👥</span> Gerenciar Revisores
            </a>
            <a href="{{ route('revisoes-editoriais.create') }}" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-all shadow-md flex items-center gap-2 cursor-pointer uppercase tracking-wider">
                <span>➕</span> Nova Revisão Editorial
            </a>
        </div>
    </div>

    <!-- Cards de Métricas de Status -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Total de Projetos -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-100 uppercase tracking-wider">Total de Projetos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">{{ $totalCount }}</h3>
                <span class="text-[10px] text-blue-100/90 font-medium block mt-1">Revisões editoriais criadas</span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center">
                <span class="text-2xl">📚</span>
            </div>
        </div>

        <!-- Aguardando Revisor -->
        <div class="bg-amber-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-100 uppercase tracking-wider">Aguardando Revisor</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">{{ $pendingCount }}</h3>
                <span class="text-[10px] text-amber-100/90 font-medium block mt-1">Aguardando análise</span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center">
                <span class="text-2xl">⏳</span>
            </div>
        </div>

        <!-- Em Revisão -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-purple-100 uppercase tracking-wider">Em Revisão</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">{{ $inProgressCount }}</h3>
                <span class="text-[10px] text-purple-100/90 font-medium block mt-1">Ativamente sendo revisados</span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center">
                <span class="text-2xl">🔍</span>
            </div>
        </div>

        <!-- Concluídos -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Concluídos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">{{ $completedCount }}</h3>
                <span class="text-[10px] text-emerald-100/90 font-medium block mt-1">Prontos para diagramação</span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center">
                <span class="text-2xl">✅</span>
            </div>
        </div>
    </div>

    <!-- Barra de Filtros e Busca -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px] shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Campo de Busca -->
        <div class="w-full md:w-80 relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                🔍
            </span>
            <input type="text" 
                   x-model="searchQuery"
                   placeholder="Buscar por título ou descrição..." 
                   class="w-full pl-9 pr-4 py-2 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-[5px] text-xs font-medium focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all text-slate-800 dark:text-slate-100">
        </div>

        <!-- Filtros por Status -->
        <div class="flex items-center gap-2 flex-wrap">
            <button type="button" 
                    @click="filterStatus = 'all'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider"
                    :class="filterStatus === 'all' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-slate-100 border-slate-200 text-slate-600 hover:bg-slate-200'">
                Todos
            </button>
            <button type="button" 
                    @click="filterStatus = 'aguardando_revisor'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider"
                    :class="filterStatus === 'aguardando_revisor' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100'">
                Aguardando
            </button>
            <button type="button" 
                    @click="filterStatus = 'em_revisao'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider"
                    :class="filterStatus === 'em_revisao' ? 'bg-purple-600 border-purple-600 text-white' : 'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100'">
                Em Revisão
            </button>
            <button type="button" 
                    @click="filterStatus = 'concluido'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider"
                    :class="filterStatus === 'concluido' ? 'bg-emerald-600 border-emerald-600 text-white' : 'bg-emerald-50 border-emerald-200 text-emerald-700 hover:bg-emerald-100'">
                Concluídos
            </button>
        </div>
    </div>

    <!-- Grid de Projetos de Revisão Editorial -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($revisions as $rev)
            @php
                $statusMap = [
                    'aguardando_revisor' => ['label' => 'Aguardando Revisor', 'bg' => 'bg-amber-100 text-amber-800'],
                    'em_revisao' => ['label' => 'Em Revisão', 'bg' => 'bg-purple-100 text-purple-800'],
                    'aguardando_autor' => ['label' => 'Aguardando Autor', 'bg' => 'bg-blue-100 text-blue-800'],
                    'concluido' => ['label' => 'Revisão Concluída', 'bg' => 'bg-emerald-100 text-emerald-800'],
                ];
                $stInfo = $statusMap[$rev->status] ?? ['label' => ucfirst($rev->status), 'bg' => 'bg-slate-100 text-slate-800'];
                $isGoogleDrive = ($rev->storage_disk === 'google');
            @endphp

            <div x-show="(filterStatus === 'all' || filterStatus === '{{ $rev->status }}') && ('{{ addslashes($rev->title) }}'.toLowerCase().includes(searchQuery.toLowerCase()))"
                 class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-all flex flex-col justify-between space-y-4"
                 x-transition>

                <!-- Cabeçalho do Card: Status & Google Drive Badge -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[9px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-[5px] {{ $stInfo['bg'] }}">
                            {{ $stInfo['label'] }}
                        </span>

                        @if($isGoogleDrive)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] bg-white border border-slate-200 text-slate-700 text-[10px] font-extrabold shadow-2xs" title="Google Drive (5 TB)">
                                <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 87.3 78" xmlns="http://www.w3.org/2000/svg">
                                    <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                                    <path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44c-.8 1.4-1.2 2.95-1.2 4.5h27.5z" fill="#00ac47"/>
                                    <path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.2-2.1 8.05-13.9c.8-1.4 1.2-2.95 1.2-4.5h-27.5l6 10.4z" fill="#ea4335"/>
                                    <path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.45-1.2h-18.6c-1.55 0-3.1.4-4.45 1.2z" fill="#00832d"/>
                                    <path d="m59.8 53h27.5c0-1.55-.4-3.1-1.2-4.5l-13.75-23.8c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8z" fill="#ffba00"/>
                                    <path d="m73.55 76.8c1.35-.8 2.5-1.95 3.3-3.3l3.85-6.65c.8-1.4 1.2-2.95 1.2-4.5h-27.5l13.75 23.8z" fill="#2684fc"/>
                                </svg>
                                <span>Google Drive</span>
                            </span>
                        @endif
                    </div>

                    @if($rev->deadline_at)
                        <span class="text-[10px] font-bold text-slate-400">
                            ⏱️ {{ $rev->deadline_at->format('d/m/Y') }}
                        </span>
                    @endif
                </div>

                <!-- Título e Detalhes do Card -->
                <div class="space-y-2">
                    <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md leading-snug truncate" title="{{ $rev->title }}">
                        {{ $rev->title }}
                    </h4>
                    
                    @if($rev->description)
                        <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed font-medium">
                            {{ $rev->description }}
                        </p>
                    @endif

                    <div class="bg-slate-50 dark:bg-slate-800/50 p-3 rounded-[5px] border border-slate-100 dark:border-slate-800 text-xs space-y-1.5">
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300">
                            <span class="text-slate-400 font-bold uppercase text-[10px]">Revisor Atribuído:</span>
                            <span class="font-extrabold text-slate-800 dark:text-slate-200">
                                {{ $rev->revisor ? $rev->revisor->name : 'Nenhum Atribuído' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between text-slate-600 dark:text-slate-300 border-t border-slate-200/50 pt-1.5">
                            <span class="text-slate-400 font-bold uppercase text-[10px]">Arquivos:</span>
                            <span class="font-extrabold text-slate-800 dark:text-slate-200">
                                📂 {{ $rev->files->count() }} {{ $rev->files->count() == 1 ? 'arquivo' : 'arquivos' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Ações e Rodapé -->
                <div class="pt-3 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-1.5">
                    <a href="{{ route('revisoes-editoriais.show', $rev->id) }}" class="flex-1 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors text-center shadow-xs flex items-center justify-center gap-1">
                        <span>🔍 Abrir</span>
                    </a>

                    <!-- Copiar Link do Revisor -->
                    <button type="button" 
                            @click="copyLink('{{ route('public.editorial.revisor.show', $rev->share_token) }}', 'Link do Revisor copiado!')" 
                            class="w-8 h-8 flex items-center justify-center bg-purple-50 hover:bg-purple-100 text-purple-700 dark:bg-purple-950/40 dark:hover:bg-purple-900/60 dark:text-purple-300 rounded-[5px] transition-colors shrink-0 cursor-pointer" 
                            title="Copiar Link do Revisor">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                        </svg>
                    </button>

                    <!-- Copiar Link do Autor -->
                    <button type="button" 
                            @click="copyLink('{{ route('public.editorial.show', $rev->share_token) }}', 'Link do Autor copiado!')" 
                            class="w-8 h-8 flex items-center justify-center bg-blue-50 hover:bg-blue-100 text-blue-700 dark:bg-blue-950/40 dark:hover:bg-blue-900/60 dark:text-blue-300 rounded-[5px] transition-colors shrink-0 cursor-pointer" 
                            title="Copiar Link do Autor (Esclarecer Dúvidas)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                        </svg>
                    </button>

                    <!-- Excluir -->
                    <form action="{{ route('revisoes-editoriais.destroy', $rev->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este projeto de Revisão Editorial?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-8 h-8 flex items-center justify-center text-rose-600 hover:bg-rose-50 rounded-[5px] transition-colors cursor-pointer" title="Excluir Projeto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>

            </div>
        @empty
            <div class="col-span-full bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 rounded-[5px] p-12 text-center text-slate-400 font-semibold text-sm">
                Nenhum projeto de Revisão Editorial cadastrado ainda.
            </div>
        @endforelse
    </div>

</div>
@endsection
