@extends('layouts.app')

@section('title', 'Registrar Lançamento - Gestor de Freelas')
@section('page_title', 'Nova Transação')

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
        <h1 class="text-2xl font-black text-slate-800">Nova Transação</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Insira receitas ou despesas recorrentes, parceladas ou únicas no seu caixa.</p>
    </div>

    <!-- Abas Tipo de Transação -->
    <div class="flex border-b border-slate-200 gap-6 no-print">
        <button 
            type="button" 
            @click="setType('entrada')" 
            class="pb-3 text-sm font-black border-b-2 transition-all"
            :class="type === 'entrada' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-400 hover:text-slate-700'"
        >
            🟢 Receita (Entrada)
        </button>
        <button 
            type="button" 
            @click="setType('saida')" 
            class="pb-3 text-sm font-black border-b-2 transition-all"
            :class="type === 'saida' ? 'border-rose-500 text-rose-600' : 'border-transparent text-slate-400 hover:text-slate-700'"
        >
            🔴 Despesa (Saída)
        </button>
    </div>

    <!-- Formulário -->
    <form 
        action="{{ route('finances.store') }}" 
        method="POST" 
        enctype="multipart/form-data"
        class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6"
    >
        @csrf
        <input type="hidden" name="type" :value="type" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Descrição Breve -->
            <div class="space-y-1 md:col-span-2">
                <label for="description" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Descrição Breve *</label>
                <input 
                    type="text" 
                    name="description" 
                    id="description" 
                    required 
                    placeholder="Ex: Assinatura Adobe, Hospedagem AWS, Faturamento Projeto X"
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
                    @input="amount = formatMoney($event.target.value)"
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
                    value="{{ date('Y-m-d') }}"
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
                    x-html="renderCategoriesOptions()"
                    class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                </select>
            </div>

            <!-- Classificação PF / PJ -->
            <div class="space-y-1">
                <label class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Tipo de Pessoa (Classificação) *</label>
                <div class="grid grid-cols-2 gap-2 mt-1">
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="classification === 'PJ' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                        <input type="radio" name="classification" value="PJ" x-model="classification" class="hidden" />
                        🏢 Jurídica (PJ / MEI)
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="classification === 'PF' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
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
                        <option value="{{ $acc->id }}">
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
                           :class="destinationType === 'bank' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                        <input type="radio" name="destination_type" value="bank" x-model="destinationType" class="hidden" />
                        🏦 Conta Bancária
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="destinationType === 'card' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
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
                        <option value="{{ $card->id }}">
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
                        <option value="{{ $proj->id }}">
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
                           :class="status === 'pago' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                        <input type="radio" name="status" value="pago" x-model="status" class="hidden" />
                        <span x-text="type === 'entrada' ? '🟢 Recebido' : '🟢 Pago'"></span>
                    </label>
                    <label class="border rounded-[5px] p-2.5 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                           :class="status === 'pendente' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                        <input type="radio" name="status" value="pendente" x-model="status" class="hidden" />
                        🟡 Pendente
                    </label>
                </div>
            </div>

        </div>

        <hr class="border-slate-100" />

        <!-- Opções de Repetição / Repetir -->
        <div class="space-y-4">
            <h3 class="text-sm font-black text-slate-800 uppercase tracking-wider">Repetição e Parcelamento</h3>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                <label class="border rounded-[5px] p-3 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                       :class="repeatType === 'single' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                    <input type="radio" name="repeat_type" value="single" x-model="repeatType" class="hidden" />
                    📅 Lançamento Único
                </label>
                <label class="border rounded-[5px] p-3 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                       :class="repeatType === 'installments' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                    <input type="radio" name="repeat_type" value="installments" x-model="repeatType" class="hidden" />
                    💳 Compras Parceladas
                </label>
                <label class="border rounded-[5px] p-3 flex items-center justify-center gap-2 cursor-pointer text-xs font-bold transition-all"
                       :class="repeatType === 'recurring' ? 'border-primary-500 bg-primary-50/20 text-primary-700 ring-2 ring-primary-500/10' : 'border-slate-200 hover:bg-slate-50 text-slate-650'">
                    <input type="radio" name="repeat_type" value="recurring" x-model="repeatType" class="hidden" />
                    🔄 Assinatura / Recorrente
                </label>
            </div>

            <!-- Detalhes de Parcelamento -->
            <div x-show="repeatType === 'installments'" class="p-4 bg-slate-50 border border-slate-200 rounded-[5px] flex flex-col gap-3" x-cloak>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <label for="installment_mode" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">O valor digitado acima é:</label>
                        <select 
                            name="installment_mode" 
                            id="installment_mode" 
                            x-model="installmentMode"
                            class="w-full bg-white px-4 py-2 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                        >
                            <option value="total">O Valor Total (Dividir pelas parcelas)</option>
                            <option value="installment">O Valor de cada Parcela</option>
                        </select>
                    </div>
                    <div class="space-y-1">
                        <label for="installments_count" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Número de Parcelas *</label>
                        <input 
                            type="number" 
                            name="installments_count" 
                            id="installments_count" 
                            min="1" 
                            x-model="installmentsCount"
                            class="w-full bg-white px-4 py-2 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                        />
                    </div>
                </div>
                <!-- Simulação de Parcelas -->
                <div x-show="amount" class="text-xs font-black text-slate-600 flex items-center gap-1.5 pt-2 border-t border-slate-200/60" x-text="installmentPreview" x-cloak>
                </div>
            </div>

            <!-- Detalhes de Recorrência -->
            <div x-show="repeatType === 'recurring'" class="p-4 bg-slate-50 border border-slate-200 rounded-[5px] space-y-1" x-cloak>
                <label for="recurrence_period" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Frequência da Recorrência (Cria 12 meses futuros) *</label>
                <select 
                    name="recurrence_period" 
                    id="recurrence_period"
                    class="w-full bg-white px-4 py-2 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-700"
                >
                    <option value="mensal">Mensal</option>
                    <option value="diaria">Diária</option>
                    <option value="semanal">Semanal</option>
                    <option value="anual">Anual</option>
                </select>
            </div>
        </div>

        <hr class="border-slate-100" />

        <!-- Upload de Anexo (NF / Comprovante) -->
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Comprovante ou Nota Fiscal (Opcional)</label>
            
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
                    <span class="text-sm font-extrabold text-slate-700 block mt-1.5" x-text="fileName ? 'Arquivo selecionado!' : 'Arraste um anexo ou clique para procurar'"></span>
                    <span class="text-xs text-slate-450 block" x-text="fileName ? fileName : 'Suporta arquivos PDF, PNG, JPG, XML até 10MB'"></span>
                </div>
            </div>
        </div>

        <!-- Ações do Formulário -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 no-print">
            <a href="{{ route('finances.index') }}" class="px-4 py-2.5 border border-slate-200 text-slate-650 hover:bg-slate-50 text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold rounded-[5px] transition-colors shadow-sm focus:ring-4 focus:ring-primary-500/20">
                Salvar Lançamento
            </button>
        </div>

    </form>

