<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __("Camera's") }}</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.camera-settings') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    {{ __('Dagdelen') }}
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('Nieuwe Camera') }}
                </button>
            </div>
        </div>

        {{-- Flash Message --}}
        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Cameras Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overzicht</p>
                <h2 class="text-lg font-semibold uppercase tracking-wider">{{ __("Alle Camera's") }}</h2>
            </div>

            @if ($cameras->isEmpty())
                <div class="p-8 text-center text-zinc-600">
                    {{ __("Nog geen camera's aangemaakt.") }}
                </div>
            @else
                <div
                    x-data
                    x-init="
                        Sortable.create($el.querySelector('#cameras-sortable'), {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd() {
                                const ids = [...$el.querySelectorAll('#cameras-sortable tr[data-id]')].map(row => parseInt(row.dataset.id));
                                $wire.updateOrder(ids);
                            }
                        })
                    "
                    class="overflow-x-auto"
                >
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-zinc-500">
                                <th class="px-3 py-3 text-left w-10"></th>
                                <th class="px-4 py-3 text-left">{{ __('Naam') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Status') }}</th>
                                <th class="px-4 py-3 text-center">{{ __('Zichtbaarheid') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('Acties') }}</th>
                            </tr>
                        </thead>
                        <tbody id="cameras-sortable" class="divide-y divide-zinc-800">
                            @foreach ($cameras as $camera)
                                <tr class="hover:bg-zinc-800/50 transition" data-id="{{ $camera->id }}">
                                    {{-- Drag Handle --}}
                                    <td class="px-3 py-4 drag-handle cursor-grab active:cursor-grabbing text-zinc-600 hover:text-zinc-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                        </svg>
                                    </td>

                                    {{-- Name --}}
                                    <td class="px-4 py-4">
                                        <span class="text-white font-medium">{{ $camera->name }}</span>
                                    </td>

                                    {{-- Status Toggle --}}
                                    <td class="px-4 py-4 text-center">
                                        <button wire:click="toggleOffline({{ $camera->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $camera->is_offline ? 'bg-red-900/30 text-red-400 border border-red-800' : 'bg-green-900/30 text-green-400 border border-green-800' }}">
                                            {{ $camera->is_offline ? __('Offline') : __('Online') }}
                                        </button>
                                    </td>

                                    {{-- Visibility Toggle --}}
                                    <td class="px-4 py-4 text-center">
                                        <button wire:click="toggleHidden({{ $camera->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $camera->is_hidden ? 'bg-zinc-800 text-zinc-500 border border-zinc-700' : 'bg-green-900/30 text-green-400 border border-green-800' }}">
                                            {{ $camera->is_hidden ? __('Verborgen') : __('Zichtbaar') }}
                                        </button>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.camera-planner', $camera) }}"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                                {{ __('Bewerken') }}
                                            </a>
                                            <button wire:click="delete({{ $camera->id }})"
                                                wire:confirm="{{ __('Weet je zeker dat je deze camera wilt verwijderen?') }}"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50 uppercase tracking-wider">
                                                {{ __('Verwijderen') }}
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            @keydown.escape.window="$wire.closeModal()"
        >
            <div class="absolute inset-0" wire:click="closeModal"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-md" @click.stop>
                {{-- Header --}}
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ __('Nieuwe Camera') }}</span>
                    <button wire:click="closeModal" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Form --}}
                <div class="p-5">
                    <form wire:submit="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">{{ __('Naam') }} *</label>
                            <input type="text" wire:model="name"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                placeholder="Camera 1 - Ingang">
                            @error('name') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3 justify-end">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-semibold text-zinc-400 border border-zinc-700 uppercase tracking-wider transition hover:text-white hover:border-zinc-500">
                                {{ __('Annuleren') }}
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                                {{ __('Aanmaken') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
