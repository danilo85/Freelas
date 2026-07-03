@extends('layouts.app')

@section('title', 'Lembretes e Notas - Gestor de Freelas')
@section('page_title', 'Lembretes e Notas')

@section('content')
<div id="pjax-container" class="space-y-6" x-data="keepNotesManager()">

    <!-- Floating Reminder Alerts Stack -->
    <div class="fixed top-4 right-4 z-[99999] space-y-2 w-80 select-none pointer-events-none">
        <template x-for="alert in activeAlerts" :key="alert.id">
            <div class="bg-slate-900 border border-slate-700 text-white rounded-lg p-4 shadow-2xl flex items-start justify-between gap-3 pointer-events-auto">
                <div class="flex-1 min-w-0">
                    <span class="text-[10px] font-bold text-amber-400 block uppercase tracking-wider">⏰ Lembrete Ativo</span>
                    <h4 class="text-sm font-black mt-1 text-white truncate" x-text="alert.title || 'Sem título'"></h4>
                    <p class="text-xs text-slate-300 mt-1 line-clamp-3 leading-relaxed" x-text="alert.content || 'Alvo atingido!'"></p>
                    <button type="button" @click="deactivateReminder(alert.id)" class="text-[10px] bg-slate-800 text-amber-400 hover:bg-slate-700 font-extrabold px-2.5 py-1 rounded mt-2.5 border border-slate-700 pointer-events-auto block transition-colors cursor-pointer select-none">
                        Desativar Alarme
                    </button>
                </div>
                <button type="button" @click="dismissAlert(alert.id)" class="text-slate-400 hover:text-white font-black text-sm shrink-0 cursor-pointer">✕</button>
            </div>
        </template>
    </div>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 select-none">
        <div>
            <h1 class="text-2xl font-black text-slate-800">Lembretes e Notas</h1>
            <p class="text-sm text-slate-500 font-medium mt-1">Guarde suas ideias, afazeres e rascunhos rápidos integrados ao seu sistema.</p>
        </div>
        
        <!-- Search bar & Show Archived Toggle (Icons / Expanding layout) -->
        <div class="flex items-center gap-3 relative" x-data="{ searchExpanded: false }">
            
            <!-- Expandable Search Input -->
            <div class="flex items-center transition-all duration-300" :class="searchExpanded || searchQuery ? 'w-48 sm:w-64 opacity-100' : 'w-0 opacity-0 overflow-hidden'">
                <div class="relative w-full">
                    <input type="text" 
                           x-model="searchQuery"
                           @blur="if(!searchQuery) searchExpanded = false"
                           placeholder="Pesquisar notas..." 
                           class="w-full pl-3 pr-8 py-2 rounded-[5px] border border-slate-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all placeholder-slate-400 text-slate-800 font-semibold shadow-xs">
                    <button type="button" 
                            x-show="searchQuery" 
                            @click="searchQuery = ''" 
                            class="absolute inset-y-0 right-0 flex items-center pr-2.5 text-slate-400 hover:text-slate-655 cursor-pointer">
                        ✕
                    </button>
                </div>
            </div>

            <!-- Search Icon Toggle Button -->
            <button type="button" 
                    @click="searchExpanded = !searchExpanded" 
                    class="p-2.5 bg-white hover:bg-slate-50 border border-slate-200 text-slate-500 hover:text-slate-800 rounded-full transition-colors focus:outline-none cursor-pointer shadow-xs"
                    title="Pesquisar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>

            <!-- Archive Toggle Icon Button -->
            <button type="button" 
                    @click="showArchivedOnly = !showArchivedOnly" 
                    class="p-2.5 border rounded-full transition-colors focus:outline-none cursor-pointer shadow-xs"
                    :class="showArchivedOnly ? 'bg-amber-600 border-amber-600 text-white shadow-sm' : 'bg-white border-slate-200 text-slate-500 hover:text-slate-800 hover:bg-slate-50'"
                    :title="showArchivedOnly ? 'Ver Notas Ativas' : 'Ver Notas Arquivadas'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Keep note creator (Expands on click) -->
    <div class="flex justify-center select-none">
        <div class="w-full max-w-xl border rounded-lg shadow-sm hover:shadow-md transition-shadow duration-200 relative overflow-hidden"
             :class="newColor"
             @click.away="closeCreator()">
            
            <!-- Image Header Preview inside Creator -->
            <div x-show="newImagePreview" class="relative w-full aspect-[3/1] bg-slate-100 border-b border-slate-200" x-cloak>
                <img :src="newImagePreview" class="w-full h-full object-cover">
                <button type="button" 
                        @click="removeNewImage()" 
                        class="absolute top-2 right-2 bg-slate-900/60 hover:bg-slate-900 text-white rounded-full p-1.5 transition-colors cursor-pointer text-xs"
                        title="Remover imagem">✕</button>
            </div>

            <!-- Expanded Título & Pin -->
            <div x-show="isCreating" class="flex items-center justify-between px-4 pt-3.5" x-cloak>
                <input type="text" 
                       x-model="newTitle" 
                       placeholder="Título" 
                       class="w-full border-0 focus:ring-0 text-base font-black text-slate-800 placeholder-slate-450 focus:outline-none bg-transparent" />
                
                <!-- SVG Pin Icon -->
                <button type="button" 
                        @click="newIsPinned = !newIsPinned" 
                        class="text-slate-400 hover:text-slate-700 transition-colors focus:outline-none cursor-pointer"
                        :title="newIsPinned ? 'Remover fixador' : 'Fixar nota'">
                    <svg class="w-5 h-5 transition-transform" :class="newIsPinned ? 'text-indigo-650 fill-indigo-600 scale-110 rotate-45' : 'rotate-0'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </button>
            </div>

            <!-- Content Area (Text Area vs Checklist inputs) -->
            <div class="px-4 py-3">
                <template x-if="newType === 'text'">
                    <textarea x-model="newContent"
                              @focus="isCreating = true"
                              placeholder="Criar uma nota..."
                              :rows="isCreating ? 3 : 1"
                              class="w-full border-0 focus:ring-0 text-sm font-semibold placeholder-slate-450 focus:outline-none resize-none bg-transparent text-slate-850"
                              @keydown.ctrl.enter="saveNote()"></textarea>
                </template>

                <!-- Checklist creation view -->
                <template x-if="newType === 'list'">
                    <div class="space-y-2" @click="isCreating = true">
                        <!-- List rows -->
                        <template x-for="(item, idx) in newListItems" :key="item.id">
                            <div class="flex items-center gap-2 group/row">
                                <input type="checkbox" disabled class="w-4 h-4 rounded border-slate-300">
                                <input type="text" 
                                       x-model="item.text" 
                                       placeholder="Item da lista" 
                                       class="w-full border-0 focus:ring-0 text-sm font-semibold focus:outline-none bg-transparent p-0 text-slate-850"
                                       @keydown.enter.prevent="addNewListItemRow(idx)"
                                       @keydown.backspace="removeNewListItemRow(idx, item.text)">
                                <button type="button" @click="removeNewListItem(idx)" class="text-slate-400 hover:text-slate-600 text-xs shrink-0 select-none">✕</button>
                            </div>
                        </template>

                        <!-- Add Item Row -->
                        <div class="flex items-center gap-2 pt-2 border-t border-slate-200/50">
                            <span class="text-slate-400 text-sm font-bold select-none">+</span>
                            <input type="text" 
                                   x-model="newItemText"
                                   placeholder="Adicionar item" 
                                   class="w-full border-0 focus:ring-0 text-sm font-semibold focus:outline-none bg-transparent p-0 placeholder-slate-450 text-slate-850"
                                   @keydown.enter.prevent="addNewListItemDirect()">
                        </div>
                    </div>
                </template>
            </div>

            <!-- Expanded Footer Actions -->
            <div x-show="isCreating" class="px-4 py-2.5 border-t border-slate-100/50 flex items-center justify-between bg-slate-900/5" x-cloak>
                <div class="flex items-center gap-2.5 relative" x-data="{ openColors: false, openRemind: false }">
                    <!-- Color Picker Button -->
                    <button type="button" 
                            @click="openColors = !openColors"
                            class="p-2 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-750 transition-colors cursor-pointer" 
                            title="Mudar cor">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                    </button>
                    <!-- Color Options Popup -->
                    <div x-show="openColors" 
                         @click.away="openColors = false" 
                         class="absolute bottom-full left-0 mb-2 p-2 bg-white border border-slate-200 shadow-lg rounded-lg flex gap-1.5 z-40 select-none"
                         x-transition x-cloak>
                        <template x-for="c in colorPalette">
                            <button type="button" 
                                    @click="newColor = c.class; openColors = false"
                                    class="w-6 h-6 rounded-full border border-slate-300 hover:scale-110 transition-transform cursor-pointer"
                                    :class="c.class"
                                    :title="c.name"></button>
                        </template>
                    </div>

                    <!-- Reminder Alarm Button -->
                    <button type="button" 
                            @click="openRemind = !openRemind" 
                            class="p-2 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-750 transition-colors cursor-pointer" 
                            title="Adicionar lembrete">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </button>
                    <!-- Reminder Alert settings popover -->
                    <div x-show="openRemind" 
                         @click.away="openRemind = false" 
                         class="absolute bottom-full left-10 mb-2 p-3 bg-white border border-slate-200 shadow-lg rounded-lg w-56 space-y-2 z-40 text-slate-700"
                         x-transition x-cloak>
                        <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Escolher Data e Hora</label>
                        <input type="datetime-local" x-model="newRemindAt" class="w-full text-xs border border-slate-200 p-1.5 rounded focus:outline-none">
                        <button type="button" @click="openRemind = false" class="w-full bg-blue-600 text-white font-bold text-xs py-1.5 rounded hover:bg-blue-750">Pronto</button>
                    </div>

                    <!-- Toggle Type Text/Checklist -->
                    <button type="button" 
                            @click="newType = (newType === 'text' ? 'list' : 'text')" 
                            class="p-2 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-750 transition-colors cursor-pointer" 
                            title="Alternar para Checklist">
                        <template x-if="newType === 'text'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                            </svg>
                        </template>
                        <template x-if="newType === 'list'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                            </svg>
                        </template>
                    </button>

                    <!-- Add Image Attachment -->
                    <label class="p-2 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-750 transition-colors cursor-pointer select-none" title="Adicionar imagem">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <input type="file" @change="handleNewImageUpload($event)" class="hidden" accept="image/*">
                    </label>
                </div>

                <div class="flex items-center gap-3">
                    <button type="button" 
                            @click="isCreating = false; resetNewStates();" 
                            class="px-3 py-1 text-slate-500 hover:text-slate-750 text-xs font-bold transition-colors cursor-pointer">
                        Cancelar
                    </button>
                    <button type="button" 
                            @click="saveNote()" 
                            class="px-3.5 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-[5px] text-xs font-bold shadow-sm transition-all cursor-pointer">
                        Criar
                    </button>
                </div>
            </div>

            <!-- Pre-expanded quick buttons (Keep style) -->
            <div x-show="!isCreating" class="absolute right-2 inset-y-0 flex items-center gap-1" x-cloak>
                <button type="button" @click="isCreating = true; newType = 'list'" class="p-2 hover:bg-slate-100 rounded-full text-slate-500" title="Nova lista">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                    </svg>
                </button>
                <label class="p-2 hover:bg-slate-100 rounded-full text-slate-500 cursor-pointer" title="Nova imagem">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <input type="file" @change="isCreating = true; handleNewImageUpload($event)" class="hidden" accept="image/*">
                </label>
            </div>

        </div>
    </div>

    <!-- Undo/Redo Floating Indicator -->
    <div x-show="history.length > 0" class="fixed bottom-6 left-6 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-lg shadow-xl flex items-center gap-3 text-xs" x-transition x-cloak>
        <span>Alteração realizada.</span>
        <button type="button" @click="undoLastAction()" class="text-blue-400 hover:text-blue-300 font-extrabold uppercase tracking-wider text-[10px]">Desfazer</button>
    </div>

    <!-- Pinned Notes Section -->
    <div x-show="hasPinnedNotes()" class="space-y-3" x-cloak>
        <h2 class="text-[10px] font-bold text-slate-450 uppercase tracking-widest flex items-center gap-1">
            📌 Fixadas
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
             @dragover.prevent>
            <template x-for="(note, index) in notes.filter(n => n.is_pinned && matchesSearch(n))" :key="note.id">
                <div :class="note.color" 
                     class="border rounded-lg shadow-xs hover:shadow-md transition-all duration-200 relative group flex flex-col justify-between select-none cursor-pointer overflow-hidden min-h-[140px]"
                     draggable="true"
                     @dragstart="dragStart(note, $event)"
                     @dragend="dragEnd($event)"
                     @drop="dragDrop(note, $event)"
                     @click="openEditModal(note, $event)">
                    
                    <!-- Attached Image Header -->
                    <template x-if="note.image_path">
                        <div class="w-full aspect-[2.5/1] bg-slate-100 border-b border-slate-200/40">
                            <img :src="'/storage/' + note.image_path" class="w-full h-full object-cover">
                        </div>
                    </template>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Title & Pin -->
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-black text-base tracking-tight line-clamp-1 text-slate-800" x-text="note.title || 'Sem título'"></h3>
                                <button type="button" 
                                        @click.stop="togglePin(note)" 
                                        class="text-slate-400 hover:text-slate-700 transition-colors focus:outline-none cursor-pointer"
                                        title="Desafixar nota">
                                    <svg class="w-5 h-5 text-indigo-650 fill-indigo-600 rotate-45" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Text Content -->
                            <template x-if="note.type === 'text'">
                                <p class="text-sm font-semibold leading-relaxed mt-2.5 whitespace-pre-line line-clamp-5 text-slate-700" x-html="renderFormattedText(note.content)"></p>
                            </template>

                            <!-- Checklist preview inside card -->
                            <template x-if="note.type === 'list'">
                                <div class="space-y-1.5 mt-2.5">
                                    <!-- Unchecked items -->
                                    <template x-for="item in (note.items || []).filter(i => !i.checked).slice(0, 4)" :key="item.id">
                                        <div class="flex items-center gap-1.5 text-sm">
                                            <input type="checkbox" @click.stop="toggleChecklistItem(note, item)" class="w-4 h-4 rounded border-slate-300">
                                            <span class="truncate font-semibold text-slate-700" x-text="item.text"></span>
                                        </div>
                                    </template>

                                    <!-- Checked items counter -->
                                    <template x-if="(note.items || []).filter(i => i.checked).length > 0">
                                        <div class="text-[10px] font-bold text-slate-450 uppercase pt-1">
                                            + <span x-text="(note.items || []).filter(i => i.checked).length"></span> item(s) concluído(s)
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Reminder Date Badge -->
                            <template x-if="note.remind_at">
                                <div class="inline-flex items-center gap-1 bg-slate-900/5 text-slate-550 border border-slate-900/10 px-2 py-0.5 rounded-full text-[9px] font-bold mt-3">
                                    ⏰ <span x-text="formatDate(note.remind_at)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Card Action Footer Buttons -->
                        <div class="flex items-center justify-between mt-4 pt-2 border-t border-slate-900/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <!-- Color list Card popup -->
                            <div class="relative" x-data="{ openColorsCard: false }">
                                <button type="button" 
                                        @click.stop="openColorsCard = !openColorsCard" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-slate-850 cursor-pointer"
                                        title="Alterar cor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                    </svg>
                                </button>
                                <div x-show="openColorsCard" 
                                     @click.away="openColorsCard = false" 
                                     class="absolute bottom-full left-0 mb-1.5 p-1.5 bg-white border border-slate-200 shadow-md rounded-lg flex gap-1 z-30"
                                     x-transition x-cloak>
                                    <template x-for="c in colorPalette">
                                        <button type="button" 
                                                @click.stop="changeNoteColor(note, c.class); openColorsCard = false"
                                                class="w-5 h-5 rounded-full border border-slate-300 hover:scale-115 transition-transform"
                                                :class="c.class"></button>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <!-- Archive Button -->
                                <button type="button" 
                                        @click.stop="toggleArchive(note)" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-slate-850 cursor-pointer"
                                        :title="note.is_archived ? 'Desarquivar' : 'Arquivar'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                    </svg>
                                </button>
                                
                                <!-- Delete Button -->
                                <button type="button" 
                                        @click.stop="deleteNote(note)" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-rose-700 cursor-pointer"
                                        title="Excluir nota">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>

    <!-- Others / General Notes Section -->
    <div class="space-y-3">
        <h2 x-show="hasPinnedNotes()" class="text-[10px] font-bold text-slate-455 uppercase tracking-widest" x-cloak>Outros</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4"
             @dragover.prevent>
            <template x-for="(note, index) in notes.filter(n => !n.is_pinned && matchesSearch(n))" :key="note.id">
                <div :class="note.color" 
                     class="border rounded-lg shadow-xs hover:shadow-md transition-all duration-200 relative group flex flex-col justify-between select-none cursor-pointer overflow-hidden min-h-[140px]"
                     draggable="true"
                     @dragstart="dragStart(note, $event)"
                     @dragend="dragEnd($event)"
                     @drop="dragDrop(note, $event)"
                     @click="openEditModal(note, $event)">
                    
                    <!-- Attached Image Header -->
                    <template x-if="note.image_path">
                        <div class="w-full aspect-[2.5/1] bg-slate-100 border-b border-slate-200/40">
                            <img :src="'/storage/' + note.image_path" class="w-full h-full object-cover">
                        </div>
                    </template>

                    <div class="p-4 flex-1 flex flex-col justify-between">
                        <div>
                            <!-- Title & Pin -->
                            <div class="flex items-start justify-between gap-2">
                                <h3 class="font-black text-base tracking-tight line-clamp-1 text-slate-800" x-text="note.title || 'Sem título'"></h3>
                                <button type="button" 
                                        @click.stop="togglePin(note)" 
                                        class="text-slate-400 hover:text-slate-700 transition-colors focus:outline-none cursor-pointer"
                                        title="Fixar nota">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                    </svg>
                                </button>
                            </div>

                            <!-- Text Content -->
                            <template x-if="note.type === 'text'">
                                <p class="text-sm font-semibold leading-relaxed mt-2.5 whitespace-pre-line line-clamp-5 text-slate-700" x-html="renderFormattedText(note.content)"></p>
                            </template>

                            <!-- Checklist preview inside card -->
                            <template x-if="note.type === 'list'">
                                <div class="space-y-1.5 mt-2.5">
                                    <!-- Unchecked items -->
                                    <template x-for="item in (note.items || []).filter(i => !i.checked).slice(0, 4)" :key="item.id">
                                        <div class="flex items-center gap-1.5 text-sm">
                                            <input type="checkbox" @click.stop="toggleChecklistItem(note, item)" class="w-4 h-4 rounded border-slate-300">
                                            <span class="truncate font-semibold text-slate-700" x-text="item.text"></span>
                                        </div>
                                    </template>

                                    <!-- Checked items counter -->
                                    <template x-if="(note.items || []).filter(i => i.checked).length > 0">
                                        <div class="text-[10px] font-bold text-slate-450 uppercase pt-1">
                                            + <span x-text="(note.items || []).filter(i => i.checked).length"></span> item(s) concluído(s)
                                        </div>
                                    </template>
                                </div>
                            </template>

                            <!-- Reminder Date Badge -->
                            <template x-if="note.remind_at">
                                <div class="inline-flex items-center gap-1 bg-slate-900/5 text-slate-550 border border-slate-900/10 px-2 py-0.5 rounded-full text-[9px] font-bold mt-3">
                                    ⏰ <span x-text="formatDate(note.remind_at)"></span>
                                </div>
                            </template>
                        </div>

                        <!-- Card Action Footer Buttons -->
                        <div class="flex items-center justify-between mt-4 pt-2 border-t border-slate-900/5 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <!-- Color list Card popup -->
                            <div class="relative" x-data="{ openColorsCard: false }">
                                <button type="button" 
                                        @click.stop="openColorsCard = !openColorsCard" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-slate-850 cursor-pointer"
                                        title="Alterar cor">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                                    </svg>
                                </button>
                                <div x-show="openColorsCard" 
                                     @click.away="openColorsCard = false" 
                                     class="absolute bottom-full left-0 mb-1.5 p-1.5 bg-white border border-slate-200 shadow-md rounded-lg flex gap-1 z-30"
                                     x-transition x-cloak>
                                    <template x-for="c in colorPalette">
                                        <button type="button" 
                                                @click.stop="changeNoteColor(note, c.class); openColorsCard = false"
                                                class="w-5 h-5 rounded-full border border-slate-300 hover:scale-115 transition-transform"
                                                :class="c.class"></button>
                                    </template>
                                </div>
                            </div>

                            <div class="flex items-center gap-1">
                                <!-- Archive Button -->
                                <button type="button" 
                                        @click.stop="toggleArchive(note)" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-slate-850 cursor-pointer"
                                        :title="note.is_archived ? 'Desarquivar' : 'Arquivar'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                                    </svg>
                                </button>
                                
                                <!-- Delete Button -->
                                <button type="button" 
                                        @click.stop="deleteNote(note)" 
                                        class="p-1 rounded hover:bg-slate-900/5 text-slate-500 hover:text-rose-700 cursor-pointer"
                                        title="Excluir nota">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="notes.filter(n => matchesSearch(n)).length === 0" class="col-span-full border border-dashed border-slate-200 bg-white p-12 text-center text-slate-400 rounded-lg font-semibold text-sm shadow-xs select-none">
            Nenhuma nota ou lembrete correspondente encontrado.
        </div>
    </div>

    <!-- Edit/Show Note Detail Modal -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm"
         style="z-index: 9999;"
         x-transition.opacity
         x-cloak>
        <div :class="activeNote.color" 
             class="border border-slate-250 shadow-2xl rounded-lg max-w-lg w-full overflow-hidden text-left relative" 
             @click.away="closeEditModal()">
            
            <!-- Attached Image Header (Inside Modal) -->
            <template x-if="activeNote.image_path">
                <div class="w-full aspect-[3/1] bg-slate-100 border-b border-slate-200 relative">
                    <img :src="'/storage/' + activeNote.image_path" class="w-full h-full object-cover">
                    <button type="button" 
                            @click="removeAttachedImage(activeNote)" 
                            class="absolute top-2 right-2 bg-slate-900/60 hover:bg-slate-900 text-white rounded-full p-1.5 transition-colors cursor-pointer text-xs"
                            title="Remover imagem">✕</button>
                </div>
            </template>

            <div class="p-5 space-y-4">
                <!-- Title & Pin -->
                <div class="flex items-center justify-between gap-4">
                    <input type="text" 
                           x-model="activeNote.title" 
                           placeholder="Título" 
                           class="w-full border-0 focus:ring-0 text-base font-black text-slate-800 placeholder-slate-450 focus:outline-none bg-transparent" />
                    
                    <button type="button" 
                            @click="togglePin(activeNote)" 
                            class="text-slate-400 hover:text-slate-700 transition-colors focus:outline-none cursor-pointer">
                        <svg class="w-5 h-5" :class="activeNote.is_pinned ? 'text-indigo-650 fill-indigo-600 scale-110 rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </button>
                </div>

                <!-- Content Area -->
                <div>
                    <!-- TEXT TYPE -->
                    <template x-if="activeNote.type === 'text'">
                        <div class="space-y-2">
                            <!-- Text Editor Toolbar -->
                            <div class="flex items-center gap-1.5 border-b border-slate-200/50 pb-2 text-[10px] font-bold text-slate-500 select-none">
                                <button type="button" @click="insertFormat('**', '**')" class="px-2 py-0.5 bg-slate-900/5 rounded hover:bg-slate-900/10 cursor-pointer">B</button>
                                <button type="button" @click="insertFormat('*', '*')" class="px-2 py-0.5 bg-slate-900/5 rounded hover:bg-slate-900/10 cursor-pointer">I</button>
                                <button type="button" @click="insertFormat('__', '__')" class="px-2 py-0.5 bg-slate-900/5 rounded hover:bg-slate-900/10 cursor-pointer">U</button>
                            </div>
                            
                            <textarea id="modalContentTextarea"
                                      x-model="activeNote.content"
                                      placeholder="Nota..."
                                      rows="6"
                                      class="w-full border-0 focus:ring-0 text-sm font-semibold text-slate-750 placeholder-slate-400 focus:outline-none resize-none bg-transparent"></textarea>
                        </div>
                    </template>

                    <!-- LIST CHECKLIST TYPE -->
                    <template x-if="activeNote.type === 'list'">
                        <div class="space-y-4">
                            <!-- Uncompleted list items -->
                            <div class="space-y-1.5" @dragover.prevent>
                                <template x-for="(item, idx) in (activeNote.items || []).filter(i => !i.checked)" :key="item.id">
                                    <div class="flex items-center gap-2"
                                         draggable="true"
                                         @dragstart="dragItemStart(item, $event)"
                                         @drop="dragItemDrop(item, $event)">
                                        <span class="text-slate-400 cursor-grab text-[10px]">☰</span>
                                        <input type="checkbox" @change="toggleChecklistItem(activeNote, item)" class="w-4 h-4 rounded border-slate-300">
                                        <input type="text" 
                                               x-model="item.text" 
                                               class="w-full border-0 focus:ring-0 text-sm font-semibold focus:outline-none bg-transparent p-0" />
                                        <button type="button" @click="removeListItem(activeNote, item.id)" class="text-slate-400 hover:text-slate-600 text-xs shrink-0 select-none">✕</button>
                                    </div>
                                </template>
                            </div>

                            <!-- Add new checklist item row -->
                            <div class="flex items-center gap-2 pt-2 border-t border-slate-900/5">
                                <span class="text-slate-400 font-extrabold text-sm select-none">+</span>
                                <input type="text" 
                                       x-model="modalNewItemText"
                                       placeholder="Adicionar item" 
                                       class="w-full border-0 focus:ring-0 text-sm font-semibold focus:outline-none bg-transparent p-0 placeholder-slate-400"
                                       @keydown.enter.prevent="addListItemToActiveNote()">
                            </div>

                            <!-- Completed checklist items -->
                            <div x-data="{ expanded: false }" class="space-y-2">
                                <button type="button" 
                                        @click="expanded = !expanded" 
                                        class="flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-700 focus:outline-none">
                                    <span x-text="expanded ? '▼' : '▶'"></span>
                                    <span x-text="(activeNote.items || []).filter(i => i.checked).length + ' item(s) concluído(s)'"></span>
                                </button>
                                
                                <div x-show="expanded" class="space-y-1.5 pl-4" x-collapse>
                                    <template x-for="item in (activeNote.items || []).filter(i => i.checked)" :key="item.id">
                                        <div class="flex items-center gap-2 opacity-60">
                                            <input type="checkbox" checked @change="toggleChecklistItem(activeNote, item)" class="w-4 h-4 rounded border-slate-300">
                                            <span class="text-sm font-semibold text-slate-700 line-through truncate" x-text="item.text"></span>
                                            <button type="button" @click="removeListItem(activeNote, item.id)" class="text-slate-400 hover:text-slate-650 text-xs shrink-0 select-none">✕</button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Reminder Alert indicator in Modal -->
                <template x-if="activeNote.remind_at">
                    <div class="inline-flex items-center gap-1.5 bg-slate-900/5 border border-slate-900/10 px-2.5 py-1 rounded-full text-xs font-bold mt-1 text-slate-600">
                        ⏰ Lembrete: <span x-text="formatDate(activeNote.remind_at)"></span>
                        <button type="button" @click="activeNote.remind_at = null" class="text-slate-400 hover:text-slate-700 font-extrabold text-[10px] pl-1 select-none">✕</button>
                    </div>
                </template>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between border-t border-slate-900/5 pt-3 select-none">
                    <div class="flex items-center gap-2 relative" x-data="{ openColorsModal: false, openRemindModal: false }">
                        <!-- Color picker button -->
                        <button type="button" 
                                @click="openColorsModal = !openColorsModal"
                                class="p-1.5 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-805 cursor-pointer" 
                                title="Mudar cor">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                            </svg>
                        </button>
                        <div x-show="openColorsModal" 
                             @click.away="openColorsModal = false" 
                             class="absolute bottom-full left-0 mb-2 p-2 bg-white border border-slate-200 shadow-lg rounded-lg flex gap-1.5 z-40 select-none"
                             x-transition x-cloak>
                            <template x-for="c in colorPalette">
                                <button type="button" 
                                        @click="changeNoteColor(activeNote, c.class); openColorsModal = false"
                                        class="w-6 h-6 rounded-full border border-slate-300 hover:scale-110 transition-transform cursor-pointer"
                                        :class="c.class"></button>
                            </template>
                        </div>

                        <!-- Date Reminder Button -->
                        <button type="button" 
                                @click="openRemindModal = !openRemindModal"
                                class="p-1.5 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-805 cursor-pointer" 
                                title="Editar lembrete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </button>
                        <div x-show="openRemindModal" 
                             @click.away="openRemindModal = false" 
                             class="absolute bottom-full left-10 mb-2 p-3 bg-white border border-slate-200 shadow-lg rounded-lg w-56 space-y-2 z-40 text-slate-700"
                             x-transition x-cloak>
                            <label class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Alterar Data e Hora</label>
                            <input type="datetime-local" x-model="activeNote.remind_at" class="w-full text-xs border border-slate-200 p-1.5 rounded focus:outline-none">
                            <button type="button" @click="openRemindModal = false" class="w-full bg-blue-600 text-white font-bold text-xs py-1.5 rounded hover:bg-blue-750">Pronto</button>
                        </div>

                        <!-- Upload image attachment -->
                        <label class="p-1.5 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-805 cursor-pointer select-none" title="Alterar imagem">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <input type="file" @change="handleActiveImageUpload($event)" class="hidden" accept="image/*">
                        </label>

                        <!-- Archive toggle -->
                        <button type="button" 
                                @click="toggleArchive(activeNote)"
                                class="p-1.5 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-slate-805 cursor-pointer" 
                                :title="activeNote.is_archived ? 'Desarquivar' : 'Arquivar'">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                            </svg>
                        </button>

                        <!-- Trash -->
                        <button type="button" 
                                @click="deleteNote(activeNote); closeEditModal()" 
                                class="p-1.5 rounded-full hover:bg-slate-900/5 text-slate-500 hover:text-rose-700 cursor-pointer" 
                                title="Excluir nota">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" 
                                @click="closeEditModal()" 
                                class="px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs uppercase tracking-wider rounded-[5px] transition-colors cursor-pointer shadow-sm">
                            Fechar
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<script>
    function keepNotesManager() {
        return {
            notes: {!! json_encode($reminders->map(function($r) {
                return [
                    'id' => $r->id,
                    'title' => $r->title ?? '',
                    'content' => $r->content ?? '',
                    'type' => $r->type,
                    'items' => $r->items ?? [],
                    'color' => $r->color,
                    'is_pinned' => (bool)$r->is_pinned,
                    'is_archived' => (bool)$r->is_archived,
                    'remind_at' => $r->remind_at ? $r->remind_at->format('Y-m-d\TH:i') : null,
                    'image_path' => $r->image_path
                ];
            })) !!},

            searchQuery: '',
            isCreating: false,
            showArchivedOnly: false,

            // Active reminder alerts
            activeAlerts: [],

            // Creator states
            newTitle: '',
            newContent: '',
            newIsPinned: false,
            newColor: 'bg-white border-slate-200 text-slate-700',
            newType: 'text',
            newListItems: [], // {id, text, checked}
            newItemText: '',
            newRemindAt: '',
            newImageFile: null,
            newImagePreview: null,

            // Drag states
            draggedNote: null,
            draggedItem: null,

            // History / Undo stack
            history: [], // [{type: 'delete|archive|color|pin', note: {}, originalState: {}}]

            // Edit states
            editModalOpen: false,
            activeNote: {},
            originalNoteStr: '',
            modalNewItemText: '',

            init() {
                // Strictly cast note items checked values to boolean on mount
                this.notes.forEach(note => {
                    if (note.items) {
                        note.items = note.items.map(i => {
                            i.checked = (i.checked === true || i.checked === 1 || i.checked === '1' || i.checked === 'true');
                            return i;
                        });
                    }
                    note.notified = false;
                });

                // Request Notification Permission
                if (window.Notification && Notification.permission === 'default') {
                    Notification.requestPermission();
                }

                // Check reminders every 10 seconds
                setInterval(() => {
                    const now = new Date();
                    this.notes.forEach(note => {
                        if (note.remind_at && !note.notified && !note.is_archived) {
                            const remindTime = new Date(note.remind_at);
                            if (remindTime <= now) {
                                note.notified = true;
                                this.triggerReminderAlert(note);
                            }
                        }
                    });
                }, 10000);
            },

            triggerReminderAlert(note) {
                // Play synthesized audio chime (D5 Bell)
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.type = 'sine';
                    osc.frequency.setValueAtTime(587.33, ctx.currentTime);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    osc.start();
                    osc.stop(ctx.currentTime + 0.3);
                } catch (e) {
                    console.warn('AudioContext blocked or unsupported:', e);
                }

                // Push alert to screen stack
                this.activeAlerts.push({
                    id: note.id,
                    title: note.title,
                    content: note.content || 'Hora do seu lembrete definido.'
                });

                // Desktop Notification
                if (window.Notification && Notification.permission === 'granted') {
                    new Notification("Lembrete: " + (note.title || 'Nota'), {
                        body: note.content || 'Prazo ou alarme definido atingido.',
                        icon: '/favicon.ico'
                    });
                }
            },

            dismissAlert(alertId) {
                this.activeAlerts = this.activeAlerts.filter(a => a.id !== alertId);
            },

            deactivateReminder(noteId) {
                const note = this.notes.find(n => n.id === noteId);
                if (note) {
                    note.remind_at = null;
                    this.saveActiveNoteData(note);
                }
                this.dismissAlert(noteId);
            },

            // Vivid colors matching Google Keep
            colorPalette: [
                { name: 'Branco', class: 'bg-white border-slate-220 text-slate-750' },
                { name: 'Coral / Vermelho', class: 'bg-[#f28b82] border-[#f28b82] text-slate-900' },
                { name: 'Laranja', class: 'bg-[#fbbc04] border-[#fbbc04] text-slate-900' },
                { name: 'Amarelo', class: 'bg-[#fff475] border-[#fff475] text-slate-900' },
                { name: 'Verde / Lima', class: 'bg-[#ccff90] border-[#ccff90] text-slate-900' },
                { name: 'Ciano', class: 'bg-[#a7ffeb] border-[#a7ffeb] text-slate-900' },
                { name: 'Azul', class: 'bg-[#cbf0f8] border-[#cbf0f8] text-slate-900' },
                { name: 'Azul Escuro', class: 'bg-[#aecbfa] border-[#aecbfa] text-slate-900' },
                { name: 'Roxo', class: 'bg-[#d7aefb] border-[#d7aefb] text-slate-900' },
                { name: 'Rosa', class: 'bg-[#fdcfe8] border-[#fdcfe8] text-slate-900' },
                { name: 'Marrom', class: 'bg-[#e6c9a8] border-[#e6c9a8] text-slate-900' },
                { name: 'Cinza', class: 'bg-[#e8eaed] border-[#e8eaed] text-slate-900' },
            ],

            resetNewStates() {
                this.newTitle = '';
                this.newContent = '';
                this.newIsPinned = false;
                this.newColor = 'bg-white border-slate-200 text-slate-700';
                this.newType = 'text';
                this.newListItems = [];
                this.newItemText = '';
                this.newRemindAt = '';
                this.newImageFile = null;
                this.newImagePreview = null;
                this.isCreating = false;
            },

            // Create image upload preview
            handleNewImageUpload(e) {
                const file = e.target.files[0];
                if (!file) return;
                this.newImageFile = file;
                this.newImagePreview = URL.createObjectURL(file);
            },

            removeNewImage() {
                this.newImageFile = null;
                this.newImagePreview = null;
            },

            // Format date string nicely
            formatDate(dateStr) {
                if (!dateStr) return '';
                const date = new Date(dateStr);
                return date.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' }) + ' ' + date.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            },

            // Bold/Italic formatting utility
            insertFormat(start, end) {
                const textarea = document.getElementById('modalContentTextarea');
                if (!textarea) return;

                const startPos = textarea.selectionStart;
                const endPos = textarea.selectionEnd;
                const text = textarea.value;
                const selectedText = text.substring(startPos, endPos);
                
                const replacement = start + selectedText + end;
                this.activeNote.content = text.substring(0, startPos) + replacement + text.substring(endPos);
                
                textarea.focus();
                this.$nextTick(() => {
                    textarea.setSelectionRange(startPos + start.length, startPos + start.length + selectedText.length);
                });
            },

            renderFormattedText(content) {
                if (!content) return '';
                // Simple bold/italic regex parsing
                return content
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/__(.*?)__/g, '<u>$1</u>');
            },

            // Checklist lists handlers
            addNewListItemDirect() {
                if (!this.newItemText) return;
                this.newListItems.push({
                    id: Date.now() + Math.random(),
                    text: this.newItemText,
                    checked: false
                });
                this.newItemText = '';
            },

            addNewListItemRow(idx) {
                this.newListItems.splice(idx + 1, 0, {
                    id: Date.now() + Math.random(),
                    text: '',
                    checked: false
                });
            },

            removeNewListItemRow(idx, text) {
                if (!text && this.newListItems.length > 0) {
                    this.newListItems.splice(idx, 1);
                }
            },

            removeNewListItem(idx) {
                this.newListItems.splice(idx, 1);
            },

            // Active note checklist actions
            addListItemToActiveNote() {
                if (!this.modalNewItemText) return;
                if (!this.activeNote.items) this.activeNote.items = [];
                
                this.activeNote.items.push({
                    id: Date.now() + Math.random(),
                    text: this.modalNewItemText,
                    checked: false
                });
                this.modalNewItemText = '';
            },

            removeListItem(note, itemId) {
                note.items = note.items.filter(i => i.id !== itemId);
            },

            toggleChecklistItem(note, item) {
                item.checked = !item.checked;
                // Save immediately
                this.saveActiveNoteData(note);
            },

            // Drag checklist rows
            dragItemStart(item, event) {
                this.draggedItem = item;
            },

            dragItemDrop(item, event) {
                if (!this.draggedItem || this.draggedItem.id === item.id) return;
                
                const items = this.activeNote.items;
                const idxFrom = items.findIndex(i => i.id === this.draggedItem.id);
                const idxTo = items.findIndex(i => i.id === item.id);
                
                items.splice(idxFrom, 1);
                items.splice(idxTo, 0, this.draggedItem);
                
                this.draggedItem = null;
                this.saveActiveNoteData(this.activeNote);
            },

            // Drag notes
            dragStart(note, event) {
                this.draggedNote = note;
                event.target.style.opacity = '0.5';
            },

            dragEnd(event) {
                event.target.style.opacity = '1';
            },

            dragDrop(note, event) {
                if (!this.draggedNote || this.draggedNote.id === note.id) return;
                
                const idxFrom = this.notes.findIndex(n => n.id === this.draggedNote.id);
                const idxTo = this.notes.findIndex(n => n.id === note.id);
                
                // Swap
                this.notes.splice(idxFrom, 1);
                this.notes.splice(idxTo, 0, this.draggedNote);
                
                // Send reorder list to backend
                const orders = this.notes.map((n, index) => {
                    return { id: n.id, sort_order: index + 1 };
                });

                fetch('{{ url("freelas/utilidades/lembretes/reorder") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ orders: orders })
                })
                .then(res => res.json())
                .catch(err => console.error('Erro ao salvar ordenação:', err));

                this.draggedNote = null;
            },

            hasPinnedNotes() {
                return this.notes.some(n => n.is_pinned && this.matchesSearch(n) && n.is_archived === this.showArchivedOnly);
            },

            matchesSearch(note) {
                if (note.is_archived !== this.showArchivedOnly) return false;
                
                if (this.searchQuery) {
                    const q = this.searchQuery.toLowerCase();
                    const t = (note.title || '').toLowerCase();
                    const c = (note.content || '').toLowerCase();
                    const itemsText = (note.items || []).map(i => i.text.toLowerCase()).join(' ');
                    return t.includes(q) || c.includes(q) || itemsText.includes(q);
                }
                return true;
            },

            saveNote() {
                if (!this.newTitle && !this.newContent && this.newListItems.length === 0) {
                    this.isCreating = false;
                    return;
                }

                // If checklist type, map newListItems
                const formData = new FormData();
                formData.append('title', this.newTitle || '');
                formData.append('content', this.newContent || '');
                formData.append('type', this.newType);
                formData.append('color', this.newColor);
                formData.append('is_pinned', this.newIsPinned ? 1 : 0);
                
                if (this.newRemindAt) {
                    formData.append('remind_at', this.newRemindAt);
                }

                if (this.newType === 'list') {
                    this.newListItems.forEach((item, idx) => {
                        formData.append(`items[${idx}][id]`, item.id);
                        formData.append(`items[${idx}][text]`, item.text);
                        formData.append(`items[${idx}][checked]`, item.checked ? 1 : 0);
                    });
                }

                if (this.newImageFile) {
                    formData.append('image', this.newImageFile);
                }

                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                fetch('{{ route("lembretes.store") }}', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.notes.unshift({
                            id: data.reminder.id,
                            title: data.reminder.title || '',
                            content: data.reminder.content || '',
                            type: data.reminder.type,
                            items: (data.reminder.items || []).map(i => {
                                i.checked = (i.checked === true || i.checked === 1 || i.checked === '1' || i.checked === 'true');
                                return i;
                            }),
                            color: data.reminder.color,
                            is_pinned: !!data.reminder.is_pinned,
                            is_archived: !!data.reminder.is_archived,
                            remind_at: data.reminder.remind_at,
                            image_path: data.reminder.image_path
                        });
                        this.resetNewStates();
                    }
                })
                .catch(err => console.error('Erro ao salvar nota:', err));
            },

            // Modal/Active Note Upload Image Attach
            handleActiveImageUpload(e) {
                const file = e.target.files[0];
                if (!file) return;

                const formData = new FormData();
                formData.append('image', file);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PUT');

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${this.activeNote.id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.activeNote.image_path = data.reminder.image_path;
                    }
                })
                .catch(err => console.error('Erro ao enviar imagem da nota ativa:', err));
            },

            removeAttachedImage(note) {
                const formData = new FormData();
                formData.append('remove_image', 1);
                formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                formData.append('_method', 'PUT');

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        note.image_path = null;
                    }
                })
                .catch(err => console.error('Erro ao remover imagem da nota:', err));
            },

            togglePin(note) {
                note.is_pinned = !note.is_pinned;

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}/pin`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        note.is_pinned = data.is_pinned;
                    }
                })
                .catch(err => {
                    note.is_pinned = !note.is_pinned;
                });
            },

            toggleArchive(note) {
                const originalState = note.is_archived;
                note.is_archived = !note.is_archived;

                // Push to undo stack
                this.history.push({
                    type: 'archive',
                    note: note,
                    originalState: { is_archived: originalState }
                });

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}/archive`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        note.is_archived = data.is_archived;
                        if (this.editModalOpen && this.activeNote.id === note.id) {
                            this.editModalOpen = false;
                        }
                    }
                })
                .catch(err => {
                    note.is_archived = originalState;
                });
            },

            changeNoteColor(note, colorClass) {
                const oldColor = note.color;
                note.color = colorClass;

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}/color`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ color: colorClass })
                })
                .then(res => res.json())
                .catch(err => {
                    note.color = oldColor;
                });
            },

            deleteNote(note) {
                const idx = this.notes.findIndex(n => n.id === note.id);
                if (idx === -1) return;
                
                const backupNote = this.notes[idx];
                this.notes.splice(idx, 1);

                // Push to undo stack
                this.history.push({
                    type: 'delete',
                    note: backupNote,
                    originalIndex: idx
                });

                fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .catch(err => console.error('Erro ao excluir nota:', err));
            },

            undoLastAction() {
                if (this.history.length === 0) return;
                const action = this.history.pop();
                
                if (action.type === 'delete') {
                    // Re-insert note to database
                    const formData = new FormData();
                    formData.append('title', action.note.title || '');
                    formData.append('content', action.note.content || '');
                    formData.append('type', action.note.type);
                    formData.append('color', action.note.color);
                    formData.append('is_pinned', action.note.is_pinned ? 1 : 0);
                    formData.append('is_archived', action.note.is_archived ? 1 : 0);
                    
                    if (action.note.remind_at) {
                        formData.append('remind_at', action.note.remind_at);
                    }

                    if (action.note.type === 'list') {
                        (action.note.items || []).forEach((item, idx) => {
                            formData.append(`items[${idx}][id]`, item.id);
                            formData.append(`items[${idx}][text]`, item.text);
                            formData.append(`items[${idx}][checked]`, item.checked ? 1 : 0);
                        });
                    }

                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    fetch('{{ route("lembretes.store") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            action.note.id = data.reminder.id;
                            this.notes.splice(action.originalIndex, 0, action.note);
                        }
                    });
                } else if (action.type === 'archive') {
                    action.note.is_archived = action.originalState.is_archived;
                    fetch(`{{ url("freelas/utilidades/lembretes") }}/${action.note.id}/archive`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                }
            },

            openEditModal(note, event) {
                if (event.target.closest('button') || event.target.closest('label') || event.target.closest('input[type="checkbox"]')) return;

                this.activeNote = note;
                this.originalNoteStr = JSON.stringify(note);
                this.editModalOpen = true;
            },

            closeEditModal() {
                if (!this.editModalOpen) return;
                this.saveActiveNoteData(this.activeNote);
                this.editModalOpen = false;
            },

            saveActiveNoteData(note) {
                const currentStr = JSON.stringify(note);
                if (this.originalNoteStr !== currentStr) {
                    fetch(`{{ url("freelas/utilidades/lembretes") }}/${note.id}`, {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            title: note.title,
                            content: note.content,
                            type: note.type,
                            items: note.items,
                            remind_at: note.remind_at
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.originalNoteStr = JSON.stringify(note);
                    })
                    .catch(err => console.error('Erro ao atualizar nota:', err));
                }
            }
        }
    }
</script>
@endsection
