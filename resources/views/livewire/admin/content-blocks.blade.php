<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Content Blocks') }}</h1>
            </div>
            @unless($showForm)
                <button wire:click="openCreateForm"
                    class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('Nieuw blok') }}
                </button>
            @endunless
        </div>

        {{-- Status flash --}}
        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- CREATE/EDIT FORM --}}
        @if($showForm)
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mb-6">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                <span>{{ $editingId ? __('Blok bewerken') : __('Nieuw blok') }}</span>
                <button wire:click="cancelForm" class="text-xs text-zinc-400 border border-zinc-700 px-2 py-1 hover:text-accent hover:border-accent transition">
                    {{ __('Annuleren') }}
                </button>
            </div>
            <div class="p-4 space-y-6">
                <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">

                    {{-- Active toggle + Placement --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_active" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm font-medium text-white">{{ __('Actief') }}</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <span class="text-xs font-medium text-zinc-500">{{ __('Positie:') }}</span>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="placement" value="above_episodes" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm text-white">{{ __('Boven afleveringen') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio" wire:model="placement" value="below_episodes" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm text-white">{{ __('Onder afleveringen') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Text content --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Pre-titel') }}</label>
                            <input type="text" wire:model="pre_title" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Optionele pre-titel">
                            @error('pre_title') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Titel') }}</label>
                            <input type="text" wire:model="title" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Optionele titel">
                            @error('title') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div wire:ignore
                         x-data="{
                             quill: null,
                             init() {
                                 this.quill = new Quill(this.$refs.editor, {
                                     theme: 'snow',
                                     placeholder: 'Optionele tekst...',
                                     modules: {
                                         toolbar: [
                                             [{ 'header': [2, 3, false] }],
                                             ['bold', 'italic', 'underline'],
                                             [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                             ['clean']
                                         ]
                                     }
                                 });
                                 // Set initial content
                                 const initial = @this.get('text');
                                 if (initial) {
                                     this.quill.root.innerHTML = initial;
                                 }
                                 // Sync changes to Livewire
                                 this.quill.on('text-change', () => {
                                     const html = this.quill.root.innerHTML;
                                     @this.set('text', html === '<p><br></p>' ? '' : html);
                                 });
                             }
                         }"
                    >
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Tekst') }}</label>
                        <div x-ref="editor" class="quill-editor-dark"></div>
                        @error('text') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Media section --}}
                    <div class="rounded-sm bg-zinc-800/50 border border-zinc-700 p-4 space-y-4">
                        <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">{{ __('Media (optioneel)') }}</h3>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Media type') }}</label>
                            <select wire:model.live="media_type" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                <option value="">{{ __('Geen media') }}</option>
                                <option value="image">{{ __('Afbeelding') }}</option>
                                <option value="video">{{ __('Video') }}</option>
                                <option value="youtube">{{ __('YouTube') }}</option>
                            </select>
                        </div>

                        @if($media_type === 'image')
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Afbeelding') }}</label>
                                <input type="file" wire:model="image_upload" accept="image/*" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                                @error('image_upload') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                @if($image_upload)
                                    <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                        <img src="{{ $image_upload->temporaryUrl() }}" alt="Preview" class="h-32 object-cover rounded-sm">
                                    </div>
                                @elseif($existing_image_path)
                                    <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                        <img src="{{ Storage::url($existing_image_path) }}" alt="Huidig" class="h-32 object-cover rounded-sm">
                                        <p class="text-xs text-zinc-500 mt-1">{{ __('Huidige afbeelding. Upload een nieuwe om te vervangen.') }}</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($media_type === 'video')
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Video') }}</label>
                                <input type="file" wire:model="video_upload" accept="video/mp4,video/webm" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                                @error('video_upload') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                @if($existing_video_path)
                                    <p class="text-xs text-zinc-500 mt-1">{{ __('Huidige video is ingesteld. Upload een nieuwe om te vervangen.') }}</p>
                                @endif
                            </div>
                        @elseif($media_type === 'youtube')
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('YouTube URL') }}</label>
                                <input type="url" wire:model="youtube_url" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="https://www.youtube.com/watch?v=...">
                                @error('youtube_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>

                    {{-- Button section --}}
                    <div class="rounded-sm bg-zinc-800/50 border border-zinc-700 p-4 space-y-4">
                        <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">{{ __('Knop (optioneel)') }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Knop label') }}</label>
                                <input type="text" wire:model="button_label" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Lees meer">
                                @error('button_label') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Knop URL') }}</label>
                                <input type="url" wire:model="button_url" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="https://...">
                                @error('button_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="button_new_tab" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm text-zinc-400">{{ __('Open in nieuw tabblad') }}</span>
                            </label>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Opslaan') : __('Aanmaken') }}</span>
                            <span wire:loading wire:target="save">{{ __('Bezig...') }}</span>
                        </button>
                        <button type="button" wire:click="cancelForm" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white">
                            {{ __('Annuleren') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- BLOCKS LIST --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overzicht</p>
                <h3 class="text-lg font-semibold uppercase tracking-wider">{{ __('Content Blocks') }}</h3>
            </div>

            @if($blocks->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('Nog geen content blocks.') }}</div>
            @else
                <div class="overflow-x-auto"
                    x-data
                    x-init="
                        Sortable.create($el.querySelector('#blocks-sortable'), {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd() {
                                const ids = [...$el.querySelectorAll('#blocks-sortable tr[data-id]')].map(row => parseInt(row.dataset.id));
                                $wire.updateOrder(ids);
                            }
                        })
                    "
                >
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="w-10 px-3 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Titel') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Media') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Positie') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Acties') }}</th>
                            </tr>
                        </thead>
                        <tbody id="blocks-sortable" class="divide-y divide-zinc-800">
                            @foreach($blocks as $block)
                                <tr class="hover:bg-zinc-800/50 transition {{ !$block->is_active ? 'opacity-50' : '' }}" data-id="{{ $block->id }}" wire:key="block-{{ $block->id }}">
                                    <td class="px-3 py-4 drag-handle cursor-grab active:cursor-grabbing text-zinc-600 hover:text-zinc-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-white">{{ $block->title ?? __('(geen titel)') }}</div>
                                        @if($block->pre_title)
                                            <div class="text-xs text-zinc-500">{{ $block->pre_title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-400">
                                        @if($block->media_type === 'image')
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                {{ __('Afbeelding') }}
                                            </span>
                                        @elseif($block->media_type === 'video')
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                                {{ __('Video') }}
                                            </span>
                                        @elseif($block->media_type === 'youtube')
                                            <span class="inline-flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                {{ __('YouTube') }}
                                            </span>
                                        @else
                                            <span class="text-zinc-600">{{ __('Geen') }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="text-zinc-400">
                                            {{ $block->placement === 'above_episodes' ? __('Boven afleveringen') : __('Onder afleveringen') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="toggleActive({{ $block->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $block->is_active ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                            {{ $block->is_active ? __('Actief') : __('Inactief') }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button wire:click="openEditForm({{ $block->id }})"
                                            class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                            {{ __('Bewerken') }}
                                        </button>
                                        <button wire:click="delete({{ $block->id }})"
                                            wire:confirm="{{ __('Content block permanent verwijderen?') }}"
                                            class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition">
                                            {{ __('Verwijderen') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

</div>
