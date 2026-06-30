@extends('layouts.app')

@section('title', 'Gerenciar Arquivos - Rodada #' . $round->round_number . ' - Gestor de Freelas')
@section('page_title', 'Gerenciador de Arquivos')

@section('content')
<div x-data="fileManager()" class="space-y-8">
    
    <!-- Link de Retorno -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes.show', $round->projectRevision->id) }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 uppercase tracking-wider flex items-center gap-1.5 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Voltar para Linha do Tempo
        </a>
    </div>

    <!-- Header Card -->
    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm">
        <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest block">Projeto: {{ $round->projectRevision->title }}</span>
        <h3 class="font-outfit font-black text-slate-800 text-lg leading-tight mt-1">
            Gerenciamento de Arquivos - Rodada #{{ $round->round_number }}
        </h3>
    </div>

    <!-- Upload & File Tree Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Enviar Arquivos Card (1/3) -->
        <div class="space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Upload de Arquivos</h4>
            
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <form action="{{ route('revisoes.rounds.upload', $round->id) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <!-- Pasta Virtual -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Pasta de Destino (Opcional)</label>
                        <input type="text" 
                               name="folder_name" 
                               placeholder="Ex: Ilustrações, Páginas Internas, Capa"
                               class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                        <p class="text-[9px] text-slate-400">Deixe em branco para salvar na pasta principal (Raiz).</p>
                    </div>

                    <!-- Input Files -->
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Arquivos a Revisar</label>
                        <input type="file" 
                               name="files[]" 
                               multiple
                               required
                               class="w-full text-xs text-slate-500 border border-slate-200 rounded-[5px] p-2 bg-slate-50 focus:outline-none file:mr-4 file:py-1 file:px-3 file:rounded-[3px] file:border-0 file:text-[10px] file:font-bold file:uppercase file:bg-slate-900 file:text-white hover:file:bg-slate-800 file:cursor-pointer cursor-pointer">
                        <p class="text-[9px] text-slate-400">Selecione um ou mais arquivos (PDF ou Imagens são recomendados).</p>
                    </div>

                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider py-3 rounded-[5px] transition-all shadow-sm shadow-blue-500/10">
                        Enviar Arquivos
                    </button>
                </form>
            </div>
        </div>

        <!-- Árvore de Pastas e Arquivos (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <h4 class="font-outfit font-black text-slate-800 text-lg uppercase tracking-tight">Arquivos Cadastrados nesta Rodada</h4>

            @if($round->files->isEmpty())
                <div class="border border-dashed border-slate-200 p-12 text-center text-slate-400 rounded-[5px] bg-white text-sm">
                    Nenhum arquivo enviado para esta rodada de revisão.
                </div>
            @else
                <div class="space-y-6">
                    @php
                        // Group files by folder_name
                        $groupedFiles = $round->files->groupBy(function($item) {
                            return $item->folder_name ?: 'Diretório Raiz';
                        });
                    @endphp

                    @foreach($groupedFiles as $folder => $files)
                        <!-- Card da Pasta Virtual -->
                        <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden" x-data="{ open: true }">
                            
                            <!-- Cabecalho da Pasta -->
                            <div @click="open = !open" class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between cursor-pointer hover:bg-slate-100/70 transition-colors">
                                <div class="flex items-center gap-2">
                                    <span class="text-lg">📁</span>
                                    <span class="font-bold text-sm text-slate-800 uppercase tracking-wide">{{ $folder }}</span>
                                    <span class="text-[10px] text-slate-400 font-bold bg-slate-200 px-2 py-0.5 rounded-full">{{ $files->count() }} arquivo(s)</span>
                                </div>
                                <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>

                            <!-- Listagem de Arquivos da Pasta -->
                            <div x-show="open" class="divide-y divide-slate-100" x-cloak>
                                @foreach($files as $file)
                                    <div class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 transition-colors">
                                        
                                        <!-- Detalhes do Arquivo -->
                                        <div class="min-w-0 flex items-center gap-3">
                                            @php
                                                $icon = '📎';
                                                if(in_array($file->file_type, ['jpg', 'jpeg', 'png', 'gif', 'svg'])) $icon = '🖼️';
                                                elseif($file->file_type === 'pdf') $icon = '📄';
                                                elseif(in_array($file->file_type, ['doc', 'docx', 'txt', 'rtf'])) $icon = '📝';
                                            @endphp
                                            <span class="text-xl shrink-0">{{ $icon }}</span>
                                            <div class="min-w-0">
                                                <a href="{{ Storage::url($file->file_path) }}" 
                                                   target="_blank" 
                                                   class="text-sm font-semibold text-slate-700 hover:text-blue-600 hover:underline block truncate" 
                                                   title="Abrir arquivo no navegador">
                                                    {{ $file->filename }}
                                                </a>
                                                <span class="text-[10px] text-slate-400 font-medium block mt-0.5 uppercase">
                                                    {{ strtoupper($file->file_type) }} • {{ number_format($file->file_size / 1024, 1) }} KB
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Ações e Contadores -->
                                        <div class="flex items-center gap-3 shrink-0">
                                            
                                            <!-- Ajustes Contador Badge -->
                                            @if($file->annotations->count() > 0)
                                                <span class="text-[10px] font-extrabold uppercase px-2.5 py-1 rounded-[5px] bg-rose-50 text-rose-600 border border-rose-200">
                                                    {{ $file->annotations->where('status', 'aberto')->count() }} Ajustes
                                                </span>
                                            @endif

                                            <!-- Deletar Arquivo -->
                                            <form action="{{ route('revisoes.files.destroy', $file->id) }}" method="POST" onsubmit="return confirm('Deseja realmente excluir este arquivo? Esta ação apagará todas as anotações do cliente feitas nele.')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="border border-slate-200 text-rose-500 hover:bg-rose-50 p-2 rounded-[5px] transition-all" title="Excluir Arquivo">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            @endif

        </div>

    </div>

</div>

<script>
    function fileManager() {
        return {
            // file manager state helper
        }
    }
</script>
@endsection
