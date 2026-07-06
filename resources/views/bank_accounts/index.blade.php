@extends('layouts.app')

@section('title', 'Carteira (Contas e Cartões) - Gestor de Freelas')
@section('page_title', 'Minha Carteira')

@section('content')
<div x-data="bankAccountList()" class="space-y-8">

    <!-- Modal de Transferência de Lucros -->
    <div x-show="openTransferModal" 
         class="fixed inset-0 flex items-center justify-center bg-slate-950/75 backdrop-blur-md"
         style="z-index: 99999;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl rounded-lg max-w-md w-full mx-4 overflow-hidden flex flex-col relative"
             @click.away="openTransferModal = false">
            
            <div class="p-5 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between shrink-0 bg-slate-50 dark:bg-slate-900/50">
                <div>
                    <h3 class="font-outfit font-black text-slate-800 dark:text-slate-100 text-sm uppercase tracking-tight">Transferência de Lucros</h3>
                    <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">PJ para PF (Neutralidade Fiscal)</p>
                </div>
                <button type="button" @click="openTransferModal = false" class="text-slate-400 hover:text-slate-700 dark:hover:text-slate-200 font-black text-sm p-1.5 shrink-0 cursor-pointer">✕</button>
            </div>

            <form action="{{ route('finances.transfer') }}" method="POST" class="p-5 space-y-4">
                @csrf

                <!-- Conta de Origem (PJ) -->
                <div class="space-y-1">
                    <label for="from_bank_account_id" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Conta de Origem (PJ / Saída)</label>
                    <select name="from_bank_account_id" id="from_bank_account_id" required class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none">
                        <option value="">Selecione a conta...</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                [{{ strtoupper($acc->person_type) ?: 'PJ' }}] {{ $acc->bank_name }} - {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Conta de Destino (PF) -->
                <div class="space-y-1">
                    <label for="to_bank_account_id" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Conta de Destino (PF / Entrada)</label>
                    <select name="to_bank_account_id" id="to_bank_account_id" required class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none">
                        <option value="">Selecione a conta...</option>
                        @foreach($bankAccounts as $acc)
                            <option value="{{ $acc->id }}">
                                [{{ strtoupper($acc->person_type) ?: 'PF' }}] {{ $acc->bank_name }} - {{ $acc->account_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Valor -->
                <div class="space-y-1">
                    <label for="amount" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Valor da Transferência</label>
                    <input type="number" name="amount" id="amount" step="0.01" min="0.01" placeholder="0,00" required class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none" />
                </div>

                <!-- Data -->
                <div class="space-y-1">
                    <label for="date" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Data da Transferência</label>
                    <input type="date" name="date" id="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none" />
                </div>

                <!-- Descrição -->
                <div class="space-y-1">
                    <label for="description" class="text-[10px] font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider block font-semibold">Descrição (Opcional)</label>
                    <input type="text" name="description" id="description" placeholder="Transferência de Lucros" class="w-full px-3 py-2 rounded-[5px] border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-750 dark:text-slate-200 bg-white dark:bg-slate-900 focus:outline-none" />
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="openTransferModal = false" class="px-4 py-2 bg-slate-200 dark:bg-slate-800 hover:bg-slate-350 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 font-bold text-xs rounded-[5px] uppercase tracking-wider">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs rounded-[5px] shadow transition-colors uppercase tracking-wider">Transferir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Header da Página -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-800 dark:text-slate-100 font-outfit">Minha Carteira</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">Gerencie suas contas bancárias, cartões de crédito e transferências.</p>
        </div>
        <div class="flex items-center gap-2">
            <button 
                type="button" 
                @click="openTransferModal = true"
                class="px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-[5px] uppercase tracking-wider transition-colors shadow-sm cursor-pointer flex items-center gap-1.5"
            >
                🔄 Transferir Lucros
            </button>
        </div>
    </div>

    <!-- Top Cards (Resumo Financeiro da Carteira) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Saldo Total em Contas (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Saldo em Contas</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    R$ {{ number_format($totalCombinedBalance, 2, ',', '.') }}
                </h3>
                <span class="text-sm text-emerald-100/90 font-medium block mt-1.5">
                    Total disponível nas suas contas bancárias
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Limite Disponível em Cartão (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Limite Disponível</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    R$ {{ number_format($totalAvailableLimit, 2, ',', '.') }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Crédito disponível nos seus cartões
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
            </div>
        </div>

        <!-- Crédito Utilizado (Card Cinza) -->
        <div class="bg-slate-700 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-sm font-bold text-slate-200 uppercase tracking-wider">Crédito Utilizado</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    R$ {{ number_format($totalUsedLimit, 2, ',', '.') }}
                </h3>
                <span class="text-sm text-slate-300 font-medium block mt-1.5 font-semibold">
                    Valor total acumulado em faturas abertas
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>

    </div>

    <!-- Abas de Navegação (Tabs) -->
    <div class="flex items-center gap-2 border-b border-slate-200">
        <button 
            type="button" 
            @click="activeTab = 'accounts'; searchQuery = ''" 
            class="px-5 py-3 text-sm font-bold border-b-2 transition-all focus:outline-none flex items-center gap-2"
            :class="activeTab === 'accounts' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-800'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Contas Bancárias ({{ $totalAccountsCount }})
        </button>
        <button 
            type="button" 
            @click="activeTab = 'cards'; searchQuery = ''" 
            class="px-5 py-3 text-sm font-bold border-b-2 transition-all focus:outline-none flex items-center gap-2"
            :class="activeTab === 'cards' ? 'border-primary-600 text-primary-600' : 'border-transparent text-slate-500 hover:text-slate-800'"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            Cartões de Crédito ({{ $totalCardsCount }})
        </button>
    </div>

    <!-- Filtro e Pesquisa -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery" 
                   :placeholder="activeTab === 'accounts' ? 'Pesquise por banco, conta, agência ou número...' : 'Pesquise por banco, nome do cartão, bandeira ou número...'" 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400 font-semibold text-slate-700">
            <!-- Limpar Filtro -->
            <button x-show="searchQuery" 
                    @click="searchQuery = ''" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-650"
                    x-cloak>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

    </div>

    <!-- Grade de Conteúdo -->
    <div class="space-y-4">
        
        <!-- ABA: CONTAS BANCÁRIAS -->
        <div x-show="activeTab === 'accounts'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            @forelse($bankAccounts as $account)
                <div x-show="matchesSearch('{{ addslashes($account->bank_name) }} {{ addslashes($account->account_name) }} {{ addslashes($account->agency) }} {{ addslashes($account->account_number) }}')" 
                     x-transition
                     class="w-full flex"
                >
                    <!-- Cartão de Banco Estilo Apple Wallet -->
                    <div class="w-full rounded-[10px] bg-gradient-to-br {{ $account->brand_style['bg'] }} {{ $account->brand_style['text'] }} p-6 shadow-md hover:shadow-lg transition-all duration-200 flex flex-col justify-between relative overflow-hidden min-h-[220px]">
                        
                        <!-- Detalhe Decorativo do Chip de Cartão -->
                        <div class="absolute top-6 right-6 font-mono font-black text-lg opacity-40 uppercase tracking-widest">
                            {{ $account->brand_style['icon'] }}
                        </div>
                        
                        <!-- Topo: Chip & Tipo de Pessoa -->
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-9 h-7 rounded-[4px] bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-500 opacity-80 shadow-sm"></div>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px] {{ $account->brand_style['badge'] }}">
                                    {{ $account->person_type }}
                                </span>
                            </div>
                            
                            <!-- Nome da Conta & Banco -->
                            <div class="mt-2">
                                <h4 class="font-bold text-base truncate tracking-wide uppercase" title="{{ $account->account_name }}">
                                    {{ $account->account_name }}
                                </h4>
                                <p class="text-xs opacity-75 font-semibold mt-0.5 tracking-wider">
                                    {{ $account->bank_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Dados de Identificação da Conta -->
                        <div class="mt-4 text-xs font-mono font-bold tracking-wider opacity-90 flex justify-between items-end">
                            <div>
                                <p>Ag: {{ $account->agency ?? '---' }}</p>
                                <p class="mt-0.5">Cc: {{ $account->account_number ?? '---' }}</p>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px] {{ $account->brand_style['badge'] }}">
                                @switch($account->account_type)
                                    @case('corrente') Corrente @break
                                    @case('poupanca') Poupança @break
                                    @case('digital') Digital @break
                                    @case('investimento') Investimento @break
                                    @default Outros
                                @endswitch
                            </span>
                        </div>

                        <!-- Rodapé: Saldo Atual & Ações -->
                        <div class="mt-6 pt-3 border-t border-white/20 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-[120px]">
                                <p class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Saldo Consolidado</p>
                                <p class="text-xl font-black mt-0.5 tracking-tight {{ $account->brand_style['accent'] }} truncate" title="R$ {{ number_format($account->current_balance, 2, ',', '.') }}">
                                    R$ {{ number_format($account->current_balance, 2, ',', '.') }}
                                </p>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-1">
                                <!-- Extrato -->
                                <a href="{{ route('bank-accounts.show', $account->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-all" title="Ver Extrato">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 112-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </a>

                                <!-- Editar -->
                                <a href="{{ route('bank-accounts.edit', $account->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-all" title="Editar Conta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                                
                                <!-- Excluir -->
                                <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Conta Bancária', message: 'Tem certeza de que deseja excluir a conta bancária <strong class=\'text-slate-800\'>{{ addslashes($account->account_name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: A conta será removida. Os pagamentos recebidos vinculados a esta conta não serão perdidos, mas perderão a referência de vínculo com a conta bancária.</span>', action: '{{ route('bank-accounts.destroy', $account->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-red-600 hover:text-white text-white transition-all" title="Excluir Conta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]">
                    Nenhuma conta bancária cadastrada ainda.
                </div>
            @endforelse
        </div>

        <!-- ABA: CARTÕES DE CRÉDITO -->
        <div x-show="activeTab === 'cards'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full" x-cloak>
            @forelse($creditCards as $card)
                <div x-show="matchesSearch('{{ addslashes($card->bank_name) }} {{ addslashes($card->card_name) }} {{ addslashes($card->flag) }} {{ addslashes($card->last_four_digits) }}')" 
                     x-transition
                     class="w-full flex"
                >
                    <!-- Cartão de Crédito Estilo Apple Wallet -->
                    <div class="w-full rounded-[10px] bg-gradient-to-br {{ $card->brand_style['bg'] }} {{ $card->brand_style['text'] }} p-6 shadow-md hover:shadow-lg transition-all duration-200 flex flex-col justify-between relative overflow-hidden min-h-[220px]">
                        
                        <!-- Detalhe Decorativo do Chip de Cartão -->
                        <div class="absolute top-6 right-6 font-mono font-black text-lg opacity-40 uppercase tracking-widest">
                            {{ $card->brand_style['icon'] }}
                        </div>
                        
                        <!-- Topo: Chip & Bandeira -->
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="w-9 h-7 rounded-[4px] bg-gradient-to-r from-amber-300 via-yellow-400 to-amber-500 opacity-80 shadow-sm"></div>
                                <span class="text-[10px] font-extrabold uppercase px-2 py-0.5 rounded-[4px] {{ $card->brand_style['flag_badge'] }}">
                                    {{ $card->brand_style['flag_label'] }}
                                </span>
                            </div>
                            
                            <!-- Nome do Cartão & Banco -->
                            <div class="mt-2">
                                <h4 class="font-bold text-base truncate tracking-wide uppercase" title="{{ $card->card_name }}">
                                    {{ $card->card_name }}
                                </h4>
                                <p class="text-xs opacity-75 font-semibold mt-0.5 tracking-wider">
                                    {{ $card->bank_name }}
                                </p>
                            </div>
                        </div>

                        <!-- Dados de Vencimento e Fechamento -->
                        <div class="mt-4 text-xs font-mono font-bold tracking-wider opacity-90 flex justify-between items-end">
                            <div>
                                <p>Fechamento: Dia <span class="font-black">{{ $card->closing_day }}</span></p>
                                <p class="mt-0.5">Vencimento: Dia <span class="font-black">{{ $card->due_day }}</span></p>
                            </div>
                            <span class="text-sm font-mono tracking-widest font-black opacity-80">
                                •••• {{ $card->last_four_digits ?? '••••' }}
                            </span>
                        </div>

                        <!-- Rodapé: Limite & Ações -->
                        <div class="mt-6 pt-3 border-t border-white/20 flex flex-wrap items-center justify-between gap-3">
                            <div class="min-w-[120px]">
                                <p class="text-[9px] uppercase tracking-wider opacity-75 font-bold">Limite do Cartão</p>
                                <p class="text-xl font-black mt-0.5 tracking-tight truncate" title="R$ {{ number_format($card->limit, 2, ',', '.') }}">
                                    R$ {{ number_format($card->limit, 2, ',', '.') }}
                                </p>
                            </div>
                            
                            <div class="flex flex-wrap items-center gap-1">
                                <!-- Ver Fatura -->
                                <a href="{{ route('credit-cards.show', $card->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-all" title="Ver Fatura / Extrato">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 112-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </a>

                                <!-- Editar -->
                                <a href="{{ route('credit-cards.edit', $card->id) }}" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-white/25 text-white transition-all" title="Editar Cartão">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                </a>
                                
                                <!-- Excluir -->
                                <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Cartão de Crédito', message: 'Tem certeza de que deseja excluir o cartão de crédito <strong class=\'text-slate-800\'>{{ addslashes($card->card_name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: O cartão será excluído permanentemente do sistema.</span>', action: '{{ route('credit-cards.destroy', $card->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center rounded-full bg-white/10 hover:bg-red-600 hover:text-white text-white transition-all" title="Excluir Cartão">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]">
                    Nenhum cartão de crédito cadastrado ainda.
                </div>
            @endforelse
        </div>

        <!-- Busca Sem Resultado -->
        <div x-show="searchQuery !== '' && countVisibleCards() === 0" 
             class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]"
             x-cloak>
            Nenhum registro atende aos critérios da sua pesquisa.
        </div>

    </div>

    <!-- Botão Flutuante Redondo (FAB) Dinâmico -->
    <a :href="activeTab === 'accounts' ? '{{ route('bank-accounts.create') }}' : '{{ route('credit-cards.create') }}'" 
       class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" 
       :title="activeTab === 'accounts' ? 'Nova Conta Bancária' : 'Novo Cartão de Crédito'">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function bankAccountList() {
        return {
            activeTab: '{{ request('tab', 'accounts') }}',
            searchQuery: '',
            openTransferModal: false,
            
            matchesSearch(text) {
                if (!this.searchQuery) return true;
                const query = this.searchQuery.toLowerCase().trim();
                return text.toLowerCase().includes(query);
            },

            countVisibleCards() {
                const container = document.querySelector(this.activeTab === 'accounts' ? '[x-show="activeTab === \'accounts\'"]' : '[x-show="activeTab === \'cards\'"]');
                if (!container) return 0;
                const cards = Array.from(container.querySelectorAll('[x-show*="matchesSearch"]'));
                return cards.filter(c => c.style.display !== 'none').length;
            }
        }
    }
</script>
@endsection
