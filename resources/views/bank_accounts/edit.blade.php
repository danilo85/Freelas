@extends('layouts.app')

@section('title', 'Editar Conta Bancária - Gestor de Freelas')
@section('page_title', 'Editar Conta Bancária')

@section('content')
@php
    $knownBanks = ["Nubank", "Itaú", "Banco do Brasil", "Bradesco", "Santander", "Caixa", "Inter", "C6 Bank", "Neon", "Stone", "PagBank", "Mercado Pago"];
    $isCustomBank = !in_array($bankAccount->bank_name, $knownBanks);
@endphp
<div x-data="bankAccountForm()" class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('bank-accounts.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Formulário de Edição -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 lg:col-span-2">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Editar Conta Bancária</h2>
                <p class="text-xs text-slate-400 mt-1">Atualize as informações de identificação, saldo inicial ou observações desta conta bancária.</p>
            </div>

            <form action="{{ route('bank-accounts.update', $bankAccount->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Banco & Custom Bank -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="bank_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Banco *</label>
                        <select 
                            name="bank_name" 
                            id="bank_name" 
                            required 
                            x-model="bank_name"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="Nubank">Nubank</option>
                            <option value="Itaú">Itaú Unibanco</option>
                            <option value="Banco do Brasil">Banco do Brasil</option>
                            <option value="Bradesco">Bradesco</option>
                            <option value="Santander">Santander</option>
                            <option value="Caixa">Caixa Econômica</option>
                            <option value="Inter">Banco Inter</option>
                            <option value="C6 Bank">C6 Bank</option>
                            <option value="Neon">Neon</option>
                            <option value="Stone">Stone</option>
                            <option value="PagBank">PagBank</option>
                            <option value="Mercado Pago">Mercado Pago</option>
                            <option value="Outro">Outro (Customizado)</option>
                        </select>
                        @error('bank_name')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Input customizado de banco se for Outro -->
                    <div class="space-y-1.5" x-show="bank_name === 'Outro'" x-cloak x-transition>
                        <label for="custom_bank_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Nome do Banco Customizado *</label>
                        <input 
                            type="text" 
                            name="custom_bank_name" 
                            id="custom_bank_name"
                            x-model="custom_bank_name"
                            :required="bank_name === 'Outro'"
                            placeholder="Ex: Cresol, Banco Pan, etc."
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('custom_bank_name')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nome Identificador da Conta -->
                <div class="space-y-1.5">
                    <label for="account_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Nome Identificador da Conta *</label>
                    <input 
                        type="text" 
                        name="account_name" 
                        id="account_name" 
                        required 
                        x-model="account_name"
                        placeholder="Ex: Conta Corrente PJ, Poupança Reserva, Pix Pessoal" 
                        class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                    />
                    @error('account_name')
                        <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tipo de Conta, Titularidade & Saldo Inicial -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label for="account_type" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Tipo de Conta *</label>
                        <select 
                            name="account_type" 
                            id="account_type" 
                            required 
                            x-model="account_type"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="digital">Conta Digital</option>
                            <option value="corrente">Conta Corrente</option>
                            <option value="poupanca">Conta Poupança</option>
                            <option value="investimento">Conta Investimento</option>
                            <option value="outros">Outros</option>
                        </select>
                        @error('account_type')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="person_type" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Pessoa (Titularidade) *</label>
                        <select 
                            name="person_type" 
                            id="person_type" 
                            required 
                            x-model="person_type"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="PJ">Pessoa Jurídica (PJ)</option>
                            <option value="PF">Pessoa Física (PF)</option>
                        </select>
                        @error('person_type')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="initial_balance" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Saldo Inicial *</label>
                        <input 
                            type="text" 
                            name="initial_balance" 
                            id="initial_balance" 
                            required 
                            x-model="initial_balance"
                            @input="initial_balance = formatMoney($event.target.value)"
                            placeholder="R$ 0,00"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-emerald-600"
                        />
                        @error('initial_balance')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Agência e Conta -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="agency" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Número da Agência</label>
                        <input 
                            type="text" 
                            name="agency" 
                            id="agency" 
                            x-model="agency"
                            placeholder="Ex: 0001"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('agency')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="space-y-1.5">
                        <label for="account_number" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Número da Conta</label>
                        <input 
                            type="text" 
                            name="account_number" 
                            id="account_number" 
                            x-model="account_number"
                            placeholder="Ex: 12345-6"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('account_number')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Observações -->
                <div class="space-y-1.5">
                    <label for="observations" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Observações</label>
                    <textarea 
                        name="observations" 
                        id="observations" 
                        rows="3" 
                        x-model="observations"
                        placeholder="Adicione informações internas adicionais sobre esta conta..."
                        class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-medium text-slate-700"
                    ></textarea>
                    @error('observations')
                        <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('bank-accounts.index') }}" class="px-5 py-2.5 rounded-[5px] text-sm font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-[5px] bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary-500/20">
                        Salvar Alterações
                    </button>
                </div>

            </form>
        </div>

        <!-- Preview do Cartão Fintech em Tempo Real -->
        <div class="sticky top-6 space-y-4">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Visualização do Cartão</span>
            <!-- Cartão Fintech -->
            <div class="w-full rounded-[10px] bg-gradient-to-br p-6 shadow-md flex flex-col justify-between relative overflow-hidden min-h-[220px] transition-all duration-300"
                 :class="brandStyle.bg + ' ' + brandStyle.text">
                
                <!-- Logo do Banco -->
                <div class="absolute top-6 right-6 font-mono font-black text-lg opacity-40 uppercase tracking-widest" x-text="brandStyle.icon"></div>
                
                <!-- Topo: Chip & Tipo de Pessoa -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-9 h-7 rounded-[4px] bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-500 opacity-80 shadow-sm"></div>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px]" :class="brandStyle.badge" x-text="person_type"></span>
                    </div>
                    
                    <!-- Nome da Conta -->
                    <div class="mt-2">
                        <h4 class="font-bold text-base truncate tracking-wide uppercase min-h-[24px]" x-text="account_name || 'NOME DA CONTA'"></h4>
                        <p class="text-xs opacity-75 font-semibold mt-0.5 tracking-wider" x-text="bank_name === 'Outro' ? (custom_bank_name || 'BANCO CUSTOMIZADO') : bank_name"></p>
                    </div>
                </div>

                <!-- Agência e Conta -->
                <div class="mt-4 text-xs font-mono font-bold tracking-wider opacity-90 flex justify-between items-end">
                    <div>
                        <p>Ag: <span x-text="agency || '0000'"></span></p>
                        <p class="mt-0.5">Cc: <span x-text="account_number || '00000-0'"></span></p>
                    </div>
                    <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px]" :class="brandStyle.badge">
                        <span x-show="account_type === 'corrente'">Corrente</span>
                        <span x-show="account_type === 'poupanca'">Poupança</span>
                        <span x-show="account_type === 'digital'">Digital</span>
                        <span x-show="account_type === 'investimento'">Investimento</span>
                        <span x-show="account_type === 'outros'">Outros</span>
                    </span>
                </div>

                <!-- Rodapé: Saldo Inicial -->
                <div class="mt-6 pt-3 border-t border-white/20">
                    <p class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Saldo Inicial</p>
                    <p class="text-xl font-black mt-0.5 tracking-tight" :class="brandStyle.accent" x-text="initial_balance || 'R$ 0,00'"></p>
                </div>
            </div>

            <!-- Dica -->
            <div class="bg-blue-50 border border-blue-200 rounded-[5px] p-4 text-xs text-blue-800 leading-relaxed">
                <strong>💡 Dica Visual:</strong> Conforme você escolhe ou altera o nome do banco, as cores e o layout do cartão mudam em tempo real de acordo com a identidade visual da instituição!
            </div>
        </div>

    </div>
