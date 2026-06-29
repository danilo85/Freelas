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
    <form action="{{ route('portfolio.settings.update') }}" method="POST" class="space-y-6">
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

                        <div class="space-y-1.5">
                            <label for="site_description" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Breve Descrição do Trabalho (Texto de Apoio da Seção Hero)</label>
                            <textarea name="site_description" id="site_description" rows="3" required
                                      class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">{{ old('site_description', $settings->site_description) }}</textarea>
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

                        <div class="space-y-1.5">
                            <label for="about_text" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Texto de Apresentação</label>
                            <textarea name="about_text" id="about_text" rows="6" required
                                      class="w-full px-4 py-3 rounded-[5px] border border-slate-200 text-slate-800 bg-white text-sm focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all">{{ old('about_text', $settings->about_text) }}</textarea>
                            @error('about_text') <p class="text-xs text-red-650 font-medium">{{ $message }}</p> @enderror
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

                <!-- Card 3: FAQ Dinâmico -->
                <div class="bg-white rounded-[5px] border border-slate-200 p-5 sm:p-8 space-y-6 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-4 flex-wrap gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900 flex items-center gap-2">
                                <span>💬</span> Perguntas Frequentes (FAQ)
                            </h2>
                            <p class="text-xs text-slate-400 mt-1">Crie dúvidas e respostas rápidas para os visitantes do seu site.</p>
                        </div>
                        <button type="button" @click="addFaq()" class="px-3.5 py-2 bg-primary-50 hover:bg-primary-100 text-primary-700 text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1 shadow-sm border border-primary-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Adicionar Pergunta</span>
                        </button>
                    </div>

                    <div class="space-y-4">
                        <template x-for="(faq, index) in faqs" :key="index">
                            <div class="bg-slate-50/50 hover:bg-slate-50 border border-slate-200 rounded-[5px] p-5 relative transition-all duration-200 shadow-sm flex flex-col gap-4">
                                <!-- FAQ Card Header -->
                                <div class="flex items-center justify-between border-b border-slate-200/60 pb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="bg-primary-50 text-primary-750 text-[10px] font-bold px-2 py-0.5 rounded border border-primary-200" x-text="'FAQ #' + (index + 1)"></span>
                                    </div>
                                    <button type="button" @click="removeFaq(index)" class="w-8 h-8 flex items-center justify-center text-slate-400 hover:text-red-650 hover:bg-red-50 rounded-[5px] transition-all border-0 shadow-none" title="Remover Pergunta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>

                                <!-- Form Fields (Vertical Stack, 100% width) -->
                                <div class="space-y-4">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Pergunta</label>
                                        <input type="text" :name="'faq[' + index + '][question]'" x-model="faq.question" required
                                               placeholder="Ex: Como funciona o prazo de entrega?"
                                               class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-[5px] text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400">
                                    </div>

                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-bold text-slate-500 uppercase tracking-wider">Resposta</label>
                                        <textarea :name="'faq[' + index + '][answer]'" x-model="faq.answer" required x-init="autosize($el); $nextTick(() => autosize($el))" @input="autosize($event.target)"
                                                  placeholder="Ex: O prazo varia de acordo com a complexidade de cada projeto, mas geralmente..."
                                                  class="w-full px-3.5 py-2.5 bg-white border border-slate-200 rounded-[5px] text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400 min-h-[100px] resize-y"></textarea>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="faqs.length === 0" class="p-6 text-center text-slate-400 text-xs italic bg-slate-50 border border-dashed border-slate-200 rounded-[5px]">
                            Nenhuma pergunta cadastrada no FAQ. Clique em adicionar para criar uma.
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

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label for="contact_email" class="text-xs font-semibold text-slate-500 uppercase tracking-wider">E-mail de Contato</label>
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

                <!-- Botões de Ação -->
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 space-y-3 shadow-sm">
                    <div class="pt-4 border-t border-slate-100 flex justify-end gap-3">
                        <a href="{{ route('portfolio.index') }}" class="px-6 py-3 border border-slate-200 text-slate-600 hover:text-slate-800 hover:bg-slate-50 transition-colors font-semibold rounded-[5px] text-sm">
                            Cancelar
                        </a>
                        <button type="submit" class="px-6 py-3 bg-primary-600 hover:bg-primary-700 text-white font-semibold rounded-[5px] transition-colors text-sm shadow-sm">
                            Salvar Configurações
                        </button>
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

            addFaq() {
                this.faqs.push({
                    question: '',
                    answer: ''
                });
            },

            removeFaq(index) {
                this.faqs.splice(index, 1);
            }
        }
    }
</script>
@endsection
