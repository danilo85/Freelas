@extends('layouts.app')

@section('title', 'Instagram & Mídia Social - Gestor de Freelas')
@section('page_title', 'Integração & Gestão do Instagram')

@section('content')
<div class="space-y-8" x-data="instagramModule">
    
    <!-- Banner de Status de Conexão com a Meta / Instagram -->
    <div class="bg-gradient-to-r {{ $account ? 'from-purple-950 via-slate-900 to-slate-900 border-purple-500/40' : 'from-slate-900 via-rose-950 to-slate-900 border-rose-500/40' }} border text-white rounded-xl p-6 shadow-md flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl {{ $account ? 'bg-gradient-to-tr from-amber-500 via-rose-500 to-purple-600 text-white shadow-lg' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center text-3xl shrink-0">
                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                </svg>
            </div>
            <div>
                <div class="flex flex-wrap items-center gap-2">
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
                        Sua conta profissional <strong class="text-white">{{ '@' . $account->username }}</strong> está pronta para publicar Feed, Carrosséis e Stories.
                    @else
                        Conecte sua conta do Instagram Profissional (vinculada a uma Página do Facebook) para agendar e publicar fotos, carrosséis e stories.
                    @endif
                </p>
            </div>
        </div>

        <div class="shrink-0 flex items-center gap-2">
            @if($accounts->count() > 1)
                <!-- Seletor de Múltiplas Contas -->
                <form action="{{ route('instagram.index') }}" method="GET" class="inline-block">
                    <select name="account_id" onchange="this.form.submit()" class="px-3 py-2 bg-slate-800 text-white text-xs font-bold rounded-[5px] border border-slate-700">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ optional($account)->id == $acc->id ? 'selected' : '' }}>
                                {{ '@' . $acc->username }}
                            </option>
                        @endforeach
                    </select>
                </form>
            @endif

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

    @if($account)
        <!-- Abas do Módulo (Novo Post / Posts do Perfil / Calendário / Banco de Imagens / Marcas) -->
        <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="border-b border-slate-100 bg-slate-50/50 p-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <button @click="tab = 'novo'" :class="tab === 'novo' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Nova Publicação & Prévia
                    </button>
                    <button @click="tab = 'feed_real'" :class="tab === 'feed_real' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069z"/></svg>
                        Posts do Perfil ({{ count($liveInstagramPosts) }})
                    </button>
                    <button @click="tab = 'calendario'" :class="tab === 'calendario' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Calendário de Agendamentos
                    </button>
                    <button @click="tab = 'banco'" :class="tab === 'banco' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Banco de Imagens ({{ $posts->count() }})
                    </button>
                    <button @click="tab = 'marcas'" :class="tab === 'marcas' ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-slate-600 hover:bg-slate-100 border border-slate-200'" class="px-4 py-2 text-xs font-bold rounded-[5px] transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                        Logo & Seta (Marcas)
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- 1. ABA: NOVA PUBLICAÇÃO & PRÉVIA AO VIVO -->
                <div x-show="tab === 'novo'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                        
                        <!-- Coluna da Esquerda: Formulário de Postagem -->
                        <div class="lg:col-span-7 space-y-6">
                            
                            <!-- Seletor do Tipo de Mídia (Feed Único, Carrossel, Story) -->
                            <div class="space-y-2">
                                <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Formato da Publicação</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <button type="button" @click="mediaType = 'IMAGE'" :class="mediaType === 'IMAGE' ? 'border-purple-600 bg-purple-50/50 text-purple-700 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="p-3 border rounded-lg text-xs transition-all text-center flex flex-col items-center gap-1.5 cursor-pointer">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Feed Único</span>
                                    </button>
                                    <button type="button" @click="mediaType = 'CAROUSEL'" :class="mediaType === 'CAROUSEL' ? 'border-purple-600 bg-purple-50/50 text-purple-700 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="p-3 border rounded-lg text-xs transition-all text-center flex flex-col items-center gap-1.5 cursor-pointer">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                        <span>Carrossel</span>
                                    </button>
                                    <button type="button" @click="mediaType = 'STORY'" :class="mediaType === 'STORY' ? 'border-purple-600 bg-purple-50/50 text-purple-700 font-bold shadow-sm' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'" class="p-3 border rounded-lg text-xs transition-all text-center flex flex-col items-center gap-1.5 cursor-pointer">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Story (24h)</span>
                                    </button>
                                </div>
                            </div>

                            <form action="{{ route('instagram.posts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                                @csrf
                                <input type="hidden" name="instagram_account_id" :value="selectedAccountId">
                                <input type="hidden" name="media_type" :value="mediaType">
                                <input type="hidden" name="has_logo_overlay" :value="hasLogoOverlay ? 1 : 0">
                                <input type="hidden" name="has_arrow_overlay" :value="hasArrowOverlay ? 1 : 0">

                                <!-- Upload de Imagem Única ou Story -->
                                <div x-show="mediaType !== 'CAROUSEL'" class="space-y-2">
                                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                                        Selecione a Imagem <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="file" name="image" @change="handleImageChange" accept="image/*" class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-[5px] file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-slate-200 rounded-lg p-1.5 cursor-pointer">
                                </div>

                                <!-- Upload de Múltiplas Imagens do Carrossel -->
                                <div x-show="mediaType === 'CAROUSEL'" class="space-y-2">
                                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">
                                        Selecione as Fotos do Carrossel (mínimo 2) <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="file" name="carousel_images[]" multiple @change="handleCarouselChange" accept="image/*" class="w-full text-xs text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-[5px] file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-slate-200 rounded-lg p-1.5 cursor-pointer">
                                </div>

                                <!-- Sobreposição de Marcas (Logo & Seta) -->
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Sobreposição de Ícones (Marca d'Água)
                                    </span>
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                            <input type="checkbox" x-model="hasLogoOverlay" class="rounded text-purple-600 focus:ring-purple-500">
                                            <span>Aplicar Ícone da Logo (Topo)</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                            <input type="checkbox" x-model="hasArrowOverlay" class="rounded text-purple-600 focus:ring-purple-500">
                                            <span>Aplicar Seta (Rodapé)</span>
                                        </label>
                                    </div>
                                    @if(!$settings->logo_path && !$settings->arrow_path)
                                        <p class="text-[11px] text-amber-600 font-medium">💡 Faça upload dos ícones na aba <strong>"Logo & Seta (Marcas)"</strong> para aplicar automaticamente.</p>
                                    @endif
                                </div>

                                <!-- Legenda do Post (Não exigida em Story) -->
                                <div x-show="mediaType !== 'STORY'" class="space-y-2">
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider">Legenda do Post</label>
                                        <span class="text-[11px] text-slate-400 font-mono" x-text="caption.length + ' / 2200'"></span>
                                    </div>
                                    <textarea name="caption" x-model="caption" rows="5" placeholder="Escreva uma legenda atraente para o seu post..." class="w-full text-xs text-slate-800 border border-slate-200 rounded-lg p-3 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none transition-all"></textarea>
                                </div>

                                <!-- GERADOR INTELIGENTE DE HASHTAGS -->
                                <div x-show="mediaType !== 'STORY'" class="p-4 bg-purple-50/50 border border-purple-100 rounded-xl space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h5 class="text-xs font-extrabold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                            Gerador de Hashtags & Em Alta
                                        </h5>
                                        <select x-model="hashtagCategory" class="text-xs font-bold text-purple-700 bg-white border border-purple-200 rounded-md px-2 py-1 outline-none">
                                            <option value="design">🎨 Design & Branding</option>
                                            <option value="freelance">💻 Freelance & Carreira</option>
                                            <option value="socialmedia">📲 Social Media</option>
                                            <option value="ilustracao">✍️ Ilustração Digital</option>
                                            <option value="trending">🔥 Em Alta / Trending</option>
                                        </select>
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="tag in hashtags[hashtagCategory]" :key="tag">
                                            <button type="button" @click="insertHashtag(tag)" class="px-2.5 py-1 bg-white hover:bg-purple-600 hover:text-white text-purple-700 border border-purple-200 text-[11px] font-semibold rounded-md transition-all cursor-pointer">
                                                <span x-text="tag"></span>
                                            </button>
                                        </template>
                                    </div>

                                    <button type="button" @click="insertCategoryHashtags(hashtagCategory)" class="text-xs font-bold text-purple-700 hover:underline flex items-center gap-1 cursor-pointer">
                                        <span>+ Inserir todas desta categoria na legenda</span>
                                    </button>
                                </div>

                                <!-- Ação: Publicar Agora ou Agendar -->
                                <div class="space-y-4 pt-2 border-t border-slate-100">
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 text-xs font-extrabold text-slate-700 cursor-pointer">
                                            <input type="radio" name="action" value="now" x-model="actionType" class="text-purple-600 focus:ring-purple-500">
                                            <span>Publicar Agora</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-extrabold text-slate-700 cursor-pointer">
                                            <input type="radio" name="action" value="schedule" x-model="actionType" class="text-purple-600 focus:ring-purple-500">
                                            <span>Agendar para Data Futura</span>
                                        </label>
                                    </div>

                                    <div x-show="actionType === 'schedule'" class="space-y-2 bg-slate-50 p-3 rounded-lg border border-slate-200">
                                        <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Data e Horário da Publicação</label>
                                        <input type="datetime-local" name="scheduled_at" class="w-full text-xs text-slate-800 border border-slate-200 rounded-lg p-2.5 bg-white font-semibold">
                                    </div>
                                </div>

                                <button type="submit" class="w-full py-3 bg-gradient-to-r from-purple-600 to-rose-600 hover:from-purple-500 hover:to-rose-500 text-white font-extrabold text-xs uppercase tracking-wider rounded-lg shadow-md transition-all cursor-pointer flex items-center justify-center gap-2">
                                    <span x-text="actionType === 'now' ? 'Publicar no Instagram Agora' : 'Confirmar Agendamento'"></span>
                                </button>
                            </form>
                        </div>

                        <!-- Coluna da Direita: Prévia ao Vivo do Smartphone Instagram -->
                        <div class="lg:col-span-5 flex flex-col items-center">
                            <h4 class="text-xs font-extrabold text-slate-500 uppercase tracking-wider mb-3 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Prévia em Tempo Real (Instagram App)
                            </h4>
                            
                            <div class="w-[320px] sm:w-[340px] bg-black text-white rounded-[44px] p-3.5 shadow-2xl border-4 border-slate-800 relative overflow-hidden">
                                <!-- Smartphone Notch -->
                                <div class="w-28 h-4 bg-slate-900 rounded-b-xl mx-auto mb-2 flex items-center justify-center">
                                    <div class="w-2.5 h-2.5 rounded-full bg-slate-800"></div>
                                </div>

                                <!-- Instagram App Header -->
                                <div class="flex items-center justify-between px-3 py-2 border-b border-slate-800">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $account->profile_picture_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($account->username) }}" class="w-8 h-8 rounded-full border border-purple-500 object-cover">
                                        <span class="text-xs font-bold text-white tracking-tight" x-text="'{{ '@' . $account->username }}'"></span>
                                    </div>
                                    <span class="text-slate-400 text-xs">•••</span>
                                </div>

                                <!-- Instagram Image Viewport with Live Overlay Badges -->
                                <div class="w-full h-[340px] sm:h-[380px] bg-slate-900 relative flex items-center justify-center overflow-hidden">
                                    <template x-if="imagePreview">
                                        <img :src="imagePreview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!imagePreview">
                                        <div class="text-center p-6 text-slate-600 space-y-2">
                                            <svg class="w-10 h-10 mx-auto text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-xs font-semibold">Sua imagem ou carrossel aparecerá aqui em tempo real</p>
                                        </div>
                                    </template>

                                    <!-- Live Overlay Badge Logo (Top Right) -->
                                    <template x-if="hasLogoOverlay">
                                        <div class="absolute top-3 right-3 bg-black/60 backdrop-blur-md px-2 py-1 rounded border border-white/20 flex items-center gap-1 shadow-lg">
                                            @if($settings->logo_path)
                                                <img src="{{ asset('storage/' . $settings->logo_path) }}" class="h-4 object-contain">
                                            @else
                                                <span class="text-[9px] font-black uppercase text-amber-300 tracking-wider">LOGO</span>
                                            @endif
                                        </div>
                                    </template>

                                    <!-- Live Overlay Badge Arrow (Bottom Right) -->
                                    <template x-if="hasArrowOverlay">
                                        <div class="absolute bottom-3 right-3 bg-purple-600/80 backdrop-blur-md px-2.5 py-1 rounded-full text-white text-[10px] font-bold flex items-center gap-1 shadow-lg animate-bounce">
                                            @if($settings->arrow_path)
                                                <img src="{{ asset('storage/' . $settings->arrow_path) }}" class="h-3 object-contain">
                                            @else
                                                <span>Arraste pro lado ➔</span>
                                            @endif
                                        </div>
                                    </template>
                                </div>

                                <!-- Instagram Actions Bar -->
                                <div class="px-3 py-2 flex items-center justify-between text-slate-300">
                                    <div class="flex items-center gap-3">
                                        <span class="text-rose-500 font-bold text-base">❤️</span>
                                        <span class="text-base">💬</span>
                                        <span class="text-base">✈️</span>
                                    </div>
                                    <span class="text-base">🔖</span>
                                </div>

                                <!-- Live Caption Preview -->
                                <div class="px-3 pb-4 space-y-1 text-xs">
                                    <p class="text-slate-200 text-[11px] leading-relaxed break-words">
                                        <strong class="text-white font-bold" x-text="'{{ '@' . $account->username }}'"></strong>
                                        <span x-text="caption || 'Sua legenda aparecerá aqui...'"></span>
                                    </p>
                                    <span class="text-[9px] text-slate-500 uppercase font-semibold block pt-1">HÁ 1 MINUTO</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ABA 2: POSTS PUBLICADOS DIRETO NO INSTAGRAM (@username) -->
                <div x-show="tab === 'feed_real'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Publicações do Perfil {{ '@' . $account->username }}</h4>
                            <p class="text-xs text-slate-500">Histórico oficial das mídias publicadas diretamente na sua conta do Instagram.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse($liveInstagramPosts as $item)
                            @php
                                $imgUrl = $item['media_url'] ?? ($item['thumbnail_url'] ?? null);
                                $postUrl = $item['permalink'] ?? '#';
                                $likes = $item['like_count'] ?? 0;
                                $comments = $item['comments_count'] ?? 0;
                                $timestamp = isset($item['timestamp']) ? \Carbon\Carbon::parse($item['timestamp'])->format('d/m/Y H:i') : null;

                                $slides = [];
                                if (isset($item['children']['data']) && is_array($item['children']['data'])) {
                                    foreach ($item['children']['data'] as $child) {
                                        $slides[] = $child['media_url'] ?? ($child['thumbnail_url'] ?? null);
                                    }
                                }
                                if (empty($slides) && $imgUrl) {
                                    $slides[] = $imgUrl;
                                }

                                $itemJson = json_encode([
                                    'caption' => $item['caption'] ?? '',
                                    'likes' => $likes,
                                    'comments' => $comments,
                                    'date' => $timestamp,
                                    'permalink' => $postUrl,
                                    'media_type' => $item['media_type'] ?? 'POST',
                                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                                $slidesJson = json_encode(array_values(array_filter($slides)), JSON_UNESCAPED_SLASHES);
                            @endphp
                            <div class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between cursor-pointer"
                                 @click="openLightbox({{ $itemJson }}, {{ $slidesJson }})">
                                <div class="relative h-60 bg-slate-900 overflow-hidden">
                                    @if($imgUrl)
                                        <img src="{{ $imgUrl }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600 text-3xl">📸</div>
                                    @endif

                                    <!-- Overlay Badges (Likes & Comments) -->
                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-4 text-white font-black text-sm">
                                        <span class="flex items-center gap-1">❤️ {{ $likes }}</span>
                                        <span class="flex items-center gap-1">💬 {{ $comments }}</span>
                                    </div>

                                    @php
                                        $rawType = $item['media_type'] ?? 'IMAGE';
                                        $typeLabel = match(true) {
                                            str_contains($rawType, 'CAROUSEL') => 'CARROSSEL',
                                            str_contains($rawType, 'VIDEO') || str_contains($rawType, 'REELS') => 'VÍDEO',
                                            str_contains($rawType, 'STORY') => 'STORY',
                                            default => 'FEED',
                                        };
                                    @endphp
                                    <span class="absolute top-2 left-2 px-2.5 py-1 text-[9px] font-black uppercase tracking-wider rounded-md text-white bg-purple-600 shadow-md">
                                        {{ $typeLabel }}
                                    </span>
                                </div>

                                <div class="p-3 space-y-2.5">
                                    <p class="text-xs text-slate-700 line-clamp-2 leading-relaxed">
                                        {{ $item['caption'] ?? 'Sem legenda' }}
                                    </p>
                                    
                                    @if($timestamp)
                                        <span class="text-[10px] text-slate-400 font-mono block">📅 {{ $timestamp }}</span>
                                    @endif

                                    <div class="flex items-center gap-1.5 pt-2 border-t border-slate-100" @click.stop>
                                        <a href="{{ $postUrl }}" target="_blank" title="Ver no Instagram" class="flex-1 py-1.5 px-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-center font-bold text-[10px] rounded transition-all flex items-center justify-center gap-1">
                                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                            <span>Ver</span>
                                        </a>
                                        @if($imgUrl)
                                            <button type="button" @click="useMediaBankImage('{{ $imgUrl }}')" title="Reutilizar Imagem" class="py-1.5 px-2 bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 font-bold text-[10px] rounded transition-all flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reutilizar</span>
                                            </button>
                                        @endif
                                        <button type="button" @click="confirmDeletePost('{{ route('instagram.posts.destroy', $item['db_id'] ?? $item['id']) }}', $event)" title="Excluir Mídia do Instagram" class="py-1.5 px-2 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 font-bold text-[10px] rounded transition-all flex items-center justify-center cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full p-12 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs space-y-2">
                                <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="font-bold text-slate-700">Nenhuma publicação encontrada diretamente no feed do Instagram.</p>
                                <p class="text-slate-500">Assim que você fizer publicações na conta {{ '@' . $account->username }}, elas aparecerão aqui automaticamente!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. ABA: CALENDÁRIO VISUAL DE AGENDAMENTOS -->
                <div x-show="tab === 'calendario'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Calendário Mensal de Publicações</h4>
                            <p class="text-xs text-slate-500">Visualize seus posts agendados e publicados nas datas do mês.</p>
                        </div>
                    </div>

                    <!-- Grid do Calendário -->
                    <div class="grid grid-cols-7 gap-2 bg-slate-100 p-2 rounded-xl border border-slate-200">
                        @php
                            $daysInMonth = now()->daysInMonth;
                            $firstDayOfWeek = now()->startOfMonth()->dayOfWeek;
                        @endphp

                        @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dayName)
                            <div class="text-center text-[11px] font-extrabold text-slate-500 uppercase tracking-wider py-1.5 bg-slate-200/60 rounded-md">
                                {{ $dayName }}
                            </div>
                        @endforeach

                        @for($i = 0; $i < $firstDayOfWeek; $i++)
                            <div class="min-h-[100px] bg-slate-50/50 rounded-lg border border-slate-100/50 opacity-40"></div>
                        @endfor

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = now()->format('Y-m-') . sprintf('%02d', $day);
                                $dayPosts = $posts->filter(function($p) use ($dateStr) {
                                    return ($p->scheduled_at && $p->scheduled_at->format('Y-m-d') === $dateStr)
                                        || ($p->published_at && $p->published_at->format('Y-m-d') === $dateStr);
                                });
                                $isToday = $day == now()->day;
                            @endphp
                            <div class="min-h-[100px] bg-white p-2 rounded-lg border {{ $isToday ? 'border-purple-500 ring-2 ring-purple-100' : 'border-slate-200' }} flex flex-col justify-between hover:border-purple-300 transition-all">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-bold {{ $isToday ? 'bg-purple-600 text-white w-5 h-5 rounded-full flex items-center justify-center' : 'text-slate-700' }}">{{ $day }}</span>
                                    @if($dayPosts->count() > 0)
                                        <span class="text-[9px] font-black bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded-full">{{ $dayPosts->count() }} post</span>
                                    @endif
                                </div>

                                <div class="space-y-1 my-1">
                                    @foreach($dayPosts as $dp)
                                        <div class="p-1 rounded text-[10px] font-bold border truncate flex items-center justify-between gap-1 {{ $dp->status === 'publicado' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($dp->status === 'erro' ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-purple-50 text-purple-700 border-purple-200') }}">
                                            <span class="truncate">{{ $dp->media_type === 'STORY' ? 'Story' : ($dp->media_type === 'CAROUSEL' ? 'Carrossel' : 'Feed') }}</span>
                                            <span>{{ $dp->scheduled_at ? $dp->scheduled_at->format('H:i') : '' }}</span>
                                        </div>
                                    @endforeach
                                </div>

                                <button @click="tab = 'novo'; actionType = 'schedule'" class="text-[10px] font-bold text-purple-600 hover:text-purple-800 text-center block pt-1 border-t border-slate-100 cursor-pointer">
                                    + Agendar
                                </button>
                            </div>
                        @endfor
                    </div>
                </div>

                <!-- 4. ABA: BANCO DE IMAGENS & HISTÓRICO -->
                <div x-show="tab === 'banco'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Banco de Imagens & Histórico de Mídias</h4>
                            <p class="text-xs text-slate-500">Todas as fotos enviadas e publicadas. Clique em "Reutilizar" para postar novamente.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @forelse($posts as $p)
                            <div class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between">
                                <div class="relative h-36 bg-slate-900 overflow-hidden cursor-pointer"
                                     @click="openLightbox({ caption: '{{ addslashes($p->caption) }}', likes: 0, comments: 0, date: '{{ optional($p->created_at)->format('d/m/Y') }}', media_type: '{{ $p->media_type }}' }, [ '{{ asset('storage/' . $p->media_path) }}' ])">
                                    @if($p->media_path)
                                        <img src="{{ asset('storage/' . $p->media_path) }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-slate-600">🖼️</div>
                                    @endif
                                    
                                    <span class="absolute top-2 left-2 px-2 py-0.5 text-[9px] font-black uppercase tracking-wider rounded-md text-white {{ $p->status === 'publicado' ? 'bg-emerald-600' : ($p->status === 'erro' ? 'bg-rose-600' : 'bg-purple-600') }}">
                                        {{ $p->status }}
                                    </span>
                                </div>

                                <div class="p-2.5 space-y-2">
                                    <p class="text-[11px] text-slate-600 line-clamp-2">{{ $p->caption ?: 'Sem legenda' }}</p>
                                    
                                    <div class="flex items-center gap-1.5 pt-2 border-t border-slate-100">
                                        <button type="button" @click="useMediaBankImage('{{ asset('storage/' . $p->media_path) }}')" title="Reutilizar Imagem" class="flex-1 py-1 bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 font-bold text-[10px] rounded transition-all flex items-center justify-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                            <span>Reutilizar</span>
                                        </button>
                                        <button type="button" @click="confirmDeletePost('{{ route('instagram.posts.destroy', $p->id) }}', $event)" title="Excluir do Histórico" class="px-2 py-1 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 font-bold text-[10px] rounded transition-all flex items-center justify-center cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full p-8 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs">
                                Nenhuma imagem registrada ainda no histórico.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 5. ABA: CONFIGURAÇÕES & MARCAS D'ÁGUA (LOGO E SETA) -->
                <div x-show="tab === 'marcas'" class="space-y-6">
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Ícones de Sobreposição (Logo & Seta)</h4>
                        <p class="text-xs text-slate-500">Cadastre a sua marca d'água para aplicar automaticamente sobre as fotos antes da publicação.</p>
                    </div>

                    <form action="{{ route('instagram.settings.overlays') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @csrf

                        <!-- Card Upload Logo -->
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                            <h5 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                Ícone da Logo (Marca D'Água Topo)
                            </h5>
                            @if($settings->logo_path)
                                <div class="h-20 bg-slate-900 rounded-lg p-2 flex items-center justify-center border border-slate-700">
                                    <img src="{{ asset('storage/' . $settings->logo_path) }}" class="h-full object-contain">
                                </div>
                            @endif
                            <input type="file" name="logo_icon" accept="image/png,image/jpeg" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-slate-200 rounded-lg p-1">
                        </div>

                        <!-- Card Upload Seta -->
                        <div class="p-5 bg-slate-50 border border-slate-200 rounded-xl space-y-3">
                            <h5 class="text-xs font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                Ícone de Seta (Marca D'Água Rodapé)
                            </h5>
                            @if($settings->arrow_path)
                                <div class="h-20 bg-slate-900 rounded-lg p-2 flex items-center justify-center border border-slate-700">
                                    <img src="{{ asset('storage/' . $settings->arrow_path) }}" class="h-full object-contain">
                                </div>
                            @endif
                            <input type="file" name="arrow_icon" accept="image/png,image/jpeg" class="w-full text-xs text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-bold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 border border-slate-200 rounded-lg p-1">
                        </div>

                        <div class="md:col-span-2">
                            <button type="submit" class="px-6 py-2.5 bg-purple-600 hover:bg-purple-700 text-white font-bold text-xs rounded-lg shadow-md transition-all cursor-pointer">
                                Salvar Ícones de Marca D'Água
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <!-- LIGHTBOX TELEPORTADO PARA BODY COM TOTAL OVERLAY SEM MARGEM -->
    <template x-teleport="body">
        <div x-show="lightboxOpen" 
             x-cloak 
             @keydown.escape.window="lightboxOpen = false"
             class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-screen h-screen z-[999999] flex items-center justify-center p-4 bg-slate-950/90 backdrop-blur-xl transition-all">
            
            <!-- Botão Fechar Modal (ESC) -->
            <button @click="lightboxOpen = false" class="absolute top-4 right-4 md:top-6 md:right-6 w-11 h-11 rounded-full bg-slate-800/90 hover:bg-slate-700 text-white font-black text-xl flex items-center justify-center border border-slate-700 shadow-2xl transition-all cursor-pointer z-50">
                ✕
            </button>

            <div class="relative flex items-center justify-center w-full max-w-[95vw] md:max-w-4xl" @click.outside="lightboxOpen = false">

                <!-- ESTRUTURA DO CELULAR (MOCKUP MODERNO E PROPORCIONAL) -->
                <div class="w-[320px] sm:w-[360px] bg-black text-white rounded-[48px] p-3.5 shadow-2xl border-4 border-slate-800 relative z-20 shadow-purple-950/50">
                    
                    <!-- Smartphone Notch Header -->
                    <div class="w-32 h-4 bg-slate-900 rounded-b-xl mx-auto mb-2 flex items-center justify-center">
                        <div class="w-3 h-3 rounded-full bg-slate-800"></div>
                    </div>

                    <!-- Instagram App Header -->
                    <div class="flex items-center justify-between px-3 py-2 border-b border-slate-800">
                        <div class="flex items-center gap-2.5">
                            <img src="{{ $account->profile_picture_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($account->username) }}" class="w-8 h-8 rounded-full border border-purple-500 object-cover">
                            <div>
                                <span class="text-xs font-bold text-white block leading-tight" x-text="'{{ '@' . $account->username }}'"></span>
                                <span class="text-[9px] text-slate-400 font-mono block" x-text="lightboxPost?.date || ''"></span>
                            </div>
                        </div>
                        <template x-if="lightboxPost?.permalink">
                            <a :href="lightboxPost.permalink" target="_blank" title="Abrir no Instagram" class="text-xs text-purple-400 hover:text-purple-300 font-bold">
                                <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </template>
                    </div>

                    <!-- Instagram Viewport Screen (Altura Moderna Proporcional) -->
                    <div class="w-full h-[380px] sm:h-[420px] bg-slate-900 relative flex items-center justify-center overflow-visible group">
                        
                        <!-- SLIDE ANTERIOR (COLADO NA BORDA ESQUERDA DA IMAGEM DO CENTRO E MESMO TAMANHO) -->
                        <div x-show="lightboxSlideIndex > 0" 
                             @click.stop="prevLightboxSlide()"
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="hidden lg:block absolute top-0 right-full z-10 w-[240px] sm:w-[280px] h-full bg-slate-900 border-y border-l border-white/10 shadow-2xl overflow-hidden cursor-pointer group transition-all duration-300"
                             style="mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,0.7) 40%, black 100%); -webkit-mask-image: linear-gradient(to right, transparent 0%, rgba(0,0,0,0.7) 40%, black 100%);">
                            <template x-if="lightboxSlides[lightboxSlideIndex - 1]">
                                <img :src="lightboxSlides[lightboxSlideIndex - 1]" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity duration-300">
                            </template>
                        </div>

                        <!-- SLIDE PRINCIPAL ATIVO DO CENTRO -->
                        <template x-for="(slide, idx) in lightboxSlides" :key="idx">
                            <div x-show="lightboxSlideIndex === idx"
                                 x-transition:enter="transition ease-out duration-400 transform"
                                 x-transition:enter-start="opacity-0 scale-105"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition ease-in duration-200 transform"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute inset-0 w-full h-full z-20">
                                <img :src="slide" class="w-full h-full object-cover">
                            </div>
                        </template>

                        <!-- SLIDE PRÓXIMO (COLADO NA BORDA DIREITA DA IMAGEM DO CENTRO E MESMO TAMANHO) -->
                        <div x-show="lightboxSlideIndex < lightboxSlides.length - 1" 
                             @click.stop="nextLightboxSlide()"
                             x-transition:enter="transition ease-out duration-300 transform"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             class="hidden lg:block absolute top-0 left-full z-10 w-[240px] sm:w-[280px] h-full bg-slate-900 border-y border-r border-white/10 shadow-2xl overflow-hidden cursor-pointer group transition-all duration-300"
                             style="mask-image: linear-gradient(to left, transparent 0%, rgba(0,0,0,0.7) 40%, black 100%); -webkit-mask-image: linear-gradient(to left, transparent 0%, rgba(0,0,0,0.7) 40%, black 100%);">
                            <template x-if="lightboxSlides[lightboxSlideIndex + 1]">
                                <img :src="lightboxSlides[lightboxSlideIndex + 1]" class="w-full h-full object-cover opacity-60 group-hover:opacity-100 transition-opacity duration-300">
                            </template>
                        </div>

                        <!-- Setas Internas de Navegação -->
                        <button x-show="lightboxSlideIndex > 0" @click.stop="prevLightboxSlide()" class="absolute left-2.5 z-30 w-8 h-8 rounded-full bg-black/60 text-white font-black text-sm flex items-center justify-center shadow hover:bg-black/90 transition-all cursor-pointer">
                            ❮
                        </button>
                        <button x-show="lightboxSlideIndex < lightboxSlides.length - 1" @click.stop="nextLightboxSlide()" class="absolute right-2.5 z-30 w-8 h-8 rounded-full bg-black/60 text-white font-black text-sm flex items-center justify-center shadow hover:bg-black/90 transition-all cursor-pointer">
                            ❯
                        </button>

                        <!-- Indicador de Posição de Slides / Dots -->
                        <template x-if="lightboxSlides.length > 1">
                            <div class="absolute bottom-2.5 left-0 right-0 z-30 flex items-center justify-center gap-1.5">
                                <template x-for="(slide, idx) in lightboxSlides" :key="idx">
                                    <span :class="idx === lightboxSlideIndex ? 'bg-purple-500 w-2.5 h-2.5 scale-110' : 'bg-white/40 w-1.5 h-1.5'" class="rounded-full transition-all"></span>
                                </template>
                            </div>
                        </template>
                    </div>

                    <!-- Instagram Likes & Comments Bar -->
                    <div class="px-3 py-2 flex items-center justify-between text-slate-300 border-b border-slate-900 relative z-20 bg-black">
                        <div class="flex items-center gap-4 text-xs font-bold">
                            <span class="flex items-center gap-1 text-rose-500">❤️ <span x-text="lightboxPost?.likes || 0"></span></span>
                            <span class="flex items-center gap-1 text-slate-300">💬 <span x-text="lightboxPost?.comments || 0"></span></span>
                        </div>
                        <span class="text-xs font-black uppercase text-purple-400 tracking-wider" x-text="formatMediaType(lightboxPost?.media_type)"></span>
                    </div>

                    <!-- Instagram Caption Box -->
                    <div class="px-3 py-3 max-h-28 overflow-y-auto space-y-1 text-xs relative z-20 bg-black">
                        <p class="text-slate-200 text-[11px] leading-relaxed break-words">
                            <strong class="text-white font-bold" x-text="'{{ '@' . $account->username }}'"></strong>
                            <span x-text="lightboxPost?.caption || 'Sem legenda'"></span>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </template>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO (ESTILOSO E SEGURO) -->
    <template x-teleport="body">
        <div x-show="confirmDeleteModalOpen" 
             x-cloak 
             @keydown.escape.window="confirmDeleteModalOpen = false"
             class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-screen h-screen z-[9999999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all">
            
            <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-5 text-center transform transition-all" @click.outside="confirmDeleteModalOpen = false">
                <div class="w-14 h-14 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto shadow-inner">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>
                
                <div class="space-y-1.5">
                    <h4 class="text-base font-extrabold text-slate-800 tracking-tight">Confirmar Exclusão da Publicação?</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Esta ação removerá a publicação do seu histórico e enviará a ordem de exclusão diretamente para a Meta Graph API do Instagram.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" @click="confirmDeleteModalOpen = false" :disabled="isDeleting" class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" @click="executeDelete()" :disabled="isDeleting" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-md transition-all cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50">
                        <template x-if="!isDeleting">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Sim, Excluir</span>
                            </span>
                        </template>
                        <template x-if="isDeleting">
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                <span>Excluindo...</span>
                            </span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('instagramModule', () => ({
        tab: (new URLSearchParams(window.location.search)).get('tab') || 'novo',
        mediaType: 'IMAGE',
        actionType: 'now',
        caption: '',
        hasLogoOverlay: false,
        hasArrowOverlay: false,
        imagePreview: null,
        carouselPreviews: [],
        currentCarouselIndex: 0,
        selectedAccountId: '{{ optional($account)->id }}',
        hashtagCategory: 'design',
        hashtags: {
            design: ['#designgrafico', '#identidadevisual', '#logodesign', '#designbr', '#designer', '#branding', '#designgraficobr', '#creative', '#graphicdesign', '#visualidentity'],
            freelance: ['#freelancerbr', '#freelance', '#gestordefreelas', '#vidadefreela', '#trabalhoremoto', '#carreiradesign', '#designindependente', '#freelancerlife'],
            socialmedia: ['#socialmedia', '#marketingdigital', '#midiasociais', '#gestordesocialmedia', '#conteudodigital', '#engajamento', '#instagramdicas', '#estrategiadedados'],
            ilustracao: ['#ilustracao', '#artedigital', '#desenhodigital', '#illustrator', '#procreate', '#vectorart', '#ilustra', '#digitalart'],
            trending: ['#viral', '#reelsbrasil', '#dicas', '#emalta', '#empreendedorismo', '#criatividade', '#portfoliodesign']
        },
        insertHashtag(tag) {
            if (!this.caption.includes(tag)) {
                this.caption = (this.caption ? this.caption.trim() + ' ' : '') + tag;
            }
        },
        insertCategoryHashtags(cat) {
            const tags = this.hashtags[cat] || [];
            tags.forEach(t => this.insertHashtag(t));
        },
        handleImageChange(e) {
            const file = e.target.files[0];
            if (file) {
                this.imagePreview = URL.createObjectURL(file);
            }
        },
        handleCarouselChange(e) {
            const files = Array.from(e.target.files);
            this.carouselPreviews = files.map(f => URL.createObjectURL(f));
            if (this.carouselPreviews.length > 0) {
                this.imagePreview = this.carouselPreviews[0];
                this.currentCarouselIndex = 0;
            }
        },
        useMediaBankImage(url) {
            this.imagePreview = url;
            this.mediaType = 'IMAGE';
            this.tab = 'novo';
        },
        lightboxOpen: false,
        lightboxPost: null,
        lightboxSlides: [],
        lightboxSlideIndex: 0,
        openLightbox(postData, slidesArray) {
            this.lightboxPost = postData;
            this.lightboxSlides = (slidesArray && slidesArray.length > 0) ? slidesArray : [postData.media_url || postData.media_path];
            this.lightboxSlideIndex = 0;
            this.lightboxOpen = true;
        },
        nextLightboxSlide() {
            if (this.lightboxSlideIndex < this.lightboxSlides.length - 1) {
                this.lightboxSlideIndex++;
            }
        },
        prevLightboxSlide() {
            if (this.lightboxSlideIndex > 0) {
                this.lightboxSlideIndex--;
            }
        },
        formatMediaType(type) {
            if (!type) return 'FEED';
            if (type.includes('CAROUSEL')) return 'CARROSSEL';
            if (type.includes('VIDEO') || type.includes('REELS')) return 'VÍDEO';
            if (type.includes('STORY')) return 'STORY';
            if (type === 'IMAGE') return 'FEED';
            return type;
        },
        confirmDeleteModalOpen: false,
        deleteFormActionUrl: '',
        targetCardElement: null,
        isDeleting: false,
        confirmDeletePost(url, event) {
            this.deleteFormActionUrl = url;
            this.targetCardElement = event ? event.target.closest('.group') : null;
            this.confirmDeleteModalOpen = true;
        },
        async executeDelete() {
            if (!this.deleteFormActionUrl) return;
            this.isDeleting = true;

            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const res = await fetch(this.deleteFormActionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _method: 'DELETE' })
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    if (this.targetCardElement) {
                        this.targetCardElement.style.transition = 'all 0.3s ease-out';
                        this.targetCardElement.style.opacity = '0';
                        this.targetCardElement.style.transform = 'scale(0.8)';
                        setTimeout(() => {
                            this.targetCardElement.remove();
                        }, 300);
                    }
                    if (data.meta_error) {
                        alert('ℹ️ Aviso da API do Instagram:\n\n' + data.meta_error + '\n\nPor regras de segurança da Meta, posts já publicados não podem ser excluídos via sistemas externos. Para remover do perfil público, abra o app do Instagram e selecione "Excluir".');
                    }
                } else {
                    window.location.href = window.location.pathname + '?tab=' + this.tab;
                }
            } catch (e) {
                window.location.href = window.location.pathname + '?tab=' + this.tab;
            } finally {
                this.isDeleting = false;
                this.confirmDeleteModalOpen = false;
            }
        }
    }));
});
</script>
@endsection