</div>

<script>
    function bankAccountForm() {
        return {
            bank_name: '{{ $isCustomBank ? "Outro" : $bankAccount->bank_name }}',
            custom_bank_name: '{{ $isCustomBank ? $bankAccount->bank_name : "" }}',
            account_name: '{{ $bankAccount->account_name }}',
            account_type: '{{ $bankAccount->account_type }}',
            person_type: '{{ $bankAccount->person_type }}',
            agency: '{{ $bankAccount->agency }}',
            account_number: '{{ $bankAccount->account_number }}',
            initial_balance: 'R$ {{ number_format($bankAccount->initial_balance, 2, ",", ".") }}',
            observations: '{{ preg_replace("/\r?\n/", "\\n", addslashes($bankAccount->observations)) }}',

            get brandStyle() {
                let name = this.bank_name;
                if (name === 'Outro') {
                    name = this.custom_bank_name;
                }
                name = (name || '').toLowerCase();

                if (name.includes('itau') || name.includes('itaú')) {
                    return {
                        bg: 'from-orange-500 to-amber-600',
                        text: 'text-white',
                        badge: 'bg-blue-900/40 text-blue-100',
                        accent: 'text-yellow-350',
                        icon: 'Itaú'
                    };
                } else if (name.includes('nubank')) {
                    return {
                        bg: 'from-purple-800 to-indigo-950',
                        text: 'text-white',
                        badge: 'bg-purple-900/40 text-purple-100',
                        accent: 'text-fuchsia-400',
                        icon: 'Nu'
                    };
                } else if (name.includes('bradesco')) {
                    return {
                        bg: 'from-red-600 to-rose-800',
                        text: 'text-white',
                        badge: 'bg-red-900/40 text-red-100',
                        accent: 'text-slate-200',
                        icon: 'Bradesco'
                    };
                } else if (name.includes('santander')) {
                    return {
                        bg: 'from-red-700 to-red-900',
                        text: 'text-white',
                        badge: 'bg-red-950/40 text-red-100',
                        accent: 'text-slate-100',
                        icon: 'Santander'
                    };
                } else if (name.includes('caixa')) {
                    return {
                        bg: 'from-blue-700 to-sky-900',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-blue-100',
                        accent: 'text-orange-400',
                        icon: 'Caixa'
                    };
                } else if (name.includes('banco do brasil') || name.includes('bb')) {
                    return {
                        bg: 'from-yellow-500 via-amber-600 to-blue-800',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-yellow-100',
                        accent: 'text-yellow-300',
                        icon: 'BB'
                    };
                } else if (name.includes('inter')) {
                    return {
                        bg: 'from-orange-500 to-orange-700',
                        text: 'text-white',
                        badge: 'bg-orange-950/40 text-orange-100',
                        accent: 'text-white',
                        icon: 'Inter'
                    };
                } else if (name.includes('c6')) {
                    return {
                        bg: 'from-neutral-800 to-zinc-950',
                        text: 'text-white',
                        badge: 'bg-zinc-800/40 text-zinc-300',
                        accent: 'text-yellow-500',
                        icon: 'C6 Bank'
                    };
                } else if (name.includes('neon')) {
                    return {
                        bg: 'from-cyan-400 to-blue-600',
                        text: 'text-white',
                        badge: 'bg-blue-900/40 text-cyan-100',
                        accent: 'text-white',
                        icon: 'Neon'
                    };
                } else if (name.includes('stone')) {
                    return {
                        bg: 'from-emerald-600 to-green-800',
                        text: 'text-white',
                        badge: 'bg-emerald-950/40 text-emerald-100',
                        accent: 'text-emerald-300',
                        icon: 'Stone'
                    };
                } else if (name.includes('pagbank') || name.includes('pagseguro')) {
                    return {
                        bg: 'from-lime-600 to-emerald-700',
                        text: 'text-white',
                        badge: 'bg-emerald-950/40 text-lime-100',
                        accent: 'text-yellow-300',
                        icon: 'PagBank'
                    };
                } else if (name.includes('mercado pago')) {
                    return {
                        bg: 'from-sky-400 to-blue-600',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-sky-100',
                        accent: 'text-white',
                        icon: 'Mercado Pago'
                    };
                }

                return {
                    bg: 'from-slate-700 to-slate-900',
                    text: 'text-white',
                    badge: 'bg-slate-800/40 text-slate-350',
                    accent: 'text-emerald-400',
                    icon: 'Banco'
                };
            },

            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
            }
        };
    }
</script>
@endsection
