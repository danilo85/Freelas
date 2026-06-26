@extends('layouts.app')

@section('title', 'Gerenciar Usuários - Gestor de Freelas')
@section('page_title', 'Gerenciamento de Usuários')

@section('content')
<div x-data="userManagement()" class="space-y-6">
    
    <!-- Cabeçalho -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-xl font-bold text-slate-900">Membros da Equipe</h2>
            <p class="text-sm text-slate-400 mt-0.5">Adicione, edite ou remova contas de acesso ao painel.</p>
        </div>
    </div>

    <!-- Lista de Membros da Equipe (Em Cards Responsivos no lugar de Tabela) -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
        @foreach($users as $user)
            <div class="bg-white border border-slate-200 p-5 rounded-[5px] shadow-sm flex flex-col justify-between space-y-4">
                
                <!-- Topo do Card (Foto + Nome) -->
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                        @if($user->avatar)
                            <img src="{{ asset('storage/' . $user->avatar) }}" class="w-full h-full object-cover">
                        @else
                            <span class="text-xs font-bold text-slate-500">
                                {{ collect(explode(' ', $user->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                            </span>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <h4 class="font-bold text-slate-900 truncate" title="{{ $user->name }}">{{ $user->name }}</h4>
                            @if($user->id === auth()->id())
                                <span class="text-[9px] font-medium bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded-[5px] shrink-0">Você</span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 truncate" title="{{ $user->email }}">{{ $user->email }}</p>
                    </div>
                </div>

                <!-- Detalhes da Conta -->
                <div class="space-y-2 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    <div class="flex justify-between">
                        <span>WhatsApp / Telefone:</span>
                        <span class="text-slate-900 font-medium truncate max-w-[150px]">{{ $user->phone ?? 'Sem telefone' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Nível de Acesso:</span>
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-[5px] text-[10px] font-medium 
                            @if($user->role === 'master') bg-purple-50 text-purple-700 border border-purple-100
                            @else bg-slate-100 text-slate-700 border border-slate-150 @endif">
                            {{ $user->role === 'master' ? 'Administrador Master' : 'Membro Comum' }}
                        </span>
                    </div>
                    <div class="flex justify-between text-[10px] text-slate-400 pt-1">
                        <span>Data de Registro:</span>
                        <span>{{ $user->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <!-- Ações -->
                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button @click="openEditModal({{ json_encode($user) }})" class="w-8 h-8 flex items-center justify-center bg-white text-primary-600 hover:text-primary-700 rounded-[5px] shadow-sm hover:bg-primary-50 transition-all" title="Editar Usuário">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                        </svg>
                    </button>

                    @if($user->id !== auth()->id())
                        <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Ação de Alta Segurança', message: 'Você está prestes a excluir permanentemente o membro da equipe <strong class=\'text-slate-850\'>{{ addslashes($user->name) }}</strong>. Esta ação não poderá ser desfeita e ele perderá todo o acesso ao painel.', action: '{{ route('users.destroy', $user->id) }}', highSecurity: true })" class="w-8 h-8 flex items-center justify-center bg-white text-red-600 hover:text-red-700 rounded-[5px] shadow-sm hover:bg-red-50 transition-all" title="Excluir Usuário">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    @endif
                </div>

            </div>
        @endforeach
    </div>

    <!-- MODAL: Criar Usuário -->
    <div x-show="createModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6" @click.away="closeCreateModal()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Novo Usuário</h3>
                <button @click="closeCreateModal()" class="text-slate-400 hover:text-slate-600">
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
                    <button type="button" @click="closeCreateModal()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-[5px] hover:bg-slate-800 transition-colors">Adicionar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Editar Usuário -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm" x-cloak>
        <div class="bg-white rounded-[5px] w-full max-w-md border border-slate-200 shadow-2xl p-6 space-y-6" @click.away="closeEditModal()">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-900">Editar Membro</h3>
                <button @click="closeEditModal()" class="text-slate-400 hover:text-slate-600">
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
                    <button type="button" @click="closeEditModal()" class="px-4 py-2 border border-slate-200 text-slate-500 text-xs font-semibold rounded-[5px] hover:bg-slate-50 transition-colors">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-[5px] hover:bg-slate-800 transition-colors">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Botão Flutuante Redondo (FAB) -->
    <button @click="openCreateModal()" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" title="Novo Usuário">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </button>

</div>

<script>
    function userManagement() {
        return {
            createModalOpen: false,
            editModalOpen: false,
            selectedUser: {},
            editActionUrl: '',

            openCreateModal() {
                this.createModalOpen = true;
            },

            closeCreateModal() {
                this.createModalOpen = false;
            },

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
        }
    }
</script>
@endsection
