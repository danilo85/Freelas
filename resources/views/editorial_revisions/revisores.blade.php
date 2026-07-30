@extends('layouts.app')

@section('content')
<div class="space-y-6" x-data="{ 
    openNewModal: false, 
    openEditModal: false, 
    editRevisor: { id: null, name: '', email: '', password: '' },
    toastMessage: '',

    showToast(msg) {
        this.toastMessage = msg;
        setTimeout(() => { this.toastMessage = ''; }, 4000);
    }
}">

    <!-- Toast Notification Banner -->
    <div x-show="toastMessage" 
         x-cloak 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed bottom-6 right-6 z-[99999] bg-slate-900 text-white px-5 py-3 rounded-[5px] shadow-2xl flex items-center gap-3 text-xs font-bold border border-slate-700">
        <span>✨</span>
        <span x-text="toastMessage"></span>
        <button type="button" @click="toastMessage = ''" class="text-slate-400 hover:text-white ml-2">✕</button>
    </div>

    <!-- Banner Superior com Título -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 rounded-[5px] shadow-sm">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <span class="text-2xl">👥</span>
                <h2 class="text-xl font-black font-outfit text-slate-800 dark:text-slate-100 uppercase tracking-tight">Gerenciamento de Revisores</h2>
            </div>
            <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                Cadastre revisores profissionais, gerencie seus logins de acesso, e-mails e senhas para os trabalhos editoriais.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('revisoes-editoriais.index') }}" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-600 dark:text-slate-300 text-xs font-bold rounded-[5px] transition-colors uppercase tracking-wider">
                ← Voltar às Revisões
            </a>

            <button type="button" @click="openNewModal = true" class="px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold rounded-[5px] transition-all shadow-md flex items-center gap-2 cursor-pointer uppercase tracking-wider">
                <span>➕</span> Novo Revisor
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-[5px] text-xs font-bold shadow-xs">
            ✅ {{ session('success') }}
        </div>
    @endif

    <!-- Tabela de Revisores Cadastrados -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] shadow-sm overflow-hidden">
        <table class="w-full text-left text-xs text-slate-600 dark:text-slate-300">
            <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800 text-[10px] uppercase font-black tracking-wider text-slate-400">
                <tr>
                    <th class="py-3.5 px-4">Nome do Revisor</th>
                    <th class="py-3.5 px-4">E-mail de Acesso</th>
                    <th class="py-3.5 px-4 text-center">Projetos Atribuídos</th>
                    <th class="py-3.5 px-4 text-center">Data de Cadastro</th>
                    <th class="py-3.5 px-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800 font-medium">
                @forelse($revisores as $rev)
                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/30 transition-colors">
                        <td class="py-4 px-4 font-bold text-slate-800 dark:text-slate-100">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-700 font-black flex items-center justify-center text-xs shrink-0">
                                    {{ mb_substr($rev->name, 0, 1) }}
                                </div>
                                <span>{{ $rev->name }}</span>
                            </div>
                        </td>
                        <td class="py-4 px-4 font-mono text-slate-600 dark:text-slate-300">
                            {{ $rev->email }}
                        </td>
                        <td class="py-4 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 font-bold text-slate-700 dark:text-slate-300 text-[11px]">
                                📚 {{ $rev->revisions_as_revisor_count }}
                            </span>
                        </td>
                        <td class="py-4 px-4 text-center text-slate-400 text-[11px]">
                            {{ $rev->created_at ? $rev->created_at->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-4 px-4 text-right space-x-2">
                            <button type="button" 
                                    @click="editRevisor = { id: {{ $rev->id }}, name: '{{ addslashes($rev->name) }}', email: '{{ addslashes($rev->email) }}', password: '' }; openEditModal = true" 
                                    class="px-3 py-1.5 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 text-slate-700 dark:text-slate-200 rounded-[5px] font-bold transition-colors">
                                ✏️ Editar
                            </button>

                            <form action="{{ route('revisoes-editoriais.revisores.destroy', $rev->id) }}" method="POST" class="inline" onsubmit="return confirm('Deseja realmente excluir a conta deste revisor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-[5px] font-bold transition-colors">
                                    🗑️ Excluir
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-400 font-semibold">
                            Nenhum revisor cadastrado até o momento. Clique em "➕ Novo Revisor" acima para cadastrar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Modal Novo Revisor -->
    <div x-show="openNewModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openNewModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase tracking-tight">➕ Cadastrar Novo Revisor</h3>
                <button type="button" @click="openNewModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form action="{{ route('revisoes-editoriais.revisores.store') }}" method="POST" class="space-y-3 text-xs">
                @csrf
                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nome Completo</label>
                    <input type="text" name="name" required placeholder="Ex: Ana Maria Silva" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">E-mail para Login</label>
                    <input type="email" name="email" required placeholder="revisora@exemplo.com" class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Senha de Acesso</label>
                    <input type="text" name="password" required placeholder="Defina a senha inicial..." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openNewModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Salvar Revisor</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar Revisor -->
    <div x-show="openEditModal" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs select-none">
        <div @click.away="openEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-800 dark:text-slate-100 rounded-xl p-6 shadow-2xl max-w-md w-full space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-md uppercase tracking-tight">✏️ Editar Dados do Revisor</h3>
                <button type="button" @click="openEditModal = false" class="text-slate-400 hover:text-slate-600">✕</button>
            </div>

            <form :action="'{{ url('/freelas/utilidades/revisao-editorial/gerenciamento/revisores') }}/' + editRevisor.id" method="POST" class="space-y-3 text-xs">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nome Completo</label>
                    <input type="text" name="name" x-model="editRevisor.name" required class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">E-mail para Login</label>
                    <input type="email" name="email" x-model="editRevisor.email" required class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nova Senha (deixe em branco para não alterar)</label>
                    <input type="password" name="password" placeholder="Digite uma nova senha..." class="w-full px-3 py-2 border rounded-[5px] bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100">
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <button type="button" @click="openEditModal = false" class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-bold rounded-[5px]">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white font-bold rounded-[5px]">Atualizar Revisor</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
