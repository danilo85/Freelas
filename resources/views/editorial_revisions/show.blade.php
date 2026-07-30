@extends('layouts.app')

@section('title', 'Revisão Editorial - ' . $editorialRevision->title . ' - Gestor de Freelas')
@section('page_title', 'Revisão Editorial')

@section('content')
<style>
    @keyframes pulse-glow-purple {
        0%, 100% { box-shadow: 0 0 5px rgba(147, 51, 234, 0.15), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(147, 51, 234, 0.35); }
        50% { box-shadow: 0 0 15px rgba(147, 51, 234, 0.5), 0 1px 2px 0 rgba(0, 0, 0, 0.05); border-color: rgba(147, 51, 234, 0.65); }
    }
    .pulse-glow-purple {
        animation: pulse-glow-purple 2s infinite ease-in-out;
    }
</style>

<div x-data="editorialShowWorkspace()" class="space-y-8">
    
    <!-- Link de Retorno -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes-editoriais.index') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 uppercase tracking-wider flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Listagem de Revisões Editoriais
        </a>
    </div>

    <!-- Toast Notification (Sem alerts nativos) -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition
         class="fixed bottom-24 right-6 z-[99999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- Ficha Técnica & Links do Projeto -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-6">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-6 border-b border-slate-100 dark:border-slate-800">
            <div>
                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto de Revisão Editorial</span>
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-xl leading-tight mt-1">
                    {{ $editorialRevision->title }}
                </h3>
                @if($editorialRevision->description)
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">{{ $editorialRevision->description }}</p>
                @endif
            </div>

            <!-- Ficha Revisor -->
            <div class="flex items-center gap-3 bg-slate-50 dark:bg-slate-800/60 px-4 py-2.5 rounded-[5px] border border-slate-150 dark:border-slate-700 shrink-0 self-start md:self-center">
                <div class="w-9 h-9 rounded-full bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 font-bold flex items-center justify-center text-sm border border-purple-200 dark:border-purple-800">
                    ✍️
                </div>
                <div>
                    <span class="text-[8px] font-bold text-slate-400 uppercase tracking-wider block">Revisor Atribuído</span>
                    <p class="text-xs font-bold text-slate-700 dark:text-slate-200 leading-tight mt-0.5">
                        {{ $editorialRevision->revisor ? $editorialRevision->revisor->name : 'Nenhum Revisor' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Links Públicos do Projeto (Link do Revisor vs Link do Autor) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Link do Revisor -->
            <div class="space-y-2 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-[5px] border border-slate-200 dark:border-slate-700">
                <label class="text-[10px] font-black text-purple-700 dark:text-purple-400 uppercase tracking-wider block">🔗 Link do Workspace do Revisor</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('public.editorial.revisor.show', $editorialRevision->share_token) }}" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs px-3 py-2 rounded-[5px] select-all font-mono">
                    <a href="{{ route('public.editorial.revisor.show', $editorialRevision->share_token) }}" target="_blank" class="px-3 py-2 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-[5px] uppercase tracking-wider">
                        Abrir
                    </a>
                </div>
            </div>

            <!-- Link do Autor -->
            <div class="space-y-2 p-4 bg-slate-50 dark:bg-slate-800/40 rounded-[5px] border border-slate-200 dark:border-slate-700">
                <label class="text-[10px] font-black text-slate-700 dark:text-slate-300 uppercase tracking-wider block">🔗 Link do Autor (Esclarecer Dúvidas)</label>
                <div class="flex items-center gap-2">
                    <input type="text" readonly value="{{ route('public.editorial.show', $editorialRevision->share_token) }}" class="flex-1 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-300 text-xs px-3 py-2 rounded-[5px] select-all font-mono">
                    <button type="button" @click="copyShareLink('{{ route('public.editorial.show', $editorialRevision->share_token) }}')" class="px-3 py-2 bg-slate-900 text-white font-bold text-xs rounded-[5px] uppercase tracking-wider">
                        Copiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Grid: Arquivos (2/3) vs Apontamentos e Métricas (1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Coluna Esquerda: Lista de Arquivos do Projeto (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <div class="flex items-center justify-between">
                <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-lg uppercase tracking-tight">Arquivos do Material</h4>
                <span class="px-3 py-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-full">
                    {{ $editorialRevision->files->count() }} Arquivos Cadastrados
                </span>
            </div>

            <div class="space-y-3">
                @forelse($editorialRevision->files as $file)
                    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px] shadow-xs flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <span class="text-2xl">
                                @if($file->file_type === 'pdf') 📄 @elseif($file->file_type === 'word') 📝 @else 🖼️ @endif
                            </span>
                            <div>
                                <h5 class="font-bold text-xs text-slate-800 dark:text-slate-200">{{ $file->filename }}</h5>
                                <p class="text-[10px] text-slate-400 font-medium">Versão {{ $file->version }} • {{ strtoupper($file->file_type) }} • {{ number_format($file->file_size / 1024, 1) }} KB</p>
                            </div>
                        </div>

                        <a href="{{ route('public.editorial.file.download', ['token' => $editorialRevision->share_token, 'fileId' => $file->id]) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-[5px] transition-colors">
                            ⬇️ Baixar
                        </a>
                    </div>
                @empty
                    <div class="p-8 border border-dashed border-slate-200 dark:border-slate-800 text-center text-slate-400 rounded-[5px] text-xs font-bold">
                        Nenhum arquivo associado a este projeto de revisão.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Coluna Direita: Métricas e Apontamentos (1/3) -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 rounded-[5px] shadow-xs space-y-4">
                <h4 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight">Resumo de Apontamentos</h4>
                
                <div class="grid grid-cols-2 gap-3 text-center text-xs font-bold">
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-rose-700 dark:text-rose-400 block">{{ $editorialRevision->corrections->where('category', 'ortografia')->count() }}</span>
                        <span class="text-[10px] text-rose-600 dark:text-rose-300 uppercase">Ortografia</span>
                    </div>
                    <div class="p-3 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-amber-700 dark:text-amber-400 block">{{ $editorialRevision->corrections->where('category', 'gramatica')->count() }}</span>
                        <span class="text-[10px] text-amber-600 dark:text-amber-300 uppercase">Gramática</span>
                    </div>
                    <div class="p-3 bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-blue-700 dark:text-blue-400 block">{{ $editorialRevision->corrections->where('category', 'duvida')->count() }}</span>
                        <span class="text-[10px] text-blue-600 dark:text-blue-300 uppercase">Dúvidas</span>
                    </div>
                    <div class="p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-900/60 rounded-[5px]">
                        <span class="text-lg font-black text-purple-700 dark:text-purple-400 block">{{ $editorialRevision->corrections->where('category', 'padronizacao')->count() }}</span>
                        <span class="text-[10px] text-purple-600 dark:text-purple-300 uppercase">Padronização</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    function editorialShowWorkspace() {
        return {
            toastMessage: '',
            copyShareLink(url) {
                navigator.clipboard.writeText(url);
                this.toastMessage = 'Link copiado com sucesso!';
                setTimeout(() => { this.toastMessage = ''; }, 4000);
            }
        }
    }
</script>
@endsection
