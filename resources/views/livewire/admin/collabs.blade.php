<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Collabs') }}</h1>
            </div>
            @unless($showForm)
                <button wire:click="openCreateForm"
                    class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('Nieuwe collab') }}
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
                <span>{{ $editingId ? __('Collab bewerken') : __('Nieuwe collab') }}</span>
                <button wire:click="cancelForm" class="text-xs text-zinc-400 border border-zinc-700 px-2 py-1 hover:text-accent hover:border-accent transition">
                    {{ __('Annuleren') }}
                </button>
            </div>
            <div class="p-4 space-y-6">
                <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">

                    {{-- Status toggles --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_published" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm font-medium text-white">{{ __('Gepubliceerd') }}</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="is_visible" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm font-medium text-white">{{ __('Zichtbaar op website') }}</span>
                        </label>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model.live="show_on_homepage" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm font-medium text-white">{{ __('Toon logo op homepage') }}</span>
                        </label>
                    </div>

                    {{-- Title --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Titel') }} *</label>
                        <input type="text" wire:model="title" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Collab titel">
                        @error('title') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Featured image --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Afbeelding') }}</label>
                            <input type="file" wire:model="featured_image" accept="image/*" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                            @error('featured_image') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            @if($featured_image)
                                <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                    <img src="{{ $featured_image->temporaryUrl() }}" alt="Preview" class="h-32 object-cover rounded-sm">
                                </div>
                            @elseif($existing_image_path)
                                <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                    <img src="{{ Storage::url($existing_image_path) }}" alt="Huidig" class="h-32 object-cover rounded-sm">
                                    <p class="text-xs text-zinc-500 mt-1">{{ __('Huidige afbeelding.') }}</p>
                                </div>
                            @endif
                        </div>

                        {{-- Logo image --}}
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Logo') }}</label>
                            <input type="file" wire:model="logo_image" accept="image/*" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                            @error('logo_image') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            @if($logo_image)
                                <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                    <img src="{{ $logo_image->temporaryUrl() }}" alt="Preview" class="h-16 object-contain rounded-sm">
                                </div>
                            @elseif($existing_logo_path)
                                <div class="mt-2 rounded-sm border border-zinc-700 p-2">
                                    <img src="{{ Storage::url($existing_logo_path) }}" alt="Huidig" class="h-16 object-contain rounded-sm">
                                    <p class="text-xs text-zinc-500 mt-1">{{ __('Huidig logo.') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Video (Episode selector) --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Video (selecteer uit afleveringen)') }}</label>
                        <select wire:model="episode_id" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            <option value="">{{ __('Geen video') }}</option>
                            @foreach($episodes as $episode)
                                <option value="{{ $episode->id }}">{{ $episode->title }}</option>
                            @endforeach
                        </select>
                        @error('episode_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Character selector --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Personage') }}</label>
                        <div class="flex items-center gap-3">
                            @if($character_id)
                                @php $selectedChar = $characters->firstWhere('id', $character_id); @endphp
                                @if($selectedChar)
                                    <div class="flex items-center gap-2 bg-zinc-800 border border-zinc-700 px-3 py-2 rounded-sm">
                                        @if($selectedChar->profile_image_path)
                                            <img src="{{ Storage::url($selectedChar->profile_image_path) }}" alt="" class="w-8 h-8 rounded-full object-cover">
                                        @endif
                                        <span class="text-sm text-white">{{ $selectedChar->full_name }}</span>
                                        <button type="button" wire:click="selectCharacter(null)" class="text-zinc-400 hover:text-red-400 ml-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                @endif
                            @else
                                <button type="button" wire:click="$set('showCharacterSelector', true)"
                                    class="inline-flex items-center border border-zinc-700 text-zinc-400 px-3 py-2 text-sm hover:text-accent hover:border-accent transition rounded-sm">
                                    {{ __('Selecteer personage') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Character selector popup --}}
                    @if($showCharacterSelector)
                        <div class="rounded-sm bg-zinc-800/50 border border-zinc-700 p-4">
                            <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-3">{{ __('Kies een personage') }}</h2>
                            <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3 max-h-64 overflow-y-auto">
                                @foreach($characters as $char)
                                    <button type="button" wire:click="selectCharacter({{ $char->id }})"
                                        class="flex flex-col items-center gap-1 p-2 rounded-sm border border-zinc-700 hover:border-accent hover:bg-zinc-700/50 transition">
                                        @if($char->profile_image_path)
                                            <img src="{{ Storage::url($char->profile_image_path) }}" alt="" class="w-12 h-12 rounded-full object-cover">
                                        @else
                                            <div class="w-12 h-12 rounded-full bg-zinc-700 flex items-center justify-center text-zinc-500 text-lg font-bold">{{ substr($char->first_name, 0, 1) }}</div>
                                        @endif
                                        <span class="text-xs text-zinc-300 text-center truncate w-full">{{ $char->first_name }}</span>
                                    </button>
                                @endforeach
                            </div>
                            <button type="button" wire:click="$set('showCharacterSelector', false)" class="mt-3 text-xs text-zinc-400 hover:text-accent transition">{{ __('Annuleren') }}</button>
                        </div>
                    @endif

                    {{-- Content (WYSIWYG) --}}
                    <div wire:ignore
                         x-data="{
                             quill: null,
                             init() {
                                 this.quill = new Quill(this.$refs.collabEditor, {
                                     theme: 'snow',
                                     placeholder: 'Collab inhoud...',
                                     modules: {
                                         toolbar: [
                                             [{ 'header': [2, 3, false] }],
                                             ['bold', 'italic', 'underline'],
                                             [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                                             [{ 'align': [] }],
                                             ['link'],
                                             ['clean']
                                         ]
                                     }
                                 });
                                 const initial = @this.get('content');
                                 if (initial) {
                                     this.quill.root.innerHTML = initial;
                                 }
                                 this.quill.on('text-change', () => {
                                     const html = this.quill.root.innerHTML;
                                     @this.set('content', html === '<p><br></p>' ? '' : html);
                                 });
                             }
                         }"
                    >
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Inhoud') }}</label>
                        <div x-ref="collabEditor" class="quill-editor-dark"></div>
                        @error('content') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Links section --}}
                    <div class="rounded-sm bg-zinc-800/50 border border-zinc-700 p-4 space-y-4">
                        <h2 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider">{{ __('Links (optioneel)') }}</h2>

                        {{-- Link 1 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Link 1 label') }}</label>
                                <input type="text" wire:model="link1_label" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Bekijk website">
                                @error('link1_label') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Link 1 URL') }}</label>
                                <input type="url" wire:model="link1_url" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="https://...">
                                @error('link1_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="link1_new_tab" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm text-zinc-400">{{ __('Open in nieuw tabblad') }}</span>
                        </label>

                        <div class="border-t border-zinc-700 my-3"></div>

                        {{-- Link 2 --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Link 2 label') }}</label>
                                <input type="text" wire:model="link2_label" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Meer info">
                                @error('link2_label') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Link 2 URL') }}</label>
                                <input type="url" wire:model="link2_url" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="https://...">
                                @error('link2_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" wire:model="link2_new_tab" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-sm text-zinc-400">{{ __('Open in nieuw tabblad') }}</span>
                        </label>
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

        {{-- COLLABS LIST --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overzicht</p>
                <h2 class="text-lg font-semibold uppercase tracking-wider">{{ __('Collabs') }}</h2>
            </div>

            @if($collabs->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('Nog geen collabs.') }}</div>
            @else
                <div class="overflow-x-auto"
                    x-data
                    x-init="
                        Sortable.create($el.querySelector('#collabs-sortable'), {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd() {
                                const ids = [...$el.querySelectorAll('#collabs-sortable tr[data-id]')].map(row => parseInt(row.dataset.id));
                                $wire.updateOrder(ids);
                            }
                        })
                    "
                >
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="w-10 px-3 py-3"></th>
                                <th class="w-16 px-3 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Titel') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Personage') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Homepage') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Zichtbaar') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Acties') }}</th>
                            </tr>
                        </thead>
                        <tbody id="collabs-sortable" class="divide-y divide-zinc-800">
                            @foreach($collabs as $collab)
                                <tr class="hover:bg-zinc-800/50 transition {{ !$collab->is_published ? 'opacity-50' : '' }}" data-id="{{ $collab->id }}" wire:key="collab-{{ $collab->id }}">
                                    <td class="px-3 py-4 drag-handle cursor-grab active:cursor-grabbing text-zinc-600 hover:text-zinc-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="px-3 py-4">
                                        @if($collab->logo_image)
                                            <img src="{{ Storage::url($collab->logo_image) }}" alt="" class="w-12 h-8 object-contain rounded-sm border border-zinc-700">
                                        @elseif($collab->featured_image)
                                            <img src="{{ Storage::url($collab->featured_image) }}" alt="" class="w-12 h-8 object-cover rounded-sm border border-zinc-700">
                                        @else
                                            <div class="w-12 h-8 bg-zinc-800 rounded-sm border border-zinc-700 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-semibold text-white">{{ $collab->title }}</div>
                                        @if($collab->episode)
                                            <div class="text-xs text-zinc-500 truncate">{{ $collab->episode->title }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($collab->character)
                                            <span class="text-xs text-zinc-400">{{ $collab->character->full_name }}</span>
                                        @else
                                            <span class="text-xs text-zinc-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($collab->show_on_homepage)
                                            <span class="inline-flex px-2 py-1 text-[10px] font-bold uppercase tracking-wider bg-accent/10 text-accent border border-accent/30 rounded-sm">Logo</span>
                                        @else
                                            <span class="text-xs text-zinc-600">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="togglePublished({{ $collab->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $collab->is_published ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                            {{ $collab->is_published ? __('Gepubliceerd') : __('Concept') }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="toggleVisible({{ $collab->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $collab->is_visible ? 'bg-blue-900/30 text-blue-400 border border-blue-800' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                            {{ $collab->is_visible ? __('Zichtbaar') : __('Verborgen') }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('collabs.show', $collab->slug) }}" target="_blank"
                                            class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                            {{ __('Preview') }}
                                        </a>
                                        <button wire:click="openEditForm({{ $collab->id }})"
                                            class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                            {{ __('Bewerken') }}
                                        </button>
                                        <button wire:click="delete({{ $collab->id }})"
                                            wire:confirm="{{ __('Collab permanent verwijderen?') }}"
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
