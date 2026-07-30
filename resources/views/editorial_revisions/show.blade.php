@extends('layouts.app')

@section('content')
<div class="space-y-6 relative" x-data="editorialShowWorkspace()">

    <!-- Banner Toast Notification (Sem alerts nativos do navegador) -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-24 right-6 z-[99999] bg-slate-900 text-white px-5 py-3.5 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span class="text-lg">✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- Botão Flutuante de Adicionar Novo Apontamento (Estilo Revisão de Autor) -->
    <div class="fixed bottom-6 right-6 z-40">
        <button type="button" 
                @click="openCorrectionModal = true" 
                class="bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs uppercase tracking-wider px-5 py-3.5 rounded-full shadow-xl hover:shadow-2xl transition-all duration-200 flex items-center gap-2.5 cursor-pointer transform hover:scale-105 border-2 border-white dark:border-slate-900">
            <span class="text-lg leading-none">➕</span>
            <span class="inline">Novo Apontamento</span>
        </button>
    </div>

    <!-- Cabeçalho do Workspace da Revisão Editorial -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-[5px] shadow-sm space-y-4">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="space-y-1">
                <div class="flex items-center gap-2">
                    <span class="text-2xl">✍️</span>
                    <h2 class="text-xl font-black font-outfit text-slate-800 dark:text-slate-100 uppercase tracking-tight">
                        {{ $editorialRevision->title }}
                    </h2>
                </div>
                <div class="flex items-center gap-3 text-xs text-slate-500 font-medium flex-wrap">
                    <span>Revisor: <strong class="text-slate-700 dark:text-slate-300">{{ $editorialRevision->revisor ? $editorialRevision->revisor->name : 'Nenhum Atribuído' }}</strong></span>
                    <span>•</span>
                    <span>Arquivos: <strong class="text-slate-700 dark:text-slate-300">{{ $editorialRevision->files->count() }}</strong></span>
                    <span>•</span>
                    <span>Prazo: <strong class="text-slate-700 dark:text-slate-300">{{ $editorialRevision->deadline_at ? $editorialRevision->deadline_at->format('d/m/Y') : 'Sem Prazo' }}</strong></span>
                </div>
            </div>

            <!-- Botões de Ação Principal -->
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('public.editorial.revisor.show', $editorialRevision->share_token) }}" 
                   target="_blank"
                   class="px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>🔍</span> Abrir Workspace do Revisor
                </a>

                <button type="button" 
                        @click="copyShareLink('{{ route('public.editorial.show', $editorialRevision->share_token) }}')" 
                        class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>🔗</span> Link do Autor
                </button>

                <button type="button" 
                        @click="openCorrectionModal = true" 
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer uppercase tracking-wider">
                    <span>➕</span> Novo Apontamento
                </button>
            </div>
        </div>

        @if($editorialRevision->description)
            <div class="p-3.5 bg-slate-50 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 rounded-[5px] text-xs text-slate-600 dark:text-slate-300 leading-relaxed font-medium">
                <strong>Orientações para o Revisor:</strong> {{ $editorialRevision->description }}
            </div>
        @endif

        <!-- Navegação por Abas -->
        <div class="flex items-center gap-2 border-t border-slate-100 dark:border-slate-800 pt-3">
            <button type="button" @click="activeTab = 'correcoes'" class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all cursor-pointer uppercase tracking-wider" :class="activeTab === 'correcoes' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                📝 Apontamentos e Correções ({{ $editorialRevision->corrections->count() }})
            </button>
            <button type="button" @click="activeTab = 'arquivos'" class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all cursor-pointer uppercase tracking-wider" :class="activeTab === 'arquivos' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                📁 Arquivos Originais ({{ $editorialRevision->files->count() }})
            </button>
            <button type="button" @click="activeTab = 'glossario'" class="px-4 py-2 rounded-[5px] text-xs font-bold transition-all cursor-pointer uppercase tracking-wider" :class="activeTab === 'glossario' ? 'bg-primary-600 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                📖 Glossário do Projeto ({{ $editorialRevision->glossaries->count() }})
            </button>
        </div>
    </div>

    <!-- Conteúdo da Aba 1: Apontamentos e Correções -->
    <div x-show="activeTab === 'correcoes'" class="space-y-4">
        <!-- Filtros por Categoria -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px] flex items-center gap-2 flex-wrap text-xs shadow-xs">
            <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider mr-1">Categorias:</span>
            <button type="button" @click="selectedCategory = 'todas'" class="px-3.5 py-1.5 rounded-full font-bold cursor-pointer transition-all border uppercase tracking-wider" :class="selectedCategory === 'todas' ? 'bg-slate-900 border-slate-900 text-white' : 'bg-slate-100 border-slate-200 text-slate-600 hover:bg-slate-200'">Todas</button>
            <button type="button" @click="selectedCategory = 'ortografia'" class="px-3.5 py-1.5 rounded-full font-bold cursor-pointer transition-all border uppercase tracking-wider" :class="selectedCategory === 'ortografia' ? 'bg-rose-600 border-rose-600 text-white' : 'bg-rose-50 border-rose-200 text-rose-700 hover:bg-rose-100'">Ortografia</button>
            <button type="button" @click="selectedCategory = 'gramatica'" class="px-3.5 py-1.5 rounded-full font-bold cursor-pointer transition-all border uppercase tracking-wider" :class="selectedCategory === 'gramatica' ? 'bg-amber-600 border-amber-600 text-white' : 'bg-amber-50 border-amber-200 text-amber-700 hover:bg-amber-100'">Gramática</button>
            <button type="button" @click="selectedCategory = 'duvida'" class="px-3.5 py-1.5 rounded-full font-bold cursor-pointer transition-all border uppercase tracking-wider" :class="selectedCategory === 'duvida' ? 'bg-blue-600 border-blue-600 text-white' : 'bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100'">Dúvida para Autor</button>
            <button type="button" @click="selectedCategory = 'padronizacao'" class="px-3.5 py-1.5 rounded-full font-bold cursor-pointer transition-all border uppercase tracking-wider" :class="selectedCategory === 'padronizacao' ? 'bg-purple-600 border-purple-600 text-white' : 'bg-purple-50 border-purple-200 text-purple-700 hover:bg-purple-100'">Padronização</button>
        </div>

        <!-- Lista de Apontamentos -->
        <div class="space-y-4">
            @forelse($editorialRevision->corrections as $cor)
                @php
                    $catColors = [
                        'ortografia' => 'bg-rose-100 text-rose-800 border-rose-200',
                        'gramatica' => 'bg-amber-100 text-amber-800 border-amber-200',
                        'duvida' => 'bg-blue-100 text-blue-800 border-blue-200',
                        'padronizacao' => 'bg-purple-100 text-purple-800 border-purple-200',
                        'clareza' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
                    ];
                    $cBadge = $catColors[$cor->category] ?? 'bg-slate-100 text-slate-800 border-slate-200';
                @endphp

                <div x-show="selectedCategory === 'todas' || selectedCategory === '{{ $cor->category }}'"
                     class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-all space-y-3">
                    
                    <!-- Linha Superior: Categoria, Status e Arquivo -->
                    <div class="flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-black uppercase px-2.5 py-0.5 rounded-[5px] border {{ $cBadge }}">
                                {{ ucfirst($cor->category) }}
                            </span>
                            @if($cor->page_number)
                                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 px-2 py-0.5 rounded-[5px]">Página {{ $cor->page_number }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-2">
                            <form action="{{ route('revisoes-editoriais.corrections.update-status', $cor->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" class="text-[10px] font-bold px-2 py-1 rounded-[5px] border border-slate-200 bg-slate-50 text-slate-700 cursor-pointer">
                                    <option value="pendente" {{ $cor->status === 'pendente' ? 'selected' : '' }}>⏳ Pendente</option>
                                    <option value="aceita" {{ $cor->status === 'aceita' ? 'selected' : '' }}>✅ Aceita</option>
                                    <option value="ignorada" {{ $cor->status === 'ignorada' ? 'selected' : '' }}>🚫 Ignorada</option>
                                    <option value="respondida" {{ $cor->status === 'respondida' ? 'selected' : '' }}>💬 Respondida</option>
                                    <option value="resolvida" {{ $cor->status === 'resolvida' ? 'selected' : '' }}>🎉 Resolvida</option>
                                </select>
                            </form>

                            <form action="{{ route('revisoes-editoriais.corrections.destroy', $cor->id) }}" method="POST" onsubmit="return confirm('Remover este apontamento?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-500 hover:text-rose-700 p-1 font-bold" title="Remover Apontamento">✕</button>
                            </form>
                        </div>
                    </div>

                    <!-- Conteúdo: Texto Original vs Sugerido -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <div class="bg-rose-50/50 dark:bg-rose-950/20 p-3.5 rounded-[5px] border border-rose-100 dark:border-rose-900/40">
                            <span class="text-[9px] font-bold uppercase text-rose-600 block mb-1">Texto Original</span>
                            <p class="font-mono text-slate-800 dark:text-slate-200 line-through">{{ $cor->original_text ?: 'Nenhum texto informado' }}</p>
                        </div>
                        <div class="bg-emerald-50/50 dark:bg-emerald-950/20 p-3.5 rounded-[5px] border border-emerald-100 dark:border-emerald-900/40">
                            <span class="text-[9px] font-bold uppercase text-emerald-600 block mb-1">Sugestão de Correção</span>
                            <p class="font-mono text-slate-800 dark:text-slate-100 font-bold">{{ $cor->suggested_text ?: 'Apenas comentário / observação' }}</p>
                        </div>
                    </div>

                    @if($cor->justification)
                        <div class="text-xs text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800/40 p-3 rounded-[5px] border border-slate-100 dark:border-slate-800 font-medium">
                            💡 <strong>Justificativa:</strong> {{ $cor->justification }}
                        </div>
                    @endif

                    <!-- Comentários / Respostas do Autor -->
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 space-y-2 text-xs">
                        <span class="font-bold text-slate-400 uppercase text-[10px] tracking-wider block">Respostas & Diálogo:</span>
                        
                        @foreach($cor->comments as $com)
                            <div class="p-3 rounded-[5px] bg-slate-50 dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-100 dark:border-slate-750">
                                <div class="flex items-center justify-between text-[10px] font-bold text-slate-500 mb-1">
                                    <span>👤 {{ $com->author_name ?: 'Usuário' }}</span>
                                    <span>{{ $com->created_at->format('d/m/Y H:i') }}</span>
                                </div>
                                <p class="font-medium leading-relaxed">{{ $com->message }}</p>
                            </div>
                        @endforeach

                        <form action="{{ route('revisoes-editoriais.corrections.comments.store', $cor->id) }}" method="POST" class="flex gap-2 pt-1">
                            @csrf
                            <input type="text" name="message" required placeholder="Escreva uma resposta ou orientação..." class="flex-1 px-3 py-2 border border-slate-200 dark:border-slate-700 rounded-[5px] text-xs bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium">
                            <button type="submit" class="px-4 py-2 bg-slate-900 dark:bg-slate-100 text-white dark:text-slate-900 font-bold rounded-[5px] text-xs uppercase tracking-wider">Enviar</button>
                        </form>
                    </div>

                </div>
            @empty
                <div class="bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 p-8 text-center text-slate-400 font-semibold text-xs rounded-[5px]">
                    Nenhum apontamento cadastrado nesta revisão ainda. Clique no botão flutuante verde para registrar a primeira correção.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Conteúdo da Aba 2: Arquivos Originais -->
    <div x-show="activeTab === 'arquivos'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4">
        <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase">Arquivos Enviados pelo Autor</h3>
        
        <div class="divide-y divide-slate-100 dark:divide-slate-800">
            @foreach($editorialRevision->files as $file)
                <div class="py-3.5 flex items-center justify-between text-xs gap-3">
                    <div class="flex items-center gap-3">
                        <span class="text-2xl">📄</span>
                        <div>
                            <h4 class="font-bold text-slate-800 dark:text-slate-200 text-sm">{{ $file->filename }}</h4>
                            <span class="text-[10px] text-slate-400 font-semibold">Tipo: {{ strtoupper($file->file_type) }} • Versão {{ $file->version }}</span>
                        </div>
                    </div>

                    <a href="{{ Storage::disk($editorialRevision->storage_disk)->url($file->file_path) }}" target="_blank" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold rounded-[5px] uppercase tracking-wider text-xs shadow-xs">
                        ⬇️ Baixar Arquivo
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Conteúdo da Aba 3: Glossário -->
    <div x-show="activeTab === 'glossario'" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase">Glossário e Padronizações do Projeto</h3>
            <button type="button" @click="openGlossaryModal = true" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-bold text-xs rounded-[5px] uppercase tracking-wider">
                ➕ Adicionar Termo
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse($editorialRevision->glossaries as $glo)
                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-[5px] border border-slate-200 dark:border-slate-700 text-xs space-y-2 relative">
                    <form action="{{ route('revisoes-editoriais.glossary.destroy', $glo->id) }}" method="POST" class="absolute top-2 right-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-rose-500 font-bold p-1">✕</button>
                    </form>

                    <div>
                        <span class="text-[9px] font-bold uppercase text-emerald-600 block">Termo Correto</span>
                        <h4 class="font-black text-sm text-slate-800 dark:text-slate-100">{{ $glo->correct_term }}</h4>
                    </div>

                    @if($glo->incorrect_terms)
                        <div>
                            <span class="text-[9px] font-bold uppercase text-rose-500 block">Evitar / Grafia Incorreta</span>
                            <p class="font-mono text-slate-600 dark:text-slate-300">{{ $glo->incorrect_terms }}</p>
                        </div>
                    @endif

                    @if($glo->description)
                        <p class="text-slate-500 italic pt-1 border-t border-slate-200 dark:border-slate-700">{{ $glo->description }}</p>
                    @endif
                </div>
            @empty
                <div class="col-span-full text-center text-slate-400 py-8 font-semibold text-xs">
                    Nenhum termo técnico cadastrado no glossário ainda.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Modal Novo Apontamento -->
    <div x-show="openCorrectionModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openCorrectionModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-lg w-full space-y-4">
            <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase">➕ Criar Novo Apontamento</h3>

            <form action="{{ route('revisoes-editoriais.corrections.store', $editorialRevision->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="font-bold block mb-1">Categoria</label>
                        <select name="category" required class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium">
                            <option value="ortografia">Ortografia</option>
                            <option value="gramatica">Gramática</option>
                            <option value="pontuacao">Pontuação</option>
                            <option value="clareza">Clareza de Frase</option>
                            <option value="padronizacao">Padronização</option>
                            <option value="duvida">Dúvida para o Autor</option>
                            <option value="termo_tecnico">Termo Técnico</option>
                            <option value="observacao">Observação Geral</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold block mb-1">Página (Opcional)</label>
                        <input type="number" name="page_number" placeholder="Ex: 12" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                    </div>
                </div>

                <div>
                    <label class="font-bold block mb-1">Trecho Original do Autor</label>
                    <textarea name="original_text" rows="2" placeholder="Copie o trecho exatamente como está no texto original..." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Sugestão de Correção / Alteração</label>
                    <textarea name="suggested_text" rows="2" placeholder="Digite a correção sugerida..." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium"></textarea>
                </div>

                <div>
                    <label class="font-bold block mb-1">Justificativa / Comentário</label>
                    <input type="text" name="justification" placeholder="Ex: Concordância verbal no plural devido ao sujeito composto." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 font-medium">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openCorrectionModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-[5px]">Salvar Apontamento</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Adicionar Termo no Glossário -->
    <div x-show="openGlossaryModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openGlossaryModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase">📖 Adicionar Termo ao Glossário</h3>

            <form action="{{ route('revisoes-editoriais.glossary.store', $editorialRevision->id) }}" method="POST" class="space-y-3 text-xs">
                @csrf
                
                <div>
                    <label class="font-bold block mb-1">Termo Correto <span class="text-rose-500">*</span></label>
                    <input type="text" name="correct_term" required placeholder="Ex: Motricidade Orofacial" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold block mb-1">Evitar / Grafia Incorreta</label>
                    <input type="text" name="incorrect_terms" placeholder="Ex: Motricidade Oro-Facial" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold block mb-1">Descrição / Regra</label>
                    <input type="text" name="description" placeholder="Ex: Sempre em maiúsculas sem hífen." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openGlossaryModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Salvar no Glossário</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    function editorialShowWorkspace() {
        return {
            activeTab: 'correcoes',
            openCorrectionModal: false,
            openGlossaryModal: false,
            selectedCategory: 'todas',
            toastMessage: '',

            copyShareLink(url) {
                navigator.clipboard.writeText(url);
                this.toastMessage = 'Link público do Autor copiado com sucesso!';
                setTimeout(() => { this.toastMessage = ''; }, 4000);
            }
        }
    }
</script>
@endsection
