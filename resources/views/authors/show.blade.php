@extends('layouts.app')

@section('title', 'Detalhes do Autor - Gestor de Freelas')
@section('page_title', 'Visualizar Autor')

@section('content')
<div class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('authors.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
    </div>

    <!-- Grid Principal (Mobile First) -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        <!-- Cartão de Perfil (Esquerda - 1 Coluna) -->
        <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm flex flex-col items-center text-center">
            
            <!-- Imagem do Avatar -->
            <div class="relative w-32 h-32 rounded-full border-2 border-slate-100 bg-slate-50 overflow-hidden shadow-inner flex items-center justify-center mb-4">
                @if($author->avatar)
                    <img src="{{ asset('storage/' . $author->avatar) }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                @endif
            </div>

            <!-- Dados Básicos -->
            <h3 class="text-xl font-bold text-slate-900 break-words max-w-full" title="{{ $author->name }}">{{ $author->name }}</h3>
            <span class="text-sm text-primary-650 font-medium break-all max-w-full mt-1">{{ $author->email }}</span>

            <!-- Divisor -->
            <div class="w-full border-t border-slate-100 my-5"></div>

            <!-- Bloco de Ações Rápidas -->
            <div class="w-full space-y-3">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block text-left mb-2">Ações Rápidas</span>

                <!-- Enviar E-mail -->
                <a href="mailto:{{ $author->email }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Enviar E-mail
                </a>

                <!-- WhatsApp -->
                @if($author->phone)
                    <a href="https://api.whatsapp.com/send?phone=55{{ preg_replace('/\D/', '', $author->phone) }}" target="_blank" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                        <!-- Ícone do WhatsApp em SVG -->
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.863-9.855.001-2.63-1.02-5.101-2.877-6.958C16.604 1.936 14.136.918 11.51.916 6.072.916 1.653 5.338 1.65 10.773c-.001 1.672.43 3.302 1.247 4.716L1.89 20.15l4.757-1.246-.002.001zM17.65 14.4c-.3-.15-1.782-.88-2.062-.98-.28-.1-.48-.15-.68.15-.2.3-.77.98-.94 1.18-.17.2-.34.22-.64.07-1.125-.56-1.933-1.004-2.705-2.327-.2-.35-.02-.54.15-.71.16-.16.35-.35.52-.53.18-.18.24-.3.35-.5.11-.2.06-.38-.03-.53-.08-.15-.68-1.63-.93-2.24-.25-.6-.5-.52-.68-.53-.18-.01-.38-.01-.58-.01-.2 0-.53.07-.8.38-.28.3-1.06 1.04-1.06 2.53s1.08 2.93 1.23 3.13c.15.2 2.13 3.25 5.16 4.56.72.31 1.28.5 1.72.64.73.23 1.39.2 1.92.12.58-.08 1.78-.73 2.03-1.43.25-.7.25-1.29.17-1.43-.08-.14-.28-.24-.58-.39z"/>
                        </svg>
                        Conversar no WhatsApp
                    </a>
                @endif

                <!-- Editar Cadastro -->
                <a href="{{ route('authors.edit', $author->id) }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Editar Autor
                </a>

                <!-- Excluir Autor -->
                <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Autor', message: 'Tem certeza de que deseja excluir o autor <strong class=\'text-slate-800\'>{{ addslashes($author->name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Esta ação excluirá permanentemente o autor do sistema.</span>', action: '{{ route('authors.destroy', $author->id) }}', highSecurity: false })" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Excluir Autor
                </button>
            </div>

        </div>

        <!-- Conteúdo Detalhado (Direita - 2 Colunas) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Grid de Métricas de Desempenho -->
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm">
                    <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">Valor Gerado</span>
                    <h5 class="text-base sm:text-lg font-black text-slate-800 mt-1">R$ {{ number_format($totalValue, 2, ',', '.') }}</h5>
                </div>
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm">
                    <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">Aceitos</span>
                    <h5 class="text-base sm:text-lg font-black text-emerald-600 mt-1">{{ $approvedCount }}</h5>
                </div>
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm">
                    <span class="text-[10px] text-slate-400 font-bold uppercase block tracking-wider">Rejeitados</span>
                    <h5 class="text-base sm:text-lg font-black text-red-600 mt-1">{{ $rejectedCount }}</h5>
                </div>
            </div>

            <!-- Cartão 1: Informações Cadastrais -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Informações Cadastrais</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <!-- WhatsApp/Telefone -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Telefone ou WhatsApp</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $author->phone ?? 'Não informado' }}</span>
                    </div>

                    <!-- CPF/CNPJ -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Documento (CPF / CNPJ)</span>
                        <span class="text-slate-800 font-mono font-bold block text-sm">{{ $author->document ?? 'Não informado' }}</span>
                    </div>

                    <!-- E-mail Comercial -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">E-mail Comercial</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $author->email }}</span>
                    </div>

                    <!-- Criado em -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Data de Cadastro</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $author->created_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Cartão 2: Biografia do Autor -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Biografia do Autor</h4>
                
                <div class="bg-slate-50 border border-slate-100 p-5 rounded-[5px] text-sm text-slate-700 leading-relaxed font-normal text-justify whitespace-pre-wrap break-words min-h-[120px]">
                    @if($author->bio)
                        {{ $author->bio }}
                    @else
                        <span class="text-slate-400 italic">Nenhuma biografia registrada para este autor.</span>
                    @endif
                </div>
            </div>

            <!-- Cartão 3: Principais Parcerias (Outros autores com quem colaborou) -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Principais Parcerias</h4>
                @if(count($partners) > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($partners as $partnerId => $p)
                            <div class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-100 rounded-[5px]">
                                <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0 shadow-inner">
                                    @if($p['avatar'])
                                        <img src="{{ asset('storage/' . $p['avatar']) }}" class="w-full h-full object-cover">
                                    @else
                                        <svg class="w-full h-full text-slate-350 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h5 class="font-bold text-slate-800 text-sm truncate">{{ $p['name'] }}</h5>
                                    <p class="text-xs text-slate-400 mt-0.5">Colaborou em <strong class="text-slate-700 font-semibold">{{ $p['count'] }}</strong> {{ $p['count'] == 1 ? 'projeto' : 'projetos' }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-400 text-sm italic">Este autor ainda não colaborou com outros membros em projetos.</span>
                @endif
            </div>

            <!-- Cartão 4: Projetos / Orçamentos Vinculados -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Projetos / Orçamentos Vinculados</h4>
                @if($projects->count() > 0)
                    <div class="space-y-3">
                        @foreach($projects as $proj)
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 border border-slate-150 rounded-[5px] hover:border-slate-300 transition-colors">
                                <div class="min-w-0">
                                    <h5 class="font-extrabold text-slate-800 text-sm truncate">{{ $proj->title }}</h5>
                                    <p class="text-xs text-slate-400 mt-1 flex flex-wrap items-center gap-1.5">
                                        <span>Cliente: <strong>{{ $proj->client->name }}</strong></span>
                                        <span>•</span>
                                        <span>Valor: <strong class="text-slate-650">R$ {{ number_format($proj->total_value, 2, ',', '.') }}</strong></span>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    @php
                                        $statusStyles = [
                                            'rascunho' => 'bg-slate-100 text-slate-700 border-slate-200',
                                            'analisando' => 'bg-amber-100 text-amber-800 border-amber-200',
                                            'aprovado' => 'bg-emerald-100 text-emerald-850 border-emerald-200',
                                            'rejeitado' => 'bg-red-100 text-red-800 border-red-200',
                                            'quitado' => 'bg-purple-100 text-purple-800 border-purple-200',
                                            'finalizado' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        ];
                                        $badgeStyle = $statusStyles[$proj->status] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="text-[9px] font-black uppercase tracking-wider px-2 py-0.5 rounded-[4px] border {{ $badgeStyle }}">
                                        {{ $proj->status }}
                                    </span>
                                    <a href="{{ route('projects.show', $proj->id) }}" class="p-1 text-slate-450 hover:text-slate-600 transition-colors" title="Ver Detalhes do Projeto">
                                        <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <span class="text-slate-400 text-sm italic">Nenhum projeto ou orçamento vinculado a este autor.</span>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
