@extends('layouts.app')

@section('title', 'Configurações de Administração - Gestor de Freelas')
@section('page_title', 'Administração do Sistema')

@section('content')
<div x-data="{ activeTab: 'users' }" class="space-y-8">
    
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
        <div class="bg-white border border-slate-200 p-6 rounded-[5px] shadow-sm">
            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Controle de Cadastros e Funções</h3>
            <p class="text-[10px] text-slate-450 mt-1">Aprove ou suspenda o acesso de usuários ao painel administrativo e gerencie quem é Master ou Comum.</p>
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

                        <!-- Excluir definitivo -->
                        <button type="button" 
                                @click="$dispatch('trigger-global-delete', { 
                                    title: 'Excluir Usuário', 
                                    message: 'Tem certeza de que deseja excluir o usuário <strong class=\'text-slate-800\'>{{ addslashes($user->name) }}</strong>?<br><span class=\'text-xs text-red-500 mt-1 block\'>Aviso: Esta ação removerá definitivamente o usuário e todas as informações associadas a ele.</span>', 
                                    action: '{{ route('users.destroy', $user->id) }}', 
                                    highSecurity: true 
                                })" 
                                class="w-8 h-8 flex items-center justify-center border border-slate-200 hover:border-red-200 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded transition-colors shrink-0"
                                title="Excluir Usuário">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
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

</div>
@endsection
