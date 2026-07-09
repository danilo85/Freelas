@extends('layouts.app')

@section('title', 'Novo Orçamento - Gestor de Freelas')
@section('page_title', 'Criar Orçamento')

@section('content')
<div x-data="projectForm()" class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('projects.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Grid Principal -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8 items-start">
        
        <!-- Formulário de Cadastro (Esquerda - 2 Colunas) -->
        <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 xl:col-span-2 shadow-sm">
            <div>
                <h2 class="text-xl font-bold text-slate-900">Novo Orçamento</h2>
                <p class="text-xs text-slate-400 mt-1">Gere propostas comerciais e orçamentos para os seus clientes com controle de parcelamento e sinal.</p>
            </div>

            <form action="{{ route('projects.store') }}" method="POST" class="space-y-6" @submit="submitForm($event)">
                @csrf
                
                <!-- Titulo -->
                <div class="space-y-1.5">
                    <label for="title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Título da Proposta / Projeto</label>
                    <input type="text" name="title" id="title" required x-model="title" placeholder="Ex: Criação de E-commerce, Identidade Visual..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                    @error('title')
    <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
@enderror
                </div>

                <!-- Cliente (Autocomplete com Tag & Cadastro On-the-fly) -->
                <div class="space-y-1.5 relative z-30" @click.outside="clientDropdown = false">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Cliente</label>
                    
                    <!-- Campo de busca / Selecionado -->
                    <div class="relative">
                        <!-- Tag do Cliente Selecionado -->
                        <template x-if="selectedClient || newClientName">
                            <div class="flex items-center justify-between p-3 rounded-[5px] border text-sm"
                                 :class="selectedClient ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-amber-50 text-amber-800 border-amber-200'">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold" x-text="selectedClient ? selectedClient.name : newClientName"></span>
                                    <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded-[5px]"
                                          :class="selectedClient ? 'bg-emerald-100/80 text-emerald-900 border border-emerald-200' : 'bg-amber-100/80 text-amber-900 border border-amber-200'"
                                          x-text="selectedClient ? 'Cadastrado' : 'Novo - Criar ao Salvar'"></span>
                                </div>
                                <button type="button" @click="clearClient()" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Input de Digitação (Oculto se já selecionou) -->
                        <div x-show="!selectedClient && !newClientName">
                            <input type="text" x-model="clientQuery" @focus="clientDropdown = true" placeholder="Pesquise o nome do cliente ou digite um novo..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        </div>
                    </div>

                    <!-- Dropdown de Resultados -->
                    <div x-show="clientDropdown && clientQuery.length > 0" class="absolute z-50 left-0 right-0 bg-white border border-slate-200 rounded-[5px] shadow-lg max-h-56 overflow-y-auto mt-1" style="z-index: 50;" x-cloak>
                        <!-- Clientes Filtrados -->
                        <template x-for="c in filteredClients()" :key="c.id">
                            <div @click="selectClient(c)" class="p-3 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-xs border-b border-slate-100">
                                <span class="font-semibold text-slate-800" x-text="c.name"></span>
                                <span class="text-[9px] font-bold uppercase text-slate-400">Cadastrado</span>
                            </div>
                        </template>
                        
                        <!-- Opção On-the-fly (Cadastrar novo cliente) -->
                        <div @click="selectNewClient()" class="p-3 hover:bg-amber-50 cursor-pointer flex items-center justify-between text-xs text-amber-800 bg-amber-50/20">
                            <span>Cadastrar novo cliente: <strong x-text="clientQuery"></strong></span>
                            <span class="text-[9px] font-bold uppercase text-amber-600 bg-amber-100 px-2 py-0.5 rounded-[5px] border border-amber-200">Criar ao Salvar</span>
                        </div>
                    </div>

                    <!-- Hidden inputs para o formulário -->
                    <input type="hidden" name="client_id" :value="selectedClient ? selectedClient.id : ''">
                    <input type="hidden" name="new_client_name" :value="newClientName">
                    @error('client_id')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Autores (Autocomplete Multiselect com Tags & Cadastro On-the-fly) -->
                <div class="space-y-1.5 relative z-20" @click.outside="authorDropdown = false">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Autores do Projeto</label>
                    
                    <!-- Campo de busca / Tags Selecionadas -->
                    <div class="p-2 border border-slate-200 rounded-[5px] flex flex-wrap gap-2 items-center bg-white min-h-[46px]">
                        <!-- Tags dos Autores Selecionados -->
                        <template x-for="(author, index) in selectedAuthors" :key="index">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-[5px] border flex items-center gap-1.5"
                                  :class="author.is_new ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'">
                                <span x-text="author.name"></span>
                                <span class="text-[8px] font-bold uppercase tracking-wider px-1 py-0.5 rounded-[5px]"
                                      :class="author.is_new ? 'bg-amber-100 text-amber-900 border-amber-200' : 'bg-emerald-100 text-emerald-950 border border-emerald-200'"
                                      x-text="author.is_new ? 'Novo' : 'Cadastrado'"></span>
                                <button type="button" @click="removeAuthor(index)" class="text-slate-400 hover:text-slate-600 focus:outline-none">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </span>
                        </template>

                        <!-- Input de digitação -->
                        <input type="text" x-model="authorQuery" @focus="authorDropdown = true" placeholder="Digite para buscar ou criar autores..." class="flex-1 text-sm border-0 focus:ring-0 p-1 min-w-[150px] outline-none">
                    </div>

                    <!-- Dropdown de Resultados -->
                    <div x-show="authorDropdown && authorQuery.length > 0" class="absolute z-50 left-0 right-0 bg-white border border-slate-200 rounded-[5px] shadow-lg max-h-56 overflow-y-auto mt-1" style="z-index: 50;" x-cloak>
                        <!-- Autores Filtrados -->
                        <template x-for="a in filteredAuthors()" :key="a.id">
                            <div @click="selectAuthor(a)" class="p-3 hover:bg-slate-50 cursor-pointer flex items-center justify-between text-xs border-b border-slate-100">
                                <span class="font-semibold text-slate-800" x-text="a.name"></span>
                                <span class="text-[9px] font-bold uppercase text-slate-400">Cadastrado</span>
                            </div>
                        </template>
                        
                        <!-- Opção On-the-fly (Cadastrar novo autor) -->
                        <div @click="selectNewAuthor()" class="p-3 hover:bg-amber-50 cursor-pointer flex items-center justify-between text-xs text-amber-800 bg-amber-50/20">
                            <span>Cadastrar novo autor: <strong x-text="authorQuery"></strong></span>
                            <span class="text-[9px] font-bold uppercase text-amber-600 bg-amber-100 px-2 py-0.5 rounded-[5px] border border-amber-200">Criar ao Salvar</span>
                        </div>
                    </div>

                    <!-- Hidden inputs para o formulário -->
                    <template x-for="a in selectedAuthors.filter(item => !item.is_new)" :key="a.id">
                        <input type="hidden" name="author_ids[]" :value="a.id">
                    </template>
                    <template x-for="a in selectedAuthors.filter(item => item.is_new)" :key="a.name">
                        <input type="hidden" name="new_author_names[]" :value="a.name">
                    </template>
                </div>

                <!-- Conteúdo da Proposta (Descrição - Editor WYSIWYG) -->
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Conteúdo da Proposta (Orçamento)</label>
                    
                    <!-- Style scoped para o Editor e o Preview -->
                    <style>
                        .wysiwyg-editor ul, .wysiwyg-content ul {
                            list-style-type: disc !important;
                            padding-left: 1.5rem !important;
                            margin-top: 0.5rem !important;
                            margin-bottom: 0.5rem !important;
                        }
                        .wysiwyg-editor ol, .wysiwyg-content ol {
                            list-style-type: decimal !important;
                            padding-left: 1.5rem !important;
                            margin-top: 0.5rem !important;
                            margin-bottom: 0.5rem !important;
                        }
                        .wysiwyg-editor a, .wysiwyg-content a {
                            color: #2563eb !important;
                            text-decoration: underline !important;
                        }
                        .wysiwyg-editor u, .wysiwyg-content u {
                            text-decoration: underline !important;
                        }
                    </style>

                    <div class="border border-slate-200 rounded-[5px] overflow-hidden focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all bg-white">
                        <!-- Toolbar -->
                        <div class="bg-slate-50 border-b border-slate-200 p-2 flex flex-wrap gap-1 items-center select-none">
                            <button type="button" @click="format('bold')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Negrito">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M15.6 10.79c.97-.67 1.65-1.77 1.65-2.79 0-2.26-1.75-4-4-4H7v14h7.04c2.09 0 3.71-1.7 3.71-3.79 0-1.52-.86-2.82-2.15-3.42zM10 6.5h3c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5h-3v-3zm3.5 9H10v-3h3.5c.83 0 1.5.67 1.5 1.5s-.67 1.5-1.5 1.5z"/></svg>
                            </button>
                            <button type="button" @click="format('italic')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Itálico">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M10 4v3h2.21l-3.42 8H6v3h8v-3h-2.21l3.42-8H18V4z"/></svg>
                            </button>
                            <button type="button" @click="format('underline')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Sublinhado">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 17c3.31 0 6-2.69 6-6V3h-2.5v8c0 1.93-1.57 3.5-3.5 3.5S8.5 12.93 8.5 11V3H6v8c0 3.31 2.69 6 6 6zm-7 2v2h14v-2H5z"/></svg>
                            </button>
                            <button type="button" @click="format('superscript')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Sobrescrito">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.62 9.59H22V8h-4.32v1.02l1.96 2.18c.24.27.36.5.36.72 0 .28-.21.46-.57.46-.35 0-.58-.15-.71-.43l-1.07.56c.3.62.88 1 1.77 1 1.09 0 1.78-.65 1.78-1.57 0-.59-.3-1.09-.77-1.59l-.98-1.07zM12 5.5v2.8h-4l5.4 6.7H9.6v2.8h7.9v-2.8l-5.4-6.7h3.9V5.5H12z"/></svg>
                            </button>
                            <button type="button" @click="format('insertUnorderedList')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Lista">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M4 10.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zm0-6c-.83 0-1.5.67-1.5 1.5S3.17 7.5 4 7.5 5.5 6.83 5.5 6 4.83 4.5 4 4.5zm0 12c-.83 0-1.5.68-1.5 1.5s.68 1.5 1.5 1.5 1.5-.68 1.5-1.5-.67-1.5-1.5-1.5zM7 19h14v-2H7v2zm0-6h14v-2H7v2zm0-7v2h14V6H7z"/></svg>
                            </button>
                            <button type="button" @click="insertLink()" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Inserir Link">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V6.4H7c-3.09 0-5.6 2.51-5.6 5.6s2.51 5.6 5.6 5.6h4v-2.5H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6.6h-4v2.5h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4v2.5h4c3.09 0 5.6-2.51 5.6-5.6s-2.51-5.6-5.6-5.6z"/></svg>
                            </button>
                            <button type="button" @click="format('undo')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Desfazer">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L2 7v9h9l-3.62-3.62c1.39-1.16 3.16-1.88 5.12-1.88 3.54 0 6.55 2.31 7.6 5.5l2.37-.78C21.08 11.03 17.15 8 12.5 8z"/></svg>
                            </button>
                            <button type="button" @click="format('insertLineBreak')" class="p-1.5 hover:bg-slate-200 text-slate-700 rounded transition-colors" title="Quebra de Linha">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Área Editável com Auto-resize -->
                        <div x-ref="editor" 
                             contenteditable="true" 
                             @input="description = $el.innerHTML; $el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'" 
                             @blur="description = $el.innerHTML" 
                             class="w-full px-4 py-3 min-h-[150px] text-sm outline-none bg-white wysiwyg-editor prose max-w-none focus:outline-none overflow-hidden resize-none"
                             style="min-height: 150px;"></div>
                    </div>
                    <input type="hidden" name="description" :value="description">
                    @error('description')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Valor Total & Prazo -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Valor Total -->
                    <div class="space-y-1.5">
                        <label for="total_value" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Valor Total (R$)</label>
                        <!-- Máscara de dinheiro R$ com formatador reativo @input -->
                        <input type="text" name="total_value" id="total_value" required x-model="totalValue" @input="totalValue = formatMoney($event.target.value)" placeholder="R$ 0,00" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all font-semibold text-slate-800">
                        @error('total_value')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prazo -->
                    <div class="space-y-1.5">
                        <label for="term" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Prazo de Entrega</label>
                        <input type="text" name="term" id="term" required x-model="term" placeholder="Ex: 30 dias úteis, 3 meses" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        @error('term')
                            <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Slider de Entrada / Divisão de Sinal (10% a 100%) -->
                <div class="space-y-3 bg-slate-50/50 p-5 rounded-[5px] border border-slate-150">
                    <div class="flex justify-between items-center text-xs font-semibold uppercase tracking-wider text-slate-500">
                        <span>Divisão do Valor Inicial (Sinal)</span>
                        <span class="text-primary-600 font-extrabold text-sm" x-text="sliderValue + '%'"></span>
                    </div>
                    
                    <input type="range" name="initial_payment_percent" min="10" max="100" step="10" x-model="sliderValue" class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-primary-600">
                    
                    <!-- Resumo dos Valores Calculados -->
                    <div class="grid grid-cols-2 gap-4 pt-2 text-xs">
                        <div class="space-y-1">
                            <span class="text-slate-400 font-medium block">Sinal de Entrada:</span>
                            <span class="font-extrabold text-slate-800" x-text="calculateSinal()">R$ 0,00</span>
                        </div>
                        <div class="space-y-1">
                            <span class="text-slate-400 font-medium block">Restante no término:</span>
                            <span class="font-extrabold text-slate-800" x-text="calculateRestante()">R$ 0,00</span>
                        </div>
                    </div>
                </div>

                <!-- Datas com Calendários Customizados -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    
                    <!-- Data do Orçamento -->
                    <div class="space-y-1.5 relative" x-data="customDatePicker('budget_date', '{{ today()->format('Y-m-d') }}', true)">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Data do Orçamento</label>
                        <div class="relative">
                            <input type="text" readonly :value="formatDate(value)" @click="openCalendar()" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer bg-white">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </span>
                        </div>
                        <input type="hidden" name="budget_date" :value="value" @change="$dispatch('budget-date-changed', value)">
                        
                        <!-- Calendário Dropdown Customizado -->
                        <div x-show="open" @click.outside="open = false" class="absolute z-50 left-0 right-0 mt-1 p-3 bg-white border border-slate-200 rounded-[5px] shadow-lg w-72" x-cloak>
                            <div class="flex items-center justify-between mb-3">
                                <button type="button" @click="prevMonth()" class="p-1 hover:bg-slate-100 rounded text-slate-650">
                                    &lt;
                                </button>
                                <span class="text-xs font-bold text-slate-800" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                                <button type="button" @click="nextMonth()" class="p-1 hover:bg-slate-100 rounded text-slate-650">
                                    &gt;
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-[10px] font-bold text-center text-slate-400 mb-1">
                                <span>Dom</span><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-xs">
                                <template x-for="d in days" :key="d.dateStr ? d.dateStr : Math.random()">
                                    <div @click="!d.disabled && selectDay(d.dateStr)" 
                                         class="aspect-square flex items-center justify-center rounded cursor-pointer transition-colors"
                                         :class="[
                                            d.disabled ? 'text-slate-200 cursor-default' : 'hover:bg-primary-50 text-slate-700',
                                            d.dateStr === value ? 'bg-primary-600 text-white font-bold hover:bg-primary-700' : ''
                                         ]">
                                        <span x-text="d.day"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Data de Validade (+10 dias automático) -->
                    <div class="space-y-1.5 relative" x-data="customDatePicker('expiration_date', '{{ today()->addDays(10)->format('Y-m-d') }}', false)" @budget-date-changed.window="updateExpiration($event.detail)">
                        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Data de Validade</label>
                        <div class="relative">
                            <input type="text" readonly :value="formatDate(value)" @click="openCalendar()" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all cursor-pointer bg-white">
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </span>
                        </div>
                        <input type="hidden" name="expiration_date" :value="value">

                        <!-- Calendário Dropdown Customizado -->
                        <div x-show="open" @click.outside="open = false" class="absolute z-50 left-0 right-0 mt-1 p-3 bg-white border border-slate-200 rounded-[5px] shadow-lg w-72" x-cloak>
                            <div class="flex items-center justify-between mb-3">
                                <button type="button" @click="prevMonth()" class="p-1 hover:bg-slate-100 rounded text-slate-650">
                                    &lt;
                                </button>
                                <span class="text-xs font-bold text-slate-800" x-text="monthNames[currentMonth] + ' ' + currentYear"></span>
                                <button type="button" @click="nextMonth()" class="p-1 hover:bg-slate-100 rounded text-slate-650">
                                    &gt;
                                </button>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-[10px] font-bold text-center text-slate-400 mb-1">
                                <span>Dom</span><span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-xs">
                                <template x-for="d in days" :key="d.dateStr ? d.dateStr : Math.random()">
                                    <div @click="!d.disabled && selectDay(d.dateStr)" 
                                         class="aspect-square flex items-center justify-center rounded cursor-pointer transition-colors"
                                         :class="[
                                            d.disabled ? 'text-slate-200 cursor-default' : 'hover:bg-primary-50 text-slate-700',
                                            d.dateStr === value ? 'bg-primary-600 text-white font-bold hover:bg-primary-700' : ''
                                         ]">
                                        <span x-text="d.day"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Status (Bolinhas Coloridas) -->
                <div class="space-y-2">
                    <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Status do Orçamento</span>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        
                        <!-- Rascunho -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'rascunho' ? 'border-slate-400 bg-slate-100/80 ring-2 ring-slate-500/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="rascunho" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-slate-400"></span>
                            <span class="text-xs font-semibold text-slate-750">Rascunho</span>
                        </label>
                        
                        <!-- Analisando -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'analisando' ? 'border-amber-400 bg-amber-100/50 ring-2 ring-amber-550/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="analisando" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-amber-500"></span>
                            <span class="text-xs font-semibold text-slate-750">Analisando</span>
                        </label>
                        
                        <!-- Aprovado -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'aprovado' ? 'border-emerald-400 bg-emerald-100/50 ring-2 ring-emerald-550/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="aprovado" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-emerald-500"></span>
                            <span class="text-xs font-semibold text-slate-750">Aprovado</span>
                        </label>
                        
                        <!-- Rejeitado -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'rejeitado' ? 'border-red-400 bg-red-100/50 ring-2 ring-red-550/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="rejeitado" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-red-500"></span>
                            <span class="text-xs font-semibold text-slate-750">Rejeitado</span>
                        </label>
                        
                        <!-- Quitado -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'quitado' ? 'border-purple-400 bg-purple-100/50 ring-2 ring-purple-550/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="quitado" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-purple-500"></span>
                            <span class="text-xs font-semibold text-slate-750">Quitado</span>
                        </label>
                        
                        <!-- Finalizado -->
                        <label class="flex items-center gap-2.5 p-3 rounded-[5px] border cursor-pointer transition-all select-none"
                               :class="status === 'finalizado' ? 'border-blue-400 bg-blue-100/50 ring-2 ring-blue-550/10' : 'border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="status" value="finalizado" x-model="status" class="hidden">
                            <span class="w-3.5 h-3.5 rounded-full bg-blue-500"></span>
                            <span class="text-xs font-semibold text-slate-750">Finalizado</span>
                        </label>
                    </div>
                    @error('status')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informações Adicionais -->
                <div class="space-y-1.5">
                    <label for="additional_info" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Informações Adicionais (Observações Internas)</label>
                    <textarea name="additional_info" id="additional_info" rows="3" x-model="additionalInfo" placeholder="Observações de faturamento, dados bancários ou detalhes extras..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all resize-none"></textarea>
                    @error('additional_info')
                        <p class="text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-4 border-t border-slate-100 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-[5px] text-sm transition-colors shadow-sm">
                        Criar Orçamento
                    </button>
                </div>
            </form>
        </div>

        <!-- Preview Card (Direita - Sticky no Desktop) -->
        <div class="space-y-4 xl:sticky xl:top-6 xl:col-span-1 w-full">
            <div class="px-2 xl:px-0">
                <span class="text-[10px] font-bold tracking-wider text-primary-600 uppercase">Visualização em Tempo Real</span>
                <h4 class="text-sm font-bold text-slate-800 mt-1">Preview da Proposta</h4>
            </div>

            <!-- Card de Visualização do Orçamento (Aparência Real do Documento) -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-4 sm:p-6 md:p-8 space-y-6 shadow-md relative min-h-[500px]">
                <!-- Cabeçalho da Proposta -->
                <div class="flex items-center justify-between">
                    <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight">PROPOSTA</h1>
                    
                    <!-- Badge Circular com número da proposta -->
                    <div class="relative shrink-0">
                        <div class="w-11 h-11 rounded-full bg-[#1e293b] flex items-center justify-center text-white font-extrabold text-sm shadow-md">
                            <span x-text="'{{ \App\Models\Project::max('id') + 1 }}'"></span>
                        </div>
                        <!-- Círculo Verde de Status -->
                        <span class="absolute -top-0.5 -right-0.5 w-3 h-3 bg-emerald-500 border-2 border-white rounded-full"></span>
                    </div>
                </div>

                <!-- Datas e Cliente -->
                <div class="text-xs text-slate-400 font-semibold space-y-1.5 border-b border-slate-100 pb-4">
                    <div>Válido de <span class="text-slate-500 font-bold" x-text="formatDate(budgetDate)"></span> a <span class="text-slate-500 font-bold" x-text="formatDate(expDate)"></span></div>
                    <div>Para <span class="font-extrabold text-slate-700" x-text="selectedClient ? selectedClient.name : (newClientName || 'Nenhum cliente selecionado')"></span></div>
                </div>

                <!-- Seção: Orçamento -->
                <div class="space-y-3 mt-6">
                    <h5 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Orçamento:</h5>
                    <p class="text-xs text-slate-600 font-semibold leading-relaxed" x-text="title || 'Título do Projeto'"></p>
                    
                    <!-- Conteúdo HTML do Editor WYSIWYG -->
                    <div x-html="description || '<p class=\'text-slate-400\'>O escopo detalhado do projeto aparecerá aqui...</p>'" 
                         class="text-xs text-slate-500 leading-relaxed space-y-2 wysiwyg-content pt-2"></div>
                </div>

                <!-- Seção: Prazo -->
                <div class="space-y-1.5 mt-6 border-t border-slate-100 pt-4">
                    <h5 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider">Prazo:</h5>
                    <p class="text-xs text-slate-500 font-medium" x-text="'Prazo estimado é de ' + (term || 'Não informado') + (term ? ' dias' : '')"></p>
                </div>

                <!-- Bloco Financeiro e Forma de Pagamento -->
                <div class="border border-slate-200 rounded-[5px] overflow-hidden mt-8 shadow-sm">
                    <!-- Topo do bloco -->
                    <div class="bg-slate-50/50 p-5 space-y-1">
                        <span class="text-[9px] font-bold text-slate-400 tracking-wider uppercase block">Total</span>
                        <h3 class="text-3xl font-black text-[#0f172a] tracking-tight" x-text="totalValue || 'R$ 0,00'"></h3>
                        <span class="text-[10px] text-slate-400 block pt-1">Forma de pagamento:</span>
                    </div>
                    <!-- Rodapé do bloco (Divisão das parcelas) -->
                    <div class="flex text-xs">
                        <!-- Sinal -->
                        <div class="bg-[#1e293b] text-white p-4 flex-1">
                            <span class="text-[8px] font-bold text-slate-300 tracking-wider block uppercase" x-text="sliderValue + '% Para Iniciar'"></span>
                            <span class="text-sm font-extrabold block mt-1" x-text="'1º ' + calculateSinal()"></span>
                        </div>
                        <!-- Restante -->
                        <div class="bg-[#334155] text-white p-4 flex-1 border-l border-slate-700">
                            <span class="text-[8px] font-bold text-slate-300 tracking-wider block uppercase" x-text="(100 - sliderValue) + '% Ao Término'"></span>
                            <span class="text-sm font-extrabold block mt-1" x-text="'2º ' + calculateRestante()"></span>
                        </div>
                    </div>
                </div>

                <!-- Rodapé informativo -->
                <div class="pt-4 flex items-center justify-between text-[9px] font-bold uppercase tracking-wider text-slate-400 border-t border-slate-100">
                    <span>Status do Orçamento</span>
                    <span class="px-2 py-0.5 rounded-[5px] border flex items-center gap-1.5"
                        :class="[
                            status === 'rascunho' ? 'bg-slate-100 text-slate-700 border-slate-300' : '',
                            status === 'analisando' ? 'bg-amber-100 text-amber-900 border-amber-300' : '',
                            status === 'aprovado' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : '',
                            status === 'rejeitado' ? 'bg-red-100 text-red-900 border-red-300' : '',
                            status === 'quitado' ? 'bg-purple-100 text-purple-900 border-purple-300' : '',
                            status === 'finalizado' ? 'bg-blue-100 text-blue-900 border-blue-300' : ''
                        ]">
                        <span class="w-1.5 h-1.5 rounded-full inline-block"
                            :class="[
                                status === 'rascunho' ? 'bg-slate-400' : '',
                                status === 'analisando' ? 'bg-amber-500' : '',
                                status === 'aprovado' ? 'bg-emerald-500' : '',
                                status === 'rejeitado' ? 'bg-red-500' : '',
                                status === 'quitado' ? 'bg-purple-500' : '',
                                status === 'finalizado' ? 'bg-blue-500' : ''
                            ]"></span>
                        <span x-text="status"></span>
                    </span>
                </div>
            </div>
            
            <p class="text-[10px] text-slate-400 text-center">Este card simula o visual final do orçamento a ser impresso/gerado.</p>
        
            <!-- Assistente IA de Precificação e Prazos -->
            <div class="bg-gradient-to-br from-indigo-50 to-slate-50 dark:from-slate-900 dark:to-slate-950 border border-indigo-100 dark:border-slate-800 rounded-[5px] p-5 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-indigo-100/50 dark:border-slate-850 pb-2">
                    <div>
                        <h4 class="text-xs font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                            💡 Assistente de Copiloto
                        </h4>
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block mt-0.5">Sugestões de valores e prazos</p>
                    </div>
                    <!-- Spinner de Loading -->
                    <div x-show="loadingSimilarity" class="flex items-center gap-1 text-[10px] text-indigo-600 dark:text-indigo-400 font-bold uppercase tracking-wider">
                        <svg class="animate-spin h-3.5 w-3.5" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Analisando...
                    </div>
                </div>

                <div class="space-y-3.5 text-xs">
                    <!-- Sem dados -->
                    <template x-if="similarProjects.length === 0 && !loadingSimilarity">
                        <div class="text-slate-450 dark:text-slate-500 py-3 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 p-4 rounded-[5px]">
                            Digite o título ou conteúdo para que o assistente analise orçamentos antigos parecidos.
                        </div>
                    </template>

                    <!-- Com dados -->
                    <template x-if="similarProjects.length > 0">
                        <div class="space-y-3">
                            <div class="p-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] space-y-2">
                                <span class="text-[10px] font-black text-indigo-650 dark:text-indigo-400 uppercase tracking-wider block">Métricas Sugeridas:</span>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Preço Médio</span>
                                        <strong class="text-emerald-655 dark:text-emerald-450 font-black text-sm" x-text="'R$ ' + avgValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })"></strong>
                                    </div>
                                    <div class="space-y-0.5">
                                        <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider block">Prazo Médio</span>
                                        <strong class="text-slate-750 dark:text-slate-200 font-black text-sm" x-text="avgTermDays ? avgTermDays + ' dias' : 'Não estimado'"></strong>
                                    </div>
                                </div>
                                
                                <button type="button" @click="applySuggestions()" class="w-full mt-2 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-[10px] font-black rounded-[5px] uppercase tracking-wider transition-colors shadow-xs flex items-center justify-center gap-1 focus:outline-none cursor-pointer">
                                    ✨ Aplicar Sugestões ao Form
                                </button>
                            </div>

                            <!-- Listagem de Orçamentos Parecidos -->
                            <div class="space-y-1.5">
                                <span class="text-[10px] font-black text-slate-450 dark:text-slate-500 uppercase tracking-wider block">Orçamentos Parecidos Encontrados:</span>
                                <div class="space-y-2 max-h-60 overflow-y-auto pr-1">
                                    <template x-for="p in similarProjects" :key="p.id">
                                        <a :href="'/freelas/projects/' + p.id" target="_blank" class="block p-2.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] hover:border-indigo-300 dark:hover:border-indigo-900 transition-all">
                                            <div class="flex justify-between items-start gap-2">
                                                <strong class="font-extrabold text-slate-750 dark:text-slate-200 truncate block text-[11px] max-w-[150px]" x-text="p.title"></strong>
                                                <span class="text-[9px] font-black text-emerald-600 dark:text-emerald-400 shrink-0" x-text="'R$ ' + p.value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })"></span>
                                            </div>
                                            <div class="flex justify-between text-[9px] text-slate-400 font-bold mt-1 uppercase">
                                                <span x-text="'Prazo: ' + p.term"></span>
                                                <span class="text-indigo-600 dark:text-indigo-400" x-text="'Relevância: ' + p.score"></span>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

    </div>