</div>

<script>
    function transactionForm() {
        return {
            type: 'saida', // entrada ou saida
            classification: 'PF', // PJ ou PF
            destinationType: 'bank', // bank ou card
            repeatType: 'single', // single, installments, recurring
            installmentMode: 'total', // total ou installment
            installmentsCount: 3,
            amount: '',
            status: 'pendente',

            // File state
            isDragging: false,
            fileName: '',

            categories: {!! json_encode($categories) !!},

            init() {
                // Configura categoria inicial com base nas carregadas
                this.setType(this.type);
            },

            setType(newType) {
                this.type = newType;
                // Ajusta status conforme tipo
                if (newType === 'entrada') {
                    this.destinationType = 'bank'; // entrada não pode ser no cartão
                }
            },

            get filteredCategories() {
                const mappedType = this.type === 'entrada' ? 'receita' : 'despesa';
                return this.categories.filter(c => c.type === mappedType || c.type === 'ambos');
            },

            renderCategoriesOptions() {
                let options = '<option value="">Selecione uma Categoria...</option>';
                this.filteredCategories.forEach(cat => {
                    options += `<option value="${cat.id}">${cat.icon} ${cat.name}</option>`;
                });
                return options;
            },

            get installmentPreview() {
                if (!this.amount) return '';
                let clean = this.amount.replace(/\D/g, '');
                let val = parseFloat(clean) / 100;
                if (isNaN(val) || val <= 0) return '';
                
                let count = parseInt(this.installmentsCount) || 1;
                if (this.installmentMode === 'total') {
                    let part = (val / count).toFixed(2);
                    return `Simulação: serão ${count} parcelas de ${this.formatMoney(part.replace('.', ''))}`;
                } else {
                    let total = (val * count).toFixed(2);
                    return `Simulação: o valor total será de ${this.formatMoney(total.replace('.', ''))}`;
                }
            },

            formatMoney(value) {
                if (!value) return 'R$ 0,00';
                let clean = value.replace(/\D/g, '');
                let number = (parseFloat(clean) / 100).toFixed(2);
                let parts = number.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                return 'R$ ' + parts.join(',');
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
