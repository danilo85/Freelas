@extends('layouts.app')

@section('title', 'Clientes - Gestor de Freelas')
@section('page_title', 'Gerenciamento de Clientes')

@section('content')
@php
    $mappedClients = $clients->map(fn($c) => [
        'id' => $c->id,
        'searchable' => strtolower($c->name . ' ' . $c->email . ' ' . $c->phone . ' ' . $c->document)
    ]);
@endphp

<div x-data="clientList()" class="space-y-8">
    
    <!-- Top Cards (Métricas com Cores Diferentes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- Total de Clientes (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Total de Clientes</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $totalClientsCount }}
                </h3>
                <span class="text-sm text-blue-100/90 font-medium block mt-1.5">
                    Clientes cadastrados na base
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Clientes com Projetos Ativos (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Projetos Ativos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $clientsWithActiveProjectsCount }}
                </h3>
                <span class="text-sm text-emerald-100/90 font-medium block mt-1.5 flex items-center gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-white inline-block animate-pulse"></span>
                    Trabalhos em andamento
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>
        </div>

        <!-- Novos Clientes (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200 sm:col-span-2 lg:col-span-1">
            <div>
                <p class="text-sm font-bold text-purple-100 uppercase tracking-wider">Novos Clientes</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $newClientsCount }}
                </h3>
                <span class="text-sm text-purple-100/90 font-medium block mt-1.5">
                    Registrados nos últimos 30 dias
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>

    </div>

    @if(!empty($suggestedDuplicates) && count($suggestedDuplicates) > 0)
    <!-- Perfis Duplicados de Clientes Sugeridos -->
    <div class="bg-gradient-to-r from-amber-500/10 to-yellow-500/5 border border-amber-300 dark:border-amber-800/80 rounded-[5px] p-6 space-y-4 shadow-sm" x-data="{ showDuplicates: true }" x-show="showDuplicates" x-cloak>
        <div class="flex items-center justify-between border-b border-amber-200/50 dark:border-amber-800/30 pb-3">
            <div class="flex items-center gap-2.5">
                <span class="text-xl">⚠️</span>
                <div>
                    <h3 class="text-xs font-black text-amber-850 dark:text-amber-300 uppercase tracking-wider font-outfit">Perfis de Clientes Duplicados Detectados</h3>
                    <p class="text-[11px] text-amber-700 dark:text-amber-400 font-medium">Encontramos clientes na sua base de dados com nomes idênticos. Escolha qual manter como perfil principal e mescle os outros.</p>
                </div>
            </div>
        </div>
        
        <div class="space-y-4">
            @foreach($suggestedDuplicates as $name => $group)
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 rounded-[5px] space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-850 pb-2">
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">Nome do Cliente: <span class="text-primary-600 font-black">{{ $name }}</span></h4>
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-amber-50 text-amber-750 dark:bg-amber-955/30 dark:text-amber-300">
                            {{ count($group) }} Perfis Duplicados
                        </span>
                    </div>

                    <form action="{{ route('clients.merge') }}" method="POST" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($group as $index => $item)
                                <div class="border border-slate-200 dark:border-slate-800 rounded-[5px] p-3 flex flex-col justify-between space-y-3 bg-slate-50 dark:bg-slate-950/40 relative">
                                    <div class="flex items-start gap-2.5">
                                        <div class="pt-0.5">
                                            <input 
                                                type="radio" 
                                                name="main_client_id" 
                                                value="{{ $item->id }}" 
                                                {{ $index === 0 ? 'checked' : '' }}
                                                required
                                                class="rounded-full border-slate-350 text-primary-600 focus:ring-primary-500/20 w-4 h-4 cursor-pointer"
                                                id="main_client_{{ $item->id }}"
                                            >
                                        </div>
                                        <label for="main_client_{{ $item->id }}" class="text-xs font-semibold text-slate-650 dark:text-slate-300 cursor-pointer select-none">
                                            <span class="block text-slate-850 dark:text-white font-bold">Perfil #{{ $item->id }}</span>
                                            <span class="block text-[10px] text-slate-400 font-medium truncate max-w-[200px]" title="{{ $item->email }}">{{ $item->email ?? 'Sem email' }}</span>
                                            <span class="block text-[10px] text-slate-400 font-medium">{{ $item->phone ?? 'Sem telefone' }}</span>
                                        </label>
                                    </div>
                                    <div class="flex justify-between items-center text-[10px] border-t border-slate-150 dark:border-slate-850 pt-2 font-bold text-slate-450">
                                        <span>Projetos: {{ $item->projects_count }}</span>
                                        <span class="text-amber-600 dark:text-amber-400 uppercase tracking-wider text-[8px] font-black">Selecionar Principal</span>
                                    </div>
                                    
                                    <!-- Inputs ocultos para os duplicados -->
                                    <input type="hidden" name="duplicate_client_ids[]" value="{{ $item->id }}">
                                </div>
                            @endforeach
                        </div>
                        <div class="flex justify-end pt-1">
                            <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-[5px] transition-colors shadow-sm uppercase tracking-wider font-outfit">
                                Mesclar Perfis no Principal
                            </button>
                        </div>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Bloco de Mesclagem Manual de Clientes -->
    <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm space-y-4" x-data="{ showManual: false }">
        <button type="button" @click="showManual = !showManual" class="w-full flex items-center justify-between text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider font-outfit">
            <span>⚙️ Ferramenta de Mesclagem Manual (Curadoria)</span>
            <span x-text="showManual ? 'Recolher [-]' : 'Expandir [+]'">Expandir [+]</span>
        </button>
        
        <div x-show="showManual" x-transition class="space-y-4 border-t border-slate-100 dark:border-slate-850 pt-4" x-cloak>
            <p class="text-xs text-slate-400 font-medium">Use esta ferramenta para mesclar dois perfis de clientes que tenham nomes ou e-mails diferentes na base. Os orçamentos/trabalhos do perfil duplicado serão migrados para o perfil principal selecionado, e o duplicado será removido.</p>
            
            <form action="{{ route('clients.merge') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label for="main_client_id" class="text-[10px] font-bold text-slate-455 uppercase tracking-wider block font-outfit">Perfil Principal (Destino - Será Mantido):</label>
                        <select name="main_client_id" required class="w-full text-xs bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-[5px] px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary-500 font-semibold text-slate-700 dark:text-slate-200">
                            <option value="">-- Selecione o Perfil Principal --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (ID: {{ $c->id }} - {{ $c->email ?? 'sem email' }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[10px] font-bold text-slate-455 uppercase tracking-wider block font-outfit">Perfil Duplicado (Origem - Será Excluído após migração):</label>
                        <select name="duplicate_client_ids[]" required class="w-full text-xs bg-white dark:bg-slate-955 border border-slate-200 dark:border-slate-800 rounded-[5px] px-3 py-2.5 focus:outline-none focus:ring-1 focus:ring-primary-500 font-semibold text-slate-700 dark:text-slate-200">
                            <option value="">-- Selecione o Perfil para Mesclar --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} (ID: {{ $c->id }} - {{ $c->email ?? 'sem email' }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-[5px] transition-all shadow-sm uppercase tracking-wider font-outfit">
                        Mesclar Perfis Manualmente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Filtro e Ações -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm">
        
        <!-- Pesquisa Moderna -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery" 
                   @input="currentPage = 1"
                   placeholder="Pesquise por nome, email, documento ou telefone..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <!-- Botão de Limpar Filtro -->
            <button x-show="searchQuery" 
                    @click="searchQuery = ''; currentPage = 1;" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600"
                    x-cloak>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

    </div>

    <!-- Seção de Clientes Ativos em formato de Cards Responsivos -->
    <div class="space-y-4">
        
        <!-- Grid de Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 w-full">
            @forelse($clients as $client)
                <div x-show="isClientOnCurrentPage({{ $client->id }})" 
                     x-transition
                     class="w-full flex"
                >
                    <div class="{{ in_array($client->id, $topClientIds) ? 'bg-gradient-to-br from-amber-100/50 to-yellow-50/20 dark:from-amber-950/40 dark:to-slate-900/40 border-amber-400 dark:border-amber-700/60 ring-1 ring-amber-400/20 dark:ring-amber-950/30 shadow-md' : (!$client->registration_completed ? 'bg-amber-50/25 border-amber-200' : 'bg-white border-slate-200') }} border p-5 rounded-[5px] shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all duration-200 w-full relative overflow-hidden">
                    
                    @if(in_array($client->id, $topClientIds))
                        <!-- Destaque Ouro -->
                        <div class="absolute top-0 right-0 bg-gradient-to-r from-amber-500 to-yellow-400 text-slate-950 text-[8px] font-black uppercase tracking-wider px-3 py-1 rounded-bl-[5px] flex items-center gap-1 shadow-md border-b border-l border-amber-600/30">
                            <span class="text-[10px] text-amber-950">★</span> <span>Cliente Principal</span>
                        </div>
                    @endif

                    <!-- Topo do Card (Foto + Identificação) -->
                    <div class="flex items-center gap-3">
                        @php
                            $firstLetter = mb_strtoupper(mb_substr($client->name, 0, 1));
                            $charVal = ord($firstLetter);
                            $themes = [
                                0 => ['bg' => 'bg-blue-50 border-blue-200 text-blue-700', 'dark' => 'dark:bg-blue-950/40 dark:border-blue-900/60 dark:text-blue-300'],
                                1 => ['bg' => 'bg-emerald-50 border-emerald-200 text-emerald-700', 'dark' => 'dark:bg-emerald-950/40 dark:border-emerald-900/60 dark:text-emerald-300'],
                                2 => ['bg' => 'bg-indigo-50 border-indigo-200 text-indigo-700', 'dark' => 'dark:bg-indigo-950/40 dark:border-indigo-900/60 dark:text-indigo-300'],
                                3 => ['bg' => 'bg-purple-50 border-purple-200 text-purple-700', 'dark' => 'dark:bg-purple-950/40 dark:border-purple-900/60 dark:text-purple-300'],
                                4 => ['bg' => 'bg-pink-50 border-pink-200 text-pink-700', 'dark' => 'dark:bg-pink-950/40 dark:border-pink-900/60 dark:text-pink-300'],
                                5 => ['bg' => 'bg-rose-50 border-rose-200 text-rose-700', 'dark' => 'dark:bg-rose-950/40 dark:border-rose-900/60 dark:text-rose-300'],
                                6 => ['bg' => 'bg-amber-50 border-amber-250 text-amber-800', 'dark' => 'dark:bg-amber-950/40 dark:border-amber-900/60 dark:text-amber-300'],
                                7 => ['bg' => 'bg-violet-50 border-violet-200 text-violet-700', 'dark' => 'dark:bg-violet-950/40 dark:border-violet-900/60 dark:text-violet-300'],
                            ];
                            $themeIndex = ($charVal ?: 0) % count($themes);
                            $selectedTheme = $themes[$themeIndex];
                        @endphp
                        <div class="w-12 h-12 rounded-full overflow-hidden flex items-center justify-center shrink-0 shadow-inner border {{ $selectedTheme['bg'] }} {{ $selectedTheme['dark'] }}">
                            @if($client->avatar)
                                <img src="{{ asset('storage/' . $client->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-extrabold uppercase">
                                    {{ collect(explode(' ', $client->name))->map(fn($n) => mb_substr($n, 0, 1))->take(2)->join('') }}
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <h4 class="font-bold text-slate-900 truncate" title="{{ $client->name }}">{{ $client->name }}</h4>
                                @if(!$client->registration_completed)
                                    <span class="bg-amber-100 text-amber-800 text-sm font-bold px-1.5 py-0.5 rounded-[5px] border border-amber-200 shrink-0">Pendente</span>
                                @endif
                            </div>
                            <p class="text-sm text-primary-600 truncate font-medium" title="{{ $client->email }}">{{ $client->email }}</p>
                        </div>
                    </div>

                    <!-- Dados de Contato -->
                    <div class="space-y-2 pt-3 border-t border-slate-100 text-sm text-slate-500">
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-medium">WhatsApp / Telefone:</span>
                            <span class="text-slate-800 font-semibold truncate max-w-[150px]">{{ $client->phone ?? 'Não informado' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-400 font-medium">CPF / CNPJ:</span>
                            <span class="text-slate-850 font-mono font-semibold truncate max-w-[150px]">{{ $client->document ?? 'Não informado' }}</span>
                        </div>

                        <!-- Bloco de Estatísticas de Projetos -->
                        <div class="grid grid-cols-2 gap-2 bg-slate-50 border border-slate-100 p-2.5 rounded-[5px] text-xs my-2">
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Total Orçamentos</span>
                                <span class="text-slate-800 font-black text-sm block mt-0.5">R$ {{ number_format($client->total_value, 2, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-[10px] text-slate-400 font-bold uppercase tracking-wider block">Status Trabalhos</span>
                                <span class="text-slate-700 font-medium block mt-0.5">
                                    <strong class="text-slate-850 font-black text-sm">{{ $client->projects_count }}</strong> projetos
                                    <span class="text-[11px] text-slate-400 block font-normal mt-0.5">
                                        Aceitos: <strong class="text-emerald-600 font-bold">{{ $client->approved_count }}</strong> | Rej: <strong class="text-red-500 font-bold">{{ $client->rejected_count }}</strong>
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="flex items-center justify-end gap-1 pt-3 border-t border-slate-100">
                        <!-- Botão Visualizar -->
                        <a href="{{ route('clients.show', $client->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-emerald-600 hover:bg-emerald-50 rounded-[5px] transition-all border-0 shadow-none" title="Visualizar Cliente">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>

                        <!-- Botão Editar -->
                        <a href="{{ route('clients.edit', $client->id) }}" class="w-8 h-8 flex items-center justify-center bg-transparent text-primary-600 hover:bg-primary-50 rounded-[5px] transition-all border-0 shadow-none" title="Editar Cliente">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </a>
                        
                        <!-- Botão Excluir -->
                        <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Cliente', message: 'Tem certeza de que deseja excluir o cliente <strong class=\'text-slate-800\'>{{ addslashes($client->name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Todos os projetos e orçamentos vinculados a este cliente também serão excluídos permanentemente.</span>', action: '{{ route('clients.destroy', $client->id) }}', highSecurity: false })" class="w-8 h-8 flex items-center justify-center bg-transparent text-red-600 hover:bg-red-55 rounded-[5px] transition-all border-0 shadow-none" title="Excluir Cliente">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    </div>
                </div>
            @empty
                <div class="col-span-full border-2 border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]">
                    Nenhum cliente cadastrado ainda.
                </div>
            @endforelse
        </div>
        
        <!-- Mensagem de Nenhum Resultado da Busca (Filtragem Alpine) -->
        <div x-show="searchQuery !== '' && totalFilteredCount === 0" 
             class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]"
             x-cloak>
            Nenhum cliente atende aos critérios da sua pesquisa.
        </div>

        <!-- Painel de Paginação Dinâmica (Sem Reload) -->
        <div x-show="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 border-t border-slate-100 dark:border-slate-800/60 pt-6 mt-4" x-cloak>
            <div class="text-xs font-semibold text-slate-400 dark:text-slate-500 uppercase tracking-wider">
                Mostrando <span class="text-slate-800 dark:text-slate-200" x-text="Math.min((currentPage - 1) * perPage + 1, totalFilteredCount)"></span> a 
                <span class="text-slate-800 dark:text-slate-200" x-text="Math.min(currentPage * perPage, totalFilteredCount)"></span> de 
                <span class="text-slate-800 dark:text-slate-200" x-text="totalFilteredCount"></span> clientes
            </div>
            
            <div class="flex items-center gap-1.5">
                <button type="button" @click="prevPage()" :disabled="currentPage === 1" 
                    class="h-8 px-3 rounded-[5px] border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    Anterior
                </button>
                
                <template x-for="p in totalPagesArray" :key="p">
                    <button type="button" 
                        @click="p !== '...' ? currentPage = p : null" 
                        :disabled="p === '...'"
                        :class="{
                            'bg-primary-500 text-white border-primary-500 shadow-sm shadow-primary-500/20': currentPage === p,
                            'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-300 border-slate-200 dark:border-slate-850 hover:bg-slate-50 dark:hover:bg-slate-800': currentPage !== p && p !== '...',
                            'text-slate-400 dark:text-slate-650 border-transparent bg-transparent cursor-default select-none': p === '...'
                        }" 
                        class="w-8 h-8 rounded-[5px] border text-xs font-bold transition-all"
                        x-text="p">
                    </button>
                </template>
                
                <button type="button" @click="nextPage()" :disabled="currentPage === totalPages" 
                    class="h-8 px-3 rounded-[5px] border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 text-[10px] font-bold uppercase tracking-wider transition-colors hover:bg-slate-50 dark:hover:bg-slate-800 disabled:opacity-40 disabled:cursor-not-allowed">
                    Próxima
                </button>
            </div>
        </div>

    </div>

    <!-- Botão Flutuante Redondo (FAB) -->
    <a href="{{ route('clients.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" title="Novo Cliente">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function clientList() {
        return {
            searchQuery: '',
            currentPage: 1,
            perPage: 12,
            clients: @json($mappedClients),

            get totalFilteredCount() {
                return this.filteredClients.length;
            },

            get filteredClients() {
                return this.clients.filter(c => {
                    if (this.searchQuery) {
                        const query = this.searchQuery.toLowerCase().trim();
                        return c.searchable.includes(query);
                    }
                    return true;
                });
            },

            get totalPages() {
                return Math.ceil(this.totalFilteredCount / this.perPage) || 1;
            },

            get totalPagesArray() {
                const total = this.totalPages;
                const current = this.currentPage;
                const delta = 1;
                const range = [];
                for (let i = Math.max(2, current - delta); i <= Math.min(total - 1, current + delta); i++) {
                    range.push(i);
                }
                if (current - delta > 2) range.unshift('...');
                range.unshift(1);
                if (current + delta < total - 1) range.push('...');
                if (total > 1) range.push(total);
                return range;
            },

            prevPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                    this.scrollToTop();
                }
            },

            nextPage() {
                if (this.currentPage < this.totalPages) {
                    this.currentPage++;
                    this.scrollToTop();
                }
            },

            scrollToTop() {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            isClientOnCurrentPage(clientId) {
                const index = this.filteredClients.findIndex(c => c.id === clientId);
                if (index === -1) return false;
                const start = (this.currentPage - 1) * this.perPage;
                const end = start + this.perPage;
                return index >= start && index < end;
            }
        }
    }
</script>
@endsection