</div>

<script>
    // Todos os Clientes e Autores do PHP
    const dbClients = @json($clients);
    const dbAuthors = @json($authors);

    function projectForm() {
        return {
            title: '',
            description: '',
            clientQuery: '',
            selectedClient: null,
            newClientName: '',
            clientDropdown: false,

            authorQuery: '',
            selectedAuthors: [],
            authorDropdown: false,

            totalValue: '',
            term: '',
            sliderValue: 40,
            status: 'rascunho',
            additionalInfo: '',

            // Similarity AI Copilot
            similarProjects: [],
            avgValue: 0,
            avgTermDays: 0,
            loadingSimilarity: false,
            similarityTimer: null,

            // Event Listeners das datas
            budgetDate: '{{ today()->format("Y-m-d") }}',
            expDate: '{{ today()->addDays(10)->format("Y-m-d") }}',

            init() {
                // Escuta os eventos globais disparados pelo datepicker customizado
                window.addEventListener('budget_date-changed', (e) => {
                    this.budgetDate = e.detail;
                    // Atualiza a validade adicionando 10 dias de forma automática se desejar
                    let date = new Date(this.budgetDate + 'T12:00:00');
                    date.setDate(date.getDate() + 10);
                    let newExp = date.toISOString().split('T')[0];
                    this.expDate = newExp;
                    // Dispara evento para o segundo datepicker
                    window.dispatchEvent(new CustomEvent('update-expiration-date', { detail: newExp }));
                });
                
                // Monitora mudanças no título e escopo para precificação inteligente
                this.$watch('title', () => this.debounceSimilarity());
                this.$watch('description', () => this.debounceSimilarity());
                
                window.addEventListener('expiration_date-changed', (e) => {
                    this.expDate = e.detail;
                });
            },

            // Máscara de Dinheiro customizada e reativa
            formatMoney(value) {
                let clean = value.replace(/\D/g, '');
                if (clean === '') return '';
                let floatVal = parseFloat(clean) / 100;
                return 'R$ ' + floatVal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            // Formatador do Editor WYSIWYG
            format(command, value = null) {
                if (command === 'insertLineBreak') {
                    document.execCommand('insertHTML', false, '<br>');
                } else {
                    document.execCommand(command, false, value);
                }
                this.description = this.$refs.editor.innerHTML;
            },

            insertLink() {
                let url = prompt('Digite a URL do link:');
                if (url) {
                    if (!/^https?:\/\//i.test(url)) {
                        url = 'http://' + url;
                    }
                    this.format('createLink', url);
                }
            },

            // Filtros de Clientes
            filteredClients() {
                if (this.clientQuery === '') return [];
                let q = this.clientQuery.toLowerCase();
                return dbClients.filter(c => c.name.toLowerCase().includes(q));
            },

            selectClient(client) {
                this.selectedClient = client;
                this.newClientName = '';
                this.clientQuery = '';
                this.clientDropdown = false;
            },

            selectNewClient() {
                this.newClientName = this.clientQuery;
                this.selectedClient = null;
                this.clientQuery = '';
                this.clientDropdown = false;
            },

            clearClient() {
                this.selectedClient = null;
                this.newClientName = '';
                this.clientQuery = '';
            },

            // Filtros de Autores
            filteredAuthors() {
                if (this.authorQuery === '') return [];
                let q = this.authorQuery.toLowerCase();
                // Filtra apenas autores cadastrados que já não foram selecionados
                return dbAuthors.filter(a => {
                    let alreadySelected = this.selectedAuthors.some(sa => sa.id === a.id);
                    return a.name.toLowerCase().includes(q) && !alreadySelected;
                });
            },

            selectAuthor(author) {
                this.selectedAuthors.push({
                    id: author.id,
                    name: author.name,
                    is_new: false
                });
                this.authorQuery = '';
                this.authorDropdown = false;
            },

            selectNewAuthor() {
                // Evita nomes vazios ou duplicados
                let name = this.authorQuery.trim();
                if (name && !this.selectedAuthors.some(sa => sa.name.toLowerCase() === name.toLowerCase())) {
                    this.selectedAuthors.push({
                        id: null,
                        name: name,
                        is_new: true
                    });
                }
                this.authorQuery = '';
                this.authorDropdown = false;
            },

            removeAuthor(index) {
                this.selectedAuthors.splice(index, 1);
            },

            // Cálculos Financeiros em Tempo Real
            getFloatValue() {
                let val = this.totalValue.replace(/\D/g, '');
                if (val === '') return 0;
                return parseFloat(val) / 100;
            },

            calculateSinal() {
                let val = this.getFloatValue();
                let percent = parseInt(this.sliderValue);
                let sinal = val * (percent / 100);
                return 'R$ ' + sinal.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            calculateRestante() {
                let val = this.getFloatValue();
                let percent = parseInt(this.sliderValue);
                let restante = val * ((100 - percent) / 100);
                return 'R$ ' + restante.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                let parts = dateStr.split('-');
                if (parts.length !== 3) return dateStr;
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            },

            debounceSimilarity() {
                clearTimeout(this.similarityTimer);
                this.similarityTimer = setTimeout(() => {
                    this.fetchSimilarity();
                }, 600);
            },

            async fetchSimilarity() {
                if (!this.title && !this.description) {
                    this.similarProjects = [];
                    this.avgValue = 0;
                    this.avgTermDays = 0;
                    return;
                }
                this.loadingSimilarity = true;
                try {
                    let response = await fetch('{{ route("projects.analyze-similarity") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            title: this.title,
                            description: this.description
                        })
                    });
                    let data = await response.json();
                    if (data.success) {
                        this.similarProjects = data.similar_projects;
                        this.avgValue = data.avg_value;
                        this.avgTermDays = data.avg_term_days;
                    }
                } catch (e) {
                    console.error('Erro ao buscar similaridade:', e);
                } finally {
                    this.loadingSimilarity = false;
                }
            },

            applySuggestions() {
                if (this.avgValue > 0) {
                    this.totalValue = 'R$ ' + this.avgValue.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
                if (this.avgTermDays > 0) {
                    this.term = this.avgTermDays + ' dias úteis';
                }
            },

            submitForm(event) {
                if (!this.selectedClient && !this.newClientName) {
                    event.preventDefault();
                    alert('Por favor, informe ou selecione um cliente.');
                }
            }
        }
    }

    // Função construtora do Datepicker Alpine.js
    function customDatePicker(name, initialValue, isBudget = true) {
        return {
            open: false,
            value: initialValue,
            currentYear: null,
            currentMonth: null,
            days: [],
            monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],

            init() {
                this.resetCalendar(this.value);

                if (!isBudget) {
                    // Escuta atualização vinda do orçamento
                    window.addEventListener('update-expiration-date', (e) => {
                        this.value = e.detail;
                        this.resetCalendar(this.value);
                    });
                }
            },

            resetCalendar(dateVal) {
                let date = dateVal ? new Date(dateVal + 'T12:00:00') : new Date();
                this.currentYear = date.getFullYear();
                this.currentMonth = date.getMonth();
                this.generateCalendar();
            },

            generateCalendar() {
                let firstDay = new Date(this.currentYear, this.currentMonth, 1).getDay();
                let totalDays = new Date(this.currentYear, this.currentMonth + 1, 0).getDate();
                let daysArray = [];
                
                // Preenchimento dos dias em branco do início do mês
                for (let i = 0; i < firstDay; i++) {
                    daysArray.push({ day: '', disabled: true });
                }
                
                // Dias do mês
                for (let d = 1; d <= totalDays; d++) {
                    let dateStr = `${this.currentYear}-${String(this.currentMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    daysArray.push({ day: d, dateStr: dateStr, disabled: false });
                }
                this.days = daysArray;
            },

            openCalendar() {
                this.open = true;
                this.resetCalendar(this.value);
            },

            selectDay(dateStr) {
                this.value = dateStr;
                this.open = false;
                // Dispara evento para o controller Alpine geral
                window.dispatchEvent(new CustomEvent(name + '-changed', { detail: dateStr }));
            },

            prevMonth() {
                if (this.currentMonth === 0) {
                    this.currentMonth = 11;
                    this.currentYear--;
                } else {
                    this.currentMonth--;
                }
                this.generateCalendar();
            },

            nextMonth() {
                if (this.currentMonth === 11) {
                    this.currentMonth = 0;
                    this.currentYear++;
                } else {
                    this.currentMonth++;
                }
                this.generateCalendar();
            },

            formatDate(dateStr) {
                if (!dateStr) return '';
                let parts = dateStr.split('-');
                if (parts.length !== 3) return dateStr;
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
        }
    }
</script>
@endsection
