@extends('layouts.app')

@section('title', 'Instagram & Mídia Social - Gestor de Freelas')
@section('page_title', 'Integração & Gestão do Instagram')

@section('content')
<!-- Flatpickr Datepicker & Modern Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>

<style>
/* Custom Styling for Flatpickr - Premium Dark Purple Theme */
.flatpickr-calendar {
    z-index: 9999999999 !important;
    border-radius: 1.25rem !important;
    border: 1px solid rgba(168, 85, 247, 0.3) !important;
    box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.5), 0 0 0 1px rgba(168, 85, 247, 0.2) !important;
    font-family: inherit !important;
    padding: 12px !important;
    background: #0f172a !important;
    color: #f8fafc !important;
}
.flatpickr-calendar.arrowTop:before, .flatpickr-calendar.arrowTop:after {
    border-bottom-color: #0f172a !important;
}
.flatpickr-calendar.arrowBottom:before, .flatpickr-calendar.arrowBottom:after {
    border-top-color: #0f172a !important;
}
.flatpickr-months {
    background: transparent !important;
    margin-bottom: 8px !important;
}
.flatpickr-months .flatpickr-month {
    color: #ffffff !important;
    fill: #ffffff !important;
    height: 38px !important;
}
.flatpickr-current-month {
    font-size: 0.9rem !important;
    font-weight: 800 !important;
    color: #ffffff !important;
}
.flatpickr-current-month .flatpickr-monthDropdown-months {
    background: #1e293b !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    border-radius: 0.5rem !important;
    padding: 2px 6px !important;
}
.flatpickr-current-month input.cur-year {
    font-weight: 800 !important;
    color: #c084fc !important;
}
.flatpickr-months .flatpickr-prev-month, .flatpickr-months .flatpickr-next-month {
    color: #c084fc !important;
    fill: #c084fc !important;
    padding: 8px !important;
}
.flatpickr-months .flatpickr-prev-month:hover svg, .flatpickr-months .flatpickr-next-month:hover svg {
    fill: #a855f7 !important;
}
span.flatpickr-weekday {
    color: #94a3b8 !important;
    font-weight: 800 !important;
    font-size: 0.75rem !important;
    text-transform: uppercase !important;
}
.flatpickr-day {
    color: #cbd5e1 !important;
    font-weight: 600 !important;
    border-radius: 0.75rem !important;
    transition: all 0.15s ease !important;
}
.flatpickr-day:hover {
    background: rgba(168, 85, 247, 0.25) !important;
    border-color: rgba(168, 85, 247, 0.4) !important;
    color: #ffffff !important;
}
.flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange, .flatpickr-day.selected:focus, .flatpickr-day.selected:hover {
    background: linear-gradient(135deg, #9333ea, #7c3aed) !important;
    border-color: #9333ea !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(147, 51, 234, 0.5) !important;
}
.flatpickr-day.today {
    border-color: #c084fc !important;
    color: #c084fc !important;
    font-weight: 800 !important;
}
.flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
    color: #334155 !important;
}
.flatpickr-time {
    border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
    margin-top: 8px !important;
    padding-top: 8px !important;
}
.flatpickr-time input {
    color: #ffffff !important;
    font-weight: 800 !important;
}
.flatpickr-time input:hover, .flatpickr-time input:focus {
    background: #1e293b !important;
    border-radius: 0.5rem !important;
}
.flatpickr-time .flatpickr-time-separator, .flatpickr-time .flatpickr-am-pm {
    color: #c084fc !important;
    font-weight: 800 !important;
}
.flatpickr-time .flatpickr-am-pm:hover {
    background: #1e293b !important;
}
.flatpickr-calendar.hasTime .flatpickr-time {
    height: 40px !important;
}
/* Ensure Flatpickr altInput takes full container styling */
.flatpickr-mobile {
    background: transparent !important;
    color: inherit !important;
}
</style>

@php
    $accUsername = optional($account)->username ?: 'seu_perfil';
    $accAvatar = optional($account)->profile_picture_url ?: ('https://ui-avatars.com/api/?name=' . urlencode($accUsername));
