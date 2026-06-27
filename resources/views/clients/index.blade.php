@extends('layouts.app')

@section('title', 'Clientes - Gestor de Freelas')
@section('page_title', 'Gerenciamento de Clientes')

@section('content')

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
                   placeholder="Pesquise por nome, email, documento ou telefone..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <!-- Botão de Limpar Filtro -->
            <button x-show="searchQuery" 
                    @click="searchQuery = ''" 
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
                <div x-show="matchesSearch('{{ addslashes($client->name) }} {{ addslashes($client->email) }} {{ addslashes($client->phone) }} {{ addslashes($client->document) }}')" 
                     x-transition
                     class="w-full flex"
                >
                    <div class="{{ in_array($client->id, $topClientIds) ? 'bg-gradient-to-br from-amber-50/40 to-yellow-50/10 border-amber-350 ring-1 ring-amber-300/30 shadow-md' : (!$client->registration_completed ? 'bg-amber-50/25 border-amber-200' : 'bg-white border-slate-200') }} border p-5 rounded-[5px] shadow-sm flex flex-col justify-between space-y-4 hover:shadow-md transition-all duration-200 w-full relative overflow-hidden">
                    
                    @if(in_array($client->id, $topClientIds))
                        <!-- Destaque Ouro -->
                        <div class="absolute top-0 right-0 bg-amber-500 text-amber-950 text-[9px] font-black uppercase tracking-wider px-2.5 py-0.5 rounded-bl-[5px] flex items-center gap-1 shadow-sm">
                            <span>★</span> <span>Principal</span>
                        </div>
                    @endif

                    <!-- Topo do Card (Foto + Identificação) -->
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                            @if($client->avatar)
                                    <img src="{{ asset('storage/' . $client->avatar) }}" class="w-full h-full object-cover">
                            @else
                                <span class="text-sm font-bold text-slate-400">
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
        <div x-show="searchQuery !== '' && countVisibleCards() === 0" 
             class="border border-dashed border-slate-200 p-10 text-center text-slate-400 rounded-[5px]"
             x-cloak>
            Nenhum cliente atende aos critérios da sua pesquisa.
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
            
            matchesSearch(text) {
                if (!this.searchQuery) return true;
                const query = this.searchQuery.toLowerCase().trim();
                return text.toLowerCase().includes(query);
            },

            countVisibleCards() {
                let count = 0;
                const cards = document.querySelectorAll('[x-show*="matchesSearch"]');
                cards.forEach(card => {
                    if (card.style.display !== 'none') {
                        count++;
                    }
                });
                return count;
            }
        }
    }
</script>
@endsection
