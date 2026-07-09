@extends('layouts.app')

@section('title', 'Editar Lançamento - Gestor de Freelas')
@section('page_title', 'Editar Transação')

@section('content')
<div class="max-w-3xl mx-auto space-y-6" x-data="transactionForm()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between no-print">
        <a href="{{ route('finances.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para o Controle Financeiro
        </a>
    </div>

    <!-- Título Principal -->
    <div>
        <h1 class="text-2xl font-black text-slate-800">Editar Transação</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Atualize as informações desta movimentação financeira.</p>
    </div>

    <!-- Formulário -->
    <form 
        action="{{ route('finances.update', $finance->id) }}" 
        method="POST" 
        enctype="multipart/form-data"
        class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6"
    >
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Descrição Breve -->
            <div class="space-y-1 md:col-span-2">
                <label for="description" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Descrição Breve *</label>
                <input 
                    type="text" 
                    name="description" 
                    id="description" 
                    required 
                    value="{{ old('description', $finance->description) }}"
                    placeholder="Ex: Assinatura Adobe, Hospedagem AWS"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                />
            </div>

            <!-- Valor -->
            <div class="space-y-1">
                <label for="amount" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Valor *</label>
                <input 
                    type="text" 
                    name="amount" 
                    id="amount" 
                    required 
                    x-model="amount"
                    @input="handleAmountInput($event.target.value)"
                    @blur="evaluateAmount()"
                    @keydown.enter.prevent="evaluateAmount()"
                    placeholder="R$ 0,00"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold"
                    :class="type === 'entrada' ? 'text-emerald-600' : 'text-rose-600'"
                />
            </div>

            <!-- Data de Vencimento -->
            <div class="space-y-1">
                <label for="due_date" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Data de Vencimento *</label>
                <input 
                    type="date" 
                    name="due_date" 
                    id="due_date" 
                    required
                    value="{{ old('due_date', $finance->due_date->format('Y-m-d')) }}"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                />
            </div>

            <!-- Categoria -->
            <div class="space-y-1">
                <div class="flex justify-between items-center">
                    <label for="category_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Categoria *</label>
                    <a href="{{ route('finances.categories.index') }}" class="text-[10px] text-primary-600 hover:underline font-bold" target="_blank">Gerenciar</a>
                </div>
                <select 
                    name="category_id" 
                    id="category_id" 
                    required
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                    <option value="">Selecione uma Categoria...</option>
                    @php
                        $isEntrada = $finance->type === 'entrada';
                    @endphp
                    @foreach($categories as $cat)
                        @php
                            $matches = $cat->type === 'ambos' || 
                                       ($isEntrada && ($cat->type === 'receita' || $cat->type === 'entrada')) ||
                                       (!$isEntrada && ($cat->type === 'despesa' || $cat->type === 'saida'));
                        @endphp
                        @if($matches)
                            <option value="{{ $cat->id }}" {{ $finance->category_id == $cat->id ? 'selected' : '' }}>
                                {{ $cat->icon }} {{ $cat->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <!-- Classificação PF / PJ -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Tipo de Pessoa (Classificação) *</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="classification === 'PJ' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="classification" value="PJ" x-model="classification" class="hidden" />
                        🏢 Jurídica (PJ / MEI)
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="classification === 'PF' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="classification" value="PF" x-model="classification" class="hidden" />
                        👤 Física (PF)
                    </label>
                </div>
            </div>

            <!-- Origem/Destino do Dinheiro (Conta Bancária) -->
            <div class="space-y-1" x-show="type === 'entrada' || (type === 'saida' && destinationType === 'bank')">
                <label for="bank_account_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Conta Bancária</label>
                <select 
                    name="bank_account_id" 
                    id="bank_account_id"
                    :required="type === 'entrada' || (type === 'saida' && destinationType === 'bank')"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                    <option value="">Selecione uma conta...</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $finance->bank_account_id == $acc->id ? 'selected' : '' }}>
                            {{ $acc->bank_name }} - {{ $acc->account_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Toggler Origem Despesa (Apenas Despesa) -->
            <div class="space-y-1" x-show="type === 'saida'">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pagar via *</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="destinationType === 'bank' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="destination_type" value="bank" x-model="destinationType" class="hidden" />
                        🏦 Conta Bancária
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="destinationType === 'card' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="destination_type" value="card" x-model="destinationType" class="hidden" />
                        💳 Cartão de Crédito
                    </label>
                </div>
            </div>

            <!-- Destino do Dinheiro (Cartão de Crédito - Apenas Despesa) -->
            <div class="space-y-1" x-show="type === 'saida' && destinationType === 'card'">
                <label for="credit_card_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Cartão de Crédito *</label>
                <select 
                    name="credit_card_id" 
                    id="credit_card_id"
                    :required="type === 'saida' && destinationType === 'card'"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                    <option value="">Selecione um cartão...</option>
                    @foreach($creditCards as $card)
                        <option value="{{ $card->id }}" {{ $finance->credit_card_id == $card->id ? 'selected' : '' }}>
                            {{ $card->bank_name }} - {{ $card->card_name }} (•••• {{ $card->last_four_digits }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Vínculo com Orçamentos / Projetos (Apenas Receitas PJ) -->
            <div class="space-y-1" x-show="type === 'entrada' && classification === 'PJ'">
                <label for="project_id" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Vincular a Orçamento (Opcional)</label>
                <select 
                    name="project_id" 
                    id="project_id"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                    <option value="">Nenhum orçamento...</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ $finance->project_id == $proj->id ? 'selected' : '' }}>
                            {{ $proj->title }} (Restante: R$ {{ number_format($proj->remaining_balance, 2, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status (Pago / Pendente) -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Status do Lançamento *</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="status === 'pago' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="status" value="pago" x-model="status" class="hidden" />
                        <span x-text="type === 'entrada' ? '🟢 Recebido' : '🟢 Pago'"></span>
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="status === 'pendente' ? 'border-primary-500 bg-primary-50/20 text-primary-700 dark:text-primary-400 dark:bg-primary-950/20 ring-2 ring-primary-500/10 dark:ring-primary-400/20' : 'border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-650 dark:text-slate-350'">
                        <input type="radio" name="status" value="pendente" x-model="status" class="hidden" />
                        🟡 Pendente
                    </label>
                </div>
            </div>

        </div>

        @if($finance->group_code)
            <hr class="border-slate-100" />
            <!-- Checkbox para atualizar todos do grupo -->
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-[5px] space-y-2">
                <label class="flex items-center gap-2 cursor-pointer text-xs font-bold text-blue-800">
                    <input type="checkbox" name="update_all_future" value="1" class="rounded text-primary-600 focus:ring-primary-500/20 w-4 h-4 border-slate-300" />
                    🔄 Aplicar edições a todas as parcelas / recorrências futuras deste grupo
                </label>
                <p class="text-[10px] text-blue-600 font-semibold leading-relaxed pl-6">
                    Se marcado, as alterações em descrição, valor, categoria, conta/cartão e classificação serão aplicadas automaticamente para os lançamentos futuros do mesmo grupo de parcelamento/assinatura.
                </p>
            </div>
        @endif

        <hr class="border-slate-100" />

        <!-- Upload de Anexo (NF / Comprovante) -->
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Comprovante ou Nota Fiscal (Opcional)</label>
            
            @if($finance->attachment_path)
                <div class="flex items-center justify-between gap-3 bg-blue-50 border border-blue-200 p-3 rounded-[5px] text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-semibold text-blue-800 truncate">Comprovante Atual: {{ basename($finance->attachment_path) }}</span>
                    </div>
                    <a href="{{ route('finances.download-attachment', $finance->id) }}" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-[5px] transition-colors shadow-sm shrink-0">
                        Visualizar
                    </a>
                </div>
            @endif

            <div 
                @dragover.prevent="isDragging = true"
                @dragleave.prevent="isDragging = false"
                @drop.prevent="handleDrop($event)"
                @click="$refs.fileInput.click()"
                :class="isDragging ? 'bg-primary-50/50 border-primary-400 shadow-inner' : (fileName ? 'bg-emerald-50/20 border-emerald-300' : 'bg-slate-50 border-slate-200 hover:bg-slate-100/50')"
                class="border-2 border-dashed rounded-[5px] p-6 text-center cursor-pointer transition-all duration-200 space-y-2 relative"
            >
                <input 
                    type="file" 
                    name="attachment" 
                    id="attachment"
                    x-ref="fileInput" 
                    @change="handleFileSelect" 
                    class="hidden"
                />
                
                <div class="flex flex-col items-center justify-center space-y-1">
                    <div class="p-2.5 bg-white rounded-full border border-slate-200 shadow-sm text-slate-400">
                        <template x-if="fileName">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </template>
                        <template x-if="!fileName">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </template>
                    </div>
                    <span class="text-sm font-extrabold text-slate-700 block mt-1.5" x-text="fileName ? 'Novo arquivo selecionado!' : 'Substituir comprovante (ou clique para procurar)'"></span>
                    <span class="text-xs text-slate-450 block" x-text="fileName ? fileName : 'Formatos aceitos: PDF, XML, PNG, JPG até 10MB'"></span>
                </div>
            </div>
        </div>

        <!-- Ações do Formulário -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 no-print">
            <a href="{{ route('finances.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-650 hover:bg-slate-50 text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-[5px] transition-colors shadow-sm focus:ring-4 focus:ring-primary-500/20">
                Salvar Alterações
            </button>
        </div>

    </form>

</div>

<script>
    function transactionForm() {
        return {
            type: '{{ $finance->type }}',
            classification: '{{ $finance->classification }}',
            destinationType: '{{ $finance->credit_card_id ? 'card' : 'bank' }}',
            amount: '{{ old('amount', 'R$ ' . number_format($finance->amount, 2, ',', '.')) }}',
            status: '{{ $finance->status }}',

            // File state
            isDragging: false,
            fileName: '',

            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
            },

            handleAmountInput(val) {
                if (/[+\-*/]/.test(val)) {
                    this.amount = val;
                } else {
                    this.amount = this.formatMoney(val);
                }
            },

            evaluateAmount() {
                let expr = this.amount;
                if (!expr) return;

                expr = expr.replace(/R\$\s*/g, '');
                
                if (expr.includes(',')) {
                    expr = expr.replace(/\./g, '');
                    expr = expr.replace(/,/g, '.');
                }

                expr = expr.replace(/[^0-9. +\-*/()]/g, '');

                try {
                    if (/[0-9]/.test(expr)) {
                        const result = new Function(`return (${expr})`)();
                        if (typeof result === 'number' && !isNaN(result) && isFinite(result)) {
                            const cents = Math.round(result * 100).toString();
                            this.amount = this.formatMoney(cents);
                        }
                    }
                } catch(e) {
                    // Fail silently or keep the user typed expression
                }
            },

            // File handlers
            handleDrop(e) {
                this.isDragging = false;
                if (e.dataTransfer.files && e.dataTransfer.files[0]) {
                    const file = e.dataTransfer.files[0];
                    this.setFile(file);
                }
            },

            handleFileSelect(e) {
                if (e.target.files && e.target.files[0]) {
                    const file = e.target.files[0];
                    this.setFile(file);
                }
            },

            setFile(file) {
                const allowedTypes = ['application/pdf', 'image/png', 'image/jpeg', 'image/jpg', 'text/xml'];
                if (!allowedTypes.includes(file.type) && !file.name.endsWith('.xml')) {
                    alert('Por favor, envie um arquivo válido (PDF, XML, PNG ou JPG).');
                    return;
                }
                if (file.size > 10 * 1024 * 1024) {
                    alert('O tamanho do arquivo não pode exceder 10MB.');
                    return;
                }
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.$refs.fileInput.files = dataTransfer.files;
                
                this.fileName = file.name;
            }
        };
    }
</script>
@endsection
