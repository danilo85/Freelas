@extends('layouts.app')

@section('title', 'Configurações do Portfólio - Gestor de Freelas')
@section('page_title', 'Configurações do Portfólio')

@section('content')
<div class="space-y-6" x-data="faqSettings()">
    <!-- Header da Página -->
    <div class="flex items-center justify-between">
        <a href="{{ route('portfolio.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para Trabalhos
        </a>
    </div>

    <!-- Feedback de Sucesso -->


    <!-- Formulário Principal -->
    <form action="{{ route('portfolio.settings.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6 pb-32">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Coluna Esquerda: Formulários de Configuração (2 colunas) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card 1: Identidade do Portfólio -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 shadow-sm">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <span>🏷️</span> Identidade do Site
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Configure o título da página e as chamadas iniciais do seu portfólio público.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-1.5">
                            <label for="site_title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Título da Página (SEO / Aba do Navegador)</label>
                            <input type="text" name="site_title" id="site_title" value="{{ old('site_title', $settings->site_title) }}" required
                                   class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('site_title') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="site_subtitle" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Subtítulo de Destaque (Título Principal da Seção Hero)</label>
                            <input type="text" name="site_subtitle" id="site_subtitle" value="{{ old('site_subtitle', $settings->site_subtitle) }}" required
                                   class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('site_subtitle') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5" x-data="siteDescriptionEditor()">
                            <label for="site_description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Breve Descrição do Trabalho (Texto de Apoio da Seção Hero)</label>
                            
                            <!-- Caixa do Editor Rico (WYSIWYG) -->
                            <div class="border border-slate-200 rounded-[5px] overflow-hidden focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all bg-white dark:bg-slate-900">
                                
                                <!-- Barra de Ferramentas -->
                                <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 p-2 select-none">
                                    <button type="button" @click="format('bold')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 rounded transition-colors border-0" title="Negrito">
                                        <span class="font-bold text-xs px-0.5">B</span>
                                    </button>
                                    <button type="button" @click="format('italic')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 rounded transition-colors border-0" title="Itálico">
                                        <span class="italic text-xs px-0.5">I</span>
                                    </button>
                                    <button type="button" @click="format('underline')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 rounded transition-colors border-0" title="Sublinhado">
                                        <span class="underline text-xs px-0.5">U</span>
                                    </button>
                                    <span class="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1"></span>
                                    <button type="button" @click="format('insertUnorderedList')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-355 rounded transition-colors border-0" title="Lista Marcadores">
                                        <span class="text-xs px-0.5">• Lista</span>
                                    </button>
                                </div>

                                <!-- Área Editável -->
                                <div x-ref="editor" 
                                     contenteditable="true" 
                                     @input="siteDescription = $el.innerHTML" 
                                     @blur="siteDescription = $el.innerHTML" 
                                     class="w-full px-4 py-3 min-h-[100px] text-sm outline-none bg-white dark:bg-slate-900 prose dark:prose-invert max-w-none focus:outline-none overflow-y-auto"
                                     style="min-height: 100px;"></div>
                            </div>
                            
                            <textarea id="site_description_source" class="hidden">{{ old('site_description', $settings->site_description) }}</textarea>
                            <input type="hidden" name="site_description" :value="siteDescription">
                            @error('site_description') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Card 2: Seção Sobre Mim -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 shadow-sm">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                            <span>🧑‍🎨</span> Apresentação Profissional (Sobre Mim)
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Insira suas informações de biografia, trajetória de carreira e competências.</p>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-1.5">
                            <label for="about_title" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Título da Seção Sobre</label>
                            <input type="text" name="about_title" id="about_title" value="{{ old('about_title', $settings->about_title) }}" required
                                   class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('about_title') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5" x-data="aboutEditor()">
                            <label for="about_text" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Texto de Apresentação</label>
                            
                            <!-- Caixa do Editor Rico (WYSIWYG) -->
                            <div class="border border-slate-200 rounded-[5px] overflow-hidden focus-within:ring-2 focus-within:ring-primary-500/20 focus-within:border-primary-500 transition-all bg-white dark:bg-slate-900">
                                
                                <!-- Barra de Ferramentas -->
                                <div class="flex items-center gap-1 bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-800 p-2 select-none">
                                    <button type="button" @click="format('bold')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 rounded transition-colors border-0" title="Negrito">
                                        <span class="font-bold text-xs px-0.5">B</span>
                                    </button>
                                    <button type="button" @click="format('italic')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-355 rounded transition-colors border-0" title="Itálico">
                                        <span class="italic text-xs px-0.5">I</span>
                                    </button>
                                    <button type="button" @click="format('underline')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-350 rounded transition-colors border-0" title="Sublinhado">
                                        <span class="underline text-xs px-0.5">U</span>
                                    </button>
                                    <span class="h-4 w-px bg-slate-200 dark:bg-slate-700 mx-1"></span>
                                    <button type="button" @click="format('insertUnorderedList')" class="p-1.5 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-355 rounded transition-colors border-0" title="Lista Marcadores">
                                        <span class="text-xs px-0.5">• Lista</span>
                                    </button>
                                </div>

                                <!-- Área Editável -->
                                <div x-ref="editor" 
                                     contenteditable="true" 
                                     @input="aboutText = $el.innerHTML" 
                                     @blur="aboutText = $el.innerHTML" 
                                     class="w-full px-4 py-3 min-h-[180px] text-sm outline-none bg-white dark:bg-slate-900 prose dark:prose-invert max-w-none focus:outline-none overflow-y-auto"
                                     style="min-height: 180px;"></div>
                            </div>
                            
                            <textarea id="about_text_source" class="hidden">{{ old('about_text', $settings->about_text) }}</textarea>
                            <input type="hidden" name="about_text" :value="aboutText">
                            @error('about_text') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5" x-data="mediaUploader()">
                            <label class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Mídia de Destaque (Vídeo, GIF ou Imagem)</label>
                            
                            @if($settings->media_path)
                                <div x-show="!previewUrl" class="flex items-center gap-4 p-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px]">
                                    <div class="w-20 h-20 rounded overflow-hidden bg-slate-950 flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-800">
                                        @if(in_array(pathinfo($settings->media_path, PATHINFO_EXTENSION), ['mp4', 'webm', 'ogg']))
                                            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                                                <source src="{{ asset('storage/' . $settings->media_path) }}">
                                            </video>
                                        @else
                                            <img src="{{ asset('storage/' . $settings->media_path) }}" class="w-full h-full object-cover">
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-slate-700 dark:text-slate-350">Mídia Atual Ativa</p>
                                        <p class="text-[10px] text-slate-400 truncate mt-0.5">{{ basename($settings->media_path) }}</p>
                                    </div>
                                </div>
                            @endif

                            <div class="relative group border border-dashed border-slate-300 dark:border-slate-700 rounded-[5px] hover:border-primary-500 dark:hover:border-primary-500 transition-all bg-white dark:bg-slate-900 p-6 text-center">
                                <input type="file" name="about_media" id="about_media" accept="video/*,image/*" @change="handleFile($event)" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                                
                                <!-- Se não houver preview selecionado -->
                                <div x-show="!previewUrl" class="space-y-1">
                                    <svg class="mx-auto h-8 w-8 text-slate-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                    </svg>
                                    <div class="text-xs text-slate-600 dark:text-slate-400">
                                        <span class="font-semibold text-primary-600 hover:text-primary-500">Clique para enviar</span> ou arraste e solte
                                    </div>
                                    <p class="text-[9px] text-slate-400">Vídeo (MP4, WebM), GIF ou Imagem (PNG, JPG) de até 100MB</p>
                                </div>

                                <!-- Se houver preview temporário selecionado -->
                                <div x-show="previewUrl" x-cloak class="flex flex-col items-center justify-center gap-3">
                                    <div class="w-32 h-32 rounded overflow-hidden bg-slate-950 flex items-center justify-center border border-slate-200 dark:border-slate-800">
                                        <template x-if="isVideo">
                                            <video autoplay loop muted playsinline class="w-full h-full object-cover">
                                                <source :src="previewUrl" type="video/mp4">
                                            </video>
                                        </template>
                                        <template x-if="!isVideo">
                                            <img :src="previewUrl" class="w-full h-full object-cover">
                                        </template>
                                    </div>
                                    <div class="text-xs flex items-center justify-center gap-2">
                                        <span class="font-bold text-emerald-600 dark:text-emerald-400 truncate max-w-[200px]" x-text="fileName"></span>
                                        <button type="button" @click.stop="clearPreview()" class="text-rose-650 hover:underline font-semibold bg-transparent border-0 p-0 cursor-pointer">Remover</button>
                                    </div>
                                </div>
                            </div>
                            @error('about_media') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="skills" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Especialidades (separadas por vírgula)</label>
                            <input type="text" name="skills" id="skills" value="{{ old('skills', $settings->skills) }}" required placeholder="Ex: Ilustração Infantil, Diagramação Editorial, Identidade Visual, Criação de Personagens"
                                   class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            <p class="text-[10px] text-slate-400 mt-1">Essas especialidades serão exibidas como pequenas tags decorativas na área "Sobre Mim" da página pública.</p>
                            @error('skills') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Card 3 & 6: Recursos Dinâmicos (FAQ & Parceiros) -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 shadow-sm" x-data="{ currentTab: 'faq', activeFaqIndex: 0 }">
                    <!-- Abas superiores do Card -->
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 flex-wrap gap-4">
                        <div class="flex gap-2">
                            <button type="button" @click="currentTab = 'faq'"
                                    class="px-4 py-2 text-xs font-extrabold rounded-[5px] transition-colors border"
                                    :class="currentTab === 'faq' ? 'bg-slate-800 text-white border-slate-800' : 'bg-slate-50 text-slate-655 border-slate-200 hover:bg-slate-100'">
                                💬 Perguntas (FAQ)
                            </button>
                            <button type="button" @click="currentTab = 'partners'"
                                    class="px-4 py-2 text-xs font-extrabold rounded-[5px] transition-colors border"
                                    :class="currentTab === 'partners' ? 'bg-slate-800 text-white border-slate-800' : 'bg-slate-50 text-slate-655 border-slate-200 hover:bg-slate-100'">
                                🤝 Parceiros
                            </button>
                        </div>
                        
                        <!-- Botões de Ação para adicionar -->
                        <div>
                            <button type="button" x-show="currentTab === 'faq'" @click="addFaq(); activeFaqIndex = faqs.length - 1" class="px-3.5 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1 shadow-sm border border-primary-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Adicionar FAQ</span>
                            </button>
                            <button type="button" x-show="currentTab === 'partners'" @click="addPartner()" class="px-3.5 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1 shadow-sm border border-primary-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Adicionar Parceiro</span>
                            </button>
                        </div>
                    </div>

                    <!-- Conteúdo da Aba FAQ -->
                    <div x-show="currentTab === 'faq'" class="space-y-4" x-cloak>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">💬 Perguntas Frequentes (FAQ)</h3>
                            <p class="text-xs text-slate-400 mt-1">Crie dúvidas e respostas rápidas. Clique nas perguntas abaixo para expandi-las e editá-las.</p>
                        </div>
                        
                        <div class="space-y-3">
                            <template x-for="(faq, index) in faqs" :key="index">
                                <div class="border border-slate-200 rounded-[5px] overflow-hidden transition-all bg-white shadow-sm">
                                    <!-- Header -->
                                    <div @click="activeFaqIndex = (activeFaqIndex === index ? null : index)" 
                                         class="flex items-center justify-between p-3.5 bg-slate-50 hover:bg-slate-100 cursor-pointer select-none">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <span class="bg-primary-50 text-primary-750 text-[10px] font-extrabold px-2 py-0.5 rounded border border-primary-200 shrink-0" x-text="'FAQ #' + (index + 1)"></span>
                                            <span class="text-xs font-bold text-slate-700 truncate" x-text="faq.question || '(Nova pergunta sem título)'"></span>
                                        </div>
                                        <div class="flex items-center gap-2 shrink-0">
                                            <button type="button" @click.stop="removeFaq(index)" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-red-655 hover:bg-red-50 rounded transition-all border-0 shadow-none" title="Remover Pergunta">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                            <svg class="w-4 h-4 text-slate-400 transform transition-transform" :class="activeFaqIndex === index ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </div>
                                    </div>

                                    <!-- Body -->
                                    <div x-show="activeFaqIndex === index" class="p-4 border-t border-slate-150 space-y-3 bg-white">
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pergunta</label>
                                            <input type="text" :name="'faq[' + index + '][question]'" x-model="faq.question" required
                                                   placeholder="Ex: Como funciona o prazo de entrega?"
                                                   class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-[5px] text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                                        </div>
                                        <div class="space-y-1.5">
                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Resposta</label>
                                            <textarea :name="'faq[' + index + '][answer]'" x-model="faq.answer" required x-init="autosize($el); $nextTick(() => autosize($el))" @input="autosize($el)"
                                                      placeholder="Ex: O prazo varia..."
                                                      class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-[5px] text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400 min-h-[80px] resize-y"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="faqs.length === 0" class="p-6 text-center text-slate-400 text-xs italic bg-slate-50 border border-dashed border-slate-200 rounded-[5px]">
                                Nenhuma pergunta cadastrada no FAQ. Clique em adicionar para criar uma.
                            </div>
                        </div>
                    </div>

                    <!-- Conteúdo da Aba Parceiros -->
                    <div x-show="currentTab === 'partners'" class="space-y-4" x-cloak>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">🤝 Principais Parceiros</h3>
                            <p class="text-xs text-slate-400 mt-1">Exiba as marcas e empresas parceiras no rodapé do seu portfólio.</p>
                        </div>

                        <!-- Ativar/Desativar Seção -->
                        <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900 p-4 rounded-[5px] border border-slate-150 dark:border-slate-800">
                            <div class="space-y-0.5">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-200">Ativar Seção de Parceiros</span>
                                <p class="text-[10px] text-slate-400">Marque para exibir os logotipos dos parceiros na página pública do portfólio.</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" name="show_partners" value="1" {{ old('show_partners', $settings->show_partners) ? 'checked' : '' }} class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-primary-600"></div>
                            </label>
                        </div>

                        <div class="space-y-4">
                            <template x-for="(partner, index) in partners" :key="index">
                                <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200 rounded-[5px] p-5 relative transition-all duration-200 shadow-sm flex flex-col gap-4">
                                    <!-- Partner Header -->
                                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                        <div class="flex items-center gap-2">
                                            <span class="bg-primary-50 text-primary-750 text-[10px] font-bold px-2 py-0.5 rounded border border-primary-200" x-text="'Parceiro #' + (index + 1)"></span>
                                        </div>
                                        <button type="button" @click="removePartner(index)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-655 hover:bg-red-50 rounded-[5px] transition-all border-0 shadow-none" title="Remover Parceiro">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>

                                    <!-- Form Fields -->
                                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                                        <!-- Left Side: Logo Input & Preview -->
                                        <div class="space-y-2 flex flex-col items-center justify-center">
                                            <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider text-center block">Logotipo</label>
                                            
                                            <!-- Logo Box Preview -->
                                            <div class="w-24 h-24 rounded border border-slate-200 bg-white flex items-center justify-center overflow-hidden relative shadow-sm">
                                                <!-- Current Logo or temp upload -->
                                                <template x-if="partner.temp_preview || partner.logo_path">
                                                    <img :src="partner.temp_preview || ('{{ asset('storage') }}/' + partner.logo_path)" class="w-full h-full object-contain p-2">
                                                </template>
                                                <template x-if="!partner.temp_preview && !partner.logo_path">
                                                    <span class="text-[10px] text-slate-400 text-center px-2">Sem Logo</span>
                                                </template>
                                            </div>

                                            <!-- File Input Wrapper -->
                                            <div class="relative w-full">
                                                <button type="button" class="w-full py-1.5 px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded transition-colors text-center border border-slate-200">Escolher Logo</button>
                                                <input type="file" :name="'partner_logos[' + index + ']'" @change="handlePartnerLogo($event, index)" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                                            </div>
                                        </div>

                                        <!-- Right Side: Text inputs -->
                                        <div class="md:col-span-2 space-y-3">
                                            <input type="hidden" :name="'partners[' + index + '][logo_path]'" x-model="partner.logo_path">
                                            
                                            <div class="space-y-1.5">
                                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Nome da Empresa</label>
                                                <input type="text" :name="'partners[' + index + '][name]'" x-model="partner.name" required
                                                       placeholder="Ex: Google, Facebook, Startup Local..."
                                                       class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-[5px] text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                            </div>

                                            <div class="space-y-1.5">
                                                <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Link do Parceiro (Opcional)</label>
                                                <input type="url" :name="'partners[' + index + '][url]'" x-model="partner.url"
                                                       placeholder="Ex: https://empresa.com.br"
                                                       class="w-full px-3.5 py-2 bg-white border border-slate-200 rounded-[5px] text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="partners.length === 0" class="p-6 text-center text-slate-400 text-xs italic bg-slate-50 border border-dashed border-slate-200 rounded-[5px]">
                                Nenhum parceiro cadastrado. Clique em adicionar para listar marcas.
                            </div>
                        </div>
                    </div>
                </div>

