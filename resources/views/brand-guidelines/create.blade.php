@extends('layouts.app')

@section('title', 'Novo Manual de Identidade - Gestor de Freelas')
@section('page_title', 'Novo Manual de Marca')

@section('content')
<div class="space-y-6">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('revisoes.brand-guidelines.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Formulário Simplificado -->
    <div class="max-w-2xl mx-auto">
        <form action="{{ route('revisoes.brand-guidelines.store') }}" method="POST" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-6">
            @csrf

            <div>
                <h3 class="text-sm font-black text-slate-850 dark:text-slate-100 uppercase tracking-wider">📁 Informações Gerais</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Identificação e associação da marca para o manual</p>
            </div>

            <div class="grid grid-cols-1 gap-6">
                <!-- Nome da Marca -->
                <div class="space-y-1.5">
                    <label for="brand_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome da Marca / Empresa</label>
                    <input type="text" name="brand_name" id="brand_name" required placeholder="Ex: Crescer Gestão Integrada" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs focus:outline-none focus:ring-1 focus:ring-primary-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 shadow-sm">
                </div>

                <!-- Cliente Associado (Busca Alpine.js via Componente) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Cliente Associado (Opcional)</label>
                    
                    <div class="relative" x-data="clientSelectComponent()">
                        <!-- Input Trigger -->
                        <div @click="open = !open" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200 cursor-pointer flex justify-between items-center select-none shadow-sm">
                            <span x-text="selectedName"></span>
                            <span class="text-slate-450 text-[10px]">▼</span>
                        </div>

                        <!-- Hidden native input -->
                        <input type="hidden" name="client_id" :value="selectedId">

                        <!-- Dropdown Menu -->
                        <div x-show="open" @click.away="open = false" class="absolute left-0 mt-1 w-full bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] shadow-md z-50 p-2 space-y-2" x-cloak>
                            <input type="text" x-model="search" placeholder="Digite para pesquisar..." class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-slate-850 rounded text-xs focus:outline-none bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200">
                            
                            <div class="max-h-48 overflow-y-auto space-y-0.5">
                                <div @click="clear()" class="px-2.5 py-2 text-xs text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 font-bold rounded cursor-pointer">
                                    ✕ Limpar seleção
                                </div>
                                <template x-for="c in filteredClients()" :key="c.id">
                                    <div @click="select(c)" class="px-2.5 py-2 text-xs text-slate-700 dark:text-slate-355 hover:bg-slate-50 dark:hover:bg-slate-800 rounded cursor-pointer font-semibold" x-text="c.name"></div>
                                </template>
                                <div x-show="filteredClients().length === 0" class="px-2.5 py-2 text-xs text-slate-400 italic text-center">Nenhum cliente encontrado</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Compartilhamento Ativado por Padrão -->
            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked class="w-4 h-4 text-primary-650 rounded border-slate-200 focus:ring-primary-500 cursor-pointer">
                <label for="is_active" class="text-xs font-bold text-slate-650 cursor-pointer">Ativar compartilhamento público imediato deste manual</label>
            </div>

            <!-- Submit Button -->
            <div class="pt-4 border-t border-slate-100 dark:border-slate-800 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-[5px] text-xs uppercase tracking-wider transition-colors shadow-sm cursor-pointer">
                    Salvar e Prosseguir ➜
                </button>
            </div>
        </form>
    </div>

</div>

<script>
    function clientSelectComponent() {
        return {
            open: false,
            search: '',
            selectedId: '',
            selectedName: 'Nenhum cliente associado',
            clients: @json($clients->map(fn($c) => ['id' => $c->id, 'name' => $c->name])),
            filteredClients() {
                if (!this.search) {
                    return this.clients;
                }
                var term = this.search.toLowerCase();
                return this.clients.filter(function(c) {
                    return c.name.toLowerCase().indexOf(term) !== -1;
                });
            },
            select(client) {
                this.selectedId = client.id;
                this.selectedName = client.name;
                this.open = false;
                this.search = '';
            },
            clear() {
                this.selectedId = '';
                this.selectedName = 'Nenhum cliente associado';
                this.open = false;
                this.search = '';
            }
        };
    }
</script>
@endsection
