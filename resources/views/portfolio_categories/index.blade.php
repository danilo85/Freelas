@extends('layouts.app')

@section('title', 'Categorias de Portfólio - Gestor de Freelas')
@section('page_title', 'Categorias de Portfólio')

@section('content')
<div class="space-y-6" x-data="categoryManager()">
    
    <!-- Retorno rápido -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para Trabalhos
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Listagem de Categorias (Esquerda - 2 Colunas) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-[5px] shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wider">Categorias Cadastradas</h3>
                <span class="text-xs text-slate-400 font-semibold">{{ $categories->count() }} registradas</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 p-5 bg-slate-50/50">
                @forelse($categories as $cat)
                    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm flex items-center justify-between hover:shadow transition-shadow">
                        <div class="min-w-0">
                            <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $cat->name }}">{{ $cat->name }}</h4>
                            <p class="text-[10px] text-slate-400 font-mono mt-0.5 truncate" title="{{ $cat->slug }}">{{ $cat->slug }}</p>
                        </div>
                        
                        <div class="flex items-center gap-1 shrink-0 ml-2">
                            <!-- Botão Editar -->
                            <button type="button" 
                                    @click="editCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                    class="w-7 h-7 flex items-center justify-center text-primary-650 hover:bg-primary-50 rounded-[5px] transition-colors border-0"
                                    title="Editar Categoria">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>

                            <!-- Botão Excluir -->
                            <button type="button" 
                                    @click="$dispatch('trigger-global-delete', { 
                                        title: 'Excluir Categoria', 
                                        message: 'Deseja excluir a categoria <strong class=\'text-slate-800\'>{{ addslashes($cat->name) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1 block\'>Aviso: Trabalhos associados a ela poderão ficar sem categoria.</span>', 
                                        action: '{{ route('portfolio-categories.destroy', $cat->id) }}', 
                                        highSecurity: false 
                                    })"
                                    class="w-7 h-7 flex items-center justify-center text-red-600 hover:bg-red-50 rounded-[5px] transition-colors border-0"
                                    title="Excluir Categoria">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-8 text-center text-slate-400 text-sm italic">
                        Nenhuma categoria cadastrada ainda.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Formulário Adicionar/Editar Categoria (Direita - 1 Coluna) -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm space-y-4">
            
            <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3"
                x-text="isEdit ? 'Editar Categoria' : 'Nova Categoria'">
                Nova Categoria
            </h4>

            <form :action="isEdit ? '{{ url('portfolio-categories') }}/' + editId : '{{ route('portfolio-categories.store') }}'" 
                  method="POST" 
                  class="space-y-4">
                @csrf
                <template x-if="isEdit">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="space-y-1">
                    <label for="name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome da Categoria</label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           x-model="catName"
                           required 
                           placeholder="Ex: Web Design, Branding, Fontes..." 
                           class="w-full px-3.5 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" 
                            class="flex-1 py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm text-center"
                            x-text="isEdit ? 'Salvar Alterações' : 'Cadastrar Categoria'">
                        Cadastrar Categoria
                    </button>
                    
                    <button type="button" 
                            x-show="isEdit"
                            @click="cancelEdit()"
                            class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-600 text-sm font-semibold rounded-[5px] transition-colors text-center">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>

<script>
    function categoryManager() {
        return {
            isEdit: false,
            editId: null,
            catName: '',

            editCategory(id, name) {
                this.isEdit = true;
                this.editId = id;
                this.catName = name;
            },

            cancelEdit() {
                this.isEdit = false;
                this.editId = null;
                this.catName = '';
            }
        }
    }
</script>
@endsection
