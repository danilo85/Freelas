@extends('layouts.app')

@section('title', 'Gerenciar Manual de Identidade - Gestor de Freelas')
@section('page_title', 'Gerenciar Manual de Marca')

@section('content')
<!-- Custom style to dynamically load uploaded fonts for live previews -->
<style id="dynamic-fonts-preview"></style>

<div x-data="brandGuidelineEdit()" class="space-y-6 relative">

    <!-- Floating Toast Notification -->
    <div x-show="toast.show" x-transition class="fixed bottom-6 left-6 text-white text-xs font-bold px-4 py-3.5 rounded-[5px] shadow-lg z-50 flex items-center gap-2"
         :class="toast.type === 'success' ? 'bg-emerald-600' : 'bg-rose-600'" x-cloak>
        <span x-text="toast.type === 'success' ? '✓' : '✕'"></span>
        <span x-text="toast.message"></span>
    </div>

    <!-- Header & Back Button -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <a href="{{ route('revisoes.brand-guidelines.index') }}" class="flex items-center gap-2 text-slate-500 hover:text-slate-800 text-sm font-medium transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7 7-7m8 14l-7-7 7-7"></path>
            </svg>
            Voltar para a Listagem
        </a>
        
        <div class="flex items-center gap-2" x-show="activeTab === 'hub'">
            <a href="{{ route('public.brand.show', $guideline->share_token) }}" target="_blank" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-sm flex items-center gap-1.5 transition-colors">
                🔗 Abrir Página Pública
            </a>
            <a href="{{ route('revisoes.brand-guidelines.zip', $guideline->id) }}" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-sm flex items-center gap-1.5 transition-colors">
                📦 Pacote ZIP Final
            </a>
        </div>

        <button x-show="activeTab !== 'hub'" @click="activeTab = 'hub'" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs uppercase tracking-wider rounded-[5px] shadow-sm flex items-center gap-1 transition-colors">
            ◀ Voltar ao Painel
        </button>
    </div>

    <!-- MAIN HUB / DASHBOARD (ActiveTab === 'hub') -->
    <div x-show="activeTab === 'hub'" class="space-y-6" x-cloak>
        <!-- Card 1: Compartilhamento -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-5 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h4 class="font-extrabold text-slate-850 dark:text-slate-200 text-base">Compartilhamento</h4>
                <p class="text-xs text-slate-400 mt-1">Compartilhe a página pública da identidade com seu cliente.</p>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 w-full md:w-auto">
                <button @click="copyShareLink('{{ $guideline->share_token }}', $event)" class="py-2.5 px-4 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-[5px] flex items-center justify-center gap-1.5 shadow-sm uppercase tracking-wider transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg> Copiar Link
                </button>
                <a href="{{ route('public.brand.show', $guideline->share_token) }}" target="_blank" class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-[5px] flex items-center justify-center gap-1.5 shadow-sm uppercase tracking-wider transition-colors text-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 00-2 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg> Abrir
                </a>
                <button @click="toggleActiveState()" class="py-2.5 px-4 border rounded-[5px] text-xs font-bold uppercase tracking-wider transition-all shadow-sm flex items-center justify-center gap-1.5 cursor-pointer"
                        :class="is_active ? 'bg-emerald-50 border-emerald-250 text-emerald-700 hover:bg-emerald-100/50' : 'bg-rose-50 border-rose-250 text-rose-700 hover:bg-rose-100/50'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                    <span x-text="is_active ? 'Ativo' : 'Oculto'"></span>
                </button>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Cores -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-slate-850 dark:text-slate-200 text-base">Cores</h4>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="'✓ ' + colors.length"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Gerencie a paleta e notas de uso de todas as variações cromáticas.</p>
                    </div>
                </div>
                <button @click="activeTab = 'cores'" class="w-full py-2.5 bg-violet-600 hover:bg-violet-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path></svg> Abrir Gestão de Cores
                </button>
            </div>

            <!-- Fontes -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-slate-855 dark:text-slate-200 text-base">Fontes</h4>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="'✓ ' + fonts.length"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Gerencie variações e arquivos das tipografias corporativas.</p>
                    </div>
                </div>
                <button @click="activeTab = 'fontes'" class="w-full py-2.5 bg-fuchsia-600 hover:bg-fuchsia-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg> Abrir Gestão de Fontes
                </button>
            </div>

            <!-- Papelaria -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-slate-855 dark:text-slate-200 text-base">Papelaria</h4>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full" x-text="'✓ ' + stationery.length"></span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Envie amostras e arquivos (PDF, SVG, imagens) de cartão de visitas, papel timbrado, envelopes, etc.</p>
                    </div>
                </div>
                <button @click="activeTab = 'papelaria'" class="w-full py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg> Abrir Gestão de Papelaria
                </button>
            </div>

            <!-- Imagens de Redes Sociais -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-black text-slate-855 dark:text-slate-200 text-base">Imagens de Redes Sociais</h4>
                        <p class="text-xs text-slate-400 mt-1">Envie capas e avatares separados por redes (Instagram, Facebook, TikTok, WhatsApp, etc).</p>
                    </div>
                </div>
                <button @click="activeTab = 'social'" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 10.742l4.755-2.717m0 7.95l-4.755-2.717M21 12a3 3 0 11-6 0 3 3 0 016 0zM6 9a3 3 0 11-6 0 3 3 0 016 0zm12 9a3 3 0 11-6 0 3 3 0 016 0z"></path></svg> Abrir Gestão Social
                </button>
            </div>

            <!-- Imagens (Logotipos) -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-black text-slate-855 dark:text-slate-200 text-base">Imagens da Logo</h4>
                            <span class="bg-emerald-50 text-emerald-700 border border-emerald-100 text-[10px] font-bold px-2 py-0.5 rounded-full" x-show="logo_primary || logo_secondary || logo_symbol">✓</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Gerencie uploads, variações (horizontal, vertical, ícone) e textos conceituais explicativos.</p>
                    </div>
                </div>
                <button @click="activeTab = 'logos'" class="w-full py-2.5 bg-teal-600 hover:bg-teal-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg> Abrir Gestão de Imagens
                </button>
            </div>

            <!-- Assinatura de E-mail -->
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-black text-slate-855 dark:text-slate-200 text-base">Assinatura de E-mail</h4>
                        <p class="text-xs text-slate-400 mt-1">Gere uma assinatura HTML profissional compatível com diversos clientes de e-mail.</p>
                    </div>
                </div>
                <button @click="activeTab = 'assinatura'" class="w-full py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21.8 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg> Abrir Gerador de Assinatura
                </button>
            </div>
        </div>

        <!-- Pacote Final (Upload de arquivo ZIP compactado) -->
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-[5px] p-6 shadow-sm space-y-4">
            <div>
                <h4 class="font-black text-slate-850 dark:text-slate-200 text-sm uppercase tracking-wider">Pacote Final</h4>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Suba um arquivo único contendo todos os assets consolidados (ex: ZIP com manuais, PDFs, EPS e logos)</p>
            </div>

            <!-- Drag & Drop Zone -->
            <div 
                @dragover.prevent="packageDragging = true"
                @dragleave.prevent="packageDragging = false"
                @drop.prevent="handlePackageDrop($event)"
                @click="$refs.packageInput.click()"
                class="border-2 border-dashed border-slate-200 dark:border-slate-850 rounded-[5px] p-8 text-center cursor-pointer transition-all duration-200 select-none flex flex-col items-center justify-center bg-slate-50/50"
                :class="packageDragging ? 'border-primary-500 bg-primary-50/15' : 'hover:bg-slate-50'"
            >
                <svg class="w-10 h-10 text-rose-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                <h4 class="font-extrabold text-xs text-slate-700">Arraste e solte o arquivo ZIP ou clique para selecionar</h4>
                <p class="text-[10px] text-slate-400 mt-1">Aceita arquivos compactados de até 100MB.</p>

                <!-- Current file preview if exists -->
                <div x-show="final_package || selectedPackageName" class="mt-4 p-2.5 bg-white border border-slate-200 rounded-[5px] inline-flex items-center gap-3 text-xs" @click.stop>
                    <span class="font-bold text-slate-750" x-text="selectedPackageName || 'pacote_atual.zip'"></span>
                    <a x-show="final_package" :href="'/storage/' + final_package" download class="text-blue-600 hover:underline">Download</a>
                </div>
            </div>
            <input type="file" x-ref="packageInput" @change="handlePackageFileChange($event)" class="hidden" accept=".zip,.rar,.tar,.gz">

            <div class="flex justify-end pt-2">
                <button type="button" @click="savePackage()" class="px-5 py-2.5 bg-slate-900 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] shadow transition-colors cursor-pointer">
                    Salvar Pacote Final
                </button>
            </div>
        </div>
    </div>

    <!-- TABS/STAGE VIEWS -->

    <!-- VIEW 1: CORES -->
    <div x-show="activeTab === 'cores'" class="space-y-6" x-cloak>
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-outfit font-black text-lg text-slate-900">Gerenciar Paleta de Cores</h3>
                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Definição dos padrões HEX, RGB e CMYK</p>
            </div>
            <div class="flex items-center gap-2 w-full sm:w-auto justify-start sm:justify-end">
                <button @click="saveColors()" class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-750 text-white text-[10px] font-bold uppercase tracking-wider rounded-[5px] transition-colors">
                    Salvar Paleta
                </button>
                <button @click="addColor()" class="px-3.5 py-1.5 bg-slate-900 text-white text-[10px] font-bold uppercase tracking-wider rounded-[5px] transition-colors">
                    + Nova Cor
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="(color, index) in colors" :key="index">
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col justify-between space-y-4">
                    <!-- Colored Box -->
                    <div class="w-full h-24 rounded-[5px] shadow-inner border border-slate-100 transition-all duration-200" :style="'background-color: ' + (color.hex || '#ccc')"></div>

                    <!-- Metrics preview text -->
                    <div class="space-y-1.5">
                        <h4 class="font-extrabold text-sm text-slate-800 truncate" x-text="color.name || 'Cor Sem Nome'"></h4>
                        <div class="text-[10px] text-slate-450 font-bold font-mono space-y-0.5">
                            <div class="flex justify-between"><span>HEX:</span><span x-text="color.hex"></span></div>
                            <div class="flex justify-between"><span>RGB:</span><span x-text="color.rgb"></span></div>
                            <div class="flex justify-between"><span>CMYK:</span><span x-text="color.cmyk"></span></div>
                        </div>
                    </div>

                    <!-- Inputs fields -->
                    <div class="space-y-2 pt-2 border-t border-slate-100">
                        <div class="flex gap-2">
                            <!-- Color Selector & HEX input -->
                            <input type="color" x-model="color.hex" @input="convertHex(color)" class="w-7 h-7 rounded border border-slate-200 cursor-pointer shrink-0">
                            <input type="text" x-model="color.hex" @input="convertHex(color)" placeholder="HEX (Ex: #000000)" class="w-full px-2 py-1 text-xs border border-slate-200 rounded text-slate-700 focus:outline-none uppercase font-mono">
                        </div>
                        <input type="text" x-model="color.name" placeholder="Nome da Cor" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs text-slate-700 focus:outline-none">
                        <input type="text" x-model="color.rgb" placeholder="RGB" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs text-slate-700 focus:outline-none font-mono">
                        <input type="text" x-model="color.cmyk" placeholder="CMYK" class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs text-slate-700 focus:outline-none font-mono">
                        <textarea x-model="color.note" rows="2" placeholder="Nota de uso..." class="w-full px-2.5 py-1 border border-slate-200 rounded text-xs text-slate-700 focus:outline-none resize-none"></textarea>
                    </div>

                    <!-- Operations -->
                    <div class="flex justify-end gap-3 pt-2">
                        <button type="button" @click="removeColor(index)" class="text-rose-600 hover:text-rose-700 text-xs font-bold uppercase flex items-center gap-1 cursor-pointer">
                            Excluir
                        </button>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- VIEW 2: FONTES -->
    <div x-show="activeTab === 'fontes'" class="space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-outfit font-black text-lg text-slate-900">Gerenciar Tipografias</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Faça upload de arquivos de fontes e confira o visual</p>
        </div>

        <div class="space-y-6">
            <!-- Drag and drop zone for font files (Full width) -->
            <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm">
                <div 
                    @dragover.prevent="fontDragging = true"
                    @dragleave.prevent="fontDragging = false"
                    @drop.prevent="handleFontDrop($event)"
                    @click="$refs.fontFileInput.click()"
                    class="border-2 border-dashed border-slate-200 dark:border-slate-800 hover:bg-slate-50/50 rounded-[5px] p-8 text-center cursor-pointer transition-all duration-200 select-none flex flex-col items-center justify-center bg-slate-50/20"
                    :class="fontDragging ? 'border-primary-500 bg-primary-50/15' : ''"
                >
                    <svg class="w-10 h-10 text-slate-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path></svg>
                    <h4 class="font-extrabold text-xs text-slate-700">Clique ou arraste múltiplas fontes aqui para adicionar instantaneamente</h4>
                    <p class="text-[10px] text-slate-400 mt-1">Formatos aceitos: .ttf, .woff, .woff2. Máximo 10MB por arquivo.</p>
                </div>
                <input type="file" x-ref="fontFileInput" @change="handleFontFileChange($event)" class="hidden" accept=".ttf,.woff,.woff2" multiple>
            </div>

            <!-- Font List and Live Previews -->
            <div class="space-y-4">
                <template x-for="(font, index) in fonts" :key="index">
                    <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4 relative">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                            <div>
                                <h4 class="font-extrabold text-sm text-slate-800" x-text="font.font_family"></h4>
                                <span class="text-[9px] text-slate-400 font-bold uppercase tracking-wider" x-text="'Uso: ' + font.usage"></span>
                            </div>
                            
                            <div class="flex items-center gap-2">
                                <!-- Normal state -->
                                <button x-show="deletingFontIndex !== index" type="button" @click="deletingFontIndex = index" class="text-rose-600 hover:text-rose-700 text-xs font-bold uppercase">
                                    Excluir
                                </button>
                                
                                <!-- Confirming state -->
                                <template x-if="deletingFontIndex === index">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[10px] font-bold text-rose-600 uppercase">Confirmar?</span>
                                        <button type="button" @click="removeFont(index)" class="px-2 py-1 bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] uppercase rounded">Sim</button>
                                        <button type="button" @click="deletingFontIndex = null" class="px-2 py-1 bg-slate-100 hover:bg-slate-200 text-slate-600 font-bold text-[10px] uppercase rounded">Não</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- Live specimen preview using custom dynamic style injection -->
                        <div class="p-4 bg-slate-50 rounded border border-slate-100 text-slate-900 overflow-hidden" :style="'font-family: \'' + font.font_family + '\', sans-serif;'">
                            <p class="text-xl md:text-2xl font-light break-all">AaBbCcDdEeFfGgHh 123</p>
                            <p class="text-xl md:text-2xl font-extrabold mt-1 break-all">AaBbCcDdEeFfGgHh 123</p>
                            <p class="text-xs text-slate-500 mt-3 break-all" x-text="font.specimen_text || 'O rato roeu a roupa do rei de Roma.'"></p>
                        </div>
                    </div>
                </template>
                <div x-show="fonts.length === 0" class="border border-dashed border-slate-200 p-8 text-center text-slate-400 text-xs rounded bg-white" x-cloak>
                    Nenhuma fonte cadastrada ainda.
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 3: PAPELARIA -->
    <div x-show="activeTab === 'papelaria'" class="space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-outfit font-black text-lg text-slate-900">Gerenciar Papelaria & Mockups</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Adicione PDFs, templates PPT, fotos de envelopes e cartões de visita (Suporta multiplos uploads de até 50MB por arquivo)</p>
        </div>

        <!-- Drag & Drop Zone for stationery -->
        <div 
            @dragover.prevent="stationeryDragging = true"
            @dragleave.prevent="stationeryDragging = false"
            @drop.prevent="handleStationeryDrop($event)"
            @click="$refs.stationeryFilesInput.click()"
            class="border-2 border-dashed border-slate-200 hover:bg-slate-50 rounded-[5px] p-8 text-center cursor-pointer transition-all duration-200 flex flex-col items-center justify-center bg-white"
            :class="stationeryDragging ? 'border-primary-500 bg-primary-50/15' : ''"
        >
            <span class="text-4xl mb-2">📁</span>
            <h4 class="font-extrabold text-xs text-slate-700">Clique ou arraste múltiplos arquivos de mockups aqui</h4>
            <p class="text-[10px] text-slate-400 mt-1">Imagens, PDFs, AI, PSD, SVG, etc. Máximo 50MB por arquivo.</p>
        </div>
        <input type="file" x-ref="stationeryFilesInput" @change="handleStationeryFileChange($event)" class="hidden" multiple>

        <!-- Temporary list of pending files to upload -->
        <template x-if="newStationeryFiles.length > 0">
            <div class="bg-white border border-slate-200 rounded-[5px] p-5 space-y-4 shadow-sm">
                <h5 class="text-xs font-black text-slate-850 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Arquivos Pendentes para Salvar
                </h5>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                    <template x-for="(file, idx) in newStationeryFiles" :key="idx">
                        <div class="bg-slate-50 border border-slate-200 rounded-[5px] p-4 flex flex-col justify-between space-y-3 relative">
                            <!-- Image Preview / File Icon -->
                            <div class="w-full h-28 bg-white border border-slate-100 rounded flex items-center justify-center overflow-hidden p-1 relative">
                                <template x-if="file.previewUrl">
                                    <img :src="file.previewUrl" class="max-w-full max-h-full object-contain">
                                </template>
                                <template x-if="!file.previewUrl">
                                    <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                </template>
                            </div>

                            <!-- Name info & edit -->
                            <div class="space-y-1.5 min-w-0">
                                <span class="font-bold text-[10px] text-slate-400 uppercase tracking-wider block truncate" :title="file.name" x-text="file.name"></span>
                                <input type="text" placeholder="Nome de exibição..." x-model="newStationeryNames[idx]" class="w-full px-2.5 py-1.5 border border-slate-200 rounded text-xs text-slate-700 focus:outline-none bg-white">
                            </div>

                            <!-- Remove button -->
                            <div class="flex justify-end pt-1 border-t border-slate-100">
                                <button type="button" @click="removePendingStationeryFile(idx)" class="text-rose-600 hover:text-rose-700 text-[10px] font-bold uppercase flex items-center gap-1 cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg> Excluir
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Upload Progress Bar -->
                <div x-show="uploadProgress > 0" class="space-y-1.5 pt-2" x-cloak>
                    <div class="flex justify-between text-[10px] font-bold text-slate-500 uppercase">
                        <span>Enviando arquivos...</span>
                        <span x-text="uploadProgress + '%'"></span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden relative">
                        <div class="bg-blue-600 h-full transition-all duration-150" :style="'width: ' + uploadProgress + '%'"></div>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-slate-100">
                    <button type="button" @click="saveStationery()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-750 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow cursor-pointer">
                        Salvar Novos Arquivos
                    </button>
                </div>
            </div>
        </template>

        <!-- List of saved stationery mockups with previews -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
            <template x-for="(item, idx) in stationery" :key="idx">
                <div class="bg-white border border-slate-200 rounded-[5px] p-4 shadow-sm flex flex-col justify-between space-y-4">
                    <!-- Image preview if item is image -->
                    <div class="w-full h-36 bg-slate-50 border border-slate-100 rounded flex items-center justify-center p-2 relative overflow-hidden">
                        <template x-if="isImageFile(item.path)">
                            <img :src="'/storage/' + item.path" class="max-w-full max-h-full object-contain">
                        </template>
                        <template x-if="!isImageFile(item.path)">
                            <span class="text-3xl">📄</span>
                        </template>
                    </div>

                    <div class="min-w-0">
                        <span class="font-bold text-xs text-slate-850 block truncate" x-text="item.name"></span>
                        <span class="text-[9px] text-slate-400 block truncate" x-text="item.original_name"></span>
                    </div>

                    <div class="flex justify-between items-center pt-2 border-t border-slate-100">
                        <a :href="'/storage/' + item.path" download class="p-1.5 hover:bg-slate-50 rounded transition-colors flex items-center justify-center" title="Baixar Arquivo">
                            <svg class="w-5 h-5 text-blue-600 hover:text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        </a>
                        
                        <div class="flex items-center gap-2">
                            <!-- Normal Excluir Button -->
                            <button x-show="deletingStationeryIndex !== idx" type="button" @click="deletingStationeryIndex = idx" class="p-1.5 hover:bg-rose-50 rounded transition-colors flex items-center justify-center cursor-pointer" title="Excluir Mockup">
                                <svg class="w-5 h-5 text-rose-600 hover:text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                            
                            <!-- Confirmation Inline -->
                            <template x-if="deletingStationeryIndex === idx">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-[9px] font-black text-rose-650 uppercase">Excluir?</span>
                                    <button type="button" @click="removeSavedStationery(idx)" class="px-1.5 py-0.5 bg-rose-600 hover:bg-rose-700 text-white text-[9px] font-bold uppercase rounded cursor-pointer">Sim</button>
                                    <button type="button" @click="deletingStationeryIndex = null" class="px-1.5 py-0.5 bg-slate-100 hover:bg-slate-200 text-slate-600 text-[9px] font-bold uppercase rounded cursor-pointer">Não</button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- VIEW 4: REDES SOCIAIS -->
    <div x-show="activeTab === 'social'" class="space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-outfit font-black text-lg text-slate-900">Gerenciar Perfis Sociais</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Suba capas e avatares para otimização de perfis nas redes (Instagram, Facebook, LinkedIn, TikTok, WhatsApp, YouTube)</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm">
            <template x-for="net in ['instagram', 'facebook', 'linkedin', 'youtube', 'tiktok', 'whatsapp']">
                <div class="p-5 bg-slate-50 border border-slate-200/60 rounded-[5px] space-y-4 relative flex flex-col justify-between">
                    
                    <div class="flex items-center justify-between border-b border-slate-200/60 pb-2.5">
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-black text-slate-800 uppercase tracking-wider" x-text="net"></span>
                        </div>
                        
                        <!-- Visual indicator tag for size -->
                        <span class="text-[9px] font-extrabold text-slate-400 uppercase tracking-wider"
                              x-text="net === 'facebook' ? 'Banner: 820x312px | Avatar: 180x180px' : 
                                     (net === 'linkedin' ? 'Banner: 1584x396px | Avatar: 400x400px' : 
                                     (net === 'youtube' ? 'Banner: 2048x1152px | Avatar: 800x800px' : 
                                     (net === 'instagram' ? 'Avatar: 320x320px' : 
                                     (net === 'tiktok' ? 'Avatar: 200x200px' : 'Avatar: 640x640px'))))">
                        </span>
                    </div>

                    <!-- Facebook, LinkedIn, YouTube Mockups (With Banner + Profile overlap) -->
                    <template x-if="['facebook', 'linkedin', 'youtube'].includes(net)">
                        <div class="space-y-3">
                            <!-- Clickable Banner Area -->
                            <div @click="document.getElementById('coverInput_' + net).click()"
                                 @dragover.prevent
                                 @drop.prevent="uploadSocialFile($event, net, 'cover')"
                                 class="w-full h-36 bg-slate-250 hover:bg-slate-300/80 rounded-[5px] relative overflow-hidden flex items-center justify-center border border-slate-300 cursor-pointer group transition-all"
                                 title="Clique ou arraste uma imagem aqui para alterar o banner de capa">
                                
                                <!-- Banner cover image -->
                                <div class="absolute inset-0 bg-cover bg-center transition-opacity" 
                                     :style="socialPreviews[net].cover ? 'background-image: url(' + socialPreviews[net].cover + ')' : ''"
                                     :class="socialPreviews[net].cover ? 'opacity-100 group-hover:opacity-85' : 'opacity-0'"></div>
                                
                                <!-- Hover indicator -->
                                <div class="relative z-10 flex flex-col items-center justify-center text-slate-500 group-hover:text-slate-800 transition-colors">
                                    <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="text-[9px] font-bold uppercase tracking-wider">Alterar Banner</span>
                                </div>
                            </div>
                            
                            <!-- Clickable Overlapping Avatar Circle -->
                            <div class="flex items-center gap-4 pl-3">
                                <div @click="document.getElementById('avatarInput_' + net).click()"
                                     @dragover.prevent
                                     @drop.prevent="uploadSocialFile($event, net, 'avatar')"
                                     class="w-16 h-16 rounded-full border-4 border-slate-50 bg-slate-300 relative overflow-hidden shrink-0 shadow cursor-pointer group transition-all hover:scale-105"
                                     title="Clique ou arraste uma foto aqui para alterar a foto de perfil">
                                    
                                    <!-- Avatar Image -->
                                    <div class="absolute inset-0 bg-cover bg-center transition-opacity" 
                                         :style="socialPreviews[net].avatar ? 'background-image: url(' + socialPreviews[net].avatar + ')' : ''"
                                         :class="socialPreviews[net].avatar ? 'opacity-100 group-hover:opacity-85' : 'opacity-0'"></div>
                                    
                                    <!-- Hover Camera Overlay -->
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity text-white">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    </div>
                                </div>
                                
                                <div>
                                    <h5 class="text-xs font-bold text-slate-700 capitalize" x-text="net"></h5>
                                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-wider">Clique na capa ou na foto para enviar</p>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Instagram, TikTok, WhatsApp Mockups (With Avatar Only) -->
                    <template x-if="['instagram', 'tiktok', 'whatsapp'].includes(net)">
                        <div class="flex items-center gap-5 p-4 bg-white border border-slate-150 rounded-[5px] justify-center sm:justify-start">
                            <!-- Clickable Large Avatar Circle -->
                            <div @click="document.getElementById('avatarInput_' + net).click()"
                                 @dragover.prevent
                                 @drop.prevent="uploadSocialFile($event, net, 'avatar')"
                                 class="w-20 h-20 rounded-full border-2 border-slate-200 bg-slate-100 relative overflow-hidden shrink-0 shadow-sm cursor-pointer group transition-all hover:scale-105"
                                 title="Clique ou arraste uma foto aqui para alterar a foto de perfil">
                                
                                <!-- Avatar Image -->
                                <div class="absolute inset-0 bg-cover bg-center transition-opacity" 
                                     :style="socialPreviews[net].avatar ? 'background-image: url(' + socialPreviews[net].avatar + ')' : ''"
                                     :class="socialPreviews[net].avatar ? 'opacity-100 group-hover:opacity-85' : 'opacity-0'"></div>
                                
                                <!-- Hover Camera Overlay -->
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity text-white">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                            </div>
                            
                            <div class="space-y-1 text-center sm:text-left">
                                <h5 class="text-xs font-black text-slate-800 capitalize" x-text="net"></h5>
                                <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider block">Foto de perfil recomendada</span>
                                <p class="text-[9px] text-slate-450 block font-semibold">Clique no círculo para fazer o upload.</p>
                            </div>
                        </div>
                    </template>

                    <!-- Hidden Input Files -->
                    <input type="file" @change="uploadSocialFile($event, net, 'avatar')" class="hidden" :id="'avatarInput_' + net" accept="image/*">
                    <input type="file" @change="uploadSocialFile($event, net, 'cover')" class="hidden" :id="'coverInput_' + net" accept="image/*">

                </div>
            </template>
        </div>
    </div>

    <!-- VIEW 5: IMAGENS DA LOGO -->
    <div x-show="activeTab === 'logos'" class="space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-outfit font-black text-lg text-slate-900">Gerenciar Imagens da Logo</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Suba as variações horizontal, vertical e ícone e escreva seus conceitos de aplicação</p>
        </div>

        <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-8">
            <div class="space-y-8">
                <!-- Variação 1: Principal / Horizontal -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start p-4 bg-slate-50 rounded-[5px] border border-slate-150">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-750 uppercase block">1. Logo Principal / Horizontal</label>
                        <div @click="document.getElementById('logoInput_primary').click()"
                             @dragover.prevent
                             @drop.prevent="uploadLogoFile($event, 'primary')"
                             class="w-full h-32 bg-white rounded border border-slate-200 flex items-center justify-center p-2 cursor-pointer hover:bg-slate-100/50 transition-colors group relative overflow-hidden"
                             title="Clique ou arraste uma imagem aqui (SVG, PNG, JPG)">
                            <img :src="logoPreviews.primary || 'https://via.placeholder.com/150'" class="max-w-full max-h-full object-contain">
                            
                            <!-- Hover camera overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-8 h-8 p-1.5 bg-white rounded-full text-slate-700 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <input type="file" @change="uploadLogoFile($event, 'primary')" id="logoInput_primary" class="hidden" accept=".svg,.png,.jpg,.jpeg,.webp">
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Texto Explicativo da Variação Horizontal</label>
                        <!-- Formatting Bar -->
                        <div class="flex gap-2 p-1.5 border border-b-0 border-slate-200 bg-slate-100 rounded-t">
                            <button type="button" @click="formatText('bold', 'horizontal-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs font-bold cursor-pointer">B</button>
                            <button type="button" @click="formatText('italic', 'horizontal-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs italic cursor-pointer">I</button>
                            <button type="button" @click="formatText('underline', 'horizontal-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs underline cursor-pointer">U</button>
                        </div>
                        <div id="horizontal-editor" contenteditable="true" class="w-full h-24 p-3 bg-white border border-slate-200 rounded-b text-xs focus:outline-none overflow-y-auto" @input="logo_horizontal_desc = $el.innerHTML" @blur="saveLogoDescription('primary')" x-html="logo_horizontal_desc"></div>
                    </div>
                </div>

                <!-- Variação 2: Logo Secundário / Vertical -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start p-4 bg-slate-50 rounded-[5px] border border-slate-150">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-750 uppercase block">2. Logo Secundário / Vertical</label>
                        <div @click="document.getElementById('logoInput_secondary').click()"
                             @dragover.prevent
                             @drop.prevent="uploadLogoFile($event, 'secondary')"
                             class="w-full h-32 bg-white rounded border border-slate-200 flex items-center justify-center p-2 cursor-pointer hover:bg-slate-100/50 transition-colors group relative overflow-hidden"
                             title="Clique ou arraste uma imagem aqui (SVG, PNG, JPG)">
                            <img :src="logoPreviews.secondary || 'https://via.placeholder.com/150'" class="max-w-full max-h-full object-contain">
                            
                            <!-- Hover camera overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-8 h-8 p-1.5 bg-white rounded-full text-slate-700 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <input type="file" @change="uploadLogoFile($event, 'secondary')" id="logoInput_secondary" class="hidden" accept=".svg,.png,.jpg,.jpeg,.webp">
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Texto Explicativo da Variação Vertical</label>
                        <!-- Formatting Bar -->
                        <div class="flex gap-2 p-1.5 border border-b-0 border-slate-200 bg-slate-100 rounded-t">
                            <button type="button" @click="formatText('bold', 'vertical-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs font-bold cursor-pointer">B</button>
                            <button type="button" @click="formatText('italic', 'vertical-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs italic cursor-pointer">I</button>
                            <button type="button" @click="formatText('underline', 'vertical-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs underline cursor-pointer">U</button>
                        </div>
                        <div id="vertical-editor" contenteditable="true" class="w-full h-24 p-3 bg-white border border-slate-200 rounded-b text-xs focus:outline-none overflow-y-auto" @input="logo_vertical_desc = $el.innerHTML" @blur="saveLogoDescription('secondary')" x-html="logo_vertical_desc"></div>
                    </div>
                </div>

                <!-- Variação 3: Símbolo / Ícone -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 items-start p-4 bg-slate-50 rounded-[5px] border border-slate-150">
                    <div class="space-y-2">
                        <label class="text-xs font-black text-slate-750 uppercase block">3. Símbolo / Ícone da Marca</label>
                        <div @click="document.getElementById('logoInput_symbol').click()"
                             @dragover.prevent
                             @drop.prevent="uploadLogoFile($event, 'symbol')"
                             class="w-full h-32 bg-white rounded border border-slate-200 flex items-center justify-center p-2 cursor-pointer hover:bg-slate-100/50 transition-colors group relative overflow-hidden"
                             title="Clique ou arraste uma imagem aqui (SVG, PNG, JPG)">
                            <img :src="logoPreviews.symbol || 'https://via.placeholder.com/150'" class="max-w-full max-h-full object-contain">
                            
                            <!-- Hover camera overlay -->
                            <div class="absolute inset-0 flex items-center justify-center bg-black/10 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-8 h-8 p-1.5 bg-white rounded-full text-slate-700 shadow-sm" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                        </div>
                        <input type="file" @change="uploadLogoFile($event, 'symbol')" id="logoInput_symbol" class="hidden" accept=".svg,.png,.jpg,.jpeg,.webp">
                    </div>
                    <div class="md:col-span-2 space-y-1">
                        <label class="text-[10px] font-bold text-slate-450 uppercase block">Texto Explicativo do Símbolo</label>
                        <!-- Formatting Bar -->
                        <div class="flex gap-2 p-1.5 border border-b-0 border-slate-200 bg-slate-100 rounded-t">
                            <button type="button" @click="formatText('bold', 'symbol-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs font-bold cursor-pointer">B</button>
                            <button type="button" @click="formatText('italic', 'symbol-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs italic cursor-pointer">I</button>
                            <button type="button" @click="formatText('underline', 'symbol-editor')" class="px-2 py-0.5 border border-slate-300 bg-white rounded text-xs underline cursor-pointer">U</button>
                        </div>
                        <div id="symbol-editor" contenteditable="true" class="w-full h-24 p-3 bg-white border border-slate-200 rounded-b text-xs focus:outline-none overflow-y-auto" @input="logo_symbol_desc = $el.innerHTML" @blur="saveLogoDescription('symbol')" x-html="logo_symbol_desc"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- VIEW 6: ASSINATURA DE E-MAIL -->
    <div x-show="activeTab === 'assinatura'" class="space-y-6" x-cloak>
        <div class="border-b border-slate-100 pb-3">
            <h3 class="font-outfit font-black text-lg text-slate-900">Gerador de Assinatura de E-mail</h3>
            <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Configure a assinatura e copie em formato HTML ou Rich Text para colar em seus e-mails</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <div class="lg:col-span-5 bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm space-y-4">
                <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-700">Opções da Assinatura</h4>
                
                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Nome</label>
                    <input type="text" x-model="sig.name" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Cargo / Registro</label>
                    <input type="text" x-model="sig.role" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">WhatsApp / Celular</label>
                    <input type="text" x-model="sig.phone1" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Telefone Secundário</label>
                    <input type="text" x-model="sig.phone2" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Endereço</label>
                    <input type="text" x-model="sig.address" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none">
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Variação do Logo</label>
                    <select x-model="sig.logoUrl" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none cursor-pointer">
                        <template x-if="logo_primary">
                            <option :value="logo_primary">Logo Principal</option>
                        </template>
                        <template x-if="logo_secondary">
                            <option :value="logo_secondary">Logo Alternativo</option>
                        </template>
                        <template x-if="logo_symbol">
                            <option :value="logo_symbol">Símbolo</option>
                        </template>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-[10px] font-bold text-slate-450 uppercase block">Cor da Linha Divider</label>
                    <select x-model="sig.lineColor" class="w-full px-3 py-2 text-xs border border-slate-200 rounded focus:outline-none cursor-pointer">
                        <template x-for="color in colors">
                            <option :value="color.hex" x-text="color.name + ' (' + color.hex + ')'"></option>
                        </template>
                        <option value="#0284c7">Padrão (Azul)</option>
                    </select>
                </div>
            </div>

            <!-- Preview panel -->
            <div class="lg:col-span-7 space-y-6">
                <div class="bg-white border border-slate-200 rounded-[5px] p-6 shadow-sm">
                    <h4 class="font-extrabold text-xs uppercase tracking-wider text-slate-400 mb-3 block border-b pb-2">Visualização</h4>
                    
                    <div class="border rounded p-4 overflow-x-auto bg-white" id="emailSignaturePreview">
                        <table cellpadding="0" cellspacing="0" style="font-family: Arial, sans-serif; border-collapse: collapse;">
                            <tr>
                                <td style="vertical-align: middle; padding-right: 20px;" x-show="sig.logoUrl">
                                    <img :src="sig.logoUrl" alt="Logo" style="max-height: 75px; max-width: 90px; display: block; object-contain: contain;">
                                </td>
                                <td :style="'width: 2px; vertical-align: stretch; background-color: ' + sig.lineColor + '; padding: 0;'" style="width: 2px; vertical-align: stretch; background-color: #0070c0; padding: 0;"></td>
                                <td style="vertical-align: middle; padding-left: 20px; text-align: left;">
                                    <div style="font-size: 15px; font-weight: bold; color: #0284c7; margin-bottom: 2px; line-height: 1.2;" :style="'color: ' + sig.lineColor" x-text="sig.name || 'Nome Completo'"></div>
                                    <div style="font-size: 12px; font-weight: 600; color: #334155; margin-bottom: 6px; line-height: 1.2;" x-text="sig.role || 'Cargo'"></div>
                                    <table cellpadding="0" cellspacing="0" style="border-collapse: collapse; font-size: 11px; color: #475569; line-height: 1.4;">
                                        <tr x-show="sig.phone1">
                                            <td style="padding-right: 4px;">🟢</td>
                                            <td x-text="sig.phone1"></td>
                                        </tr>
                                        <tr x-show="sig.phone2">
                                            <td style="padding-right: 4px;">📞</td>
                                            <td x-text="sig.phone2"></td>
                                        </tr>
                                        <tr x-show="sig.address">
                                            <td style="padding-right: 4px;">📍</td>
                                            <td x-text="sig.address"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>

                <div class="flex gap-4">
                    <button type="button" @click="copyRichTextSignature()" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors flex items-center justify-center gap-1.5 shadow-sm">
                        📋 Copiar Assinatura
                    </button>
                    <button type="button" @click="copyRawHtmlSignature()" class="px-5 py-3 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors shadow-sm">
                        Copiar Código HTML
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
    function brandGuidelineEdit() {
        return {
            activeTab: 'hub',
            id: {{ $guideline->id }},
            is_active: {{ $guideline->is_active ? 'true' : 'false' }},
            brand_name: @json($guideline->brand_name),
            client_id: @json($guideline->client_id),

            // Toast State
            toast: {
                show: false,
                message: '',
                type: 'success'
            },
            showToast(msg, type = 'success') {
                this.toast.message = msg;
                this.toast.type = type;
                this.toast.show = true;
                setTimeout(() => { this.toast.show = false; }, 3000);
            },

            // Previews for file structures
            logoPreviews: {
                primary: @json($guideline->logo_primary ? asset('storage/' . $guideline->logo_primary) : ''),
                secondary: @json($guideline->logo_secondary ? asset('storage/' . $guideline->logo_secondary) : ''),
                symbol: @json($guideline->logo_symbol ? asset('storage/' . $guideline->logo_symbol) : '')
            },
            logo_primary: @json($guideline->logo_primary ? asset('storage/' . $guideline->logo_primary) : ''),
            logo_secondary: @json($guideline->logo_secondary ? asset('storage/' . $guideline->logo_secondary) : ''),
            logo_symbol: @json($guideline->logo_symbol ? asset('storage/' . $guideline->logo_symbol) : ''),

            logo_description: @json($guideline->logo_description ?? ''),
            logo_horizontal_desc: @json($guideline->logo_horizontal_desc ?? ''),
            logo_vertical_desc: @json($guideline->logo_vertical_desc ?? ''),
            logo_symbol_desc: @json($guideline->logo_symbol_desc ?? ''),

            // Colors
            colors: @json($guideline->color_palette ?? []),

            // Fonts
            fonts: @json($guideline->typography ?? []),
            deletingFontIndex: null,
            newFont: {
                font_family: '',
                usage: 'Geral',
                specimen_text: 'Abc123',
                file: null
            },

            // Stationery mockups
            stationery: @json($guideline->stationery ?? []),
            deletingStationeryIndex: null,
            uploadProgress: 0,
            newStationeryFiles: [],
            newStationeryNames: [],

            // Social Previews and Models
            socialPreviews: {
                instagram: { avatar: @json(!empty($guideline->social_media['instagram']['avatar']) ? asset('storage/' . $guideline->social_media['instagram']['avatar']) : ''), cover: @json(!empty($guideline->social_media['instagram']['cover']) ? asset('storage/' . $guideline->social_media['instagram']['cover']) : '') },
                facebook: { avatar: @json(!empty($guideline->social_media['facebook']['avatar']) ? asset('storage/' . $guideline->social_media['facebook']['avatar']) : ''), cover: @json(!empty($guideline->social_media['facebook']['cover']) ? asset('storage/' . $guideline->social_media['facebook']['cover']) : '') },
                linkedin: { avatar: @json(!empty($guideline->social_media['linkedin']['avatar']) ? asset('storage/' . $guideline->social_media['linkedin']['avatar']) : ''), cover: @json(!empty($guideline->social_media['linkedin']['cover']) ? asset('storage/' . $guideline->social_media['linkedin']['cover']) : '') },
                youtube: { avatar: @json(!empty($guideline->social_media['youtube']['avatar']) ? asset('storage/' . $guideline->social_media['youtube']['avatar']) : ''), cover: @json(!empty($guideline->social_media['youtube']['cover']) ? asset('storage/' . $guideline->social_media['youtube']['cover']) : '') },
                tiktok: { avatar: @json(!empty($guideline->social_media['tiktok']['avatar']) ? asset('storage/' . $guideline->social_media['tiktok']['avatar']) : ''), cover: @json(!empty($guideline->social_media['tiktok']['cover']) ? asset('storage/' . $guideline->social_media['tiktok']['cover']) : '') },
                whatsapp: { avatar: @json(!empty($guideline->social_media['whatsapp']['avatar']) ? asset('storage/' . $guideline->social_media['whatsapp']['avatar']) : ''), cover: @json(!empty($guideline->social_media['whatsapp']['cover']) ? asset('storage/' . $guideline->social_media['whatsapp']['cover']) : '') }
            },

            // Signature generator model
            sig: {
                name: 'Silvana Rodrigues Mota',
                role: 'Psicóloga - CRP:20/06594',
                phone1: '(95) 99125-9059',
                phone2: '(95) 3623-0348',
                address: 'Rua da Jaqueira, 78, 2º andar — Caçari - Boa Vista - RR',
                logoUrl: '',
                lineColor: '#0284c7'
            },

            // Packages
            final_package: @json($guideline->final_package),
            packageDragging: false,
            fontDragging: false,
            stationeryDragging: false,
            selectedPackageFile: null,
            selectedPackageName: '',

            init() {
                // Initialize active color if palette has one
                if (this.colors.length > 0) {
                    this.sig.lineColor = this.colors[0].hex;
                }
                if (this.logo_primary) {
                    this.sig.logoUrl = this.logo_primary;
                }

                // Inject font face dynamically for previews
                this.updateDynamicFontFaces();
            },

            updateDynamicFontFaces() {
                let styleContent = '';
                this.fonts.forEach(f => {
                    if (f.font_file) {
                        styleContent += `
                            @font-face {
                                font-family: '${f.font_family}';
                                src: url('/storage/${f.font_file}');
                            }
                        `;
                    }
                });
                document.getElementById('dynamic-fonts-preview').innerHTML = styleContent;
            },

            // Color methods
            addColor() {
                this.colors.push({ hex: '#3b82f6', name: '', rgb: '59 130 246', cmyk: '76 47 0 4', note: '' });
            },

            removeColor(idx) {
                this.colors.splice(idx, 1);
            },

            convertHex(color) {
                let hex = color.hex.replace('#', '');
                if (hex.length === 3) {
                    hex = hex[0]+hex[0]+hex[1]+hex[1]+hex[2]+hex[2];
                }
                if (hex.length === 6) {
                    let r = parseInt(hex.substring(0,2), 16);
                    let g = parseInt(hex.substring(2,4), 16);
                    let b = parseInt(hex.substring(4,6), 16);
                    color.rgb = `${r} ${g} ${b}`;

                    // CMYK conversion
                    let c = 1 - (r / 255);
                    let m = 1 - (g / 255);
                    let y = 1 - (b / 255);
                    let k = Math.min(c, Math.min(m, y));

                    if (k === 1) {
                        c = 0; m = 0; y = 0;
                    } else {
                        c = Math.round(((c - k) / (1 - k)) * 100);
                        m = Math.round(((m - k) / (1 - k)) * 100);
                        y = Math.round(((y - k) / (1 - k)) * 100);
                    }
                    k = Math.round(k * 100);
                    color.cmyk = `${c} ${m} ${y} ${k}`;
                }
            },

            saveColors() {
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'colors');
                this.colors.forEach((c, idx) => {
                    fd.append(`colors[${idx}][name]`, c.name);
                    fd.append(`colors[${idx}][hex]`, c.hex);
                    fd.append(`colors[${idx}][rgb]`, c.rgb);
                    fd.append(`colors[${idx}][cmyk]`, c.cmyk);
                    fd.append(`colors[${idx}][note]`, c.note);
                });

                this.submitAjax(fd, 'Paleta de cores atualizada com sucesso!');
            },

            // Fonts methods
            autoFillFontFamily(file) {
                if (!file) return;
                let fileName = file.name;
                let nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
                // Clean up hyphens and underscores, replacing with spaces
                let cleanName = nameWithoutExt.replace(/[-_]/g, ' ');
                // Capitalize each word nicely
                cleanName = cleanName.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
                this.newFont.font_family = cleanName;
            },

            handleFontDrop(e) {
                this.fontDragging = false;
                if (e.dataTransfer.files.length > 0) {
                    this.uploadMultipleFonts(e.dataTransfer.files);
                }
            },

            handleFontFileChange(e) {
                if (e.target.files.length > 0) {
                    this.uploadMultipleFonts(e.target.files);
                }
            },

            uploadMultipleFonts(files) {
                var self = this;
                Array.from(files).forEach(function(file) {
                    let cleanName = self.extractFontName(file.name);
                    let fd = new FormData();
                    fd.append('_method', 'PUT');
                    fd.append('stage', 'fonts');
                    fd.append('new_font[font_family]', cleanName);
                    fd.append('new_font[usage]', 'Geral');
                    fd.append('new_font[specimen_text]', 'Abc123');
                    fd.append('new_font[font_file]', file);

                    fetch(`/freelas/utilidades/identidades-visuais/${self.id}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: fd
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            self.fonts = data.guideline.typography || [];
                            self.updateDynamicFontFaces();
                            self.showToast('Fonte ' + cleanName + ' salva com sucesso!');
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        self.showToast('Erro ao salvar fonte ' + cleanName + '.', 'error');
                    });
                });
            },

            extractFontName(fileName) {
                let nameWithoutExt = fileName.substring(0, fileName.lastIndexOf('.')) || fileName;
                let cleanName = nameWithoutExt.replace(/[-_]/g, ' ');
                return cleanName.replace(/\b\w/g, function(c) { return c.toUpperCase(); });
            },

            saveNewFont() {
                if (!this.newFont.font_family) {
                    this.showToast('Insira o nome da família tipográfica.', 'error');
                    return;
                }
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'fonts');
                fd.append('new_font[font_family]', this.newFont.font_family);
                fd.append('new_font[usage]', this.newFont.usage);
                fd.append('new_font[specimen_text]', this.newFont.specimen_text);
                if (this.newFont.file) {
                    fd.append('new_font[font_file]', this.newFont.file);
                }

                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.fonts = data.guideline.typography || [];
                        this.newFont = { font_family: '', usage: 'Geral', specimen_text: 'Abc123', file: null };
                        this.updateDynamicFontFaces();
                        this.showToast('Fonte salva com sucesso!');
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.showToast('Erro ao salvar nova fonte.', 'error');
                });
            },

            removeFont(idx) {
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'fonts');
                fd.append('remove_font_index', idx);

                var self = this;
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.fonts = data.guideline.typography || [];
                        self.updateDynamicFontFaces();
                        self.deletingFontIndex = null;
                        self.showToast('Fonte removida.');
                    }
                });
            },

            // Stationery multi-uploads
            handleStationeryDrop(e) {
                this.stationeryDragging = false;
                for (let file of e.dataTransfer.files) {
                    file.previewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : '';
                    this.newStationeryFiles.push(file);
                    this.newStationeryNames.push(file.name);
                }
            },

            handleStationeryFileChange(e) {
                for (let file of e.target.files) {
                    file.previewUrl = file.type.startsWith('image/') ? URL.createObjectURL(file) : '';
                    this.newStationeryFiles.push(file);
                    this.newStationeryNames.push(file.name);
                }
            },

            removePendingStationeryFile(idx) {
                this.newStationeryFiles.splice(idx, 1);
                this.newStationeryNames.splice(idx, 1);
            },

            saveStationery() {
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'stationery');
                this.newStationeryFiles.forEach((file, idx) => {
                    fd.append('stationery_files[]', file);
                    fd.append('stationery_names[]', this.newStationeryNames[idx]);
                });

                var self = this;
                let xhr = new XMLHttpRequest();
                xhr.open('POST', `/freelas/utilidades/identidades-visuais/${this.id}`);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');

                xhr.upload.onprogress = function(event) {
                    if (event.lengthComputable) {
                        let percent = Math.round((event.loaded / event.total) * 100);
                        self.uploadProgress = percent;
                    }
                };

                xhr.onload = function() {
                    self.uploadProgress = 0;
                    if (xhr.status >= 200 && xhr.status < 300) {
                        let data = JSON.parse(xhr.responseText);
                        if (data.success) {
                            self.stationery = data.guideline.stationery || [];
                            self.newStationeryFiles = [];
                            self.newStationeryNames = [];
                            self.showToast('Arquivos salvos com sucesso!');
                        } else {
                            self.showToast('Erro ao salvar os arquivos.', 'error');
                        }
                    } else {
                        self.showToast('Erro ao enviar os arquivos.', 'error');
                    }
                };

                xhr.onerror = function() {
                    self.uploadProgress = 0;
                    self.showToast('Erro na conexão.', 'error');
                };

                xhr.send(fd);
            },

            removeSavedStationery(idx) {
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'stationery');
                fd.append('remove_stationery_indexes[]', idx);

                var self = this;
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.stationery = data.guideline.stationery || [];
                        self.deletingStationeryIndex = null;
                        self.showToast('Mockup removido com sucesso!');
                    }
                })
                .catch(err => {
                    console.error(err);
                    self.showToast('Erro ao excluir mockup.', 'error');
                });
            },

            isImageFile(path) {
                if (!path) return false;
                let ext = path.split('.').pop().toLowerCase();
                return ['png', 'jpg', 'jpeg', 'svg', 'webp', 'gif'].includes(ext);
            },

            // Social media
            uploadSocialFile(e, net, type) {
                let file = null;
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    file = e.dataTransfer.files[0];
                } else if (e.target && e.target.files.length > 0) {
                    file = e.target.files[0];
                }
                if (!file) return;
                
                // Set temporary local preview
                this.socialPreviews[net][type] = URL.createObjectURL(file);
                
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'social');
                fd.append(`social_media_${net}_${type}`, file);

                let label = type === 'avatar' ? 'foto de perfil' : 'banner de capa';
                this.showToast(`Enviando ${label} do ${net}...`);

                var self = this;
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.showToast(`${label.charAt(0).toUpperCase() + label.slice(1)} do ${net} atualizada com sucesso!`);
                    } else {
                        self.showToast('Erro ao atualizar imagem.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    self.showToast('Erro na conexão.', 'error');
                });
            },

            // Logos Management
            uploadLogoFile(e, type) {
                let file = null;
                if (e.dataTransfer && e.dataTransfer.files.length > 0) {
                    file = e.dataTransfer.files[0];
                } else if (e.target && e.target.files.length > 0) {
                    file = e.target.files[0];
                }
                if (!file) return;

                this.logoPreviews[type] = URL.createObjectURL(file);

                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'logos');

                let fieldName = type === 'primary' ? 'logo_primary' : (type === 'secondary' ? 'logo_secondary' : 'logo_symbol');
                fd.append(fieldName, file);

                // Append all description texts to prevent controller overwriting to null
                fd.append('logo_horizontal_desc', this.logo_horizontal_desc || '');
                fd.append('logo_vertical_desc', this.logo_vertical_desc || '');
                fd.append('logo_symbol_desc', this.logo_symbol_desc || '');

                this.showToast('Enviando logotipo...');

                var self = this;
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.logoPreviews[type] = data.guideline[fieldName] ? '/storage/' + data.guideline[fieldName] : '';
                        if (type === 'primary') {
                            self.logo_primary = self.logoPreviews[type];
                            self.sig.logoUrl = self.logo_primary;
                        }
                        self.showToast('Logotipo atualizado com sucesso!');
                    } else {
                        self.showToast('Erro ao enviar logotipo.', 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    self.showToast('Erro na conexão.', 'error');
                });
            },

            saveLogoDescription(type) {
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'logos');
                
                fd.append('logo_horizontal_desc', this.logo_horizontal_desc || '');
                fd.append('logo_vertical_desc', this.logo_vertical_desc || '');
                fd.append('logo_symbol_desc', this.logo_symbol_desc || '');

                var self = this;
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        self.showToast('Descrição da logo salva!');
                    }
                })
                .catch(err => {
                    console.error(err);
                });
            },

            formatText(cmd, editorId) {
                document.execCommand(cmd, false, null);
                let editor = document.getElementById(editorId);
                if (editorId === 'horizontal-editor') this.logo_horizontal_desc = editor.innerHTML;
                if (editorId === 'vertical-editor') this.logo_vertical_desc = editor.innerHTML;
                if (editorId === 'symbol-editor') this.logo_symbol_desc = editor.innerHTML;
            },

            // Package multi-uploads
            handlePackageDrop(e) {
                this.packageDragging = false;
                if (e.dataTransfer.files.length > 0) {
                    this.selectedPackageFile = e.dataTransfer.files[0];
                    this.selectedPackageName = this.selectedPackageFile.name;
                }
            },

            handlePackageFileChange(e) {
                if (e.target.files.length > 0) {
                    this.selectedPackageFile = e.target.files[0];
                    this.selectedPackageName = this.selectedPackageFile.name;
                }
            },

            savePackage() {
                if (!this.selectedPackageFile) {
                    this.showToast('Nenhum arquivo ZIP selecionado.', 'error');
                    return;
                }
                let fd = new FormData();
                fd.append('_method', 'PUT');
                fd.append('stage', 'package');
                fd.append('final_package_file', this.selectedPackageFile);

                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: fd
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.final_package = data.guideline.final_package;
                        this.selectedPackageFile = null;
                        this.selectedPackageName = '';
                        this.showToast('Pacote final salvo com sucesso!');
                    }
                });
            },

            // Toggle active manual
            toggleActiveState() {
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}/toggle-active`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.is_active = data.is_active;
                    }
                });
            },

            // Signature Methods
            copyRichTextSignature() {
                let container = document.getElementById('emailSignaturePreview');
                let range = document.createRange();
                range.selectNode(container);
                window.getSelection().removeAllRanges();
                window.getSelection().addRange(range);
                try {
                    document.execCommand('copy');
                    this.showToast('Assinatura copiada! Cole (Ctrl+V) no seu e-mail.');
                } catch (err) {
                    this.showToast('Selecione e copie manualmente.', 'error');
                }
                window.getSelection().removeAllRanges();
            },

            copyRawHtmlSignature() {
                let container = document.getElementById('emailSignaturePreview');
                navigator.clipboard.writeText(container.innerHTML).then(() => {
                    this.showToast('Código HTML da assinatura copiado!');
                });
            },

            // Shared AJAX Helper
            submitAjax(formData, successMsg) {
                fetch(`/freelas/utilidades/identidades-visuais/${this.id}`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.showToast(successMsg);
                    }
                })
                .catch(err => {
                    console.error(err);
                    this.showToast('Erro ao salvar os dados.', 'error');
                });
            },

            copyShareLink(token, event) {
                let publicUrl = `${window.location.origin}/brand/${token}`;
                navigator.clipboard.writeText(publicUrl).then(() => {
                    let btn = event.currentTarget;
                    let originalHTML = btn.innerHTML;
                    btn.innerHTML = '✓ Copiado!';
                    setTimeout(() => btn.innerHTML = originalHTML, 2000);
                });
            }
        }
    }
</script>
@endsection
