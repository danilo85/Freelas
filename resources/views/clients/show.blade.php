@extends('layouts.app')

@section('title', 'Detalhes do Cliente - Gestor de Freelas')
@section('page_title', 'Visualizar Cliente')

@section('content')
<div class="space-y-6">
    
    <!-- Link de Voltar -->
    <div class="flex items-center justify-between">
        <a href="{{ route('clients.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
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
                @if($client->avatar)
                    <img src="{{ asset('storage/' . $client->avatar) }}" class="w-full h-full object-cover">
                @else
                    <svg class="w-full h-full text-slate-300 bg-slate-100" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M24 20.993V24H0v-2.996A14.977 14.977 0 0112.004 15c4.904 0 9.26 2.354 11.996 5.993zM16.002 8.999a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                @endif
            </div>

            <!-- Dados Básicos -->
            <h3 class="text-xl font-bold text-slate-900 break-words max-w-full" title="{{ $client->name }}">{{ $client->name }}</h3>
            <span class="text-sm text-primary-650 font-medium break-all max-w-full mt-1">{{ $client->email }}</span>

            <!-- Divisor -->
            <div class="w-full border-t border-slate-100 my-5"></div>

            <!-- Bloco de Ações Rápidas -->
            <div class="w-full space-y-3">
                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider block text-left mb-2">Ações Rápidas</span>

                <!-- Enviar E-mail -->
                <a href="mailto:{{ $client->email }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Enviar E-mail
                </a>

                <!-- WhatsApp -->
                @if($client->phone)
                    <a href="https://api.whatsapp.com/send?phone=55{{ preg_replace('/\D/', '', $client->phone) }}" target="_blank" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                        <!-- Ícone do WhatsApp em SVG -->
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946C.06 5.348 5.397.01 12.008.01c3.202.001 6.212 1.246 8.477 3.514 2.266 2.268 3.507 5.28 3.505 8.484-.004 6.657-5.34 11.997-11.953 11.997-2.005-.001-3.973-.502-5.73-1.45L0 24zm6.59-4.846c1.6.95 3.188 1.449 4.825 1.451 5.436 0 9.86-4.42 9.863-9.855.001-2.63-1.02-5.101-2.877-6.958C16.604 1.936 14.136.918 11.51.916 6.072.916 1.653 5.338 1.65 10.773c-.001 1.672.43 3.302 1.247 4.716L1.89 20.15l4.757-1.246-.002.001zM17.65 14.4c-.3-.15-1.782-.88-2.062-.98-.28-.1-.48-.15-.68.15-.2.3-.77.98-.94 1.18-.17.2-.34.22-.64.07-1.125-.56-1.933-1.004-2.705-2.327-.2-.35-.02-.54.15-.71.16-.16.35-.35.52-.53.18-.18.24-.3.35-.5.11-.2.06-.38-.03-.53-.08-.15-.68-1.63-.93-2.24-.25-.6-.5-.52-.68-.53-.18-.01-.38-.01-.58-.01-.2 0-.53.07-.8.38-.28.3-1.06 1.04-1.06 2.53s1.08 2.93 1.23 3.13c.15.2 2.13 3.25 5.16 4.56.72.31 1.28.5 1.72.64.73.23 1.39.2 1.92.12.58-.08 1.78-.73 2.03-1.43.25-.7.25-1.29.17-1.43-.08-.14-.28-.24-.58-.39z"/>
                        </svg>
                        Conversar no WhatsApp
                    </a>
                @endif

                <!-- Editar Cadastro -->
                <a href="{{ route('clients.edit', $client->id) }}" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                    </svg>
                    Editar Cliente
                </a>

                <!-- Excluir Cliente -->
                <button type="button" @click="$dispatch('trigger-global-delete', { title: 'Excluir Cliente', message: 'Tem certeza de que deseja excluir o cliente <strong class=\'text-slate-800\'>{{ addslashes($client->name) }}</strong>?<br><span class=\'text-sm text-red-500 mt-1.5 block bg-red-50/50 p-2.5 rounded-[5px] border border-red-100\'>Aviso: Todos os projetos e orçamentos vinculados a este cliente também serão excluídos permanentemente.</span>', action: '{{ route('clients.destroy', $client->id) }}', highSecurity: false })" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Excluir Cliente
                </button>

                <!-- Link Compartilhável de Extrato -->
                <div class="pt-4 border-t border-slate-100 mt-4 text-left" x-data="{ copied: false, shareUrl: '{{ route('public.client.statement', $client->share_token) }}' }">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block text-left mb-2">Link do Extrato (Cliente)</span>
                    <div class="flex items-center gap-1.5">
                        <input type="text" readonly :value="shareUrl" class="w-full px-2.5 py-2 bg-slate-50 border border-slate-200 rounded-[5px] text-[10px] font-mono text-slate-500 focus:outline-none" />
                        <button type="button" @click="navigator.clipboard.writeText(shareUrl); copied = true; setTimeout(() => copied = false, 2000)" class="px-3 py-2 bg-slate-800 hover:bg-slate-900 text-white text-[10px] font-bold rounded-[5px] transition-colors shrink-0">
                            <span x-text="copied ? 'Copiado!' : 'Copiar'"></span>
                        </button>
                    </div>
                    <a :href="shareUrl" target="_blank" class="flex items-center justify-center gap-2.5 w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-[5px] transition-colors shadow-sm mt-3.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Visualizar Extrato Público
                    </a>
                </div>
            </div>

        </div>

        <!-- Conteúdo Detalhado (Direita - 2 Colunas) -->
        <div class="lg:col-span-2 space-y-6">
            
            <!-- Cartão 1: Informações Cadastrais -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="text-sm font-bold text-slate-800 border-b border-slate-100 pb-3 uppercase tracking-wider">Informações Cadastrais</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <!-- WhatsApp/Telefone -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Telefone ou WhatsApp</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $client->phone ?? 'Não informado' }}</span>
                    </div>

                    <!-- CPF/CNPJ -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Documento (CPF / CNPJ)</span>
                        <span class="text-slate-800 font-mono font-bold block text-sm">{{ $client->document ?? 'Não informado' }}</span>
                    </div>

                    <!-- E-mail Comercial -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">E-mail Comercial</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $client->email }}</span>
                    </div>

                    <!-- Criado em -->
                    <div class="space-y-1">
                        <span class="text-slate-400 font-semibold uppercase tracking-wider block">Data de Cadastro</span>
                        <span class="text-slate-800 font-bold block text-sm">{{ $client->created_at->format('d/m/Y \à\s H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Cartão 2: Projetos Associados -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-6">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h4 class="text-sm font-bold text-slate-800 uppercase tracking-wider">Projetos do Cliente</h4>
                    <span class="text-sm text-slate-400 font-bold uppercase">{{ $projects->count() }} {{ $projects->count() == 1 ? 'Projeto' : 'Projetos' }}</span>
                </div>

                <!-- Estatísticas de Projetos -->
                @if($projects->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-[5px] flex items-center justify-between shadow-inner">
                            <div>
                                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">Investimento Total</span>
                                <h5 class="text-lg font-black text-slate-800 mt-1">R$ {{ number_format($projects->sum('total_value'), 2, ',', '.') }}</h5>
                            </div>
                            <div class="w-10 h-10 rounded-[5px] bg-slate-200/50 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>

                        <div class="bg-slate-50 border border-slate-100 p-4 rounded-[5px] flex items-center justify-between shadow-inner">
                            <div>
                                <span class="text-sm font-bold text-slate-400 uppercase tracking-wider">Projetos em Andamento</span>
                                <h5 class="text-lg font-black text-slate-800 mt-1">
                                    {{ $projects->where('status', 'em andamento')->count() }}
                                </h5>
                            </div>
                            <div class="w-10 h-10 rounded-[5px] bg-slate-200/50 flex items-center justify-center text-slate-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Projetos -->
                    <div class="space-y-4">
                        @foreach($projects as $project)
                            <div class="border border-slate-100 hover:border-slate-200 p-4 rounded-[5px] flex flex-col md:flex-row md:items-center justify-between gap-4 transition-colors bg-white hover:shadow-sm">
                                <div class="space-y-1">
                                    <h5 class="font-bold text-slate-800 text-sm">{{ $project->title }}</h5>
                                    <p class="text-sm text-slate-500 line-clamp-2 leading-relaxed" title="{{ $project->description }}">{{ $project->description ?? 'Sem descrição fornecida.' }}</p>
                                    <span class="text-sm text-slate-400 block font-medium">Registrado em: {{ $project->created_at->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    <!-- Valor -->
                                    <span class="text-sm font-extrabold text-slate-800 bg-slate-50 px-2.5 py-1.5 rounded-[5px] border border-slate-100 shadow-inner">
                                        R$ {{ number_format($project->total_value, 2, ',', '.') }}
                                    </span>
                                    <!-- Badge Status -->
                                    <span class="text-sm font-bold uppercase tracking-wider px-2.5 py-1.5 rounded-[5px] border
                                        {{ $project->status === 'rascunho' ? 'bg-slate-100 text-slate-700 border-slate-300' : '' }}
                                        {{ $project->status === 'analisando' || $project->status === 'pendente' ? 'bg-amber-100 text-amber-900 border-amber-300' : '' }}
                                        {{ $project->status === 'aprovado' || $project->status === 'concluido' ? 'bg-emerald-100 text-emerald-900 border-emerald-300' : '' }}
                                        {{ $project->status === 'rejeitado' || $project->status === 'suspenso' ? 'bg-red-100 text-red-900 border-red-300' : '' }}
                                        {{ $project->status === 'quitado' ? 'bg-purple-100 text-purple-900 border-purple-300' : '' }}
                                        {{ $project->status === 'finalizado' || $project->status === 'em andamento' ? 'bg-blue-100 text-blue-900 border-blue-300' : '' }}">
                                        {{ $project->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="border-2 border-dashed border-slate-200 p-8 text-center text-slate-400 rounded-[5px] text-sm">
                        Nenhum projeto associado a este cliente ainda.
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
