@extends('layouts.app')

@section('title', 'Gerenciar Categorias - Gestor de Freelas')
@section('page_title', 'Categorias Financeiras')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="categoryManager()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('finances.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para o Controle Financeiro
        </a>
    </div>

    <!-- Título Principal -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Categorias Financeiras</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Organize suas movimentações com categorias personalizadas.</p>
        </div>
        <button @click="openCreateModal()" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm self-start sm:self-auto no-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Categoria
        </button>
    </div>

    @if ($errors->any())
        <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-[5px] text-xs font-semibold">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Grid de Categorias -->
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
        @foreach($categories as $category)
            @php
                $isSystem = is_null($category->user_id);
            @endphp
            <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex items-center justify-between gap-3 hover:shadow-md transition-shadow">
                <div class="flex items-center gap-3 min-w-0">
                    <span class="w-10 h-10 rounded-[5px] bg-slate-50 border border-slate-100 flex items-center justify-center text-xl shrink-0">
                        {{ $category->icon }}
                    </span>
                    <div class="min-w-0">
                        <h4 class="font-bold text-sm text-slate-800 truncate">{{ $category->name }}</h4>
                        <span class="inline-block text-[10px] font-bold px-1.5 py-0.5 rounded-[4px] mt-0.5 uppercase tracking-wider
                            {{ $category->type === 'receita' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : '' }}
                            {{ $category->type === 'despesa' ? 'bg-rose-50 text-rose-700 border border-rose-100' : '' }}
                            {{ $category->type === 'ambos' ? 'bg-blue-50 text-blue-700 border border-blue-100' : '' }}
                        ">
                            {{ $category->type }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0 no-print">
                    <!-- Editar -->
                    <button type="button" @click="openEditModal({ id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', type: '{{ $category->type }}', icon: '{{ addslashes($category->icon) }}' })" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none cursor-pointer" title="Editar">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>
                    <!-- Excluir -->
                    <button type="button" @click="confirmDelete({ id: {{ $category->id }}, name: '{{ addslashes($category->name) }}' }, '{{ route('finances.categories.destroy', $category->id) }}')" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-650 hover:bg-red-50 rounded-[5px] transition-all border-0 shadow-none cursor-pointer" title="Excluir Categoria">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Modal Form (Criar/Editar) -->
    <div x-show="showModal" class="fixed inset-0 z-50 overflow-y-auto" x-cloak>
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs transition-opacity" @click="showModal = false"></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div class="relative w-full max-w-md transform overflow-hidden rounded-[5px] bg-white p-6 shadow-xl transition-all border border-slate-200 space-y-4">
                
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-base font-extrabold text-slate-800" x-text="isEdit ? 'Editar Categoria' : 'Nova Categoria'"></h3>
                    <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form :action="formAction" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_method" :value="isEdit ? 'PUT' : 'POST'">

                    <!-- Nome -->
                    <div class="space-y-1">
                        <label for="name" class="text-[11px] font-bold text-slate-450 uppercase tracking-wider block">Nome da Categoria *</label>
                        <input 
                            type="text" 
                            name="name" 
                            id="name" 
                            required 
                            x-model="form.name" 
                            placeholder="Ex: Alimentação, Softwares"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                        />
                    </div>

                    <!-- Tipo -->
                    <div class="space-y-1">
                        <label for="type" class="text-[11px] font-bold text-slate-450 uppercase tracking-wider block">Tipo *</label>
                        <select 
                            name="type" 
                            id="type" 
                            required 
                            :value="form.type"
                            @change="form.type = $event.target.value"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                        >
                            <option value="receita" :selected="form.type === 'receita'">Receita</option>
                            <option value="despesa" :selected="form.type === 'despesa'">Despesa</option>
                            <option value="ambos" :selected="form.type === 'ambos'">Ambos (Receita e Despesa)</option>
                        </select>
                    </div>

                    <!-- Ícone (Emoji) -->
                    <div class="space-y-1">
                        <label for="icon" class="text-[11px] font-bold text-slate-450 uppercase tracking-wider block">Ícone (Emoji) *</label>
                        <div class="flex gap-2">
                            <input 
                                type="text" 
                                name="icon" 
                                id="icon" 
                                required 
                                x-model="form.icon" 
                                placeholder="🍔, 💻, 🏠..."
                                class="w-20 text-center px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-slate-700 text-lg"
                            />
                            <div class="flex-1 flex flex-wrap gap-1 p-2 bg-slate-50 border border-slate-100 rounded-[5px] max-h-[85px] overflow-y-auto">
                                <template x-for="emoji in presetEmojis">
                                    <button type="button" @click="form.icon = emoji" class="w-8 h-8 rounded-full hover:bg-slate-200 transition-colors flex items-center justify-center text-lg bg-white shadow-xs border border-slate-200" x-text="emoji"></button>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-[5px] text-xs font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                            Cancelar
                        </button>
                        <button type="submit" class="px-5 py-2 rounded-[5px] bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary-500/20" x-text="isEdit ? 'Salvar Alterações' : 'Criar Categoria'"></button>
                    </div>
                </form>

            </div>
        </div>
    </div>

</div>

<script>
    function categoryManager() {
        return {
            showModal: false,
            isEdit: false,
            formAction: '',
            form: {
                id: null,
                name: '',
                type: 'despesa',
                icon: '🍔'
            },
            presetEmojis: [
                '🍔', '🚗', '🏠', '✈️', '❤️', '📚', '🔔', '📣', '📄', '💳', '💻', '💼', '📈', '💵', '💰', '🛒', '⚡', '💧', '🔧', '💻', '🏋️', '🏥', '🎮', '🎁'
            ],

            openCreateModal() {
                this.isEdit = false;
                this.formAction = '{{ route("finances.categories.store") }}';
                this.form = {
                    id: null,
                    name: '',
                    type: 'despesa',
                    icon: '🍔'
                };
                this.showModal = true;
            },

            openEditModal(category) {
                this.isEdit = true;
                this.formAction = '{{ route("finances.categories.index") }}' + '/' + category.id;
                this.form = {
                    id: category.id,
                    name: category.name,
                    type: category.type,
                    icon: category.icon
                };
                this.showModal = true;
            },

            confirmDelete(category, destroyRoute) {
                this.$dispatch('trigger-global-delete', {
                    title: 'Excluir Categoria',
                    message: `Tem certeza que deseja excluir a categoria <strong class="text-slate-800">${category.name}</strong>?<br><span class="text-xs text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100">Aviso: Transações vinculadas a esta categoria não serão perdidas, mas perderão a referência de vínculo com a categoria.</span>`,
                    action: destroyRoute,
                    highSecurity: false
                });
            }
        };
    }
</script>
@endsection
