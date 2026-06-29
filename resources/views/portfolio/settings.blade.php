@extends('layouts.app')

@section('title', 'Configurações do Portfólio')

@section('content')
<div class="space-y-6" x-data="faqSettings()">
    <!-- Header da Página -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Configurações do Portfólio</h1>
            <p class="text-sm text-slate-400">Configure as informações, contatos e seções exibidas na sua página pública de portfólio.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('public.home') }}" target="_blank" class="px-4 py-2 bg-slate-800 hover:bg-slate-750 text-white text-xs font-semibold uppercase tracking-wider rounded-[5px] transition-colors flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                </svg>
                <span>Visualizar Site</span>
            </a>
        </div>
    </div>

    <!-- Feedback de Sucesso -->
    @if(session('success'))
        <div class="p-4 bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 rounded-[5px] text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <!-- Formulário Principal -->
    <form action="{{ route('portfolio.settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Coluna Esquerda: Identidade & Sobre (2/3 de largura no LG) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Card 1: Identidade do Portfólio -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-[5px] p-6 space-y-4">
                    <div class="border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span class="text-lg">🏷️</span>
                        <h2 class="text-base font-bold text-white uppercase tracking-wider">Identidade do Site</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-1">
                            <label for="site_title" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Título da Página (SEO)</label>
                            <input type="text" name="site_title" id="site_title" value="{{ old('site_title', $settings->site_title) }}" required
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('site_title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="site_subtitle" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Subtítulo de Destaque (Hero)</label>
                            <input type="text" name="site_subtitle" id="site_subtitle" value="{{ old('site_subtitle', $settings->site_subtitle) }}" required
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('site_subtitle') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="site_description" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Breve Descrição do Trabalho</label>
                            <textarea name="site_description" id="site_description" rows="3" required
                                      class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">{{ old('site_description', $settings->site_description) }}</textarea>
                            @error('site_description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Card 2: Seção Sobre Mim -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-[5px] p-6 space-y-4">
                    <div class="border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span class="text-lg">🧑‍🎨</span>
                        <h2 class="text-base font-bold text-white uppercase tracking-wider">Apresentação (Sobre Mim)</h2>
                    </div>

                    <div class="grid grid-cols-1 gap-4">
                        <div class="space-y-1">
                            <label for="about_title" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Título da Seção Sobre</label>
                            <input type="text" name="about_title" id="about_title" value="{{ old('about_title', $settings->about_title) }}" required
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('about_title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="about_text" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Texto de Apresentação Completo</label>
                            <textarea name="about_text" id="about_text" rows="6" required
                                      class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">{{ old('about_text', $settings->about_text) }}</textarea>
                            @error('about_text') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="skills" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Especialidades (separadas por vírgula)</label>
                            <input type="text" name="skills" id="skills" value="{{ old('skills', $settings->skills) }}" required placeholder="Ex: Ilustração Infantil, Diagramação Editorial, Identidade Visual"
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            <p class="text-[10px] text-slate-500 mt-1">Essas tags serão exibidas em formato de especialidades no seu portfólio.</p>
                            @error('skills') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

            </div>

            <!-- Coluna Direita: Contato e FAQ (1/3 de largura no LG) -->
            <div class="space-y-6">
                
                <!-- Card 3: Links e Contatos -->
                <div class="bg-slate-900/50 border border-slate-800 rounded-[5px] p-6 space-y-4">
                    <div class="border-b border-slate-800 pb-3 flex items-center gap-2">
                        <span class="text-lg">📞</span>
                        <h2 class="text-base font-bold text-white uppercase tracking-wider">Contato & Redes</h2>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1">
                            <label for="contact_email" class="text-xs font-bold text-slate-400 uppercase tracking-wider">E-mail de Contato</label>
                            <input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $settings->contact_email) }}" required
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('contact_email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="contact_phone" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Telefone WhatsApp</label>
                            <input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $settings->contact_phone) }}" required placeholder="Ex: (14) 99143-6268"
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('contact_phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="behance_url" class="text-xs font-bold text-slate-400 uppercase tracking-wider">Perfil Behance (URL)</label>
                            <input type="text" name="behance_url" id="behance_url" value="{{ old('behance_url', $settings->behance_url) }}" placeholder="Ex: behance.net/danilomiguel"
                                   class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            @error('behance_url') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Botão de Salvar Alterações -->
                <button type="submit" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-[5px] transition-colors text-center text-sm shadow-md shadow-blue-600/10">
                    Salvar Configurações
                </button>

            </div>

        </div>

        <!-- Card FAQ: Perguntas Frequentes (Comportamento Dinâmico de Edição) -->
        <div class="bg-slate-900/50 border border-slate-800 rounded-[5px] p-6 space-y-4">
            <div class="border-b border-slate-800 pb-3 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-lg">💬</span>
                    <h2 class="text-base font-bold text-white uppercase tracking-wider">Perguntas Frequentes (FAQ)</h2>
                </div>
                <button type="button" @click="addFaq()" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-750 text-slate-300 hover:text-white text-xs font-bold rounded-[5px] transition-colors flex items-center gap-1">
                    <span>+ Adicionar Pergunta</span>
                </button>
            </div>

            <!-- Lista Dinâmica de FAQs -->
            <div class="space-y-4">
                <template x-for="(faq, index) in faqs" :key="index">
                    <div class="p-4 bg-slate-950/60 border border-slate-850 rounded-[5px] relative space-y-3">
                        <!-- Botão de Remover -->
                        <button type="button" @click="removeFaq(index)" class="absolute top-4 right-4 text-xs font-bold text-red-500 hover:text-red-400 hover:underline">
                            Remover
                        </button>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1 pr-12 md:pr-0">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Pergunta</label>
                                <input type="text" :name="'faq[' + index + '][question]'" x-model="faq.question" required
                                       class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors">
                            </div>

                            <div class="space-y-1">
                                <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Resposta</label>
                                <textarea :name="'faq[' + index + '][answer]'" x-model="faq.answer" required rows="2"
                                          class="w-full px-3.5 py-2 bg-slate-950 border border-slate-800 rounded-[5px] text-sm text-slate-200 focus:outline-none focus:border-primary-500 transition-colors"></textarea>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="faqs.length === 0" class="p-6 text-center text-slate-500 text-xs italic bg-slate-950/30 border border-dashed border-slate-800 rounded-[5px]">
                    Nenhuma pergunta cadastrada no FAQ. Clique em adicionar para criar uma.
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
