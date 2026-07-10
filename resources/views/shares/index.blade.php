@extends('layouts.app')

@section('title', 'Compartilhamento de Arquivos - Gestor de Freelas')
@section('page_title', 'Compartilhamento de Arquivos')

@section('content')
<style>
    .pulse-glow-rose {
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.15);
        animation: pulse-rose 1.5s infinite alternate;
    }
    @keyframes pulse-rose {
        0% {
            box-shadow: 0 0 5px rgba(239, 68, 68, 0.15), inset 0 0 5px rgba(239, 68, 68, 0.05);
            border-color: rgba(239, 68, 68, 0.35);
        }
        100% {
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.45), inset 0 0 12px rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.8);
        }
    }
</style>
<div x-data="sharesManager()" class="space-y-6">
    
    <!-- Top Cards (Métricas com Cores Diferentes) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        
        <!-- Armazenamento Total (Card Azul) -->
        <div class="bg-blue-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-blue-100 uppercase tracking-wider">Armazenamento</p>
                <h3 class="text-2xl font-extrabold text-white mt-2">
                    {{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($totalStorage) ?? '0 Bytes' }}
                </h3>
                <span class="text-xs text-blue-100/95 font-medium block mt-1.5">
                    Espaço total utilizado
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">💾</span>
            </div>
        </div>

        <!-- Downloads Totais (Card Verde) -->
        <div class="bg-emerald-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-emerald-100 uppercase tracking-wider">Downloads</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $totalDownloads }}
                </h3>
                <span class="text-xs text-emerald-100/95 font-medium block mt-1.5">
                    Downloads realizados
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">📥</span>
            </div>
        </div>

        <!-- Links Ativos (Card Roxo) -->
        <div class="bg-purple-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-purple-100 uppercase tracking-wider">Links Ativos</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $activeSharesCount }}
                </h3>
                <span class="text-xs text-purple-100/95 font-medium block mt-1.5">
                    Compartilhamentos ativos
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">🔗</span>
            </div>
        </div>

        <!-- Links Expirados (Card Vermelho) -->
        <div class="bg-rose-600 rounded-[5px] p-6 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow duration-200">
            <div>
                <p class="text-sm font-bold text-rose-100 uppercase tracking-wider">Expirados</p>
                <h3 class="text-3xl font-extrabold text-white mt-2">
                    {{ $expiredSharesCount }}
                </h3>
                <span class="text-xs text-rose-100/95 font-medium block mt-1.5">
                    Validades encerradas
                </span>
            </div>
            <div class="w-12 h-12 rounded-[5px] bg-white/20 text-white flex items-center justify-center shadow-sm">
                <span class="text-2xl">⏳</span>
            </div>
        </div>
    </div>

    <!-- Busca e Filtros -->
    <div class="bg-white border border-slate-200 p-4 rounded-[5px] shadow-sm select-none space-y-4">
        
        <!-- Campo de Busca -->
        <div class="relative w-full">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-slate-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" 
                   x-model="searchQuery"
                   placeholder="Pesquise por título ou descrição..." 
                   class="w-full pl-10 pr-10 py-2.5 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
            <button type="button" 
                    x-show="searchQuery" 
                    @click="searchQuery = ''" 
                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-655 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- Filtros Rápidos (Status) -->
        <div class="flex flex-wrap items-center gap-2 text-xs">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mr-1">Status:</span>
            
            <!-- Todos -->
            <button type="button" 
                    @click="filterStatus = ''" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="!filterStatus ? 'bg-slate-900 dark:bg-slate-100 border-slate-900 dark:border-slate-100 text-white dark:text-slate-900' : 'bg-slate-100 dark:bg-slate-800 border-slate-200 dark:border-slate-750 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700/50'">
                Todos
            </button>

            <!-- Ativos -->
            <button type="button" 
                    @click="filterStatus = 'ativo'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="filterStatus === 'ativo' ? 'bg-emerald-600 border-emerald-600 text-white shadow-md shadow-emerald-600/10' : 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 dark:border-emerald-900/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/30'">
                Ativos
            </button>

            <!-- Desativados -->
            <button type="button" 
                    @click="filterStatus = 'inativo'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="filterStatus === 'inativo' ? 'bg-red-600 border-red-600 text-white shadow-md shadow-red-650/10' : 'bg-red-50 dark:bg-red-950/40 border-red-200 dark:border-red-900/40 text-red-700 dark:text-red-300 hover:bg-red-100 dark:hover:bg-red-900/30'">
                Desativados
            </button>

            <!-- Expirados -->
            <button type="button" 
                    @click="filterStatus = 'expirado'" 
                    class="px-3.5 py-1.5 rounded-full text-xs font-bold transition-all cursor-pointer border uppercase tracking-wider focus:outline-none"
                    :class="filterStatus === 'expirado' ? 'bg-amber-600 border-amber-600 text-white shadow-md shadow-amber-600/10' : 'bg-amber-50 dark:bg-amber-950/40 border-amber-200 dark:border-amber-900/40 text-amber-700 dark:text-amber-300 hover:bg-amber-100 dark:hover:bg-amber-900/30'">
                Expirados
            </button>
        </div>
    </div>

    <!-- Grid de Compartilhamentos -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($shares as $share)
            @php
                $isExpired = $share->expires_at->isPast();
                $isActive = $share->is_active;
                
                // Cálculo de dias restantes
                $daysRemaining = round(now()->diffInDays($share->expires_at, false));

                // Estilização dinâmica por validade
                $shareCardClass = 'bg-white border-slate-200';
                $glowClass = '';

                if (!$isActive) {
                    $shareCardClass = 'bg-slate-50/50 border-slate-200 opacity-75';
                } elseif ($isExpired) {
                    $shareCardClass = 'bg-rose-50/20 border-rose-350 dark:bg-rose-950/10';
                    $glowClass = 'pulse-glow-rose';
                } elseif ($daysRemaining <= 1) {
                    $shareCardClass = 'bg-rose-50/45 border-rose-300 dark:bg-rose-950/15';
                    $glowClass = 'pulse-glow-rose animate-pulse';
                } elseif ($daysRemaining <= 4) {
                    $shareCardClass = 'bg-amber-50/45 border-amber-300 dark:bg-amber-950/15';
                } else {
                    $shareCardClass = 'bg-emerald-50/40 border-emerald-250 dark:bg-emerald-950/10';
                }

                $totalFilesSize = $share->items->sum('file_size');
            @endphp
            
            <div x-show="shouldShowShare('{{ addslashes($share->title) }}', '{{ addslashes(strip_tags($share->description)) }}', {{ $isActive ? 'true' : 'false' }}, {{ $isExpired ? 'true' : 'false' }})"
                 class="{{ $shareCardClass }} {{ $glowClass }} border rounded-[5px] p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between space-y-4 relative overflow-hidden"
                 x-transition>
                
                <!-- Tag Superior de Status / Vencimento -->
                <div class="flex items-center justify-between">
                    @if(!$isActive)
                        <span class="bg-slate-200 text-slate-700 text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[5px]">Desativado</span>
                    @elseif($isExpired)
                        <span class="bg-rose-600 text-white text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[5px] flex items-center gap-0.5">
                            <span>⏳</span> Expirado
                        </span>
                    @else
                        <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[5px]
                            {{ $daysRemaining <= 1 ? 'bg-rose-100 text-rose-800' : ($daysRemaining <= 4 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                            @if($daysRemaining <= 0)
                                Expira hoje
                            @else
                                Expira em {{ $daysRemaining }} {{ $daysRemaining == 1 ? 'dia' : 'dias' }}
                            @endif
                        </span>
                    @endif

                    <!-- Tamanho Total -->
                    <span class="text-xs font-bold text-slate-400">
                        {{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($totalFilesSize) }}
                    </span>
                </div>

                <!-- Corpo do Card: Título, Descrição, Arquivos -->
                <div class="space-y-2">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm truncate" title="{{ $share->title }}">{{ $share->title }}</h4>
                        @if($share->description)
                            <p class="text-xs text-slate-400 line-clamp-2 mt-1 leading-relaxed">{{ $share->description }}</p>
                        @endif
                    </div>

                    <!-- Lista Resumida de Arquivos -->
                    <div class="bg-slate-50/70 dark:bg-slate-800/40 border border-slate-100 dark:border-slate-800 p-2.5 rounded-[5px] space-y-1 text-xs text-slate-500 dark:text-slate-400">
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wide block mb-1">Arquivos ({{ $share->items->count() }})</span>
                        @foreach($share->items->take(2) as $item)
                            <div class="flex items-center justify-between gap-2">
                                <span class="truncate font-medium text-slate-700 dark:text-slate-200" title="{{ $item->filename }}">📄 {{ $item->filename }}</span>
                                <span class="shrink-0 text-slate-400 font-semibold">{{ app(\App\Http\Controllers\FileShareController::class)->formatBytes($item->file_size) }}</span>
                            </div>
                        @endforeach
                        @if($share->items->count() > 2)
                            <span class="text-[10px] text-primary-500 font-extrabold block pt-0.5">e mais {{ $share->items->count() - 2 }} arquivo(s)...</span>
                        @endif
                    </div>
                </div>

                <!-- Detalhes de Download, Visualização e Segurança -->
                <div class="grid grid-cols-3 gap-2 text-center text-xs border-t border-b border-slate-100 py-2.5">
                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Visualizações</span>
                        <span class="font-extrabold text-slate-700">{{ $share->view_count }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Downloads</span>
                        <span class="font-extrabold text-slate-700">
                            {{ $share->download_count }}
                            @if($share->download_limit)
                                <span class="text-slate-400 font-medium">/{{ $share->download_limit }}</span>
                            @endif
                        </span>
                    </div>
                    <div>
                        <span class="text-slate-400 font-bold uppercase tracking-wider block text-[10px]">Segurança</span>
                        <span class="font-extrabold {{ $share->password ? 'text-emerald-600' : 'text-slate-500' }}">
                            {{ $share->password ? '🔐 Com Senha' : '🔓 Aberto' }}
                        </span>
                    </div>
                </div>

                <!-- Ações e Rodapé -->
                <div class="flex items-center gap-1 shrink-0 pt-2 no-print">
                    <button type="button" 
                            @click="copyShareLink('{{ route('public.share.show', $share->share_token) }}', $event)"
                            class="flex-1 text-center bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider py-2.5 rounded-[5px] transition-all shadow-sm flex items-center justify-center gap-1"
                            title="Copiar Link"
                    >
                        <span>🔗</span> <span class="inline md:hidden xl:inline">Copiar Link</span>
                    </button>

                    <!-- Toggle Ativo -->
                    <form action="{{ route('revisoes.shares.toggle-active', $share->id) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="w-8 h-8 flex items-center justify-center rounded-[5px] transition-all border-0 shadow-none bg-transparent cursor-pointer
                                    {{ $isActive ? 'text-emerald-600 hover:bg-emerald-50' : 'text-slate-400 hover:bg-slate-50' }}"
                                title="{{ $isActive ? 'Desativar Link' : 'Ativar Link' }}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.07 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                            </svg>
                        </button>
                    </form>

                    <!-- Configurar Modal (Inline Edit) -->
                    <button type="button" 
                            @click="openSettingsModal({{ $share }})"
                            class="w-8 h-8 flex items-center justify-center bg-transparent border-0 shadow-none text-primary-650 hover:bg-primary-50 rounded-[5px] transition-all cursor-pointer" 
                            title="Editar Configurações">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                        </svg>
                    </button>

                    <!-- Excluir -->
                    <button type="button" 
                            @click="$dispatch('trigger-global-delete', { title: 'Excluir Compartilhamento', message: 'Deseja realmente excluir este compartilhamento e deletar todos os seus arquivos do servidor de forma irreversível?', action: '{{ route('revisoes.shares.destroy', $share->id) }}', highSecurity: false })"
                            class="w-8 h-8 flex items-center justify-center bg-transparent border-0 shadow-none text-rose-600 hover:bg-rose-50 rounded-[5px] transition-all cursor-pointer" 
                            title="Excluir Compartilhamento">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>

                </div>

            </div>
        @empty
            <div class="col-span-full border border-dashed border-slate-200 bg-white p-12 text-center text-slate-400 rounded-[5px] font-semibold text-sm shadow-xs">
                Nenhum compartilhamento ativo encontrado.
            </div>
        @endforelse
    </div>

    <!-- Estado de Filtro Vazio (Client-side) -->
    <div x-show="sharesList.filter(s => shouldShowShare(s.title, s.description, s.is_active, s.is_expired)).length === 0 && sharesList.length > 0" 
         class="text-center py-12 bg-white border border-slate-200 rounded-[5px] shadow-sm select-none" 
         x-cloak>
        <span class="text-5xl block">🔍</span>
        <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight mt-4">Nenhum compartilhamento corresponde à busca</h3>
        <p class="text-xs text-slate-400 mt-1">Experimente limpar a sua busca ou trocar as tags selecionadas.</p>
    </div>

    <!-- Modal de Configurações do Compartilhamento -->
    <div x-show="settingsModalOpen" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 9999;"
         x-transition.opacity
         x-cloak>
        <div class="bg-white border border-slate-250 shadow-2xl rounded-lg max-w-md w-full p-6 space-y-4 text-left select-none" @click.away="settingsModalOpen = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-outfit font-black text-slate-800 text-md uppercase tracking-tight">Editar Compartilhamento</h3>
                <button @click="settingsModalOpen = false" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form :action="'{{ route('revisoes.shares.index') }}' + '/' + editShareData.id + '/settings'" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <!-- Slider Dias de Expiração (1 a 30 dias) -->
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <label for="edit_expires_days" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Prazo de Expiração</label>
                        <span class="text-xs font-black text-primary-600 uppercase" x-text="editExpiresDays + (editExpiresDays == 1 ? ' Dia' : ' Dias')">7 Dias</span>
                    </div>
                    <input type="range" name="expires_days" id="edit_expires_days" min="1" max="30" x-model="editExpiresDays" class="w-full h-2 bg-slate-100 rounded-lg appearance-none cursor-pointer accent-primary-500" />
                    <p class="text-[10px] text-slate-400">Altere a validade calculada a partir do envio original do arquivo.</p>
                </div>

                <!-- Limite de Downloads -->
                <div class="space-y-1.5">
                    <label for="edit_download_limit" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Limite de Downloads</label>
                    <input type="number" name="download_limit" id="edit_download_limit" x-model="editDownloadLimit" placeholder="Ex: 5 (vazio para ilimitado)" class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                </div>

                <!-- Senha de Acesso (Segurança) -->
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2 mb-1">
                        <input type="checkbox" name="has_password" value="1" id="edit_has_password" x-model="editHasPassword" class="rounded text-primary-600 border-slate-350 focus:ring-primary-500/20 w-4 h-4 cursor-pointer" />
                        <label for="edit_has_password" class="text-xs font-semibold text-slate-700 cursor-pointer select-none">Proteger link com Senha de Segurança</label>
                    </div>
                    <div x-show="editHasPassword" x-transition>
                        <input type="password" name="password" id="edit_password" placeholder="Defina ou altere a senha..." class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                        <p class="text-[10px] text-slate-400 mt-1">Apenas as pessoas que possuírem essa senha conseguirão acessar os arquivos.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" @click="settingsModalOpen = false" class="px-4 py-2 border border-slate-200 text-xs font-bold uppercase rounded-[5px] hover:bg-slate-100 transition-colors text-slate-600">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider px-6 py-2.5 rounded-[5px] transition-colors">
                        Salvar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Botão de Adicionar Flutuante -->
    <a href="{{ route('revisoes.shares.create') }}" class="fixed bottom-8 right-8 z-40 w-14 h-14 bg-primary-600 hover:bg-primary-700 text-white rounded-full flex items-center justify-center shadow-lg hover:shadow-xl transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/30" title="Novo Compartilhamento">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
        </svg>
    </a>

</div>

<script>
    function sharesManager() {
        return {
            settingsModalOpen: false,
            editShareData: {},
            editExpiresDays: 7,
            editDownloadLimit: '',
            editHasPassword: false,

            // Filter states
            searchQuery: '{{ request('search', '') }}',
            filterStatus: '{{ request('status', '') }}',
            sharesList: {!! json_encode($shares->map(function($s) {
                return [
                    'id' => $s->id,
                    'title' => $s->title,
                    'description' => $s->description,
                    'is_active' => $s->is_active,
                    'is_expired' => $s->expires_at->isPast()
                ];
            })) !!},

            shouldShowShare(title, description, isActive, isExpired) {
                if (this.filterStatus) {
                    if (this.filterStatus === 'ativo' && (!isActive || isExpired)) return false;
                    if (this.filterStatus === 'inativo' && isActive) return false;
                    if (this.filterStatus === 'expirado' && !isExpired) return false;
                }
                
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const t = (title || '').toLowerCase();
                    const d = (description || '').toLowerCase();
                    return t.includes(q) || d.includes(q);
                }
                return true;
            },

            copyShareLink(url, e) {
                const button = e.currentTarget;
                const originalText = button.innerHTML;
                navigator.clipboard.writeText(url).then(() => {
                    button.innerHTML = '<span>✓</span> <span>Copiado!</span>';
                    button.classList.add('bg-emerald-600');
                    setTimeout(() => {
                        button.innerHTML = originalText;
                        button.classList.remove('bg-emerald-600');
                    }, 2000);
                });
            },

            openSettingsModal(share) {
                this.editShareData = share;
                this.editDownloadLimit = share.download_limit || '';
                this.editHasPassword = !!share.password;
                
                // Calcula dias passados desde a criação e define slider proporcional
                const createdDate = new Date(share.created_at);
                const expiresDate = new Date(share.expires_at);
                const diffTime = Math.abs(expiresDate - createdDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                this.editExpiresDays = diffDays || 7;

                this.settingsModalOpen = true;
            }
        }
    }
</script>
@endsection
