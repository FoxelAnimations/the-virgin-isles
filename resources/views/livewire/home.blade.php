<div class="bg-black min-h-screen -mt-16 pt-16 text-white">

    {{-- ============================================================
         HERO SECTION
         ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 md:gap-12 items-stretch">
            {{-- Left: Video or Image (1/3) --}}
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm overflow-hidden relative md:col-span-1">
                @if ($heroVideo)
                    <div x-data="{ muted: true }" class="w-full h-full relative cursor-pointer" @click="muted = !muted; $refs.heroVid.muted = muted">
                        <video
                            x-ref="heroVid"
                            autoplay
                            muted
                            loop
                            playsinline
                            class="w-full h-full object-cover"
                        >
                            <source src="{{ Storage::url($heroVideo->video_path) }}" type="video/mp4">
                        </video>
                        {{-- Mute/Unmute indicator --}}
                        <div class="absolute bottom-3 right-3 bg-black/60 rounded-full p-2 transition-opacity hover:bg-black/80">
                            <template x-if="muted">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/></svg>
                            </template>
                            <template x-if="!muted">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072M18.364 5.636a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/></svg>
                            </template>
                        </div>
                    </div>
                @else
                    <div class="w-full h-full flex items-center justify-center">
                        <img src="{{ asset('img/TVI-Logo-inline.png') }}" alt="TVI" class="max-w-[80%] max-h-[80%] object-contain opacity-80">
                    </div>
                @endif
            </div>

            {{-- Right: Info + Social Links (2/3) --}}
            <div class="flex flex-col justify-center gap-6 md:col-span-2">
                <div>
                    @if ($heroContent?->pre_title)
                        <p class="text-sm tracking-[0.3em] uppercase text-zinc-400 mb-4">{{ $heroContent->pre_title }}</p>
                    @endif
                    @if ($heroContent?->title)
                        <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold uppercase leading-none mb-4">
                            {{ $heroContent->title }}
                        </h1>
                    @endif
                    @if ($heroContent?->description)
                        <p class="text-zinc-400 text-lg leading-relaxed max-w-md">
                            {{ $heroContent->description }}
                        </p>
                    @endif
                </div>

                {{-- Social Links --}}
                @if ($socialLinks->isNotEmpty())
                    <div class="flex flex-col gap-3 mt-4">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="group flex items-center justify-between bg-accent text-black px-6 py-3 text-xl md:text-2xl font-bold uppercase tracking-wider transition hover:brightness-90">
                                <span>{{ $link->label }}</span>
                                <svg class="w-6 h-6 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================
         CHARACTERS SECTION — Slider
         ============================================================ --}}
    <section id="characters" class="py-12 md:py-20 scroll-mt-16">
        <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-10">
            Personages
        </h2>

        @if ($characters->isNotEmpty())
            <div
                x-data="{
                    scrollAmount: 320,
                    prev() { this.$refs.slider.scrollBy({ left: -this.scrollAmount, behavior: 'smooth' }) },
                    next() { this.$refs.slider.scrollBy({ left: this.scrollAmount, behavior: 'smooth' }) },
                    open: false,
                    char: null,
                    show(c) { this.char = c; this.open = true; document.body.classList.add('overflow-hidden'); },
                    close() { this.open = false; this.char = null; document.body.classList.remove('overflow-hidden'); },
                }"
                @keydown.escape.window="close()"
                class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8"
            >
                {{-- Prev Button --}}
                <button
                    @click="prev()"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-10 bg-accent text-black w-10 h-10 items-center justify-center hover:brightness-90 transition hidden md:flex"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </button>

                {{-- Slider --}}
                <div
                    x-ref="slider"
                    class="flex items-stretch gap-6 overflow-x-auto scroll-smooth snap-x snap-mandatory pb-4 px-2 md:px-12 scrollbar-hide"
                    style="-ms-overflow-style: none; scrollbar-width: none;"
                >
                    @foreach ($characters as $character)
                        <div
                            class="snap-start shrink-0 w-64 md:w-72 group cursor-pointer"
                            @click="show({
                                name: {{ Js::from($character->full_name) }},
                                nickname: {{ Js::from($character->nick_name) }},
                                job: {{ Js::from($character->job?->title) }},
                                bio: {{ Js::from($character->bio) }},
                                image: {{ Js::from($character->profile_image_path ? Storage::url($character->profile_image_path) : null) }},
                                fullBody: {{ Js::from($character->full_body_image_path ? Storage::url($character->full_body_image_path) : null) }},
                            })"
                        >
                            <div class="h-full border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden transition hover:border-accent flex flex-col">
                                {{-- Character Image --}}
                                @if ($character->profile_image_path)
                                    <img
                                        src="{{ Storage::url($character->profile_image_path) }}"
                                        alt="{{ $character->full_name }}"
                                        class="w-full h-80 object-cover object-top transition group-hover:scale-105 duration-300"
                                    >
                                @else
                                    <div class="w-full h-80 bg-zinc-800 flex items-center justify-center text-zinc-600">
                                        <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    </div>
                                @endif

                                {{-- Character Info --}}
                                <div class="p-4 flex-1">
                                    <h3 class="text-lg font-bold uppercase tracking-wider">
                                        {{ $character->full_name }}
                                    </h3>
                                    @if ($character->nick_name)
                                        <p class="text-accent text-sm uppercase tracking-wider">
                                            "{{ $character->nick_name }}"
                                        </p>
                                    @endif
                                    @if ($character->job)
                                        <p class="text-zinc-500 text-sm mt-1">{{ $character->job->title }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Next Button --}}
                <button
                    @click="next()"
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-10 bg-accent text-black w-10 h-10 items-center justify-center hover:brightness-90 transition hidden md:flex"
                >
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                {{-- Character Popup Modal --}}
                <template x-teleport="body">
                    <div
                        x-show="open"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100"
                        x-transition:leave-end="opacity-0"
                        class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
                        @click.self="close()"
                        style="display: none;"
                    >
                        {{-- Close Button --}}
                        <button @click="close()" class="absolute top-4 right-4 z-10 text-white hover:text-accent transition">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>

                        {{-- Modal Content --}}
                        <div
                            x-show="open"
                            x-transition:enter="transition ease-out duration-200 delay-75"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            class="bg-zinc-900 border border-zinc-800 w-full max-w-4xl max-h-[85vh] overflow-y-auto"
                            @click.stop
                        >
                            <div class="grid grid-cols-1 md:grid-cols-2">
                                {{-- Left: Image --}}
                                <div class="aspect-square bg-zinc-800 overflow-hidden">
                                    <template x-if="char?.fullBody || char?.image">
                                        <img :src="char?.fullBody || char?.image" :alt="char?.name" class="w-full h-full object-cover object-top">
                                    </template>
                                    <template x-if="!char?.fullBody && !char?.image">
                                        <div class="w-full h-full flex items-center justify-center text-zinc-700">
                                            <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        </div>
                                    </template>
                                </div>

                                {{-- Right: Info --}}
                                <div class="p-6 md:p-8 flex flex-col justify-center">
                                    <h2 class="text-3xl md:text-4xl font-bold uppercase tracking-wider text-white mb-1" x-text="char?.name"></h2>
                                    <template x-if="char?.nickname">
                                        <p class="text-accent text-sm uppercase tracking-wider mb-2">"<span x-text="char?.nickname"></span>"</p>
                                    </template>
                                    <template x-if="char?.job">
                                        <p class="text-zinc-500 text-lg uppercase tracking-wider mb-6" x-text="char?.job"></p>
                                    </template>
                                    <template x-if="char?.bio">
                                        <p class="text-zinc-400 text-sm leading-relaxed" x-text="char?.bio"></p>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @else
            <p class="text-center text-zinc-600 text-lg">Nog geen personages.</p>
        @endif
    </section>

    {{-- ============================================================
         LATEST EPISODES SECTION
         ============================================================ --}}
    <section id="episodes" class="py-12 md:py-20 scroll-mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-10">
                Afleveringen
            </h2>

            @if ($latestEpisodes->isNotEmpty())
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($latestEpisodes as $episode)
                        <a href="{{ route('episodes.index') }}" class="group border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden transition hover:border-accent block">
                            <div class="relative aspect-video bg-zinc-800 overflow-hidden">
                                @if ($episode->thumbnailUrl())
                                    <img src="{{ $episode->thumbnailUrl() }}" alt="{{ $episode->title }}" class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-zinc-700">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </div>
                                @endif
                                <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition">
                                    <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>
                            <div class="p-3">
                                <h3 class="text-sm font-bold uppercase tracking-wider truncate">{{ $episode->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>

                <div class="text-center mt-8">
                    <a href="{{ route('episodes.index') }}" class="inline-flex items-center bg-accent text-black px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:brightness-90">
                        {{ __('Bekijk Alle Afleveringen') }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @else
                <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8 md:p-16 flex items-center justify-center min-h-[200px]">
                    <p class="text-zinc-600 text-xl uppercase tracking-widest">{{ __('Binnenkort') }}</p>
                </div>
            @endif
        </div>
    </section>

</div>
