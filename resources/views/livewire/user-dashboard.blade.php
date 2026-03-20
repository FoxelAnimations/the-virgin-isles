<div class="bg-black min-h-screen -mt-16 pt-16 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">

        {{-- Welcome --}}
        <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8 mb-10">
            <h1 class="text-2xl font-bold uppercase tracking-wider">{{ __('Welcome back!') }}</h1>
            <p class="mt-2 text-zinc-400">{{ __('This is your user dashboard.') }}</p>
        </div>

        {{-- Collected Badges --}}
        <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8">
            <div class="mb-6">
                <p class="text-sm tracking-[0.3em] uppercase text-zinc-500 mb-1">{{ __('Collection') }}</p>
                <h2 class="text-3xl font-bold uppercase tracking-wider">{{ __('Your Badges') }}</h2>
                <p class="mt-2 text-zinc-400 text-sm">
                    {{ __('Scan beacons to collect badges. You have :count so far!', ['count' => $collectedBeacons->count()]) }}
                </p>
            </div>

            @if ($collectedBeacons->isEmpty())
                <div class="text-center py-12 text-zinc-600">
                    <svg class="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    <p class="text-lg font-semibold uppercase tracking-wider">{{ __('No badges yet') }}</p>
                    <p class="mt-1 text-sm">{{ __('Start scanning beacons to build your collection!') }}</p>
                </div>
            @else
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
            @endif
        </div>
    </div>
</div>
