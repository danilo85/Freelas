@extends('layouts.app')

@section('title', 'Configurações de Administração - Gestor de Freelas')
@section('page_title', 'Administração do Sistema')

@section('content')
<div x-data="{ 
    activeTab: 'users',
    createModalOpen: false,
    editModalOpen: false,
    selectedUser: {},
    editActionUrl: '',
    openCreateModal() { this.createModalOpen = true; },
    closeCreateModal() { this.createModalOpen = false; },
    openEditModal(user) {
        this.selectedUser = Object.assign({}, user);
        this.editActionUrl = `/users/${user.id}`;
        this.editModalOpen = true;
    },
    closeEditModal() {
        this.editModalOpen = false;
        this.selectedUser = {};
        this.editActionUrl = '';
    }
}" class="space-y-8">
    
    <!-- Header visual do Painel -->
    <div class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm flex items-center justify-between flex-wrap gap-4">
        <div class="space-y-1">
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <span>⚙️</span> Painel Administrativo
            </h2>
            <p class="text-xs text-slate-400">Controle a liberação de novos usuários, gerencie funções e altere as opções gerais do sistema.</p>
        </div>
        
        <!-- Tab selector -->
        <div class="flex gap-2 bg-slate-50 border border-slate-150 p-1 rounded-[5px]">
            <button type="button" @click="activeTab = 'users'"
                     class="px-4 py-2 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5"
                     :class="activeTab === 'users' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                <span>👥</span> Gerenciar Usuários
            </button>
            <button type="button" @click="activeTab = 'system'"
                     class="px-4 py-2 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1.5"
                     :class="activeTab === 'system' ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:text-slate-900'">
                <span>🔧</span> Configurações Globais
            </button>
        </div>
    </div>

    <!-- Aba 1: Gerenciamento de Usuários -->
    <div x-show="activeTab === 'users'" class="space-y-6" x-cloak>
        <div class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Controle de Cadastros e Funções</h3>
                <p class="text-[10px] text-slate-450 mt-1">Aprove ou suspenda o acesso de usuários ao painel administrativo e gerencie quem é Master ou Comum.</p>
            </div>
            <button type="button" @click="openCreateModal()" class="bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs uppercase tracking-wider px-4 py-2.5 rounded-[5px] transition-all flex items-center gap-1.5 shadow-sm cursor-pointer select-none">
                <span>➕</span> Novo Usuário
            </button>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($users as $user)
                <div class="bg-white border border-slate-200 rounded-[5px] shadow-sm p-6 flex flex-col justify-between gap-4 hover:border-slate-350 transition-all">
                    
                    <!-- Top section with user avatar, name & status -->
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-primary-50 border border-primary-200 flex items-center justify-center font-bold text-primary-700 uppercase shrink-0 text-sm">
                                {{ substr($user->name, 0, 2) }}
                            </div>
                            <div class="space-y-0.5 min-w-0">
                                <span class="font-extrabold text-slate-800 block text-sm truncate">{{ $user->name }}</span>
                                <span class="text-[10px] text-slate-400 block truncate">{{ $user->email }}</span>
                            </div>
                        </div>
                        
                        <!-- Status Badge -->
                        <span class="px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-[4px] border shrink-0
                            {{ $user->is_approved ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                            {{ $user->is_approved ? 'Aprovado' : 'Aguardando' }}
                        </span>
                    </div>

                    <!-- Role Dropdown & Date info -->
                    <div class="border-t border-b border-slate-100 py-3 flex items-center justify-between gap-4">
                        <div class="space-y-0.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Função / Nível</span>
                            <form action="{{ route('admin.users.role', $user->id) }}" method="POST" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="role" onchange="this.form.submit()" 
                                        class="px-2 py-1 bg-slate-50 border border-slate-200 rounded text-[10px] font-bold text-slate-700 focus:outline-none focus:ring-1 focus:ring-primary-500">
                                    <option value="comum" {{ $user->role === 'comum' ? 'selected' : '' }}>Comum (Limitado)</option>
                                    <option value="master" {{ $user->role === 'master' ? 'selected' : '' }}>Master (Total)</option>
                                </select>
                            </form>
                        </div>
                        <div class="text-right space-y-0.5">
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Cadastro</span>
                            <span class="text-[10px] text-slate-700 font-bold block">{{ $user->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="flex items-center justify-between gap-3 pt-1">
                        @if($user->is_approved)
                            <form action="{{ route('admin.users.disapprove', $user->id) }}" method="POST" class="inline-block shrink-0">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 text-[10px] font-bold rounded transition-colors shadow-sm">
                                    Suspender Acesso
                                </button>
                            </form>
                        @else
                            <form action="{{ route('admin.users.approve', $user->id) }}" method="POST" class="inline-block shrink-0">
                                @csrf
                                <button type="submit" class="px-3.5 py-1.5 bg-green-50 hover:bg-green-100 text-green-700 border border-green-200 text-[10px] font-bold rounded transition-colors shadow-sm animate-pulse">
                                    Aprovar Acesso
                                </button>
                            </form>
                        @endif

                        <div class="flex items-center gap-1.5 shrink-0">
                            <!-- Editar Usuário -->
                            <button type="button" 
                                    @click="openEditModal({{ json_encode($user) }})"
                                    class="w-8 h-8 flex items-center justify-center border border-slate-200 hover:border-blue-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded transition-colors shrink-0 cursor-pointer"
                                    title="Editar Usuário">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                </svg>
                            </button>

                            <!-- Excluir definitivo -->
                            <button type="button" 
                                    @click="$dispatch('trigger-global-delete', { 
                                        title: 'Excluir Usuário', 
                                        message: 'Tem certeza de que deseja excluir o usuário <strong class=\'text-slate-800\'>{{ addslashes($user->name) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1 block\'>Aviso: Esta ação removerá definitivamente o usuário e todas as informações associadas a ele.</span>', 
                                        action: '{{ route('users.destroy', $user->id) }}', 
                                        highSecurity: true 
                                    })" 
                                    class="w-8 h-8 flex items-center justify-center border border-slate-200 hover:border-red-200 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors shrink-0 cursor-pointer"
                                    title="Excluir Usuário">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                </div>
            @empty
                <div class="col-span-full bg-white border border-slate-200 rounded-[5px] p-8 text-center text-slate-400 italic">
                    Nenhum outro usuário registrado no sistema.
                </div>
            @endforelse
        </div>

        @if($users->hasPages())
            <div class="p-4 bg-white border border-slate-200 rounded-[5px] shadow-sm">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    <!-- Aba 2: Configurações Globais -->
    <div x-show="activeTab === 'system'" class="bg-white border border-slate-200 rounded-[5px] p-6 sm:p-8 space-y-6 shadow-sm" x-cloak>
        <div>
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-3">Controle Geral das Funcionalidades Públicas</h3>
            <p class="text-[10px] text-slate-400 mt-1">Habilite ou desabilite o registro de novas contas públicas ou ative o modo de manutenção do portfólio.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Controle de Cadastro -->
                <div class="border border-slate-150 rounded-[5px] p-5 hover:bg-slate-50/50 transition-colors flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span>🔑</span> Habilitar Cadastro de Usuários
                        </span>
                        <p class="text-[10px] text-slate-400 max-w-sm">Quando ativado, exibe a opção de cadastro de novas contas na tela de login.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="allow_registration" value="1" {{ $settings->allow_registration ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>

                <!-- Controle de Manutenção -->
                <div class="border border-slate-150 rounded-[5px] p-5 hover:bg-slate-50/50 transition-colors flex items-center justify-between gap-4">
                    <div class="space-y-1">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <span>🚧</span> Portfólio em Modo de Manutenção
                        </span>
                        <p class="text-[10px] text-slate-400 max-w-sm">Quando ativo, visitantes do site de portfólio verão uma tela de manutenção elegante.</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="portfolio_maintenance" value="1" {{ $settings->portfolio_maintenance ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                    </label>
                </div>
            </div>

            <!-- Botão de Salvar -->
            <div class="pt-4 border-t border-slate-100 flex justify-end">
                <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm">
                    Salvar Configurações Globais
                </button>
            </div>
        </form>
    </div>

    <!-- MODAL: Criar Usuário -->
    <div x-show="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6" @click.away="closeCreateModal()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Novo Usuário</h3>
                <button @click="closeCreateModal()" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                
                <!-- Nome -->
                <div class="space-y-1">
                    <label for="create_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome</label>
                    <input type="text" name="name" id="create_name" required placeholder="Ex: Maria Souza" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label for="create_email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                    <input type="email" name="email" id="create_email" required placeholder="maria@exemplo.com" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Telefone -->
                <div class="space-y-1">
                    <label for="create_phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone</label>
                    <input type="text" name="phone" id="create_phone" placeholder="Ex: (11) 98888-8888" x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Nível de Acesso -->
                <div class="space-y-1">
                    <label for="create_role" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nível de Acesso</label>
                    <select name="role" id="create_role" required class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                        <option value="comum">Comum (Visualizador)</option>
                        <option value="master">Master (Administrador)</option>
                    </select>
                </div>

                <!-- Senha Inicial -->
                <div class="space-y-1">
                    <label for="create_password" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Senha Inicial</label>
                    <input type="password" name="password" id="create_password" required placeholder="Senha de acesso" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="closeCreateModal()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-[5px] hover:bg-slate-800 transition-colors cursor-pointer">Adicionar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Editar Usuário -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6" @click.away="closeEditModal()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Editar Membro</h3>
                <button @click="closeEditModal()" class="text-slate-400 hover:text-slate-650 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form dinâmico mapeando para a rota de update de ID correspondente -->
            <form :action="editActionUrl" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                
                <!-- Nome -->
                <div class="space-y-1">
                    <label for="edit_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nome</label>
                    <input type="text" name="name" id="edit_name" required x-model="selectedUser.name" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Email -->
                <div class="space-y-1">
                    <label for="edit_email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail</label>
                    <input type="email" name="email" id="edit_email" required x-model="selectedUser.email" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Telefone -->
                <div class="space-y-1">
                    <label for="edit_phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone</label>
                    <input type="text" name="phone" id="edit_phone" x-model="selectedUser.phone" x-mask:dynamic="$input.replace(/\D/g, '').length > 10 ? '(99) 99999-9999' : '(99) 9999-9999'" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                </div>

                <!-- Nível de Acesso -->
                <div class="space-y-1">
                    <label for="edit_role" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Nível de Acesso</label>
                    <select name="role" id="edit_role" required x-model="selectedUser.role" class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-slate-500/20 focus:border-slate-800">
                        <option value="comum">Comum (Visualizador)</option>
                        <option value="master">Master (Administrador)</option>
                    </select>
                </div>

                <div class="pt-4 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button" @click="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-[5px] hover:bg-slate-800 transition-colors cursor-pointer">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
