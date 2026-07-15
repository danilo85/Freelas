@extends('layouts.app')

@section('title', 'Editar Recebimento - Gestor de Freelas')
@section('page_title', 'Editar Recebimento')

@section('content')
<div class="max-w-4xl mx-auto space-y-6" x-data="paymentForm()" x-init="init()">

    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('payments.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para o Calendário
        </a>
    </div>

    <!-- Alerta Toast de Sucesso -->
    <div x-show="toastMessage" x-transition class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-[5px] p-3 text-sm font-bold flex justify-between items-center" x-cloak>
        <div class="flex items-center gap-2">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span x-text="toastMessage"></span>
        </div>
        <button type="button" @click="toastMessage = ''" class="text-emerald-600 hover:text-emerald-800">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <!-- Título Principal -->
    <div>
        <h1 class="text-2xl font-black text-slate-800">Editar Recebimento</h1>
        <p class="text-sm text-slate-500 font-medium mt-1">Atualize as informações do recebimento vinculado aos seus orçamentos aprovados.</p>
    </div>

    <!-- Formulário de Edição -->
    <form 
        action="{{ route('payments.update', $payment->id) }}" 
        method="POST" 
        enctype="multipart/form-data"
        class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6"
    >
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Orçamento Principal -->
            <div class="space-y-2">
                <label for="project_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Orçamento (Status: Aprovado) *</label>
                <select 
                    name="project_id" 
                    id="project_id" 
                    required 
                    x-model="selectedProjectId"
                    @change="updateAmount()"
                    class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                >
                    <option value="">Selecione um Orçamento...</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}">
                            {{ $proj->title }} (Restante: R$ {{ number_format($projectBalances[$proj->id], 2, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('project_id')
                    <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Valor do Pagamento -->
            <div class="space-y-2">
                <label for="amount" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Valor Recebido *</label>
                <input 
                    type="text" 
                    name="amount" 
                    id="amount" 
                    required 
                    x-model="amount" 
                    @input="amount = formatMoney($event.target.value)"
                    placeholder="R$ 0,00"
                    class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-emerald-600"
                />
                @error('amount')
                    <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Conta Bancária -->
            <div class="space-y-2">
                <label for="bank_account_id" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Conta Bancária (Controle)</label>
                <select 
                    name="bank_account_id" 
                    id="bank_account_id"
                    class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                >
                    <option value="">Selecione uma conta...</option>
                    @foreach($bankAccounts as $acc)
                        <option value="{{ $acc->id }}" {{ $payment->bank_account_id == $acc->id ? 'selected' : '' }}>
                            {{ $acc->bank_name }} - {{ $acc->account_name }} (Ag: {{ $acc->agency ?? '---' }} / Cc: {{ $acc->account_number ?? '---' }})
                        </option>
                    @endforeach
                </select>
                <div class="mt-1">
                    <button type="button" @click="showModal = true" class="text-xs text-primary-600 hover:text-primary-700 hover:underline font-semibold flex items-center gap-1 bg-transparent border-0 cursor-pointer p-0 shadow-none focus:outline-none">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Cadastrar nova conta bancária
                    </button>
                </div>
                
                @error('bank_account_id')
                    <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Data do Pagamento -->
            <div class="space-y-2">
                <label for="paid_at" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Data do Pagamento *</label>
                <input 
                    type="date" 
                    name="paid_at" 
                    id="paid_at" 
                    required
                    value="{{ old('paid_at', $payment->paid_at->format('Y-m-d')) }}"
                    class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                />
                @error('paid_at')
                    <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                @enderror
            </div>

            <!-- Forma de Pagamento -->
            <div class="space-y-2">
                <label for="payment_method" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Forma de Pagamento *</label>
                <select 
                    name="payment_method" 
                    id="payment_method" 
                    required 
                    class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                >
                    <option value="pix" {{ $payment->payment_method === 'pix' ? 'selected' : '' }}>PIX</option>
                    <option value="dinheiro" {{ $payment->payment_method === 'dinheiro' ? 'selected' : '' }}>Dinheiro (Em espécie)</option>
                    <option value="deposito" {{ $payment->payment_method === 'deposito' ? 'selected' : '' }}>Depósito / Transferência</option>
                    <option value="outros" {{ $payment->payment_method === 'outros' ? 'selected' : '' }}>Outros</option>
                </select>
                @error('payment_method')
                    <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
                @enderror
            </div>

        </div>

        <!-- Anexo de Nota Fiscal (Drag & Drop) -->
        <div class="space-y-2">
            <label class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Anexo de Nota Fiscal (Opcional)</label>
            
            @if($payment->invoice_path)
                <div class="flex items-center justify-between gap-3 bg-blue-50 border border-blue-200 p-3 rounded-[5px] text-sm mb-2">
                    <div class="flex items-center gap-2 min-w-0">
                        <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-semibold text-blue-800 truncate text-xs">Nota Fiscal Cadastrada: {{ basename($payment->invoice_path) }}</span>
                    </div>
                    <a href="{{ route('payments.download-invoice', $payment->id) }}" class="px-2.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm shrink-0">
                        Visualizar Nota Fiscal
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
                    name="invoice" 
                    id="invoice"
                    x-ref="fileInput" 
                    @change="handleFileSelect" 
                    accept=".pdf,.png,.jpg,.jpeg,.xml"
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
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </template>
                    </div>
                    
                    <p class="text-sm font-bold text-slate-700">
                        <template x-if="fileName">
                            <span>Arquivo selecionado: <strong class="text-emerald-650" x-text="fileName"></strong></span>
                        </template>
                        <template x-if="!fileName">
                            <span>Arraste a nota fiscal aqui ou <span class="text-primary-600 hover:underline">clique para selecionar</span></span>
                        </template>
                    </p>
                    <p class="text-xs text-slate-400 font-medium">
                        Aceita arquivos PDF, XML, PNG ou JPG de até 10MB (Deixe em branco para manter a nota atual se houver)
                    </p>
                </div>
            </div>
            @error('invoice')
                <span class="text-red-500 text-xs font-bold block mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Observações -->
        <div class="space-y-2">
            <label for="observations" class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Observações</label>
            <textarea 
                name="observations" 
                id="observations" 
                rows="3" 
                placeholder="Adicione informações adicionais sobre esta parcela ou transferência..."
                class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-medium text-slate-700"
            >{{ old('observations', $payment->observations) }}</textarea>
            @error('observations')
                <span class="text-red-500 text-xs font-bold">{{ $message }}</span>
            @enderror
        </div>

        <!-- Orçamentos Adicionais Contemplados (Opcional) -->
        <div class="space-y-3">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block">Orçamentos Adicionais Contemplados (Opcional)</span>
            <p class="text-xs text-slate-500 font-medium -mt-1">Selecione outros orçamentos que também estão contemplados por este pagamento ou nota fiscal.</p>
            
            <div class="relative">
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    placeholder="Pesquise por orçamento ou cliente..." 
                    class="w-full pl-9 pr-4 py-2.5 text-xs border border-slate-200 rounded-[5px] focus:outline-none focus:ring-1 focus:ring-primary-500 focus:border-primary-500 font-semibold text-slate-700 bg-white"
                >
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
            </div>

            @php
                $statusColorMap = [
                    'aprovado' => 'bg-emerald-50/70 border-emerald-250 text-emerald-950 hover:bg-emerald-100/70',
                    'quitado' => 'bg-purple-50/70 border-purple-250 text-purple-950 hover:bg-purple-100/70',
                    'finalizado' => 'bg-blue-50/70 border-blue-250 text-blue-950 hover:bg-blue-100/70',
                ];
            @endphp

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-48 overflow-y-auto p-2 bg-slate-50 border border-slate-200 rounded-[5px]">
                @foreach($allProjects as $proj)
                    <label 
                        x-show="selectedProjectId != '{{ $proj->id }}' && ('{{ strtolower(addslashes($proj->title)) }}'.includes(searchQuery.toLowerCase()) || '{{ strtolower(addslashes($proj->client->name)) }}'.includes(searchQuery.toLowerCase()))" 
                        x-cloak 
                        class="flex items-center gap-3 p-2.5 border rounded-[5px] cursor-pointer transition-all text-xs font-bold {{ $statusColorMap[$proj->status] ?? 'bg-white border-slate-150 text-slate-700 hover:bg-slate-50' }}"
                    >
                        <input 
                            type="checkbox" 
                            name="related_project_ids[]" 
                            value="{{ $proj->id }}"
                            {{ in_array($proj->id, $relatedProjectIds) ? 'checked' : '' }}
                            class="rounded border-slate-350 text-primary-600 focus:ring-primary-500/20 w-4 h-4"
                        />
                        <span class="truncate flex-1">{{ $proj->title }} <span class="opacity-60 text-[10px] font-medium block sm:inline sm:ml-1">({{ $proj->client->name }})</span></span>
                    </label>
                @endforeach
            </div>
        </div>

        <!-- Botões de Ação -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
            <a href="{{ route('payments.index') }}" class="px-5 py-2.5 rounded-[5px] text-sm font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors">
                Cancelar
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-[5px] bg-primary-600 hover:bg-primary-700 text-white text-sm font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary-500/20">
                Salvar Alterações
            </button>
        </div>

    </form>

    <!-- Modal para Cadastrar Nova Conta Bancária -->
    <div 
        x-show="showModal" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div 
            @click.away="showModal = false" 
            class="bg-white rounded-[5px] border border-slate-200 w-full max-w-2xl max-h-[90vh] overflow-y-auto shadow-2xl p-6 sm:p-8 space-y-6"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-lg font-black text-slate-800 uppercase tracking-wider">Nova Conta Bancária</h3>
                    <p class="text-xs text-slate-400 font-medium mt-0.5">Adicione uma nova conta bancária sem perder as informações digitadas.</p>
                </div>
                <button type="button" @click="showModal = false" class="text-slate-400 hover:text-slate-650 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Formulário no Modal -->
            <div class="space-y-6">
                <!-- Erros de Validação do Modal -->
                <div x-show="Object.keys(modalErrors).length > 0" class="bg-red-50 border border-red-200 text-red-800 rounded-[5px] p-3 text-xs font-bold space-y-1" x-cloak>
                    <p>Corrija os seguintes erros:</p>
                    <ul class="list-disc list-inside font-semibold">
                        <template x-for="(messages, field) in modalErrors" :key="field">
                            <li>
                                <span class="capitalize" x-text="field.replace('_', ' ')"></span>: 
                                <template x-for="msg in messages">
                                    <span x-text="msg"></span>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Banco -->
                    <div class="space-y-1.5">
                        <label for="modal_bank_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Banco *</label>
                        <select 
                            id="modal_bank_name" 
                            x-model="modalForm.bank_name"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
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
                    </div>

                    <!-- Custom Bank Name -->
                    <div class="space-y-1.5" x-show="modalForm.bank_name === 'Outro'" x-cloak x-transition>
                        <label for="modal_custom_bank_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nome do Banco Customizado *</label>
                        <input 
                            type="text" 
                            id="modal_custom_bank_name"
                            x-model="modalForm.custom_bank_name"
                            placeholder="Ex: Cresol, Pan, etc."
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                    </div>
                </div>

                <!-- Nome da Conta -->
                <div class="space-y-1.5">
                    <label for="modal_account_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Nome Identificador da Conta *</label>
                    <input 
                        type="text" 
                        id="modal_account_name" 
                        x-model="modalForm.account_name"
                        placeholder="Ex: Conta Corrente PJ, Pix Pessoal" 
                        class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                    />
                </div>

                <!-- Tipo, Titularidade & Saldo Inicial -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="space-y-1.5">
                        <label for="modal_account_type" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Tipo de Conta *</label>
                        <select 
                            id="modal_account_type" 
                            x-model="modalForm.account_type"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="digital">Conta Digital</option>
                            <option value="corrente">Conta Corrente</option>
                            <option value="poupanca">Conta Poupança</option>
                            <option value="investimento">Conta Investimento</option>
                            <option value="outros">Outros</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="modal_person_type" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pessoa (Titularidade) *</label>
                        <select 
                            id="modal_person_type" 
                            x-model="modalForm.person_type"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        >
                            <option value="PJ">Pessoa Jurídica (PJ)</option>
                            <option value="PF">Pessoa Física (PF)</option>
                        </select>
                    </div>

                    <div class="space-y-1.5">
                        <label for="modal_initial_balance" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Saldo Inicial *</label>
                        <input 
                            type="text" 
                            id="modal_initial_balance" 
                            x-model="modalForm.initial_balance"
                            @input="modalForm.initial_balance = formatMoney($event.target.value)"
                            placeholder="R$ 0,00"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-bold text-emerald-600"
                        />
                    </div>
                </div>

                <!-- Agência e Conta -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="modal_agency" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Número da Agência</label>
                        <input 
                            type="text" 
                            id="modal_agency" 
                            x-model="modalForm.agency"
                            placeholder="Ex: 0001"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                    </div>

                    <div class="space-y-1.5">
                        <label for="modal_account_number" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Número da Conta</label>
                        <input 
                            type="text" 
                            id="modal_account_number" 
                            x-model="modalForm.account_number"
                            placeholder="Ex: 12345-6"
                            class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800"
                        />
                    </div>
                </div>

                <!-- Observações -->
                <div class="space-y-1.5">
                    <label for="modal_observations" class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Observações</label>
                    <textarea 
                        id="modal_observations" 
                        rows="2" 
                        x-model="modalForm.observations"
                        placeholder="Adicione informações adicionais..."
                        class="w-full px-4 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-medium text-slate-700"
                    ></textarea>
                </div>

                <!-- Ações do Modal -->
                <div class="flex items-center justify-end gap-3 border-t border-slate-100 pt-4">
                    <button 
                        type="button" 
                        @click="showModal = false" 
                        class="px-4 py-2 rounded-[5px] text-xs font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-800 transition-colors"
                        :disabled="modalSubmitting"
                    >
                        Cancelar
                    </button>
                    <button 
                        type="button" 
                        @click="submitBankAccount()" 
                        class="px-5 py-2 rounded-[5px] bg-primary-600 hover:bg-primary-700 text-white text-xs font-bold shadow-sm transition-all focus:ring-4 focus:ring-primary-500/20 flex items-center gap-1.5"
                        :class="modalSubmitting ? 'opacity-50 cursor-not-allowed' : ''"
                        :disabled="modalSubmitting"
                    >
                        <template x-if="modalSubmitting">
                            <svg class="animate-spin h-3.5 w-3.5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        Salvar Conta
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function paymentForm() {
        return {
            selectedProjectId: '{{ old('project_id', $payment->project_id) }}',
            amount: '{{ old('amount', 'R$ ' . number_format($payment->amount, 2, ',', '.')) }}',
            searchQuery: '',
            projectBalances: {!! json_encode($projectBalances) !!},
            
            // File upload state
            isDragging: false,
            fileName: '',
            
            // Toast / Modal state
            toastMessage: '',
            showModal: false,
            modalSubmitting: false,
            modalErrors: {},
            
            // Modal form data
            modalForm: {
                bank_name: 'Nubank',
                custom_bank_name: '',
                account_name: '',
                account_type: 'digital',
                person_type: 'PJ',
                initial_balance: 'R$ 0,00',
                agency: '',
                account_number: '',
                observations: ''
            },

            init() {
                // Initialize default amount if select changes but we keep current amount
                // No need to override the loaded payment value on start unless changed.
            },

            updateAmount() {
                if (this.selectedProjectId && this.projectBalances[this.selectedProjectId]) {
                    const balance = this.projectBalances[this.selectedProjectId];
                    this.amount = this.formatNumberToMoney(balance);
                } else {
                    this.amount = '';
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

            formatNumberToMoney(val) {
                let number = parseFloat(val).toFixed(2);
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
                
                // Update file input element file list
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                this.$refs.fileInput.files = dataTransfer.files;
                
                this.fileName = file.name;
            },

            // AJAX submit for Bank Account
            async submitBankAccount() {
                this.modalSubmitting = true;
                this.modalErrors = {};

                try {
                    const response = await fetch('{{ route("bank-accounts.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            bank_name: this.modalForm.bank_name,
                            custom_bank_name: this.modalForm.custom_bank_name,
                            account_name: this.modalForm.account_name,
                            account_type: this.modalForm.account_type,
                            person_type: this.modalForm.person_type,
                            initial_balance: this.modalForm.initial_balance,
                            agency: this.modalForm.agency,
                            account_number: this.modalForm.account_number,
                            observations: this.modalForm.observations
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        if (response.status === 422) {
                            this.modalErrors = data.errors || { general: ['Erro de validação nos campos informados.'] };
                        } else {
                            this.modalErrors = { general: [data.message || 'Ocorreu um erro ao processar sua requisição.'] };
                        }
                    } else if (data.success && data.bank_account) {
                        // Success! Add to dropdown list and select
                        const select = document.getElementById('bank_account_id');
                        const opt = document.createElement('option');
                        opt.value = data.bank_account.id;
                        
                        let displayBank = data.bank_account.bank_name;
                        let agencyText = data.bank_account.agency || '---';
                        let accountText = data.bank_account.account_number || '---';
                        
                        opt.text = `${displayBank} - ${data.bank_account.account_name} (Ag: ${agencyText} / Cc: ${accountText})`;
                        select.add(opt);
                        select.value = data.bank_account.id;

                        // Reset modal form
                        this.modalForm = {
                            bank_name: 'Nubank',
                            custom_bank_name: '',
                            account_name: '',
                            account_type: 'digital',
                            person_type: 'PJ',
                            initial_balance: 'R$ 0,00',
                            agency: '',
                            account_number: '',
                            observations: ''
                        };

                        this.showModal = false;
                        this.toastMessage = 'Conta bancária cadastrada e selecionada!';
                        setTimeout(() => this.toastMessage = '', 4000);
                    }
                } catch (error) {
                    console.error(error);
                    this.modalErrors = { general: ['Erro de rede ou servidor ao conectar. Tente novamente mais tarde.'] };
                } finally {
                    this.modalSubmitting = false;
                }
            }
        };
    }
</script>
@endsection
