@extends('layouts.app')

@section('title', 'Instagram & Mídia Social - Gestor de Freelas')
@section('page_title', 'Integração & Gestão do Instagram')

@section('content')
<div class="space-y-8" x-data="{ 
    tab: 'novo', 
    actionType: 'now',
    caption: '',
    imagePreview: null,
    handleImageChange(e) {
        const file = e.target.files[0];
        if (file) {
            this.imagePreview = URL.createObjectURL(file);
        }
    }
}">
    
    <!-- Banner de Status de Conexão com a Meta / Instagram -->
    <div class="bg-gradient-to-r {{ $account ? 'from-purple-950 via-slate-900 to-slate-900 border-purple-500/40' : 'from-slate-900 via-rose-950 to-slate-900 border-rose-500/40' }} border text-white rounded-xl p-6 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl {{ $account ? 'bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center text-3xl shrink-0">
                <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h3 class="text-base font-extrabold text-white">Integração do Instagram Graph API</h3>
                    @if($account)
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 rounded-full flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Conectado: {{ '@' . $account->username }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-full">
                            ○ Não Conectado
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    @if($account)
                        Sua conta profissional <strong class="text-white">{{ '@' . $account->username }}</strong> está pronta para publicar e agendar conteúdos diretamente do seu painel.
                    @else
                        Conecte sua conta do Instagram Profissional (vinculada a uma Página do Facebook) para publicar fotos, Reels e agendar postagens.
                    @endif
                </p>
            </div>
        </div>

        <div class="shrink-0 flex items-center gap-2">
            @if($account)
                <form action="{{ route('instagram.disconnect', $account->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold rounded-[5px] border border-slate-700 transition-colors cursor-pointer">
                        Desconectar
                    </button>
                </form>
            @else
                <a href="{{ route('instagram.connect') }}" class="px-5 py-2.5 bg-gradient-to-r from-purple-600 to-rose-600 hover:from-purple-500 hover:to-rose-500 text-white text-xs font-bold rounded-[5px] transition-all shadow-md flex items-center gap-2 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                    Conectar Instagram Profissional
                </a>
            @endif
        </div>
    </div>

    @if(!$account)
        <!-- Instruções de Conexão se não estiver conectado -->
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm space-y-4">
            <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                <span>💡</span> Como conectar sua conta:
            </h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-slate-600">
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg space-y-1.5">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">1</span>
                    <h5 class="font-bold text-slate-800">Conta Profissional</h5>
                    <p class="text-slate-500">Garante que a sua conta do Instagram é de Criador de Conteúdo ou Empresa.</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg space-y-1.5">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">2</span>
                    <h5 class="font-bold text-slate-800">Página do Facebook</h5>
                    <p class="text-slate-500">A conta do Instagram deve estar associada a uma Página do Facebook no seu perfil.</p>
                </div>
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-lg space-y-1.5">
                    <span class="w-6 h-6 rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-xs">3</span>
                    <h5 class="font-bold text-slate-800">Autorização Meta</h5>
                    <p class="text-slate-500">Clique em "Conectar Instagram", faça login com a conta do Facebook e conceda as permissões.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Abas do Módulo (Novo Post / Agendamentos / Histórico) -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button type="button" @click="tab = 'novo'" 
                            :class="tab === 'novo' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 text-xs font-extrabold uppercase tracking-wider rounded-[5px] transition-all cursor-pointer">
                        ➕ Nova Publicação
                    </button>
                    <button type="button" @click="tab = 'posts'" 
                            :class="tab === 'posts' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'"
                            class="px-4 py-2 text-xs font-extrabold uppercase tracking-wider rounded-[5px] transition-all cursor-pointer flex items-center gap-1.5">
                        <span>📋 Publicações & Agendamentos</span>
                        <span class="bg-purple-100 text-purple-800 text-[10px] px-1.5 py-0.5 rounded-full font-black" x-text="'{{ count($posts) }}'"></span>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Conteúdo Aba 1: Novo Post / Agendamento -->
                <div x-show="tab === 'novo'" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    <!-- Formulário -->
                    <div class="lg:col-span-7 space-y-6">
                        <form action="{{ route('instagram.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                            @csrf
                            
                            <!-- Upload de Imagem -->
                            <div class="space-y-1">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Imagem do Post *</label>
                                <div class="border-2 border-dashed border-slate-200 hover:border-purple-400 bg-slate-50/50 rounded-xl p-6 text-center transition-all cursor-pointer relative">
                                    <input type="file" name="image" accept="image/*" @change="handleImageChange" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                    <div class="space-y-2 pointer-events-none">
                                        <div class="w-12 h-12 rounded-full bg-purple-100 text-purple-600 mx-auto flex items-center justify-center text-xl">
                                            📸
                                        </div>
                                        <p class="text-xs font-bold text-slate-700">Clique para selecionar ou arraste uma foto</p>
                                        <span class="text-[10px] text-slate-400">Suporta JPG ou PNG (Máx 10 MB)</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Legenda do Post -->
                            <div class="space-y-1">
                                <label for="caption" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Legenda & Hashtags</label>
                                <textarea id="caption" name="caption" rows="5" x-model="caption" placeholder="Escreva a legenda do seu post aqui... Use #hashtags e emojis!" class="w-full p-3 border border-slate-200 rounded-[5px] text-sm focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition-all placeholder-slate-400"></textarea>
                            </div>

                            <!-- Tipo de Ação (Publicar Agora vs Agendar) -->
                            <div class="space-y-3 pt-2">
                                <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Quando Publicar?</label>
                                <div class="grid grid-cols-2 gap-3">
                                    <label class="border p-3 rounded-[5px] flex items-center gap-3 cursor-pointer transition-all"
                                           :class="actionType === 'now' ? 'border-purple-500 bg-purple-50/50 text-purple-900 font-bold' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" name="action" value="now" x-model="actionType" class="accent-purple-600">
                                        <span class="text-xs">🚀 Publicar Agora</span>
                                    </label>

                                    <label class="border p-3 rounded-[5px] flex items-center gap-3 cursor-pointer transition-all"
                                           :class="actionType === 'schedule' ? 'border-purple-500 bg-purple-50/50 text-purple-900 font-bold' : 'border-slate-200 text-slate-600'">
                                        <input type="radio" name="action" value="schedule" x-model="actionType" class="accent-purple-600">
                                        <span class="text-xs">🗓️ Agendar Post</span>
                                    </label>
                                </div>

                                <!-- Data e Hora para Agendamento -->
                                <div x-show="actionType === 'schedule'" x-cloak class="pt-2 space-y-1">
                                    <label for="scheduled_at" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Data e Hora do Agendamento</label>
                                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="w-full p-2.5 border border-slate-200 rounded-[5px] text-xs focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500">
                                </div>
                            </div>

                            <!-- Botão de Envio -->
                            <div class="pt-4 border-t border-slate-100">
                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-rose-600 hover:from-purple-500 hover:to-rose-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-[5px] transition-all shadow-md cursor-pointer flex items-center justify-center gap-2">
                                    <span x-text="actionType === 'now' ? '🚀 Publicar no Instagram Agora' : '🗓️ Confirmar Agendamento'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview ao Vivo do Post no Instagram -->
                    <div class="lg:col-span-5 flex flex-col items-center">
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Pré-visualização do Post</span>
                        
                        <!-- Card Mockup Instagram -->
                        <div class="w-full max-w-[340px] bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden text-xs">
                            <!-- Header Mockup -->
                            <div class="p-3 border-b border-slate-100 flex items-center justify-between bg-white">
                                <div class="flex items-center gap-2.5">
                                    <img src="{{ $account->profile_picture_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($account->username) }}" class="w-8 h-8 rounded-full border object-cover">
                                    <div>
                                        <strong class="font-extrabold text-slate-900 block leading-none">{{ '@' . $account->username }}</strong>
                                        <span class="text-[10px] text-slate-400">Patrocinado / Feed</span>
                                    </div>
                                </div>
                                <span class="text-slate-400 font-bold">•••</span>
                            </div>

                            <!-- Imagem Mockup -->
                            <div class="aspect-square bg-slate-100 flex items-center justify-center overflow-hidden">
                                <template x-if="imagePreview">
                                    <img :src="imagePreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!imagePreview">
                                    <div class="text-center p-6 text-slate-400 space-y-2">
                                        <span class="text-3xl block">🖼️</span>
                                        <span class="text-[11px] block">Selecione uma imagem no formulário ao lado para ver a prévia</span>
                                    </div>
                                </template>
                            </div>

                            <!-- Botões de Ação Mockup -->
                            <div class="p-3 space-y-2">
                                <div class="flex items-center justify-between text-xl">
                                    <div class="flex items-center gap-3">
                                        <span>❤️</span>
                                        <span>💬</span>
                                        <span>✈️</span>
                                    </div>
                                    <span>🔖</span>
                                </div>
                                <div class="text-slate-800 font-normal leading-relaxed line-clamp-3">
                                    <strong class="font-bold mr-1">{{ '@' . $account->username }}</strong>
                                    <span x-text="caption || 'Sua legenda aparecerá aqui...'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo Aba 2: Lista de Publicações -->
                <div x-show="tab === 'posts'" class="space-y-4">
                    <div class="overflow-x-auto border border-slate-200 rounded-lg">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                                <tr>
                                    <th class="p-3">Mídia</th>
                                    <th class="p-3">Legenda</th>
                                    <th class="p-3">Status</th>
                                    <th class="p-3">Data</th>
                                    <th class="p-3 text-right">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($posts as $post)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="p-3">
                                            <div class="w-12 h-12 rounded bg-slate-100 overflow-hidden shrink-0 border border-slate-200">
                                                <img src="{{ asset('storage/' . $post->media_path) }}" class="w-full h-full object-cover">
                                            </div>
                                        </td>
                                        <td class="p-3 max-w-xs">
                                            <p class="line-clamp-2 leading-relaxed text-slate-700 font-medium">{{ $post->caption ?: 'Sem legenda' }}</p>
                                        </td>
                                        <td class="p-3">
                                            @if($post->status === 'publicado')
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    ● Publicado
                                                </span>
                                            @elseif($post->status === 'agendado')
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-blue-100 text-blue-800 border border-blue-200">
                                                    ⏰ Agendado
                                                </span>
                                            @elseif($post->status === 'erro')
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-rose-100 text-rose-800 border border-rose-200" title="{{ $post->error_message }}">
                                                    ⚠️ Erro ao publicar
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 text-[10px] font-extrabold uppercase rounded bg-slate-100 text-slate-700 border border-slate-200">
                                                    Rascunho
                                                </span>
                                            @endif
                                        </td>
                                        <td class="p-3 text-slate-500 font-medium">
                                            {{ $post->published_at ? $post->published_at->format('d/m/Y H:i') : ($post->scheduled_at ? $post->scheduled_at->format('d/m/Y H:i') : $post->created_at->format('d/m/Y H:i')) }}
                                        </td>
                                        <td class="p-3 text-right">
                                            @if($post->status === 'publicado' && $post->instagram_media_id)
                                                <a href="https://www.instagram.com/p/{{ $post->instagram_media_id }}" target="_blank" class="text-purple-600 hover:text-purple-800 font-bold hover:underline">
                                                    Ver no IG ↗
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="p-8 text-center text-slate-400">
                                            Nenhum post agendado ou publicado ainda.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
