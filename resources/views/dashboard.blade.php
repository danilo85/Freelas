@extends('layouts.app')

@section('title', 'Painel Geral - Gestor de Freelas')
@section('page_title', 'Painel Geral')

@section('content')
<div class="space-y-8">
    
    <!-- Seção de Métricas (Cards) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Faturamento do Mês -->
        <div class="bg-white rounded-[5px] p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Faturamento do Mês</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-2">
                    R$ {{ number_format($currentMonthRevenue, 2, ',', '.') }}
                </h3>
                <span class="text-xs text-green-600 font-medium flex items-center gap-1 mt-1.5">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Recebidos (entradas)
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-green-50 text-green-600 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Orçamentos Pendentes -->
        <div class="bg-white rounded-[5px] p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Orçamentos Pendentes</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-2">
                    R$ {{ number_format($pendingProposalsValue, 2, ',', '.') }}
                </h3>
                <span class="text-xs text-slate-500 font-medium flex items-center gap-1 mt-1.5">
                    Aguardando retorno do cliente
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-amber-50 text-amber-600 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
            </div>
        </div>

        <!-- Projetos Ativos -->
        <div class="bg-white rounded-[5px] p-6 border border-slate-200 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Projetos Ativos</p>
                <h3 class="text-2xl font-bold text-slate-900 mt-2">
                    {{ $activeProjectsCount }}
                </h3>
                <span class="text-xs text-green-600 font-medium flex items-center gap-1 mt-1.5">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 inline-block animate-pulse"></span>
                    Em andamento
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-blue-50 text-blue-600 flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
            </div>
        </div>
        
    </div>

    <!-- Kanban Interativo (Alpine.js) -->
    <div x-data="kanbanBoard()" x-init="init()" class="space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Projetos & Fluxo de Trabalho</h2>
                <p class="text-sm text-slate-400 mt-0.5">Visualize e altere o status de seus projetos de forma dinâmica.</p>
            </div>
            
            <!-- Indicador de Carregamento Global -->
            <div x-show="loading" x-cloak class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                <svg class="animate-spin h-4 w-4 text-primary-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Salvando alterações...
            </div>
        </div>

        <!-- Colunas do Kanban -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Coluna: Prospect -->
            <div class="bg-slate-100 rounded-[5px] p-4 flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h4 class="font-bold text-slate-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        Prospects
                    </h4>
                    <span class="bg-white text-slate-600 text-xs font-semibold px-2 py-0.5 rounded-[5px] shadow-sm" x-text="countByStatus('prospect')">0</span>
                </div>
                
                <div class="space-y-3 flex-1 overflow-y-auto">
                    <template x-for="project in getProjectsByStatus('prospect')" :key="project.id">
                        <div class="bg-white rounded-[5px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative">
                            <!-- Loading individual overlay -->
                            <div x-show="updatingId === project.id" class="absolute inset-0 bg-white/70 rounded-[5px] flex items-center justify-center z-10">
                                <svg class="animate-spin h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <h5 class="font-semibold text-slate-900 text-base" x-text="project.title"></h5>
                            <p class="text-xs text-slate-400 mt-1" x-text="'Cliente: ' + (project.client ? project.client.name : 'N/A')"></p>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2" x-text="project.description"></p>
                            
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-sm font-bold text-slate-700" x-text="formatCurrency(project.total_value)"></span>
                                
                                <div class="flex items-center gap-1">
                                    <!-- Ação Iniciar Projeto -->
                                    <button @click="updateStatus(project.id, 'em andamento')" class="text-xs font-semibold px-2.5 py-1.5 rounded-[5px] bg-green-50 text-green-700 hover:bg-green-100 transition-colors flex items-center gap-1">
                                        Iniciar
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="countByStatus('prospect') === 0" class="border-2 border-dashed border-slate-200 rounded-[5px] p-8 text-center text-slate-400 text-sm">
                        Nenhum prospect.
                    </div>
                </div>
            </div>

            <!-- Coluna: Em Andamento -->
            <div class="bg-slate-100 rounded-[5px] p-4 flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h4 class="font-bold text-slate-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500 animate-pulse"></span>
                        Em Andamento
                    </h4>
                    <span class="bg-white text-slate-600 text-xs font-semibold px-2 py-0.5 rounded-[5px] shadow-sm" x-text="countByStatus('em andamento')">0</span>
                </div>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    <template x-for="project in getProjectsByStatus('em andamento')" :key="project.id">
                        <div class="bg-white rounded-[5px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative">
                            <!-- Loading individual overlay -->
                            <div x-show="updatingId === project.id" class="absolute inset-0 bg-white/70 rounded-[5px] flex items-center justify-center z-10">
                                <svg class="animate-spin h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <h5 class="font-semibold text-slate-900 text-base" x-text="project.title"></h5>
                            <p class="text-xs text-slate-400 mt-1" x-text="'Cliente: ' + (project.client ? project.client.name : 'N/A')"></p>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2" x-text="project.description"></p>
                            
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-sm font-bold text-slate-700" x-text="formatCurrency(project.total_value)"></span>
                                
                                <div class="flex items-center gap-1.5">
                                    <!-- Ação Voltar para Prospect -->
                                    <button @click="updateStatus(project.id, 'prospect')" class="w-8 h-8 flex items-center justify-center bg-white text-slate-500 hover:text-slate-800 rounded-[5px] shadow-sm hover:bg-slate-50 transition-all" title="Retornar a Prospect">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </button>
                                    <!-- Ação Finalizar Projeto -->
                                    <button @click="updateStatus(project.id, 'finalizado')" class="text-xs font-semibold px-2.5 py-1.5 rounded-[5px] bg-blue-50 text-blue-700 hover:bg-blue-100 transition-colors flex items-center gap-1">
                                        Concluir
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="countByStatus('em andamento') === 0" class="border-2 border-dashed border-slate-200 rounded-[5px] p-8 text-center text-slate-400 text-sm">
                        Nenhum projeto em andamento.
                    </div>
                </div>
            </div>

            <!-- Coluna: Finalizado -->
            <div class="bg-slate-100 rounded-[5px] p-4 flex flex-col min-h-[400px]">
                <div class="flex items-center justify-between mb-4 px-2">
                    <h4 class="font-bold text-slate-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-green-500"></span>
                        Finalizados
                    </h4>
                    <span class="bg-white text-slate-600 text-xs font-semibold px-2 py-0.5 rounded-[5px] shadow-sm" x-text="countByStatus('finalizado')">0</span>
                </div>

                <div class="space-y-3 flex-1 overflow-y-auto">
                    <template x-for="project in getProjectsByStatus('finalizado')" :key="project.id">
                        <div class="bg-white rounded-[5px] p-5 border border-slate-200 shadow-sm hover:shadow-md transition-shadow relative">
                            <!-- Loading individual overlay -->
                            <div x-show="updatingId === project.id" class="absolute inset-0 bg-white/70 rounded-[5px] flex items-center justify-center z-10">
                                <svg class="animate-spin h-5 w-5 text-slate-700" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>

                            <h5 class="font-semibold text-slate-900 text-base" x-text="project.title"></h5>
                            <p class="text-xs text-slate-400 mt-1" x-text="'Cliente: ' + (project.client ? project.client.name : 'N/A')"></p>
                            <p class="text-xs text-slate-500 mt-2 line-clamp-2" x-text="project.description"></p>
                            
                            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-3">
                                <span class="text-sm font-bold text-slate-700" x-text="formatCurrency(project.total_value)"></span>
                                
                                <div class="flex items-center gap-1">
                                    <!-- Ação Reabrir Projeto -->
                                    <button @click="updateStatus(project.id, 'em andamento')" class="text-xs font-semibold px-2.5 py-1.5 rounded-[5px] bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors flex items-center gap-1">
                                        Reabrir
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div x-show="countByStatus('finalizado') === 0" class="border-2 border-dashed border-slate-200 rounded-[5px] p-8 text-center text-slate-400 text-sm">
                        Nenhum projeto finalizado.
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Script do Alpine.js para Controle de Estado do Kanban -->
<script>
    function kanbanBoard() {
        return {
            projects: @js($projects),
            loading: false,
            updatingId: null,

            init() {
                // Configurar o token CSRF padrão para requisições fetch
                this.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            },

            getProjectsByStatus(status) {
                return this.projects.filter(p => p.status === status);
            },

            countByStatus(status) {
                return this.getProjectsByStatus(status).length;
            },

            formatCurrency(value) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
            },

            async updateStatus(projectId, newStatus) {
                this.loading = true;
                this.updatingId = projectId;

                try {
                    const response = await fetch(`/api/projects/${projectId}/status`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ status: newStatus })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Encontra o projeto local e atualiza o status dele para redesenhar o Kanban
                        const index = this.projects.findIndex(p => p.id === projectId);
                        if (index !== -1) {
                            this.projects[index].status = newStatus;
                        }
                    } else {
                        alert(data.message || 'Ocorreu um erro ao atualizar o status do projeto.');
                    }
                } catch (error) {
                    console.error('Erro na requisição:', error);
                    alert('Erro de conexão ao atualizar o status do projeto.');
                } finally {
                    this.loading = false;
                    this.updatingId = null;
                }
            }
        }
    }
</script>
@endsection