<script>
  function autosize(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
  }
</script>

            </div>

            <!-- Coluna Direita: Contato e Ações (1 coluna) -->
            <div class="space-y-6">
                
                <!-- Card 4: Links e Contatos -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 space-y-6 shadow-sm">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span>📞</span> Contatos e Redes
                        </h3>
                        <p class="text-[10px] text-slate-400 mt-1">Configure as formas de contato e o link do seu portfólio externo.</p>
                    </div>

                    <div class="space-y-5">
                        <div class="space-y-1.5">
                            <label for="contact_email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail de Contato (Destino do Formulário)</label>
                            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings->contact_email) }}" required
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('contact_email') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="contact_phone" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Telefone WhatsApp</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}" required placeholder="Ex: (14) 99143-6268"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('contact_phone') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="behance_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perfil Behance (URL)</label>
                            <input type="text" name="behance_url" id="behance_url" value="{{ old('behance_url', $settings->behance_url) }}" placeholder="Ex: behance.net/danilomiguel"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('behance_url') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="instagram_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perfil Instagram (URL)</label>
                            <input type="text" name="instagram_url" id="instagram_url" value="{{ old('instagram_url', $settings->instagram_url) }}" placeholder="Ex: instagram.com/danilomiguel"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('instagram_url') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="linkedin_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perfil LinkedIn (URL)</label>
                            <input type="text" name="linkedin_url" id="linkedin_url" value="{{ old('linkedin_url', $settings->linkedin_url) }}" placeholder="Ex: linkedin.com/in/danilomiguel"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('linkedin_url') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1.5">
                            <label for="facebook_url" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Perfil Facebook (URL)</label>
                            <input type="text" name="facebook_url" id="facebook_url" value="{{ old('facebook_url', $settings->facebook_url) }}" placeholder="Ex: facebook.com/danilomiguel"
                                   class="w-full px-4 py-3 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">
                            @error('facebook_url') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Card 5: Cores do Portfólio (Tema) -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 space-y-6 shadow-sm" x-data="{ 
                    presets: [
                        { name: 'Azul Moderno', primary: '#3b82f6', secondary: '#1d4ed8' },
                        { name: 'Roxo Indigo', primary: '#6366f1', secondary: '#4338ca' },
                        { name: 'Violeta Elegante', primary: '#8b5cf6', secondary: '#5b21b6' },
                        { name: 'Verde Esmeralda', primary: '#10b981', secondary: '#047857' },
                        { name: 'Laranja Amber', primary: '#f59e0b', secondary: '#b45309' },
                        { name: 'Rosa Rose', primary: '#f43f5e', secondary: '#be123c' }
                    ],
                    primaryColor: '{{ old('primary_color', $settings->primary_color ?? '#3b82f6') }}',
                    secondaryColor: '{{ old('secondary_color', $settings->secondary_color ?? '#1d4ed8') }}',
                    applyPreset(preset) {
                        this.primaryColor = preset.primary;
                        this.secondaryColor = preset.secondary;
                    }
                }">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <span>🎨</span> Tema e Cores
                        </h3>
                        <p class="text-[10px] text-slate-400 mt-1">Defina o estilo visual e as cores que darão o tom do seu portfólio público.</p>
                    </div>

                    <div class="space-y-5">
                        <!-- Modo do Tema (Claro / Escuro) -->
                        <div class="space-y-2 pb-3 border-b border-slate-100" x-data="{ themeMode: '{{ old('theme_mode', $settings->theme_mode ?? 'escuro') }}' }">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Estilo do Fundo (Tema)</span>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="border rounded-[5px] p-3 flex items-center justify-between cursor-pointer transition-all hover:bg-slate-50"
                                       :class="themeMode === 'escuro' ? 'border-primary-500 bg-primary-50/10 ring-2 ring-primary-500/10' : 'border-slate-200 bg-white'">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">🌙</span>
                                        <div class="text-left">
                                            <p class="text-xs font-bold text-slate-800">Modo Escuro</p>
                                            <p class="text-[9px] text-slate-400">Fundo escuro premium (Original)</p>
                                        </div>
                                    </div>
                                    <input type="radio" name="theme_mode" value="escuro" x-model="themeMode" class="text-primary-600 focus:ring-primary-500">
                                </label>

                                <label class="border rounded-[5px] p-3 flex items-center justify-between cursor-pointer transition-all hover:bg-slate-50"
                                       :class="themeMode === 'claro' ? 'border-primary-500 bg-primary-50/10 ring-2 ring-primary-500/10' : 'border-slate-200 bg-white'">
                                    <div class="flex items-center gap-2">
                                        <span class="text-lg">☀️</span>
                                        <div class="text-left">
                                            <p class="text-xs font-bold text-slate-800">Modo Claro</p>
                                            <p class="text-[9px] text-slate-400">Fundo claro minimalista</p>
                                        </div>
                                    </div>
                                    <input type="radio" name="theme_mode" value="claro" x-model="themeMode" class="text-primary-600 focus:ring-primary-500">
                                </label>
                            </div>
                        </div>

                        <!-- Presets Rápidos -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block">Temas de Cores Sugeridos</span>
                            <div class="grid grid-cols-3 gap-2">
                                <template x-for="preset in presets" :key="preset.name">
                                    <button type="button" @click="applyPreset(preset)" 
                                            class="p-2 border rounded-[5px] text-[10px] font-semibold flex flex-col items-center gap-1.5 transition-all text-slate-700 bg-slate-50 hover:bg-slate-100 hover:border-slate-300"
                                            :class="(primaryColor.toLowerCase() === preset.primary.toLowerCase() && secondaryColor.toLowerCase() === preset.secondary.toLowerCase()) ? 'border-primary-500 bg-primary-50/30 ring-2 ring-primary-500/10 font-bold text-primary-750' : 'border-slate-200'">
                                        <!-- Color Dots -->
                                        <div class="flex gap-1">
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/10 inline-block" :style="'background-color: ' + preset.primary"></span>
                                            <span class="w-3.5 h-3.5 rounded-full border border-black/10 inline-block" :style="'background-color: ' + preset.secondary"></span>
                                        </div>
                                        <span x-text="preset.name"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Custom Colors -->
                        <div class="grid grid-cols-2 gap-4 pt-2">
                            <div class="space-y-1.5">
                                <label for="primary_color" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Cor Principal</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="primaryColor" class="w-10 h-10 border border-slate-200 rounded-[5px] cursor-pointer p-0.5 bg-white">
                                    <input type="text" name="primary_color" id="primary_color" x-model="primaryColor" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-xs focus:outline-none uppercase">
                                </div>
                                @error('primary_color') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                            </div>

                            <div class="space-y-1.5">
                                <label for="secondary_color" class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Cor Secundária</label>
                                <div class="flex items-center gap-2">
                                    <input type="color" x-model="secondaryColor" class="w-10 h-10 border border-slate-200 rounded-[5px] cursor-pointer p-0.5 bg-white">
                                    <input type="text" name="secondary_color" id="secondary_color" x-model="secondaryColor" required
                                           class="w-full px-3 py-2 bg-white border border-slate-200 rounded-[5px] text-slate-800 text-xs focus:outline-none uppercase">
                                </div>
                                @error('secondary_color') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Barra Flutuante de Ações (Salvar / Cancelar) -->
                <div class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 max-w-xl w-[90%] px-2 sm:px-4 drop-shadow-xl">
                    <div class="bg-white/95 dark:bg-slate-900/95 backdrop-blur-md border border-slate-200/60 dark:border-slate-850 rounded-full px-4 sm:px-6 py-2.5 sm:py-3 flex items-center justify-center sm:justify-between gap-3 sm:gap-4 shadow-2xl">
                        <div class="hidden sm:flex items-center gap-2 shrink-0">
                            <span class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider">Painel Portfólio</span>
                        </div>
                        <div class="flex items-center gap-2 sm:gap-3 w-full sm:w-auto justify-between sm:justify-end">
                            <a href="{{ route('portfolio.index') }}" class="px-6 py-2 border border-slate-200 dark:border-slate-850 text-slate-600 dark:text-slate-400 hover:text-slate-850 dark:hover:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-950/40 transition-colors font-bold rounded-full text-xs text-center shrink-0">
                                Cancelar
                            </a>
                            <button type="submit" class="px-5 py-2 bg-primary-600 hover:bg-primary-700 text-white font-extrabold rounded-full transition-all text-xs shadow-md border-0 shrink-0">
                                Salvar Configurações
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<!-- Scripts Alpine.js para o FAQ -->
<script>
    function faqSettings() {
        return {
            faqs: @json($settings->faq_items ?? []),
            partners: @json($settings->partner_items ?? []),

            addFaq() {
                this.faqs.push({
                    question: '',
                    answer: ''
                });
            },

            removeFaq(index) {
                this.faqs.splice(index, 1);
            },

            addPartner() {
                this.partners.push({
                    name: '',
                    url: '',
                    logo_path: '',
                    temp_preview: ''
                });
            },

            removePartner(index) {
                this.partners.splice(index, 1);
            },

            handlePartnerLogo(event, index) {
                const file = event.target.files[0];
                if (file) {
                    this.partners[index].temp_preview = URL.createObjectURL(file);
                }
            }
        }
    }
    function siteDescriptionEditor() {
        return {
            siteDescription: '',
            init() {
                const raw = document.getElementById('site_description_source').value;
                this.siteDescription = this.cleanHtml(raw);
                this.$refs.editor.innerHTML = this.siteDescription;
            },
            cleanHtml(html) {
                const div = document.createElement('div');
                div.innerHTML = html;
                div.querySelectorAll('[style]').forEach(el => {
                    el.style.backgroundColor = '';
                    el.style.color = '';
                });
                return div.innerHTML;
            },
            format(command) {
                document.execCommand(command, false, null);
            }
        }
    }

    function aboutEditor() {
        return {
            aboutText: '',
            init() {
                const raw = document.getElementById('about_text_source').value;
                this.aboutText = this.cleanHtml(raw);
                this.$refs.editor.innerHTML = this.aboutText;
            },
            cleanHtml(html) {
                const div = document.createElement('div');
                div.innerHTML = html;
                div.querySelectorAll('[style]').forEach(el => {
                    el.style.backgroundColor = '';
                    el.style.color = '';
                });
                return div.innerHTML;
            },
            format(command) {
                document.execCommand(command, false, null);
            }
        }
    }
    function mediaUploader() {
        return {
            previewUrl: null,
            isVideo: false,
            fileName: '',
            handleFile(e) {
                const file = e.target.files[0];
                if (!file) return;

                this.fileName = file.name;
                this.isVideo = file.type.startsWith('video/');
                
                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                }
                this.previewUrl = URL.createObjectURL(file);
            },
            clearPreview() {
                if (this.previewUrl) {
                    URL.revokeObjectURL(this.previewUrl);
                }
                this.previewUrl = null;
                this.isVideo = false;
                this.fileName = '';
                document.getElementById('about_media').value = '';
            }
        }
    }
</script>
@endsection
