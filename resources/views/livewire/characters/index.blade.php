<div class="bg-black min-h-screen -mt-16 pt-16 text-white">
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <div class="mb-10 text-center">
            <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase tracking-wider">{{ __('Characters') }}</h1>
            <p class="mt-3 text-zinc-400 text-lg">{{ __('Meet all characters and their roles.') }}</p>
        </div>

        @if ($characters->isEmpty())
            <p class="text-center text-zinc-600 text-lg">{{ __('No characters found yet.') }}</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($characters as $character)
                    <div class="border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden group transition hover:border-accent">
                        @if ($character->profile_photo_path || $character->profile_image_path)
                            <img src="{{ Storage::url($character->profile_photo_path ?? $character->profile_image_path) }}" alt="{{ $character->first_name }}" class="h-72 w-full object-cover object-top transition group-hover:scale-105 duration-300">
                        @else
                            <div class="h-72 w-full bg-zinc-800 flex items-center justify-center text-zinc-600">
                                <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                        @endif

                        <div class="p-5 space-y-2">
                            <h2 class="text-xl font-bold uppercase tracking-wider">
                                {{ $character->full_name }}
                            </h2>
                            @if ($character->nick_name)
                                <p class="text-accent text-sm uppercase tracking-wider">"{{ $character->nick_name }}"</p>
                            @endif

                            @if ($character->job)
                                <p class="text-zinc-500 text-sm">{{ $character->job->title }}</p>
                            @endif

                            @if ($character->bio)
                                <p class="text-zinc-400 text-sm leading-relaxed line-clamp-3">{{ Illuminate\Support\Str::limit($character->bio, 150) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
