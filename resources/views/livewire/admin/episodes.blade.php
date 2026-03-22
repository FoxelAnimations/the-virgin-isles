<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Episodes') }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.episode-analytics') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    {{ __('Analytics') }}
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('New Episode') }}
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Episodes Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Resource</p>
                    <h2 class="text-lg font-semibold uppercase tracking-wider">{{ __('All Episodes') }}</h2>
                </div>
                <button wire:click="openCreate" class="inline-flex items-center bg-zinc-800 text-white px-3 py-2 text-sm font-semibold tracking-wider uppercase transition hover:bg-zinc-700">
                    {{ __('New Episode') }}
                </button>
            </div>

            @if ($episodes->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('No episodes yet. Create the first one!') }}</div>
            @else
                <div class="overflow-x-auto"
                    x-data
                    x-init="
                        Sortable.create($el.querySelector('#episodes-sortable'), {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd() {
                                const ids = [...$el.querySelectorAll('#episodes-sortable tr[data-id]')].map(row => parseInt(row.dataset.id));
                                $wire.updateEpisodeOrder(ids);
                            }
                        })
                    "
                >
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="w-10 px-3 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Title') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Category') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Characters') }}</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Views') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="episodes-sortable" class="divide-y divide-zinc-800">
                            @foreach ($episodes as $episode)
                                <tr class="hover:bg-zinc-800/50 transition" data-id="{{ $episode->id }}">
                                    <td class="px-3 py-4 drag-handle cursor-grab active:cursor-grabbing text-zinc-600 hover:text-zinc-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-2">
                                            <span class="font-semibold text-white">{{ $episode->title }}</span>
                                            @if (!$episode->visible)
                                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-zinc-700/30 text-zinc-500 rounded-sm">{{ __('Hidden') }}</span>
                                            @endif
                                            @if ($episode->age_restricted)
                                                <span class="inline-flex px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wider bg-red-900/30 text-red-400 rounded-sm">18+</span>
                                            @endif
                                        </div>
                                        @if ($episode->description)
                                            <div class="text-xs text-zinc-500 max-w-xs truncate font-description">{{ \Illuminate\Support\Str::limit($episode->description, 60) }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold uppercase tracking-wider {{ $episode->category === 'episode' ? 'bg-blue-900/30 text-blue-400' : ($episode->category === 'short' ? 'bg-purple-900/30 text-purple-400' : ($episode->category === 'special' ? 'bg-amber-900/30 text-amber-400' : 'bg-emerald-900/30 text-emerald-400')) }}">
                                            {{ $episode->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold uppercase tracking-wider {{ $episode->isYoutube() ? 'bg-red-900/30 text-red-400' : 'bg-accent/10 text-accent' }}">
                                            {{ $episode->source_type }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            @foreach ($episode->characters as $char)
                                                <span class="inline-flex px-2 py-0.5 text-xs bg-zinc-800 text-zinc-300 rounded-sm">{{ $char->first_name }}</span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="inline-flex items-center gap-1 text-sm text-zinc-400" title="{{ $episode->views_count }} views">
                                            <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            {{ number_format($episode->views_count) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button wire:click="edit({{ $episode->id }})" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                            {{ __('Edit') }}
                                        </button>
                                        <button
                                            wire:click="delete({{ $episode->id }})"
                                            wire:confirm="{{ __('Are you sure you want to delete this episode?') }}"
                                            class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition"
                                        >
                                            {{ __('Delete') }}
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

    {{-- Create / Edit Modal --}}
    @if ($showModal)
        <div
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            x-init="document.body.classList.add('overflow-hidden')"
            x-on:episode-saved.window="document.body.classList.remove('overflow-hidden')"
            @keydown.escape.window="$wire.closeModal(); document.body.classList.remove('overflow-hidden')"
        >
            {{-- Backdrop click --}}
            <div class="absolute inset-0" wire:click="closeModal" x-on:click="document.body.classList.remove('overflow-hidden')"></div>

            {{-- Modal Content --}}
            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-2xl max-h-[85vh] overflow-y-auto" @click.stop>
                {{-- Header --}}
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $editingId ? __('Edit Episode') : __('New Episode') }}</span>
                    <button wire:click="closeModal" x-on:click="document.body.classList.remove('overflow-hidden')" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-5">
                    <form wire:submit="{{ $editingId ? 'update' : 'save' }}">
                        {{-- Title --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Title') }} *</label>
                            <input type="text" wire:model="title" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            @error('title') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Category --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Category') }} *</label>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('category', 'episode')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $category === 'episode' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('Episode') }}
                                </button>
                                <button type="button" wire:click="$set('category', 'short')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $category === 'short' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('Short') }}
                                </button>
                                <button type="button" wire:click="$set('category', 'mini')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $category === 'mini' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('Mini') }}
                                </button>
                                <button type="button" wire:click="$set('category', 'special')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $category === 'special' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('Special') }}
                                </button>
                            </div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-4" wire:ignore
                             x-data="{
                                 quill: null,
                                 init() {
                                     this.quill = new Quill(this.$refs.descEditor, {
                                         theme: 'snow',
                                         placeholder: 'Optionele beschrijving...',
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
                                     const initial = @this.get('description');
                                     if (initial) {
                                         this.quill.root.innerHTML = initial;
                                     }
                                     this.quill.on('text-change', () => {
                                         const html = this.quill.root.innerHTML;
                                         @this.set('description', html === '<p><br></p>' ? '' : html);
                                     });
                                 }
                             }"
                        >
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Description') }}</label>
                            <div x-ref="descEditor" class="quill-editor-dark"></div>
                            @error('description') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Source Type Toggle --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Video Source') }} *</label>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('source_type', 'upload')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $source_type === 'upload' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('Upload') }}
                                </button>
                                <button type="button" wire:click="$set('source_type', 'youtube')"
                                    class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $source_type === 'youtube' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white' }}">
                                    {{ __('YouTube') }}
                                </button>
                            </div>
                        </div>

                        {{-- Video Upload (conditional) --}}
                        @if ($source_type === 'upload')
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">
                                    {{ __('Video File') }} {{ $editingId ? '' : '*' }}
                                </label>
                                <input type="file" wire:model="video" accept="video/mp4,video/webm,video/quicktime"
                                    class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-sm file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                                <div wire:loading wire:target="video" class="text-sm text-zinc-500 mt-1">{{ __('Processing file...') }}</div>
                                @error('video') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        @else
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('YouTube URL') }} *</label>
                                <input type="url" wire:model="youtube_url" placeholder="https://www.youtube.com/watch?v=..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                @error('youtube_url') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        @endif

                        {{-- Thumbnail --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Thumbnail') }} {{ __('(optional, auto-generated for YouTube)') }}</label>
                            <input type="file" wire:model="thumbnail" accept="image/*"
                                class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-sm file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                            @error('thumbnail') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Age Restricted Toggle --}}
                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="age_restricted" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm font-medium text-white">{{ __('18+ Age Gate') }}</span>
                            </label>
                            <p class="text-xs text-zinc-500 mt-1">{{ __('When enabled, viewers must confirm their age before watching this episode.') }}</p>
                        </div>

                        {{-- Visible Toggle --}}
                        <div class="mb-4">
                            <label class="flex items-center gap-3 cursor-pointer">
                                <input type="checkbox" wire:model="visible" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-sm font-medium text-white">{{ __('Visible on site') }}</span>
                            </label>
                            <p class="text-xs text-zinc-500 mt-1">{{ __('When disabled, this episode will not be shown on the homepage or episodes page.') }}</p>
                        </div>

                        {{-- Characters Multi-Select --}}
                        <div class="mb-4" x-data="{ open: false }" @click.outside="open = false">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Characters') }}</label>
                            <div class="relative">
                                <button type="button" @click="open = !open" class="w-full bg-zinc-800 border border-zinc-700 text-left text-white px-3 py-2 text-sm rounded-sm flex items-center justify-between">
                                    <span class="text-zinc-400">
                                        @if (count($selectedCharacters) > 0)
                                            {{ count($selectedCharacters) }} {{ __('selected') }}
                                        @else
                                            {{ __('Select characters...') }}
                                        @endif
                                    </span>
                                    <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>

                                <div x-show="open" x-transition class="absolute z-20 mt-1 w-full bg-zinc-800 border border-zinc-700 rounded-sm max-h-48 overflow-y-auto">
                                    @foreach ($characters as $character)
                                        <label class="flex items-center gap-2 px-3 py-2 hover:bg-zinc-700 cursor-pointer text-sm">
                                            <input type="checkbox" value="{{ $character->id }}" wire:model="selectedCharacters" class="rounded-sm border-zinc-600 bg-zinc-700 text-accent focus:ring-accent">
                                            <span class="text-white">{{ $character->first_name }} {{ $character->last_name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Social Links --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-2">{{ __('Social Links') }}</label>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1">Instagram</label>
                                    <input type="url" wire:model="instagram_url" placeholder="https://instagram.com/..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    @error('instagram_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1">YouTube</label>
                                    <input type="url" wire:model="youtube_link" placeholder="https://youtube.com/..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    @error('youtube_link') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1">TikTok</label>
                                    <input type="url" wire:model="tiktok_url" placeholder="https://tiktok.com/..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    @error('tiktok_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-zinc-500 mb-1">Twitter</label>
                                    <input type="url" wire:model="twitter_url" placeholder="https://twitter.com/..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    @error('twitter_url') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Submit --}}
                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="{{ $editingId ? 'update' : 'save' }}">
                                    {{ $editingId ? __('Update Episode') : __('Create Episode') }}
                                </span>
                                <span wire:loading wire:target="{{ $editingId ? 'update' : 'save' }}">
                                    {{ __('Saving...') }}
                                </span>
                            </button>
                            <button type="button" wire:click="closeModal" x-on:click="document.body.classList.remove('overflow-hidden')" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                                {{ __('Cancel') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
