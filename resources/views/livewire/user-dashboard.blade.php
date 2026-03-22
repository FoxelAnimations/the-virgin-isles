<div class="bg-black min-h-screen -mt-16 pt-16 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">

        {{-- User Name Title --}}
        <div class="mb-6">
            @if ($editingName)
                <form wire:submit="saveName" class="flex items-center gap-3">
                    <input type="text" wire:model="userName"
                        class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-3xl font-bold uppercase tracking-wider focus:border-accent focus:ring-accent rounded-sm w-auto"
                        placeholder="{{ __('Je naam') }}">
                    <button type="submit"
                        class="px-3 py-1.5 text-xs font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                        {{ __('Opslaan') }}
                    </button>
                    <button type="button" wire:click="cancelEditingName"
                        class="px-3 py-1.5 text-xs font-semibold border border-zinc-700 text-zinc-400 uppercase tracking-wider transition hover:text-white hover:border-zinc-500">
                        {{ __('Annuleren') }}
                    </button>
                </form>
                @error('userName') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            @else
                <h1 class="text-3xl font-bold uppercase tracking-wider flex items-center gap-3">
                    {{ auth()->user()->name }}
                    <button wire:click="startEditingName" class="text-accent text-xs uppercase tracking-wider hover:brightness-90 transition">
                        {{ __('Bewerken') }}
                    </button>
                </h1>
            @endif
        </div>

        {{-- Welcome --}}
        <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8 mb-10">
            <h2 class="text-2xl font-bold uppercase tracking-wider">{{ $welcomeTitle ?? __('Welkom terug!') }}</h2>
            @if($welcomeText)
                <p class="mt-2 text-zinc-400 text-sm">{{ $welcomeText }}</p>
            @endif
        </div>

        {{-- Nieuwtjes --}}
        @if($showNews && !empty($newsItems))
            <div class="border border-accent/30 bg-zinc-900 rounded-sm p-6 mb-10">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold uppercase tracking-wider text-accent">{{ __('Nieuwtjes') }}</h2>
                    <button wire:click="dismissNews" class="text-zinc-500 hover:text-white transition" title="{{ __('Sluiten') }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <ul class="list-disc list-inside space-y-1.5 text-sm text-zinc-300">
                    @foreach($newsItems as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Earned Badges (new Badge model) --}}
        <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8 mb-10">
            <div class="mb-6">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-sm tracking-[0.3em] uppercase text-zinc-500 mb-1">{{ $badgeSectionLabel ?? __('Collectie') }}</p>
                        <h2 class="text-3xl font-bold uppercase tracking-wider">{{ $badgeSectionTitle ?? __('Jouw Badges') }}</h2>
                    </div>
                    <button @click="$dispatch('open-scanner')"
                        class="inline-flex items-center gap-2 bg-accent text-black px-5 py-3 text-sm font-semibold uppercase tracking-wider transition hover:brightness-90 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        {{ __('Scannen') }}
                    </button>
                </div>
                @if($badgeSectionText ?? null)
                    <p class="mt-2 text-zinc-400 text-sm">{{ $badgeSectionText }}</p>
                @else
                    <p class="mt-2 text-zinc-400 text-sm">
                        {{ __('Scan beacons om badges te verzamelen. Je hebt er al :count!', ['count' => $earnedBadges->count()]) }}
                    </p>
                @endif
            </div>

            @if ($earnedBadges->isEmpty())
                <div class="text-center py-12 text-zinc-600">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <p class="text-lg font-semibold uppercase tracking-wider">{{ __('Nog geen badges') }}</p>
                    <p class="mt-1 text-sm">{{ __('Begin met het scannen van beacons om je collectie op te bouwen!') }}</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                    @foreach ($earnedBadges as $badge)
                        <div class="group text-center {{ !$badge->is_active ? 'opacity-50 grayscale' : '' }}">
                            <div class="relative mx-auto w-28 h-28 mb-3">
                                @if ($badge->image_path)
                                    <img src="{{ Storage::url($badge->image_path) }}"
                                         alt="{{ $badge->title }}"
                                         class="w-full h-full object-contain rounded-full border-2 border-accent/30 bg-zinc-800 p-1 group-hover:border-accent transition">
                                @else
                                    <div class="w-full h-full rounded-full border-2 border-accent/30 bg-zinc-800 flex items-center justify-center group-hover:border-accent transition">
                                        <svg class="w-10 h-10 text-accent/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <p class="text-sm font-semibold text-white uppercase tracking-wider leading-tight">{{ $badge->title }}</p>
                            <p class="text-[10px] text-zinc-500 mt-1">{{ $badge->pivot->collected_at->diffForHumans() }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Collected Beacons (legacy) --}}
        @if ($collectedBeacons->isNotEmpty())
        <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8">
            <div class="mb-6">
                <p class="text-sm tracking-[0.3em] uppercase text-zinc-500 mb-1">{{ __('Beacons') }}</p>
                <h2 class="text-3xl font-bold uppercase tracking-wider">{{ __('Verzamelde Beacons') }}</h2>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-6">
                @foreach ($collectedBeacons as $beacon)
                    <div class="group text-center">
                        <div class="relative mx-auto w-28 h-28 mb-3">
                            @if ($beacon->badge_image_path)
                                <img src="{{ Storage::url($beacon->badge_image_path) }}"
                                     alt="{{ $beacon->title }}"
                                     class="w-full h-full object-contain rounded-full border-2 border-accent/30 bg-zinc-800 p-1 group-hover:border-accent transition">
                            @else
                                <div class="w-full h-full rounded-full border-2 border-accent/30 bg-zinc-800 flex items-center justify-center group-hover:border-accent transition">
                                    <svg class="w-10 h-10 text-accent/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                </div>
                            @endif
                        </div>
                        <p class="text-sm font-semibold text-white uppercase tracking-wider leading-tight">{{ $beacon->title }}</p>
                        <p class="text-[10px] text-zinc-500 mt-1">{{ $beacon->pivot->collected_at->diffForHumans() }}</p>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
