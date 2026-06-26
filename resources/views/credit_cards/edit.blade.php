@extends('layouts.app')

@section('title', 'Editar Cartão de Crédito - Gestor de Freelas')
@section('page_title', 'Editar Cartão de Crédito')

@section('content')
@php
    $knownBanks = ["Nubank", "Itaú", "Banco do Brasil", "Bradesco", "Santander", "Caixa", "Inter", "C6 Bank", "Neon", "Stone", "PagBank", "Mercado Pago"];
    $isCustomBank = !in_array($creditCard->bank_name, $knownBanks);
@endphp
<div x-data="creditCardForm()" class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('bank-accounts.index', ['tab' => 'cards']) }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Carteira
        </a>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
        
        <!-- Formulário de Edição -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 lg:col-span-2">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Editar Cartão de Crédito</h2>
                <p class="text-xs text-slate-400 mt-1">Atualize os limites, dias de vencimento/fechamento ou observações deste cartão.</p>
            </div>

            <form action="{{ route('credit-cards.update', $creditCard->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Banco -->
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
                            <p class="text-xs text-red-650 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nome Identificador do Cartão -->
                <div class="space-y-1.5">
                    <label for="card_name" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Nome Identificador do Cartão *</label>
                    <input 
                        type="text" 
                        name="card_name" 
                        id="card_name" 
                        required 
                        x-model="card_name"
                        placeholder="Ex: Nubank Ultra Violeta, Itaú Uniclass Black" 
                        class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                    />
                    @error('card_name')
                        <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Limite, Bandeira & Final -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Limite de Crédito -->
                    <div class="space-y-1.5">
                        <label for="limit" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Limite de Crédito *</label>
                        <input 
                            type="text" 
                            name="limit" 
                            id="limit" 
                            required 
                            x-model="limit"
                            @input="limit = formatMoney($event.target.value)"
                            placeholder="R$ 0,00"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-emerald-600"
                        />
                        @error('limit')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Bandeira -->
                    <div class="space-y-1.5">
                        <label for="flag" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Bandeira *</label>
                        <select 
                            name="flag" 
                            id="flag" 
                            required 
                            x-model="flag"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="visa">Visa</option>
                            <option value="mastercard">Mastercard</option>
                            <option value="elo">Elo</option>
                            <option value="amex">American Express</option>
                            <option value="hipercard">Hipercard</option>
                            <option value="outros">Outros</option>
                        </select>
                        @error('flag')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Últimos 4 Dígitos -->
                    <div class="space-y-1.5">
                        <label for="last_four_digits" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Últimos 4 Dígitos</label>
                        <input 
                            type="text" 
                            name="last_four_digits" 
                            id="last_four_digits" 
                            x-model="last_four_digits"
                            maxlength="4"
                            placeholder="Ex: 1234"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('last_four_digits')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Dias de Fechamento e Vencimento -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Dia de Fechamento -->
                    <div class="space-y-1.5">
                        <label for="closing_day" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Dia de Fechamento da Fatura *</label>
                        <input 
                            type="number" 
                            name="closing_day" 
                            id="closing_day" 
                            required 
                            min="1" 
                            max="31"
                            x-model="closing_day"
                            placeholder="Ex: 5"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('closing_day')
                            <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Dia de Vencimento -->
                    <div class="space-y-1.5">
                        <label for="due_day" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Dia de Vencimento da Fatura *</label>
                        <input 
                            type="number" 
                            name="due_day" 
                            id="due_day" 
                            required 
                            min="1" 
                            max="31"
                            x-model="due_day"
                            placeholder="Ex: 12"
                            class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                        @error('due_day')
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
                        placeholder="Adicione informações adicionais..."
                        class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-medium text-slate-700"
                    ></textarea>
                    @error('observations')
                        <p class="text-xs text-red-600 font-bold mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Botões de Ação -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                    <a href="{{ route('bank-accounts.index', ['tab' => 'cards']) }}" class="px-5 py-2.5 rounded-[5px] text-sm font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2.5 rounded-[5px] bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary-500/20">
                        Salvar Alterações
                    </button>
                </div>

            </form>
        </div>

        <!-- Preview do Cartão em Tempo Real -->
        <div class="sticky top-6 space-y-4">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Visualização do Cartão</span>
            
            <!-- Cartão Mockup Premium -->
            <div class="w-full rounded-[10px] bg-gradient-to-br p-6 shadow-md flex flex-col justify-between relative overflow-hidden min-h-[220px] transition-all duration-300"
                 :class="brandStyle.bg + ' ' + brandStyle.text">
                
                <!-- Logo do Banco -->
                <div class="absolute top-6 right-6 font-mono font-black text-lg opacity-40 uppercase tracking-widest" x-text="brandStyle.icon"></div>
                
                <!-- Topo: Chip & Bandeira -->
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-9 h-7 rounded-[4px] bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-500 opacity-80 shadow-sm"></div>
                        <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px]" :class="brandStyle.flag_badge" x-text="brandStyle.flag_label"></span>
                    </div>
                    
                    <!-- Nome do Cartão -->
                    <div class="mt-2">
                        <h4 class="font-bold text-base truncate tracking-wide uppercase min-h-[24px]" x-text="card_name || 'NOME DO CARTÃO'"></h4>
                        <p class="text-xs opacity-75 font-semibold mt-0.5 tracking-wider" x-text="bank_name === 'Outro' ? (custom_bank_name || 'BANCO CUSTOMIZADO') : bank_name"></p>
                    </div>
                </div>

                <!-- Datas de Fechamento / Vencimento e Dígitos -->
                <div class="mt-4 text-xs font-mono font-bold tracking-wider opacity-90 flex justify-between items-end">
                    <div>
                        <p>Fechamento: Dia <span x-text="closing_day || '--'"></span></p>
                        <p class="mt-0.5">Vencimento: Dia <span x-text="due_day || '--'"></span></p>
                    </div>
                    <span class="text-sm font-mono tracking-widest font-black opacity-80" x-text="'•••• ' + (last_four_digits || '••••')"></span>
                </div>

                <!-- Rodapé: Limite -->
                <div class="mt-6 pt-3 border-t border-white/20">
                    <p class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Limite de Crédito</p>
                    <p class="text-xl font-black mt-0.5 tracking-tight" x-text="limit || 'R$ 0,00'"></p>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    function creditCardForm() {
        return {
            bank_name: '{{ $isCustomBank ? 'Outro' : $creditCard->bank_name }}',
            custom_bank_name: '{{ $isCustomBank ? $creditCard->bank_name : '' }}',
            card_name: '{{ old('card_name', $creditCard->card_name) }}',
            limit: 'R$ {{ number_format($creditCard->limit, 2, ',', '.') }}',
            closing_day: '{{ old('closing_day', $creditCard->closing_day) }}',
            due_day: '{{ old('due_day', $creditCard->due_day) }}',
            flag: '{{ old('flag', $creditCard->flag) }}',
            last_four_digits: '{{ old('last_four_digits', $creditCard->last_four_digits) }}',
            observations: {!! json_encode(old('observations', $creditCard->observations)) !!},

            get brandStyle() {
                let name = this.bank_name;
                if (name === 'Outro') {
                    name = this.custom_bank_name;
                }
                name = (name || '').toLowerCase();

                let style = {
                    bg: 'from-slate-700 to-slate-900',
                    text: 'text-white',
                    badge: 'bg-slate-800/40 text-slate-350',
                    icon: 'Banco'
                };

                if (name.includes('itau') || name.includes('itaú')) {
                    style = {
                        bg: 'from-orange-500 to-amber-600',
                        text: 'text-white',
                        badge: 'bg-blue-900/40 text-blue-100',
                        icon: 'Itaú'
                    };
                } else if (name.includes('nubank')) {
                    style = {
                        bg: 'from-purple-800 to-indigo-950',
                        text: 'text-white',
                        badge: 'bg-purple-900/40 text-purple-100',
                        icon: 'Nu'
                    };
                } else if (name.includes('bradesco')) {
                    style = {
                        bg: 'from-red-600 to-rose-800',
                        text: 'text-white',
                        badge: 'bg-red-900/40 text-red-100',
                        icon: 'Bradesco'
                    };
                } else if (name.includes('santander')) {
                    style = {
                        bg: 'from-red-700 to-red-900',
                        text: 'text-white',
                        badge: 'bg-red-950/40 text-red-100',
                        icon: 'Santander'
                    };
                } else if (name.includes('caixa')) {
                    style = {
                        bg: 'from-blue-700 to-sky-900',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-blue-100',
                        icon: 'Caixa'
                    };
                } else if (name.includes('banco do brasil') || name.includes('bb')) {
                    style = {
                        bg: 'from-yellow-500 via-amber-600 to-blue-800',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-yellow-100',
                        icon: 'BB'
                    };
                } else if (name.includes('inter')) {
                    style = {
                        bg: 'from-orange-500 to-orange-700',
                        text: 'text-white',
                        badge: 'bg-orange-950/40 text-orange-100',
                        icon: 'Inter'
                    };
                } else if (name.includes('c6')) {
                    style = {
                        bg: 'from-neutral-800 to-zinc-950',
                        text: 'text-white',
                        badge: 'bg-zinc-800/40 text-zinc-300',
                        icon: 'C6 Bank'
                    };
                } else if (name.includes('neon')) {
                    style = {
                        bg: 'from-cyan-400 to-blue-600',
                        text: 'text-white',
                        badge: 'bg-blue-900/40 text-cyan-100',
                        icon: 'Neon'
                    };
                } else if (name.includes('stone')) {
                    style = {
                        bg: 'from-emerald-600 to-green-800',
                        text: 'text-white',
                        badge: 'bg-emerald-950/40 text-emerald-100',
                        icon: 'Stone'
                    };
                } else if (name.includes('pagbank') || name.includes('pagseguro')) {
                    style = {
                        bg: 'from-lime-600 to-emerald-700',
                        text: 'text-white',
                        badge: 'bg-emerald-950/40 text-lime-100',
                        icon: 'PagBank'
                    };
                } else if (name.includes('mercado pago')) {
                    style = {
                        bg: 'from-sky-400 to-blue-600',
                        text: 'text-white',
                        badge: 'bg-blue-950/40 text-sky-100',
                        icon: 'Mercado Pago'
                    };
                }

                // Add flag details
                let flg = this.flag.toLowerCase();
                style.flag_label = flg === 'outros' ? 'Card' : flg.charAt(0).toUpperCase() + flg.slice(1);
                
                switch (flg) {
                    case 'visa':
                        style.flag_badge = 'bg-blue-600 text-white';
                        break;
                    case 'mastercard':
                        style.flag_badge = 'bg-orange-500 text-white';
                        break;
                    case 'elo':
                        style.flag_badge = 'bg-yellow-500 text-slate-900';
                        break;
                    case 'amex':
                        style.flag_badge = 'bg-cyan-600 text-white';
                        break;
                    case 'hipercard':
                        style.flag_badge = 'bg-red-600 text-white';
                        break;
                    default:
                        style.flag_badge = 'bg-slate-500 text-white';
                        break;
                }

                return style;
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