@endphp
<script>
(function() {
    function initInstagramModule() {
        if (typeof Alpine !== 'undefined') {
            Alpine.data('instagramModule', () => ({
                tab: (new URLSearchParams(window.location.search)).get('tab') || 'novo',
                mediaType: 'IMAGE',
                actionType: 'now',
                caption: '',
                hasLogoOverlay: false,
                hasArrowOverlay: false,
                imagePreview: null,
                carouselPreviews: [],
                selectedFiles: [],
                currentCarouselIndex: 0,
                selectedAccountId: '{{ optional($account)->id }}',
                hashtagCategory: 'design',
                hashtags: {
                    design: ['#designgrafico', '#identidadevisual', '#logodesign', '#designbr', '#designer', '#branding', '#designgraficobr', '#creative', '#graphicdesign', '#visualidentity'],
                    freelance: ['#freelancerbr', '#freelance', '#gestordefreelas', '#vidadefreela', '#trabalhoremoto', '#carreiradesign', '#designindependente', '#freelancerlife'],
                    socialmedia: ['#socialmedia', '#marketingdigital', '#midiasociais', '#gestordesocialmedia', '#conteudodigital', '#engajamento', '#instagramdicas', '#estrategiadedados'],
                    tecnologia: ['#tecnologia', '#desenvolvimentoweb', '#programador', '#uiux', '#frontend', '#webdesign', '#codebr', '#techdicas', '#softwarehouse'],
                    vendas: ['#vendas', '#negocios', '#empreendedorismo', '#marketingdeconteudo', '#copywriting', '#leads', '#vendasonline', '#sucesso'],
                    geral: ['#geral', '#postnovo', '#dicanova', '#paravoce', '#foryou', '#inspiração', '#criatividade', '#novidade']
                },
                isGeneratingHashtags: false,
                savedThemes: @json(optional($settings)->saved_themes ?: []),
                newThemeName: '',
                newThemeCategory: 'design',
                newThemeTagsText: '',
                showSaveThemeModal: false,
                lightboxOpen: false,
                lightboxPost: null,
                lightboxSlides: [],
                lightboxSlideIndex: 0,
                confirmDeleteModalOpen: false,
                postToDeleteId: null,
                postToDeleteCaption: '',
                deleteFormActionUrl: '',
                targetCardElement: null,
                isDeleting: false,

                // Drag & Drop + Progress State
                isDragging: false,
                isSubmittingPost: false,
                postProgress: 0,
                postProgressStep: 'Iniciando publicação...',

                // Estado do Calendário Dinâmico de Meses e Anos
                currentYear: (new Date()).getFullYear(),
                currentMonth: (new Date()).getMonth(),
                monthDropdownOpen: false,
                yearDropdownOpen: false,
                monthNames: ['Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'],
                allPosts: @json($posts),

                // Modal de Gerenciamento / Edição do Post Agendado
                managePostModalOpen: false,
                editingPost: null,
                editCaption: '',
                editScheduledAt: '',

                openManagePost(post) {
                    console.log("Opening manage post modal:", post);
                    this.editingPost = post;
                    this.editCaption = post.caption || '';
                    if (post.scheduled_at) {
                        try {
                            const dateStr = String(post.scheduled_at).substring(0, 16);
                            this.editScheduledAt = dateStr;
                        } catch (e) {
                            this.editScheduledAt = '';
                        }
                    } else {
                        this.editScheduledAt = '';
                    }
                    this.managePostModalOpen = true;

                    this.$nextTick(() => {
                        const modalInput = document.getElementById('modal_edit_scheduled_at');
                        if (modalInput && modalInput._flatpickr) {
                            if (this.editScheduledAt) {
                                modalInput._flatpickr.setDate(this.editScheduledAt);
                            } else {
                                modalInput._flatpickr.clear();
                            }
                        }
                    });
                },

                prevMonth() {
                    if (this.currentMonth === 0) {
                        this.currentMonth = 11;
                        this.currentYear--;
                    } else {
                        this.currentMonth--;
                    }
                },

                nextMonth() {
                    if (this.currentMonth === 11) {
                        this.currentMonth = 0;
                        this.currentYear++;
                    } else {
                        this.currentMonth++;
                    }
                },

                goToToday() {
                    const now = new Date();
                    this.currentYear = now.getFullYear();
                    this.currentMonth = now.getMonth();
                },

                get calendarGrid() {
                    const year = this.currentYear;
                    const month = this.currentMonth;
                    
                    const firstDayIndex = new Date(year, month, 1).getDay();
                    const totalDays = new Date(year, month + 1, 0).getDate();
                    const prevMonthTotalDays = new Date(year, month, 0).getDate();

                    const days = [];

                    // Preenchimento dos dias do mês anterior
                    for (let i = firstDayIndex - 1; i >= 0; i--) {
                        days.push({
                            day: prevMonthTotalDays - i,
                            isCurrentMonth: false,
                            dateStr: null,
                            posts: []
                        });
                    }

                    const todayObj = new Date();
                    const todayStr = `${todayObj.getFullYear()}-${String(todayObj.getMonth() + 1).padStart(2, '0')}-${String(todayObj.getDate()).padStart(2, '0')}`;

                    // Dias do mês atual
                    for (let day = 1; day <= totalDays; day++) {
                        const mmStr = String(month + 1).padStart(2, '0');
                        const ddStr = String(day).padStart(2, '0');
                        const dateStr = `${year}-${mmStr}-${ddStr}`;

                        const dayPosts = this.allPosts.filter(p => {
                            if (p.scheduled_at && p.scheduled_at.startsWith(dateStr)) return true;
                            if (p.published_at && p.published_at.startsWith(dateStr)) return true;
                            if (p.created_at && p.created_at.startsWith(dateStr) && !p.scheduled_at) return true;
                            return false;
                        });

                        days.push({
                            day: day,
                            isCurrentMonth: true,
                            isToday: dateStr === todayStr,
                            dateStr: dateStr,
                            posts: dayPosts
                        });
                    }

                    // Preenchimento dos dias do próximo mês para fechar o grid (linhas completas de 7)
                    const remaining = 7 - (days.length % 7);
                    if (remaining < 7) {
                        for (let i = 1; i <= remaining; i++) {
                            days.push({
                                day: i,
                                isCurrentMonth: false,
                                dateStr: null,
                                posts: []
                            });
                        }
                    }

                    return days;
                },

                startSubmitting(e) {
                    if (this.$refs.fileInput && this.selectedFiles.length > 0) {
                        try {
                            const dt = new DataTransfer();
                            this.selectedFiles.forEach(file => {
                                if (file instanceof File) {
                                    dt.items.add(file);
                                }
                            });
                            this.$refs.fileInput.files = dt.files;
                        } catch (err) {
                            console.error('DataTransfer submit error:', err);
                        }
                    }

                    this.isSubmittingPost = true;
                    this.postProgress = 10;
                    this.postProgressStep = 'Processando e otimizando imagem...';

                    let interval = setInterval(() => {
                        if (this.postProgress < 40) {
                            this.postProgress += 10;
                            this.postProgressStep = 'Aplicando sobreposição de marcas (Logo/Seta)...';
                        } else if (this.postProgress < 75) {
                            this.postProgress += 12;
                            this.postProgressStep = 'Enviando container para a API Graph do Instagram...';
                        } else if (this.postProgress < 95) {
                            this.postProgress += 5;
                            this.postProgressStep = 'Finalizando publicação oficial no perfil...';
                        } else {
                            clearInterval(interval);
                        }
                    }, 400);
                },

                handleFileDrop(e) {
                    this.isDragging = false;
                    const files = Array.from(e.dataTransfer.files).filter(f => f.type.startsWith('image/'));
                    if (files.length > 0) {
                        this.addFilesToList(files);
                    }
                },

                handleImageInputChange(e) {
                    const files = Array.from(e.target.files);
                    if (files.length > 0) {
                        this.addFilesToList(files);
                    }
                },

                handleImageChange(e) {
                    this.handleImageInputChange(e);
                },

                handleSingleFile(e) {
                    this.handleImageInputChange(e);
                },

                handleCarouselChange(e) {
                    this.handleImageInputChange(e);
                },

                handleCarouselFiles(e) {
                    this.handleImageInputChange(e);
                },

                addFilesToList(newFiles) {
                    if (this.mediaType === 'IMAGE' || this.mediaType === 'STORY') {
                        if (newFiles.length > 1) {
                            this.mediaType = 'CAROUSEL';
                            this.selectedFiles = newFiles;
                        } else {
                            this.selectedFiles = newFiles;
                        }
                    } else {
                        // Acumula imagens no modo Carrossel
                        this.selectedFiles = [...this.selectedFiles, ...newFiles];
                    }
                    this.syncFileInputAndPreviews();
                },

                syncFileInputAndPreviews() {
                    // Sincroniza os arquivos reais no <input type="file"> com DataTransfer
                    if (this.$refs.fileInput) {
                        try {
                            const dt = new DataTransfer();
                            this.selectedFiles.forEach(file => {
                                if (file instanceof File) {
                                    dt.items.add(file);
                                }
                            });
                            this.$refs.fileInput.files = dt.files;
                        } catch (e) {
                            console.error('DataTransfer sync error:', e);
                        }
                    }

                    // Atualiza miniaturas de visualização
                    this.carouselPreviews = [];
                    if (this.selectedFiles.length === 0) {
                        this.imagePreview = null;
                        return;
                    }

                    let loadedCount = 0;
                    this.selectedFiles.forEach((file, index) => {
                        if (file instanceof File) {
                            const reader = new FileReader();
                            reader.onload = (evt) => {
                                this.carouselPreviews[index] = evt.target.result;
                                loadedCount++;
                                if (index === 0) {
                                    this.imagePreview = evt.target.result;
                                }
                            };
                            reader.readAsDataURL(file);
                        } else if (typeof file === 'string') {
                            this.carouselPreviews[index] = file;
                            if (index === 0) this.imagePreview = file;
                        }
                    });

                    this.currentCarouselIndex = 0;
                    if (this.selectedFiles.length > 1 && this.mediaType !== 'STORY') {
                        this.mediaType = 'CAROUSEL';
                    }
                },

                clearAllImages() {
                    this.selectedFiles = [];
                    this.carouselPreviews = [];
                    this.imagePreview = null;
                    this.currentCarouselIndex = 0;
                    if (this.$refs.fileInput) {
                        this.$refs.fileInput.value = '';
                    }
                },

                removeCarouselSlide(index) {
                    this.selectedFiles.splice(index, 1);
                    if (this.currentCarouselIndex >= this.selectedFiles.length) {
                        this.currentCarouselIndex = Math.max(0, this.selectedFiles.length - 1);
                    }
                    this.syncFileInputAndPreviews();
                    if (this.selectedFiles.length <= 1 && this.mediaType === 'CAROUSEL') {
                        this.mediaType = 'IMAGE';
                    }
                },

                useMediaBankImage(url) {
                    this.selectedFiles = [url];
                    this.imagePreview = url;
                    this.carouselPreviews = [url];
                    this.mediaType = 'IMAGE';
                    this.tab = 'novo';
                },

                prevCarouselSlide() {
                    if (this.currentCarouselIndex > 0) {
                        this.currentCarouselIndex--;
                    } else {
                        this.currentCarouselIndex = this.carouselPreviews.length - 1;
                    }
                },

                nextCarouselSlide() {
                    if (this.currentCarouselIndex < this.carouselPreviews.length - 1) {
                        this.currentCarouselIndex++;
                    } else {
                        this.currentCarouselIndex = 0;
                    }
                },

                openLightboxFromElement(el) {
                    if (!el) return;
                    const rawSlidesB64 = el.getAttribute('data-slides');
                    const captionB64 = el.getAttribute('data-caption');

                    let slides = [];
                    let caption = '';

                    try {
                        if (rawSlidesB64) {
                            slides = JSON.parse(atob(rawSlidesB64));
                        }
                    } catch (e) {
                        console.error('Erro ao ler slides b64:', e);
                    }

                    try {
                        if (captionB64) {
                            caption = decodeURIComponent(escape(atob(captionB64)));
                        }
                    } catch (e) {
                        caption = '';
                    }

                    this.lightboxPost = {
                        caption: caption,
                        likes: el.getAttribute('data-likes') || 0,
                        comments: el.getAttribute('data-comments') || 0,
                        date: el.getAttribute('data-date') || '',
                        permalink: el.getAttribute('data-permalink') || '',
                        media_type: el.getAttribute('data-media-type') || 'FEED'
                    };

                    this.lightboxSlides = (Array.isArray(slides) && slides.length > 0) ? slides : [];
                    this.lightboxSlideIndex = 0;
                    this.lightboxOpen = true;
                },

                openLightbox(postData, slidesArray) {
                    this.lightboxPost = postData;
                    this.lightboxSlides = (slidesArray && slidesArray.length > 0) ? slidesArray : [postData.media_url || postData.media_path];
                    this.lightboxSlideIndex = 0;
                    this.lightboxOpen = true;
                },

                closeLightbox() {
                    this.lightboxOpen = false;
                    this.lightboxPost = null;
                    this.lightboxSlides = [];
                    this.lightboxSlideIndex = 0;
                },

                prevLightboxSlide() {
                    if (this.lightboxSlideIndex > 0) {
                        this.lightboxSlideIndex--;
                    }
                },

                nextLightboxSlide() {
                    if (this.lightboxSlideIndex < this.lightboxSlides.length - 1) {
                        this.lightboxSlideIndex++;
                    }
                },

                async generateAiHashtags() {
                    this.isGeneratingHashtags = true;
                    try {
                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        const res = await fetch('{{ route('instagram.hashtags.generate') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ caption: this.caption })
                        });
                        const data = await res.json();
                        if (data.success && data.formatted) {
                            if (!this.caption.includes('#')) {
                                this.caption = (this.caption ? this.caption.trim() + '\n\n' : '') + data.formatted;
                            } else {
                                const newTags = (data.hashtags || []).filter(t => !this.caption.includes(t));
                                if (newTags.length > 0) {
                                    this.caption = this.caption.trim() + ' ' + newTags.join(' ');
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Erro ao gerar hashtags:', e);
                    } finally {
                        this.isGeneratingHashtags = false;
                    }
                },

                insertHashtag(tag) {
                    if (!this.caption.includes(tag)) {
                        this.caption = (this.caption ? this.caption.trim() + ' ' : '') + tag;
                    }
                },

                appendTagToCaption(tag) {
                    this.insertHashtag(tag);
                },

                insertThemeTags(tags) {
                    const tagsStr = Array.isArray(tags) ? tags.join(' ') : tags;
                    if (tagsStr && !this.caption.includes(tagsStr)) {
                        this.caption = (this.caption ? this.caption.trim() + '\n\n' : '') + tagsStr;
                    }
                },

                applyTheme(theme) {
                    if (!theme || !theme.tags) return;
                    this.insertThemeTags(theme.tags);
                },

                saveTheme() {
                    if (this.newThemeName.trim() && !this.newThemeTagsText.trim()) {
                        this.newThemeTagsText = this.caption;
                    }
                    if (!this.newThemeName.trim()) return;

                    const tags = this.newThemeTagsText
                        .split(',')
                        .map(t => t.trim())
                        .filter(t => t.length > 0)
                        .map(t => t.startsWith('#') ? t : '#' + t);

                    fetch('{{ route("instagram.themes.save") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            name: this.newThemeName,
                            category: this.newThemeCategory || 'design',
                            tags: tags.length > 0 ? tags : [this.caption]
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.savedThemes = data.themes;
                            this.newThemeName = '';
                            this.newThemeTagsText = '';
                            this.showSaveThemeModal = false;
                        }
                    });
                },

                saveNewTheme() {
                    this.saveTheme();
                },

                deleteTheme(index) {
                    fetch('{{ route("instagram.themes.delete", ["index" => 0]) }}'.replace('/0', '/' + index), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ index: index })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            this.savedThemes = data.themes;
                        }
                    });
                },

                formatMediaType(type) {
                    if (!type) return 'FEED';
                    if (type.includes('CAROUSEL')) return 'Carrossel';
                    if (type.includes('VIDEO') || type.includes('REELS')) return 'Vídeo / Reel';
                    if (type.includes('STORY')) return 'Story';
                    return 'Feed Image';
                },

                confirmDeletePost(urlOrId, eventOrCaption) {
                    if (typeof urlOrId === 'string' && urlOrId.includes('/')) {
                        this.deleteFormActionUrl = urlOrId;
                        this.targetCardElement = eventOrCaption ? eventOrCaption.target.closest('.group') : null;
                    } else {
                        this.postToDeleteId = urlOrId;
                        this.postToDeleteCaption = eventOrCaption || 'Publicação sem legenda';
                        this.deleteFormActionUrl = '/freelas/utilidades/instagram/' + urlOrId;
                    }
                    this.confirmDeleteModalOpen = true;
                },

                async executeDelete() {
                    if (!this.deleteFormActionUrl && this.postToDeleteId) {
                        this.deleteFormActionUrl = '/freelas/utilidades/instagram/' + this.postToDeleteId;
                    }
                    if (!this.deleteFormActionUrl) return;

                    this.isDeleting = true;
                    try {
                        const response = await fetch(this.deleteFormActionUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'X-Requested-With': 'XMLHttpRequest',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({ _method: 'DELETE' })
                        });

                        const data = await response.json();
                        if (response.ok && data.success) {
                            const card = this.targetCardElement || document.getElementById('post-card-' + this.postToDeleteId);
                            if (card) {
                                card.style.transition = 'all 0.3s ease';
                                card.style.opacity = '0';
                                card.style.transform = 'scale(0.8)';
                                setTimeout(() => {
                                    card.remove();
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
                },

                async deleteConfirmed() {
                    await this.executeDelete();
                }
            }));
        }
    }

    document.addEventListener('alpine:init', initInstagramModule);
    if (window.Alpine) {
        initInstagramModule();
    }
})();
</script>

