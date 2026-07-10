@extends('layouts.app')

@section('title', 'Identidades Visuais - Gestor de Freelas')
@section('page_title', 'Identidades Visuais')

@section('content')
<div x-data="brandGuidelineList()" class="space-y-8">

    <!-- Top Cards (Métricas) - Igual à revisão de trabalhos -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Total de Manuais (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 text-white">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Identidades Visuais</p>
                <h3 class="text-3xl font-extrabold text-white mt-2" x-text="totalCount"></h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Manuais de marcas cadastrados
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                🎨
            </div>
        </div>

        <!-- Links Ativos (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 text-white">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Links Ativos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2" x-text="activeCount"></h3>
                <span class="text-sm text-emerald-100/90 font-medium block mt-1.5">
                    Manuais compartilhados publicamente
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                🔗
            </div>
        </div>

        <!-- Inativos (Card Cinza) -->
        <div class="bg-slate-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 text-white">
            <div>
                <p class="text-sm font-bold text-slate-100 uppercase tracking-wider">Inativos / Ocultos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2" x-text="inactiveCount"></h3>
                <span class="text-sm text-slate-100/90 font-medium block mt-1.5">
                    Manuais com visualização desativada
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                🔒
            </div>
        </div>

    </div>

    <!-- Busca e Filtros - Igual à revisão de trabalhos -->
    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm select-none space-y-4">
        <!-- Campo de Busca -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery"
                   placeholder="Pesquisar por nome da marca..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <button type="button" 
                    x-show="searchQuery" 
                    @click="searchQuery = ''" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-655 cursor-pointer" x-cloak>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Filtros Rápidos (Status) -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Status:</span>
            
            <!-- Todos -->
            <button type="button" 
                    @click="statusFilter = 'all'"
                    class="px-3.5 py-1.5 rounded-[5px] font-bold uppercase tracking-wider transition-all border text-[10px]"
                    :class="statusFilter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                Todos
            </button>

            <!-- Ativos -->
            <button type="button" 
                    @click="statusFilter = 'active'"
                    class="px-3.5 py-1.5 rounded-[5px] font-bold uppercase tracking-wider transition-all border text-[10px]"
                    :class="statusFilter === 'active' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                Ativos
            </button>

            <!-- Inativos -->
            <button type="button" 
                    @click="statusFilter = 'inactive'"
                    class="px-3.5 py-1.5 rounded-[5px] font-bold uppercase tracking-wider transition-all border text-[10px]"
                    :class="statusFilter === 'inactive' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'">
                Inativos
            </button>
        </div>
    </div>

    <!-- List Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="item in filteredGuidelines()" :key="item.id">
            <div class="bg-white border border-slate-200 rounded-[5px] p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4 relative overflow-hidden">
                <!-- Status Badge -->
                <div class="flex items-center justify-between">
                    <span :class="item.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700'" class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[5px]" x-text="item.is_active ? 'Ativo' : 'Inativo'"></span>
                    <span class="text-[10px] font-bold text-slate-400" x-text="formatDate(item.created_at)"></span>
                </div>

                <!-- Info -->
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <span class="w-10 h-10 rounded-[5px] bg-slate-50 border border-slate-100 flex items-center justify-center text-xl shrink-0">
                            🎨
                        </span>
                        <div class="min-w-0 flex-1">
                            <h4 class="font-extrabold text-slate-800 text-sm truncate" :title="item.brand_name" x-text="item.brand_name"></h4>
                            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5" x-text="item.client ? 'Cliente: ' + item.client.name : 'Sem Cliente'"></p>
                        </div>
                    </div>

                    <!-- Items Summary -->
                    <div class="text-[11px] text-slate-500 space-y-1 bg-slate-50 p-2.5 rounded-[5px] border border-slate-100">
                        <div class="flex justify-between">
                            <span>Cores:</span>
                            <span class="font-bold text-slate-750" x-text="(item.color_palette || []).length + ' cores'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Fontes:</span>
                            <span class="font-bold text-slate-750" x-text="(item.typography || []).length + ' fontes'"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Mockups:</span>
                            <span class="font-bold text-slate-750" x-text="(item.stationery || []).length + ' arquivos'"></span>
                        </div>
                    </div>
                </div>

                <!-- Action Footer -->
                <div class="flex items-center gap-1 shrink-0 pt-2 border-t border-slate-100 no-print">
                    <!-- Copiar Link Público -->
                    <button type="button" 
                            @click="copyShareLink(item.share_token, $event)"
                            class="flex-1 text-center bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider py-2 rounded-[5px] transition-all shadow-sm flex items-center justify-center gap-1"
                            title="Copiar Link"
                            :disabled="!item.is_active"
                            :class="!item.is_active ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                    >
                        <span>🔗</span> <span class="inline md:hidden xl:inline">Copiar Link</span>
                    </button>

                    <!-- Toggle Active -->
                    <button type="button" 
                            @click="toggleActive(item)"
                            class="w-8 h-8 flex items-center justify-center rounded-[5px] transition-all border-0 shadow-none bg-transparent cursor-pointer"
                            :class="item.is_active ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-50'"
                            :title="item.is_active ? 'Desativar' : 'Ativar'">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.07 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                        </svg>
                    </button>

                    <!-- Editar -->
                    <a :href="'/freelas/utilidades/identidades-visuais/' + item.id + '/editar'"
                       class="w-8 h-8 flex items-center justify-center bg-transparent border-0 shadow-none text-primary-650 hover:bg-primary-50 rounded-[5px] transition-all cursor-pointer" 
                       title="Editar Manual">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </a>

                    <!-- Excluir -->
                    <form :action="'/freelas/utilidades/identidades-visuais/' + item.id" method="POST" class="inline" @submit="confirmDeletion($event)">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-8 h-8 flex items-center justify-center bg-transparent border-0 shadow-none text-rose-600 hover:bg-rose-50 rounded-[5px] transition-all cursor-pointer" 
                                title="Excluir Manual">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </template>
    </div>

    <!-- Empty State -->
    <div x-show="filteredGuidelines().length === 0" class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px] text-xs font-semibold bg-white" x-cloak>
        Nenhum manual de identidade visual encontrado.
    </div>

</div>

<!-- Botão Flutuante Adicionar Identidade - Igual à revisão de trabalhos -->
<a href="{{ route('revisoes.brand-guidelines.create') }}" class="fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full flex items-center justify-center shadow-lg shadow-blue-600/30 hover:scale-105 hover:rotate-90 transition-all duration-300 z-40 focus:outline-none" title="Nova Identidade Visual">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
    </svg>
</a>

<script>
    function brandGuidelineList() {
        return {
            searchQuery: '',
            statusFilter: 'all',
            guidelines: @json($guidelines),
            totalCount: {{ $totalCount }},
            activeCount: {{ $activeCount }},
            inactiveCount: {{ $inactiveCount }},

            filteredGuidelines() {
                var query = this.searchQuery.toLowerCase();
                var filter = this.statusFilter;
                
                return this.guidelines.filter(function(item) {
                    var matchesSearch = item.brand_name.toLowerCase().indexOf(query) !== -1;
                    var matchesStatus = true;
                    if (filter === 'active') matchesStatus = item.is_active;
                    if (filter === 'inactive') matchesStatus = !item.is_active;
                    return matchesSearch && matchesStatus;
                });
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                let date = new Date(dateStr);
                return date.toLocaleDateString('pt-BR');
            },

            toggleActive(item) {
                fetch(`/freelas/utilidades/identidades-visuais/${item.id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        item.is_active = data.is_active;
                        this.activeCount = this.guidelines.filter(g => g.is_active).length;
                        this.inactiveCount = this.guidelines.filter(g => !g.is_active).length;
                    }
                })
                .catch(err => console.error('Erro:', err));
            },

            copyShareLink(token, event) {
                let publicUrl = `${window.location.origin}/brand/${token}`;
                navigator.clipboard.writeText(publicUrl).then(() => {
                    let btn = event.currentTarget;
                    let originalHTML = btn.innerHTML;
                    btn.innerHTML = '<span>✓</span> Copiado!';
                    setTimeout(() => btn.innerHTML = originalHTML, 2000);
                });
            },

            confirmDeletion(event) {
                if (!confirm('Deseja realmente excluir este manual de identidade visual?')) {
                    event.preventDefault();
                }
            }
        };
    }
</script>
@endsection