<div class="space-y-8" x-data="instagramModule">

    <!-- Mensagens de Alerta Flash (Sucesso, Erros da API ou Erros de Validação) -->
    @if (session('success'))
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-700 dark:text-emerald-300 p-4 rounded-2xl flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <p class="text-xs font-bold">{{ session('success') }}</p>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-sm">✕</button>
        </div>
    @endif

    @if (session('error'))
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 p-4 rounded-2xl flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <div>
                    <h5 class="text-xs font-black uppercase tracking-wider text-rose-800 dark:text-rose-200">Aviso / Erro no Instagram</h5>
                    <p class="text-xs font-bold mt-0.5">{{ session('error') }}</p>
                </div>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold text-sm">✕</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-rose-500/10 border border-rose-500/30 text-rose-700 dark:text-rose-300 p-4 rounded-2xl space-y-2 shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🚨</span>
                    <h5 class="text-xs font-black uppercase tracking-wider text-rose-800 dark:text-rose-200">Erros no Envio do Formulário</h5>
                </div>
                <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-rose-500 hover:text-rose-700 font-bold text-sm">✕</button>
            </div>
            <ul class="list-disc list-inside text-xs font-semibold space-y-1 pl-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
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
                            Conectado: {{ '@' . $accUsername }}
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30 rounded-full">
                            ○ Não Conectado
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                    @if($account)
                        Sua conta profissional <strong class="text-white">{{ '@' . $accUsername }}</strong> está pronta para publicar Feed, Carrosséis e Stories.
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

                            <form action="{{ route('instagram.posts.store') }}" method="POST" enctype="multipart/form-data" @submit="startSubmitting($event)" class="space-y-5">
                                @csrf
                                 <input type="hidden" name="instagram_account_id" :value="selectedAccountId">
                                <input type="hidden" name="media_type" :value="mediaType">

                                <!-- ÁREA MODERNA DRAG & DROP PARA SELEÇÃO DE IMAGENS -->
                                <div class="space-y-3"
                                     @dragover.prevent="isDragging = true"
                                     @dragleave.prevent="isDragging = false"
                                     @drop.prevent="handleFileDrop($event)">
                                     
                                    <div class="flex items-center justify-between">
                                        <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block flex items-center gap-1.5">
                                            <span>Mídias da Publicação</span>
                                            <span class="text-rose-500">*</span>
                                        </label>
                                        <span class="text-[11px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded-full border border-purple-100" x-text="mediaType === 'CAROUSEL' ? 'Modo Carrossel (Múltiplas Fotos)' : (mediaType === 'STORY' ? 'Modo Story (24h)' : 'Modo Feed Único')"></span>
                                    </div>

                                    <div :class="isDragging ? 'border-purple-600 bg-purple-50 scale-[1.01] ring-4 ring-purple-100' : 'border-slate-300 bg-white hover:border-purple-400 hover:bg-purple-50/30'"
                                         class="border-2 border-dashed rounded-xl p-6 text-center transition-all duration-200 cursor-pointer shadow-2xs relative group"
                                         @click="$refs.fileInput.click()">
                                        
                                        <input type="file"
                                               x-ref="fileInput"
                                               name="carousel_images[]"
                                               multiple
                                               @change="handleImageInputChange($event)"
                                               accept="image/*"
                                               class="hidden">
                                               
                                        <div class="space-y-3 pointer-events-none">
                                            <div class="w-12 h-12 mx-auto rounded-full bg-purple-100/80 text-purple-600 flex items-center justify-center group-hover:scale-110 transition-transform shadow-xs">
                                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                            <div>
                                                <p class="text-xs font-extrabold text-slate-800">
                                                    Arraste & Solte suas imagens aqui ou <span class="text-purple-600 underline">clique para selecionar</span>
                                                </p>
                                                <p class="text-[11px] text-slate-500 mt-1">
                                                    Selecione 1 foto para Feed/Story ou várias fotos para Carrossel automático (PNG, JPG, WEBP até 10MB)
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Miniaturas das Imagens Selecionadas -->
                                    <template x-if="carouselPreviews.length > 0">
                                        <div class="space-y-2 pt-1">
                                            <div class="flex items-center justify-between">
                                                <span class="text-[11px] font-extrabold uppercase text-slate-600 tracking-wider">
                                                    Fotos Selecionadas (<span x-text="carouselPreviews.length"></span>)
                                                </span>
                                                <button type="button" @click="clearAllImages()" class="text-[10px] font-extrabold text-rose-600 hover:underline">Limpar todas</button>
                                            </div>
                                            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-2.5">
                                                <template x-for="(slideUrl, sIdx) in carouselPreviews" :key="sIdx">
                                                    <div class="relative group rounded-lg overflow-hidden border border-slate-200 bg-slate-900 h-24 shadow-sm transition-all hover:ring-2 hover:ring-purple-500">
                                                        <img :src="slideUrl" class="w-full h-full object-cover">
                                                        <span class="absolute top-1 left-1 px-1.5 py-0.5 bg-black/80 backdrop-blur-xs text-white font-black text-[9px] rounded"
                                                              x-text="'Slide ' + (sIdx + 1)"></span>
                                                        <button type="button" 
                                                                @click.stop="removeCarouselSlide(sIdx)" 
                                                                title="Remover esta foto" 
                                                                class="absolute top-1 right-1 w-5 h-5 rounded-full bg-rose-600 hover:bg-rose-700 text-white font-bold text-[10px] flex items-center justify-center shadow transition-transform group-hover:scale-110 cursor-pointer">
                                                            ✕
                                                        </button>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <!-- Sobreposição de Marcas (Logo & Seta) -->
                                <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg space-y-2">
                                    <span class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                        Sobreposição de Ícones (Marca d'Água)
                                    </span>
                                    <div class="flex items-center gap-6">
                                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                            <input type="checkbox" name="has_logo_overlay" value="1" x-model="hasLogoOverlay" class="rounded text-purple-600 focus:ring-purple-500">
                                            <span>Aplicar Ícone da Logo (Topo)</span>
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-semibold text-slate-700 cursor-pointer">
                                            <input type="checkbox" name="has_arrow_overlay" value="1" x-model="hasArrowOverlay" class="rounded text-purple-600 focus:ring-purple-500">
                                            <span>Aplicar Seta (Rodapé)</span>
                                        </label>
                                    </div>
                                    @if(!optional($settings)->logo_path && !optional($settings)->arrow_path)
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

                                <!-- GERADOR INTELIGENTE DE HASHTAGS & TEMAS PERSONALIZADOS -->
                                <div x-show="mediaType !== 'STORY'" class="p-4 bg-gradient-to-br from-purple-50/70 via-slate-50 to-purple-50/30 border border-purple-200/80 rounded-xl space-y-4 shadow-sm">
                                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-purple-100 pb-3">
                                        <div>
                                            <h5 class="text-xs font-extrabold text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                                                <svg class="w-4 h-4 text-purple-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                IA Gerador de Hashtags & Temas
                                            </h5>
                                            <p class="text-[11px] text-slate-500">Gere 30 hashtags com base no texto da sua legenda ou escolha temas salvos.</p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button" @click="generateAiHashtags()" :disabled="isGeneratingHashtags" class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-extrabold rounded-lg shadow-sm transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                                                <template x-if="!isGeneratingHashtags">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                                        <span>✨ Gerar 30 Hashtags com IA</span>
                                                    </span>
                                                </template>
                                                <template x-if="isGeneratingHashtags">
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                        <span>Analisando Texto...</span>
                                                    </span>
                                                </template>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Seleção de Categoria Rápida -->
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-extrabold text-slate-700 uppercase tracking-wider">Categorias Rápidas</span>
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
                                                <button type="button" @click="insertHashtag(tag)" class="px-2 py-1 bg-white hover:bg-purple-600 hover:text-white text-purple-700 border border-purple-200 text-[11px] font-semibold rounded-md transition-all cursor-pointer">
                                                    <span x-text="tag"></span>
                                                </button>
                                            </template>
                                        </div>
                                    </div>

                                    <!-- TEMAS SALVOS PELO USUÁRIO -->
                                    <div class="pt-3 border-t border-purple-100 space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-[11px] font-extrabold text-slate-700 uppercase tracking-wider flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                                Meus Temas Salvos (<span x-text="savedThemes.length"></span>)
                                            </span>
                                            <button type="button" @click="showSaveThemeModal = true" class="text-xs font-extrabold text-purple-700 hover:text-purple-900 underline flex items-center gap-1 cursor-pointer">
                                                <span>+ Salvar Tema da Legenda</span>
                                            </button>
                                        </div>

                                        <template x-if="savedThemes.length > 0">
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                                <template x-for="(thm, tIdx) in savedThemes" :key="tIdx">
                                                    <div class="p-2 bg-white border border-slate-200 rounded-lg flex items-center justify-between gap-2 shadow-2xs">
                                                        <div class="truncate cursor-pointer" @click="insertThemeTags(thm.hashtags)">
                                                            <span class="text-xs font-bold text-slate-800 block truncate" x-text="thm.name"></span>
                                                            <span class="text-[10px] text-slate-400 font-mono block" x-text="(thm.hashtags ? (Array.isArray(thm.hashtags) ? thm.hashtags.length : thm.hashtags.split(' ').length) : 0) + ' hashtags'"></span>
                                                        </div>
                                                        <div class="flex items-center gap-1">
                                                            <button type="button" @click="insertThemeTags(thm.hashtags)" title="Inserir no Post" class="p-1 bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 rounded text-[10px] font-bold cursor-pointer">
                                                                + Inserir
                                                            </button>
                                                            <button type="button" @click="deleteTheme(tIdx)" title="Excluir Tema" class="p-1 bg-rose-50 hover:bg-rose-600 hover:text-white text-rose-600 rounded text-[10px] font-bold cursor-pointer">
                                                                ✕
                                                            </button>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="savedThemes.length === 0">
                                            <p class="text-[11px] text-slate-400 italic">Você ainda não possui temas salvos. Escreva suas hashtags na legenda e clique em "+ Salvar Tema da Legenda".</p>
                                        </template>
                                    </div>
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

                                    <div x-show="actionType === 'schedule'" class="space-y-2 bg-purple-50/60 p-3.5 rounded-xl border border-purple-200/80 transition-all">
                                        <label class="text-[11px] font-black text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Data e Horário da Publicação
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   id="create_scheduled_at"
                                                   name="scheduled_at" 
                                                   x-init="
                                                       $nextTick(() => {
                                                           flatpickr($el, {
                                                               enableTime: true,
                                                               dateFormat: 'Y-m-d H:i',
                                                               altInput: true,
                                                               altFormat: 'j \\d\\e F \\d\\e Y \\à\\s H:i',
                                                               time_24hr: true,
                                                               locale: 'pt',
                                                               minDate: 'today',
                                                               defaultHour: (new Date()).getHours() + 1,
                                                               defaultMinute: 0
                                                           });
                                                       });
                                                   "
                                                   placeholder="Clique para selecionar a data e hora..."
                                                   class="w-full text-xs text-slate-800 border border-purple-200 rounded-xl p-2.5 bg-white font-bold pl-9 shadow-xs focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-purple-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            </div>
                                        </div>
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
                                        <img src="{{ $accAvatar }}" class="w-8 h-8 rounded-full border border-purple-500 object-cover">
                                        <span class="text-xs font-bold text-white tracking-tight" x-text="'{{ '@' . $accUsername }}'"></span>
                                    </div>
                                    <span class="text-slate-400 text-xs">•••</span>
                                </div>

                                <!-- Instagram Image Viewport with Live Carousel & Overlay Badges -->
                                <div class="w-full h-[340px] sm:h-[380px] bg-slate-900 relative flex items-center justify-center overflow-hidden group">
                                    
                                    <!-- PREVIEW DE FEED ÚNICO OU STORY OU SEM CARROSSEL ATIVO -->
                                    <template x-if="imagePreview && (mediaType !== 'CAROUSEL' || carouselPreviews.length === 0)">
                                        <img :src="imagePreview" class="w-full h-full object-cover">
                                    </template>

                                    <!-- PREVIEW INTERATIVO DE CARROSSEL AO VIVO -->
                                    <template x-if="mediaType === 'CAROUSEL' && carouselPreviews.length > 0">
                                        <div class="w-full h-full relative inset-0">
                                            <template x-for="(slide, idx) in carouselPreviews" :key="idx">
                                                <div x-show="currentCarouselIndex === idx"
                                                     x-transition:enter="transition ease-out duration-300 transform"
                                                     x-transition:enter-start="opacity-0 scale-105"
                                                     x-transition:enter-end="opacity-100 scale-100"
                                                     class="absolute inset-0 w-full h-full">
                                                    <img :src="slide" class="w-full h-full object-cover">
                                                </div>
                                            </template>

                                            <!-- Setas de Navegação do Carrossel na Prévia do Celular -->
                                            <button type="button" x-show="currentCarouselIndex > 0" @click.stop="currentCarouselIndex--" class="absolute left-2 top-1/2 -translate-y-1/2 z-30 w-7 h-7 rounded-full bg-black/60 text-white font-bold text-xs flex items-center justify-center shadow hover:bg-black/90 cursor-pointer">
                                                ❮
                                            </button>
                                            <button type="button" x-show="currentCarouselIndex < carouselPreviews.length - 1" @click.stop="currentCarouselIndex++" class="absolute right-2 top-1/2 -translate-y-1/2 z-30 w-7 h-7 rounded-full bg-black/60 text-white font-bold text-xs flex items-center justify-center shadow hover:bg-black/90 cursor-pointer">
                                                ❯
                                            </button>

                                            <!-- Dots Indicadores na Prévia do Celular -->
                                            <div class="absolute bottom-2.5 left-0 right-0 z-30 flex items-center justify-center gap-1.5">
                                                <template x-for="(slide, idx) in carouselPreviews" :key="idx">
                                                    <span :class="idx === currentCarouselIndex ? 'bg-purple-500 w-2.5 h-2.5 scale-110' : 'bg-white/40 w-1.5 h-1.5'" class="rounded-full transition-all"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </template>

                                    <template x-if="!imagePreview && carouselPreviews.length === 0">
                                        <div class="text-center p-6 text-slate-600 space-y-2">
                                            <svg class="w-10 h-10 mx-auto text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            <p class="text-xs font-semibold">Sua imagem ou carrossel aparecerá aqui em tempo real</p>
                                        </div>
                                    </template>

                                    <!-- Live Overlay Badge Logo (Top Right) -->
                                    <template x-if="hasLogoOverlay">
                                        <div class="absolute top-3 right-3 flex items-center justify-center pointer-events-none">
                                            @if(optional($settings)->logo_path)
                                                <img src="{{ asset('storage/' . optional($settings)->logo_path) }}" class="h-6 max-w-[80px] object-contain drop-shadow-md">
                                            @else
                                                <span class="text-[9px] font-black uppercase text-amber-300 tracking-wider">LOGO</span>
                                            @endif
                                        </div>
                                    </template>

                                    <!-- Live Overlay Badge Arrow (Bottom Right) -->
                                    <template x-if="hasArrowOverlay">
                                        <div class="absolute bottom-3 right-3 flex items-center justify-center pointer-events-none">
                                            @if(optional($settings)->arrow_path)
                                                <img src="{{ asset('storage/' . optional($settings)->arrow_path) }}" class="h-6 max-w-[80px] object-contain drop-shadow-md">
                                            @else
                                                <span class="text-[10px] font-bold text-white">Arraste pro lado ➔</span>
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
                                        <strong class="text-white font-bold" x-text="'{{ '@' . $accUsername }}'"></strong>
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
                            <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">Publicações do Perfil {{ '@' . $accUsername }}</h4>
                            <p class="text-xs text-slate-500">Histórico oficial das mídias publicadas diretamente na sua conta do Instagram.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                        @forelse($liveInstagramPosts as $item)
                            @php
                                $slides = [];
                                if (isset($item['children']['data']) && is_array($item['children']['data'])) {
                                    foreach ($item['children']['data'] as $child) {
                                        $url = $child['media_url'] ?? ($child['thumbnail_url'] ?? null);
                                        if ($url) {
                                            $slides[] = $url;
                                        }
                                    }
                                }
                                $imgUrl = $item['media_url'] ?? ($item['thumbnail_url'] ?? ($slides[0] ?? null));
                                if (empty($slides) && $imgUrl) {
                                    $slides[] = $imgUrl;
                                }
                                $postUrl = $item['permalink'] ?? '#';
                                $likes = $item['like_count'] ?? 0;
                                $comments = $item['comments_count'] ?? 0;
                                $timestamp = isset($item['timestamp']) ? \Carbon\Carbon::parse($item['timestamp'])->format('d/m/Y H:i') : null;

                                $slidesB64 = base64_encode(json_encode(array_values(array_filter($slides))));
                                $captionB64 = base64_encode($item['caption'] ?? '');
                            @endphp
                            <div class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between cursor-pointer"
                                 @click="openLightboxFromElement($el)"
                                 data-caption="{{ $captionB64 }}"
                                 data-likes="{{ $likes }}"
                                 data-comments="{{ $comments }}"
                                 data-date="{{ $timestamp }}"
                                 data-permalink="{{ $postUrl }}"
                                 data-media-type="{{ $item['media_type'] ?? 'FEED' }}"
                                 data-slides="{{ $slidesB64 }}">
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
                                            <span>Ver no Instagram</span>
                                        </a>
                                        @if($imgUrl)
                                            <button type="button" @click="useMediaBankImage('{{ $imgUrl }}')" title="Reutilizar Imagem" class="py-1.5 px-2 bg-purple-50 hover:bg-purple-600 hover:text-white text-purple-700 font-bold text-[10px] rounded transition-all flex items-center gap-1 cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>Reutilizar</span>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-span-full p-12 text-center bg-slate-50 border border-slate-200 rounded-xl text-slate-500 text-xs space-y-2">
                                <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <p class="font-bold text-slate-700">Nenhuma publicação encontrada diretamente no feed do Instagram.</p>
                                <p class="text-slate-500">Assim que você fizer publicações na conta {{ '@' . $accUsername }}, elas aparecerão aqui automaticamente!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- 3. ABA: CALENDÁRIO VISUAL DE AGENDAMENTOS E CONTROLE DE MESES/ANOS -->
                <div x-show="tab === 'calendario'" class="space-y-6">
                    
                    <!-- Cabeçalho do Calendário com Navegação de Mês/Ano e Filtros -->
                    <div class="relative z-30 bg-gradient-to-r from-slate-900 via-purple-950 to-slate-900 text-white p-5 rounded-2xl shadow-md border border-purple-900/50 flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-0.5 text-[10px] font-black uppercase tracking-wider bg-purple-500/30 text-purple-200 border border-purple-400/40 rounded-full">
                                    Controle de Mídia
                                </span>
                                <h4 class="text-base font-black text-white tracking-tight">Calendário Mensal & Anual de Agendamentos</h4>
                            </div>
                            <p class="text-xs text-slate-300 mt-1">Gerencie, edite legendas, mude datas/horários e altere publicações agendadas.</p>
                        </div>

                        <!-- Seletores de Mês, Ano e Botões de Navegação Ultra-Modernos -->
                        <div class="flex items-center gap-2 bg-black/30 backdrop-blur-xl p-1.5 rounded-2xl border border-white/10 shadow-2xl">
                            <!-- Botão Mês Anterior -->
                            <button type="button" 
                                    @click="prevMonth()" 
                                    title="Mês Anterior" 
                                    class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center border border-white/10 shadow-sm transition-all active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                            </button>

                            <!-- Custom Dropdown Mês -->
                            <div class="relative" @click.outside="monthDropdownOpen = false">
                                <button type="button" 
                                        @click="monthDropdownOpen = !monthDropdownOpen" 
                                        class="flex items-center gap-2 bg-purple-600/40 hover:bg-purple-600/60 text-white text-xs font-black px-3.5 py-2 rounded-xl border border-purple-400/40 shadow-sm transition-all active:scale-95 cursor-pointer">
                                    <span x-text="monthNames[currentMonth]"></span>
                                    <svg class="w-3.5 h-3.5 text-purple-200 transition-transform duration-200" :class="monthDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="monthDropdownOpen" 
                                     x-transition:enter="transition ease-out duration-150 transform"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100 transform"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     x-cloak
                                     class="absolute top-full left-0 mt-2 z-[100] w-44 bg-slate-900/95 backdrop-blur-xl border border-purple-500/40 rounded-2xl shadow-2xl p-1.5 grid grid-cols-1 max-h-60 overflow-y-auto custom-scrollbar">
                                    <template x-for="(mName, idx) in monthNames" :key="idx">
                                        <button type="button" 
                                                @click="currentMonth = idx; monthDropdownOpen = false" 
                                                :class="currentMonth === idx ? 'bg-purple-600 text-white font-extrabold shadow-sm' : 'text-slate-300 hover:bg-purple-900/50 hover:text-white font-bold'"
                                                class="w-full text-left px-3 py-1.5 text-xs rounded-xl transition-all flex items-center justify-between cursor-pointer">
                                            <span x-text="mName"></span>
                                            <template x-if="currentMonth === idx">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Custom Dropdown Ano -->
                            <div class="relative" @click.outside="yearDropdownOpen = false">
                                <button type="button" 
                                        @click="yearDropdownOpen = !yearDropdownOpen" 
                                        class="flex items-center gap-2 bg-white/10 hover:bg-white/20 text-purple-200 text-xs font-black px-3.5 py-2 rounded-xl border border-white/10 shadow-sm transition-all active:scale-95 cursor-pointer">
                                    <span x-text="currentYear"></span>
                                    <svg class="w-3.5 h-3.5 text-purple-300 transition-transform duration-200" :class="yearDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="yearDropdownOpen" 
                                     x-transition:enter="transition ease-out duration-150 transform"
                                     x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
                                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave="transition ease-in duration-100 transform"
                                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                                     x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
                                     x-cloak
                                     class="absolute top-full left-0 mt-2 z-[100] w-32 bg-slate-900/95 backdrop-blur-xl border border-purple-500/40 rounded-2xl shadow-2xl p-1.5 grid grid-cols-1 max-h-60 overflow-y-auto custom-scrollbar">
                                    <template x-for="y in [2024, 2025, 2026, 2027, 2028, 2029, 2030]" :key="y">
                                        <button type="button" 
                                                @click="currentYear = y; yearDropdownOpen = false" 
                                                :class="currentYear === y ? 'bg-purple-600 text-white font-extrabold shadow-sm' : 'text-slate-300 hover:bg-purple-900/50 hover:text-white font-bold'"
                                                class="w-full text-left px-3 py-1.5 text-xs rounded-xl transition-all flex items-center justify-between cursor-pointer">
                                            <span x-text="y"></span>
                                            <template x-if="currentYear === y">
                                                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Botão Próximo Mês -->
                            <button type="button" 
                                    @click="nextMonth()" 
                                    title="Próximo Mês" 
                                    class="w-9 h-9 rounded-xl bg-white/10 hover:bg-white/20 text-white flex items-center justify-center border border-white/10 shadow-sm transition-all active:scale-95 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                            </button>

                            <!-- Divisor Vertical -->
                            <div class="h-6 w-px bg-white/20 mx-0.5"></div>

                            <!-- Botão Ir para Hoje -->
                            <button type="button" 
                                    @click="goToToday()" 
                                    class="px-3.5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-extrabold text-xs rounded-xl shadow-md border border-purple-400/30 transition-all active:scale-95 cursor-pointer flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Hoje</span>
                            </button>
                        </div>
                    </div>

                    <!-- Legenda de Status de Postagens -->
                    <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <span class="text-[11px] font-extrabold uppercase text-slate-400">Legenda:</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-purple-500"></span> Agendado</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Publicado</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Erro</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span> Carrossel</span>
                        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span> Story</span>
                    </div>

                    <!-- Grid do Calendário Interativo -->
                    <div class="grid grid-cols-7 gap-2 bg-slate-100 p-2.5 rounded-2xl border border-slate-200 shadow-xs">
                        
                        <!-- Nomes dos Dias da Semana -->
                        <template x-for="dayName in ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb']" :key="dayName">
                            <div class="text-center text-[11px] font-black text-slate-500 uppercase tracking-wider py-2 bg-slate-200/80 rounded-lg">
                                <span x-text="dayName"></span>
                            </div>
                        </template>

                        <!-- Dias do Mês (Grid Dinâmico Alpine JS) -->
                        <template x-for="(cell, cIdx) in calendarGrid" :key="cIdx">
                            <div :class="[
                                    cell.isCurrentMonth ? 'bg-white' : 'bg-slate-50/50 opacity-40',
                                    cell.isToday ? 'border-2 border-purple-600 ring-2 ring-purple-100 shadow-sm' : 'border border-slate-200 hover:border-purple-300'
                                 ]"
                                 class="min-h-[110px] p-2 rounded-xl flex flex-col justify-between transition-all relative group">
                                
                                <div class="flex items-center justify-between mb-1">
                                    <span :class="cell.isToday ? 'bg-purple-600 text-white w-6 h-6 rounded-full flex items-center justify-center font-black shadow-xs' : 'text-slate-700 font-bold'"
                                          class="text-xs"
                                          x-text="cell.day"></span>
                                    
                                    <template x-if="cell.posts && cell.posts.length > 0">
                                        <span class="text-[9px] font-black bg-purple-100 text-purple-800 px-1.5 py-0.5 rounded-full border border-purple-200"
                                              x-text="cell.posts.length + (cell.posts.length === 1 ? ' post' : ' posts')"></span>
                                    </template>
                                </div>

                                <!-- Cards das Postagens Agendadas/Publicadas no Dia -->
                                <div class="space-y-1.5 my-1">
                                    <template x-for="p in cell.posts" :key="p.id">
                                        <div @click.stop="openManagePost(p)"
                                             :class="[
                                                p.status === 'publicado' ? 'bg-emerald-100/80 text-emerald-950 border-emerald-300 hover:bg-emerald-200/80' :
                                                (p.status === 'erro' ? 'bg-rose-100/80 text-rose-950 border-rose-300 hover:bg-rose-200/80' : 'bg-purple-100/80 text-purple-950 border-purple-300 hover:bg-purple-200/80')
                                             ]"
                                             class="p-2 rounded-xl border text-[10px] font-bold transition-all cursor-pointer space-y-1 hover:shadow-md border-slate-300">
                                            
                                            <div class="flex items-center justify-between gap-1">
                                                <span class="px-1.5 py-0.5 bg-white/90 rounded-md font-black text-[9px] uppercase tracking-wider text-slate-800 shadow-2xs"
                                                      x-text="p.media_type === 'STORY' ? 'Story' : (p.media_type === 'CAROUSEL' ? 'Carrossel' : 'Feed')"></span>
                                                <span class="text-[9px] font-mono font-extrabold text-slate-700"
                                                      x-text="p.published_at ? p.published_at.substring(11, 16) : (p.scheduled_at ? p.scheduled_at.substring(11, 16) : (p.created_at ? p.created_at.substring(11, 16) : ''))"></span>
                                            </div>

                                            <p class="truncate text-[10px] text-slate-800 font-semibold"
                                               x-text="p.caption || 'Sem legenda'"></p>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
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
                            @php
                                $bancoSlidesB64 = base64_encode(json_encode([asset('storage/' . $p->media_path)]));
                                $bancoCaptionB64 = base64_encode($p->caption ?? '');
                            @endphp
                            <div class="group bg-white border border-slate-200 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all flex flex-col justify-between cursor-pointer"
                                 @click="openLightboxFromElement($el)"
                                 data-caption="{{ $bancoCaptionB64 }}"
                                 data-likes="0"
                                 data-comments="0"
                                 data-date="{{ $p->created_at ? $p->created_at->format('d/m/Y H:i') : '' }}"
                                 data-permalink=""
                                 data-media-type="{{ $p->media_type ?? 'FEED' }}"
                                 data-slides="{{ $bancoSlidesB64 }}">
                                <div class="relative h-36 bg-slate-900 overflow-hidden">
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
                            @if(optional($settings)->logo_path)
                                <div class="h-20 bg-slate-900 rounded-lg p-2 flex items-center justify-center border border-slate-700">
                                    <img src="{{ asset('storage/' . optional($settings)->logo_path) }}" class="h-full object-contain">
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
                            @if(optional($settings)->arrow_path)
                                <div class="h-20 bg-slate-900 rounded-lg p-2 flex items-center justify-center border border-slate-700">
                                    <img src="{{ asset('storage/' . optional($settings)->arrow_path) }}" class="h-full object-contain">
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
    @endif

    <!-- LIGHTBOX OVERLAY COM TOTAL Z-INDEX -->
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
                        <img src="{{ $accAvatar }}" class="w-8 h-8 rounded-full border border-purple-500 object-cover">
                        <div>
                            <span class="text-xs font-bold text-white block leading-tight" x-text="'{{ '@' . $accUsername }}'"></span>
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
                        <strong class="text-white font-bold" x-text="'{{ '@' . $accUsername }}'"></strong>
                        <span x-text="lightboxPost?.caption || 'Sem legenda'"></span>
                    </p>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL DE CONFIRMAÇÃO DE EXCLUSÃO (ESTILOSO E SEGURO) -->
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

    <!-- MODAL DE CONFIRMAÇÃO PARA SALVAR TEMA -->
    <div x-show="showSaveThemeModal" 
         x-cloak 
         @keydown.escape.window="showSaveThemeModal = false"
         class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-screen h-screen z-[9999999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all">
        
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-100 space-y-4" @click.outside="showSaveThemeModal = false">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                    Salvar Novo Tema de Hashtags
                </h4>
                <button type="button" @click="showSaveThemeModal = false" class="text-slate-400 hover:text-slate-600 font-bold">✕</button>
            </div>
            
            <div class="space-y-3">
                <div>
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block mb-1">Nome do Tema</label>
                    <input type="text" x-model="newThemeName" placeholder="Ex: Posts de Identidade Visual, Motion, etc." class="w-full text-xs text-slate-800 border border-slate-200 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-purple-500">
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Este tema armazenará as hashtags atualmente escritas na sua legenda para reutilização em 1 clique nas próximas postagens.
                </p>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" @click="showSaveThemeModal = false" class="py-2 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-lg transition-all cursor-pointer">
                    Cancelar
                </button>
                <button type="button" @click="saveTheme()" class="py-2 px-4 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-lg shadow-md transition-all cursor-pointer">
                    Salvar Tema
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL DE PROGRESSO E COMUNICAÇÃO DE PUBLICAÇÃO NO INSTAGRAM -->
    <div x-show="isSubmittingPost" 
         x-cloak 
         class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-screen h-screen z-[99999999] flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-md transition-all">
        
        <div class="bg-white rounded-3xl p-8 max-w-md w-full shadow-2xl border border-slate-100 text-center space-y-6 transform transition-all">
            
            <!-- Anel e Porcentagem de Progresso -->
            <div class="relative w-24 h-24 mx-auto flex items-center justify-center">
                <svg class="w-full h-full transform -rotate-90" viewBox="0 0 36 36">
                    <path class="text-slate-100" stroke-width="3" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                    <path class="text-purple-600 transition-all duration-300 ease-out" stroke-dasharray="100" :stroke-dashoffset="100 - postProgress" stroke-linecap="round" stroke-width="3.5" stroke="currentColor" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <span class="text-xl font-black text-slate-800 tracking-tight" x-text="postProgress + '%'"></span>
                </div>
            </div>

            <div class="space-y-2">
                <h4 class="text-base font-extrabold text-slate-800 uppercase tracking-wider flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-purple-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Publicando no Instagram
                </h4>
                <p class="text-xs text-purple-600 font-bold" x-text="postProgressStep"></p>
                <p class="text-[11px] text-slate-400">Por favor, mantenha esta janela aberta enquanto o Facebook/Meta valida sua mídia.</p>
            </div>

            <!-- Barra de Progresso Gradiente -->
            <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-200">
                <div class="bg-gradient-to-r from-purple-600 to-rose-500 h-full rounded-full transition-all duration-300 shadow-sm" :style="'width: ' + postProgress + '%'"></div>
            </div>
        </div>
    </div>

    <!-- MODAL DE GERENCIAMENTO & EDIÇÃO DE POSTAGEM AGENDADA -->
    <div x-show="managePostModalOpen" 
         x-cloak 
         @keydown.escape.window="managePostModalOpen = false"
         class="fixed inset-0 top-0 left-0 right-0 bottom-0 w-screen h-screen z-[9999999] flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md transition-all">
        
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full shadow-2xl border border-slate-100 space-y-5" @click.outside="managePostModalOpen = false">
            
            <!-- Cabeçalho do Modal -->
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span :class="editingPost?.status === 'publicado' ? 'bg-emerald-500' : (editingPost?.status === 'erro' ? 'bg-rose-500' : 'bg-purple-600')"
                          class="w-2.5 h-2.5 rounded-full"></span>
                    <h4 class="text-sm font-extrabold text-slate-800 uppercase tracking-wider">
                        Gerenciar Postagem <span x-text="'#' + (editingPost?.id || '')"></span>
                    </h4>
                </div>
                <button type="button" @click="managePostModalOpen = false" class="text-slate-400 hover:text-slate-600 font-bold text-lg cursor-pointer">✕</button>
            </div>

            <!-- Formulário de Edição -->
            <form :action="'/freelas/utilidades/instagram/posts/' + (editingPost?.id || '')" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" value="PUT">

                <!-- Mini Prévia da Imagem -->
                <template x-if="editingPost?.media_path">
                    <div class="relative h-44 rounded-xl overflow-hidden bg-slate-900 border border-slate-200 shadow-inner">
                        <img :src="'/storage/' + editingPost.media_path" class="w-full h-full object-cover">
                        <span class="absolute top-2 left-2 px-2 py-0.5 bg-black/80 backdrop-blur-xs text-white text-[10px] font-black uppercase rounded"
                              x-text="editingPost.media_type"></span>
                    </div>
                </template>

                <!-- Alterar Data e Horário -->
                <div class="space-y-1.5">
                    <label class="text-[11px] font-black text-purple-900 uppercase tracking-wider flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Data e Horário Agendados
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="modal_edit_scheduled_at"
                               name="scheduled_at" 
                               x-model="editScheduledAt" 
                               x-init="
                                   $nextTick(() => {
                                       flatpickr($el, {
                                           enableTime: true,
                                           dateFormat: 'Y-m-d H:i',
                                           altInput: true,
                                           altFormat: 'j \\d\\e F \\d\\e Y \\à\\s H:i',
                                           time_24hr: true,
                                           locale: 'pt',
                                           minDate: 'today'
                                       });
                                   });
                               "
                               placeholder="Clique para alterar a data e hora..."
                               class="w-full text-xs text-slate-800 border border-purple-200 rounded-xl p-2.5 bg-white font-bold pl-9 shadow-xs focus:ring-2 focus:ring-purple-500 outline-none cursor-pointer">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-purple-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                </div>

                <!-- Alterar Legenda -->
                <div class="space-y-1.5">
                    <label class="text-xs font-extrabold text-slate-700 uppercase tracking-wider block">Legenda da Publicação</label>
                    <textarea name="caption" 
                              x-model="editCaption" 
                              rows="4" 
                              class="w-full text-xs text-slate-800 border border-slate-200 rounded-lg p-2.5 outline-none focus:ring-2 focus:ring-purple-500"></textarea>
                </div>

                <!-- Botões de Ação (Salvar / Publicar Agora / Excluir) -->
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    <div class="grid grid-cols-2 gap-2">
                        <button type="submit" 
                                class="py-2.5 px-4 bg-purple-600 hover:bg-purple-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <span>💾 Salvar Alterações</span>
                        </button>

                        <button type="submit" 
                                name="publish_now" 
                                value="1" 
                                @click="startSubmitting($event); managePostModalOpen = false;"
                                class="py-2.5 px-4 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-extrabold text-xs rounded-xl shadow-sm transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <span>🚀 Publicar Agora</span>
                        </button>
                    </div>

                    <button type="button" 
                            @click="confirmDeletePost(editingPost.id, editingPost.caption); managePostModalOpen = false;" 
                            class="w-full py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-extrabold text-xs rounded-xl transition-all cursor-pointer flex items-center justify-center gap-1 border border-rose-200">
                        <span>🗑️ Excluir Agendamento</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
