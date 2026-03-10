<div class="bg-black min-h-screen -mt-16 pt-16 text-white overflow-x-hidden">

    {{-- ============================================================
         CHARACTER CAROUSEL
         ============================================================ --}}
    @if ($characters->count() >= 1)
        <section
            class="w-full overflow-hidden bg-black"
            x-data="{
                charOpen: false,
                char: null,
                showChar(c) { this.char = c; this.charOpen = true; document.body.classList.add('overflow-hidden'); },
                closeChar() { this.charOpen = false; this.char = null; document.body.classList.remove('overflow-hidden'); },
            }"
            @keydown.escape.window="closeChar()"
            @character-popup.window="showChar($event.detail)"
        >
            @if ($carouselTitle)
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider pt-10 pb-2">
                    {{ $carouselTitle }}
                </h1>
            @endif

            @php
                $originalCount = $characters->count();
                $slides = $characters;
                while ($slides->count() < 32) {
                    $slides = $slides->concat($characters);
                }
            @endphp

            {{-- MOBILE Carousel (< md) --}}
            <div class="md:hidden" wire:ignore>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 w-1/3 z-10 pointer-events-none bg-gradient-to-r from-black/40 via-black/20 to-transparent"></div>
                    <div class="absolute inset-y-0 right-0 w-1/3 z-10 pointer-events-none bg-gradient-to-l from-black/40 via-black/20 to-transparent"></div>

                    <div class="character-carousel character-carousel-mobile carousel-entering swiper">
                        <div class="swiper-wrapper">
                            @foreach ($slides as $character)
                                @php $isDuplicate = $loop->index >= $originalCount; @endphp
                                <div
                                    class="swiper-slide group"
                                    data-name="{{ $character->first_name }}"
                                    data-job="{{ $character->job?->title }}"
                                    @if ($isDuplicate) tabindex="-1" aria-hidden="true" @endif
                                    data-character-json="{{ json_encode([
                                        'name' => $character->full_name,
                                        'nickname' => $character->nick_name,
                                        'age' => $character->age,
                                        'job' => $character->job?->title,
                                        'bio' => $character->bio,
                                        'image' => ($character->profile_photo_path ?? $character->profile_image_path) ? Storage::url($character->profile_photo_path ?? $character->profile_image_path) : null,
                                        'imageHover' => ($character->profile_photo_path ? $character->profile_photo_hover_path : $character->profile_image_hover_path) ? Storage::url($character->profile_photo_path ? $character->profile_photo_hover_path : $character->profile_image_hover_path) : null,
                                        'fullBody' => Storage::url($character->full_body_image_path),
                                        'fullBodyHover' => $character->full_body_image_hover_path ? Storage::url($character->full_body_image_hover_path) : null,
                                        'background' => $character->background_image_path ? Storage::url($character->background_image_path) : null,
                                        'links' => $character->socialLinks->map(fn($l) => ['title' => $l->title, 'url' => $l->url]),
                                    ]) }}"
                                >
                                    <img src="{{ Storage::url($character->full_body_image_path) }}" alt="{{ $character->first_name }}" class="character-static-img" loading="eager" draggable="false" />
                                    @if ($character->full_body_image_hover_path)
                                        <img src="{{ Storage::url($character->full_body_image_hover_path) }}" alt="{{ $character->first_name }}" class="absolute bottom-0 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 character-hover-img" loading="eager" draggable="false" />
                                    @endif
                                    @if ($character->full_body_image_animated_path)
                                        @if (str_ends_with($character->full_body_image_animated_path, '.webm'))
                                            <video src="{{ Storage::url($character->full_body_image_animated_path) }}" class="absolute bottom-0 left-1/2 -translate-x-1/2 character-animated-layer" style="opacity: 0.01;" muted playsinline preload="auto" draggable="false"></video>
                                        @else
                                            <img src="{{ Storage::url($character->full_body_image_animated_path) }}" alt="{{ $character->first_name }} animated" class="absolute bottom-0 left-1/2 -translate-x-1/2 character-animated-layer" style="opacity: 0.01;" loading="eager" draggable="false" />
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="text-center py-3">
                    <div class="carousel-center-name text-xl font-bold uppercase tracking-wider text-white"></div>
                    <div class="carousel-center-job text-sm uppercase tracking-wider text-accent"></div>
                </div>
            </div>

            {{-- DESKTOP Carousel (>= md) --}}
            <div class="hidden md:block" wire:ignore>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 w-1/3 z-10 pointer-events-none bg-gradient-to-r from-black/40 via-black/20 to-transparent"></div>
                    <div class="absolute inset-y-0 right-0 w-1/3 z-10 pointer-events-none bg-gradient-to-l from-black/40 via-black/20 to-transparent"></div>

                    <div class="character-carousel character-carousel-desktop carousel-entering swiper">
                        <div class="swiper-wrapper">
                            @foreach ($slides as $character)
                                @php $isDuplicate = $loop->index >= $originalCount; @endphp
                                <div
                                    class="swiper-slide group"
                                    data-name="{{ $character->first_name }}"
                                    data-job="{{ $character->job?->title }}"
                                    @if ($isDuplicate) tabindex="-1" aria-hidden="true" @endif
                                    data-character-json="{{ json_encode([
                                        'name' => $character->full_name,
                                        'nickname' => $character->nick_name,
                                        'age' => $character->age,
                                        'job' => $character->job?->title,
                                        'bio' => $character->bio,
                                        'image' => ($character->profile_photo_path ?? $character->profile_image_path) ? Storage::url($character->profile_photo_path ?? $character->profile_image_path) : null,
                                        'imageHover' => ($character->profile_photo_path ? $character->profile_photo_hover_path : $character->profile_image_hover_path) ? Storage::url($character->profile_photo_path ? $character->profile_photo_hover_path : $character->profile_image_hover_path) : null,
                                        'fullBody' => Storage::url($character->full_body_image_path),
                                        'fullBodyHover' => $character->full_body_image_hover_path ? Storage::url($character->full_body_image_hover_path) : null,
                                        'background' => $character->background_image_path ? Storage::url($character->background_image_path) : null,
                                        'links' => $character->socialLinks->map(fn($l) => ['title' => $l->title, 'url' => $l->url]),
                                    ]) }}"
                                >
                                    <img src="{{ Storage::url($character->full_body_image_path) }}" alt="{{ $character->first_name }}" class="character-static-img" loading="eager" draggable="false" />
                                    @if ($character->full_body_image_hover_path)
                                        <img src="{{ Storage::url($character->full_body_image_hover_path) }}" alt="{{ $character->first_name }}" class="absolute bottom-0 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 character-hover-img" loading="eager" draggable="false" />
                                    @endif
                                    @if ($character->full_body_image_animated_path)
                                        @if (str_ends_with($character->full_body_image_animated_path, '.webm'))
                                            <video src="{{ Storage::url($character->full_body_image_animated_path) }}" class="absolute bottom-0 left-1/2 -translate-x-1/2 character-animated-layer" style="opacity: 0.01;" muted playsinline preload="auto" draggable="false"></video>
                                        @else
                                            <img src="{{ Storage::url($character->full_body_image_animated_path) }}" alt="{{ $character->first_name }} animated" class="absolute bottom-0 left-1/2 -translate-x-1/2 character-animated-layer" style="opacity: 0.01;" loading="eager" draggable="false" />
                                        @endif
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="text-center py-3">
                    <div class="carousel-center-name text-2xl font-bold uppercase tracking-wider text-white"></div>
                    <div class="carousel-center-job text-base uppercase tracking-wider text-accent"></div>
                </div>
            </div>

            {{-- Character Popup Modal --}}
            <template x-teleport="body">
                <div
                    x-show="charOpen"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
                    @click.self="closeChar()"
                    style="display: none;"
                >
                    <button @click="closeChar()" class="absolute top-4 right-4 z-10 text-white hover:text-accent transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <div
                        x-show="charOpen"
                        x-transition:enter="transition ease-out duration-200 delay-75"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        class="bg-zinc-900 border border-zinc-800 w-full max-w-4xl max-h-[85vh] overflow-y-auto"
                        @click.stop
                    >
                        <div class="grid grid-cols-1 md:grid-cols-2">
                            {{-- Left: Image --}}
                            <div class="aspect-square bg-zinc-800 overflow-hidden relative group/modal"
                                :style="char?.background ? 'background-image: url(\'' + char.background + '\'); background-size: cover; background-position: center;' : ''"
                            >
                                <template x-if="char?.image || char?.fullBody">
                                    <div class="w-full h-full relative">
                                        <img :src="char?.image || char?.fullBody" :alt="char?.name"
                                            class="w-full h-full object-cover object-top relative z-[1] transition duration-300"
                                            :class="(char?.image ? char?.imageHover : char?.fullBodyHover) ? 'group-hover/modal:opacity-0' : ''">
                                        <template x-if="char?.image ? char?.imageHover : char?.fullBodyHover">
                                            <img :src="char?.image ? char?.imageHover : char?.fullBodyHover" :alt="char?.name"
                                                class="absolute inset-0 w-full h-full object-cover object-top z-[2] opacity-0 transition duration-300 group-hover/modal:opacity-100">
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!char?.image && !char?.fullBody && !char?.background">
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
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mb-6">
                                    <template x-if="char?.job">
                                        <span class="text-white text-lg uppercase tracking-wider" x-text="char?.job"></span>
                                    </template>
                                    <template x-if="char?.age">
                                        <span class="text-zinc-500 text-lg uppercase tracking-wider">Leeftijd : <span class="text-accent" x-text="char?.age"></span></span>
                                    </template>
                                </div>
                                <template x-if="char?.bio">
                                    <p class="text-zinc-400 text-sm leading-relaxed font-description" x-text="char?.bio"></p>
                                </template>
                                <template x-if="char?.links?.length > 0">
                                    <div class="flex flex-wrap gap-2 mt-6">
                                        <template x-for="(link, i) in char.links" :key="i">
                                            <a :href="link.url" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" x-text="link.title"></a>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </section>
    @endif

    {{-- ============================================================
         HERO SECTION
         ============================================================ --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:pb-20">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
            {{-- Left: Video or Image (1:1) --}}
            <div class="bg-zinc-900 border border-zinc-800 rounded-sm overflow-hidden relative aspect-square">
                @if ($heroVideo?->video_path)
                    <div x-data="{ muted: true }" class="absolute inset-0 cursor-pointer" @click="muted = !muted; $refs.heroVid.muted = muted">
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
                @elseif ($heroVideo?->image_path)
                    <div class="absolute inset-0">
                        <img src="{{ Storage::url($heroVideo->image_path) }}" alt="Hero" class="w-full h-full object-cover">
                    </div>
                @else
                    <div class="absolute inset-0 flex items-center justify-center">
                        <img src="{{ asset('img/TVI-Logo-inline.png') }}" alt="TVI" class="max-w-[80%] max-h-[80%] object-contain opacity-80">
                    </div>
                @endif
            </div>

            {{-- Right: Info + Social Links (1/2) --}}
            <div class="flex flex-col justify-center gap-6">
                <div>
                    @if ($heroContent?->pre_title)
                        <p class="text-sm tracking-[0.3em] uppercase text-zinc-400 mb-4">{{ $heroContent->pre_title }}</p>
                    @endif
                    @if ($heroContent?->title)
                        <h2 class="text-5xl md:text-6xl lg:text-7xl font-bold uppercase leading-none mb-4 break-words">
                            {{ $heroContent->title }}
                        </h2>
                    @endif
                    @if ($heroContent?->description)
                        <p class="text-zinc-400 text-lg leading-relaxed max-w-md font-description">
                            {{ $heroContent->description }}
                        </p>
                    @endif
                </div>

                {{-- Social Links --}}
                @if ($socialLinks->isNotEmpty())
                    <div class="flex flex-col gap-3 mt-4">
                        @foreach ($socialLinks as $link)
                            <a href="{{ $link->url }}" target="_blank" rel="noopener" class="group flex items-center justify-between bg-accent text-black px-6 py-3 text-xl md:text-2xl font-bold uppercase tracking-wider transition hover:brightness-90">
                                <span class="inline-flex items-center" style="gap: 20px;">
                                    @if (str_contains(strtolower($link->url), 'instagram') || str_contains(strtolower($link->label), 'instagram'))
                                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    @elseif (str_contains(strtolower($link->url), 'youtube') || str_contains(strtolower($link->label), 'youtube'))
                                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    @elseif (str_contains(strtolower($link->url), 'tiktok') || str_contains(strtolower($link->label), 'tiktok'))
                                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                    @elseif (str_contains(strtolower($link->url), 'twitter') || str_contains(strtolower($link->label), 'twitter') || str_contains(strtolower($link->url), 'x.com') || str_contains(strtolower($link->label), 'x'))
                                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    @elseif (str_contains(strtolower($link->url), 'facebook') || str_contains(strtolower($link->label), 'facebook'))
                                        <svg class="w-7 h-7 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                    @endif
                                    {{ $link->label }}
                                </span>
                                <svg class="w-6 h-6 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ============================================================
         CONTENT BLOCKS (ABOVE EPISODES)
         ============================================================ --}}
    @if ($blocksAbove->isNotEmpty())
        <section class="py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @foreach ($blocksAbove as $block)
                    @include('partials.content-block', ['block' => $block, 'loop' => $loop])
                @endforeach
            </div>
        </section>
    @endif

    {{-- ============================================================
         AFLEVERINGEN SECTIONS (Episodes, Shorts, Minis)
         ============================================================ --}}
    <div id="episodes"
        x-data="{
            epOpen: false,
            ep: null,
            ageConfirmed: false,
            showEp(data) { this.ep = data; this.ageConfirmed = !data.ageRestricted; this.epOpen = true; document.body.classList.add('overflow-hidden'); },
            closeEp() { this.epOpen = false; this.ep = null; this.ageConfirmed = false; document.body.classList.remove('overflow-hidden'); },
            confirmAge() { this.ageConfirmed = true; },
        }"
        @keydown.escape.window="closeEp()"
    >
        @foreach ([
            ['items' => $latestEpisodes, 'title' => 'Episodes'],
            ['items' => $latestShorts, 'title' => 'Shorts'],
            ['items' => $latestMinis, 'title' => 'Minis'],
        ] as $section)
            @if ($section['items']->isNotEmpty())
                <section class="py-8 md:py-12 scroll-mt-16">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <h2 class="text-3xl md:text-4xl font-bold uppercase text-center tracking-wider mb-6">
                            {{ $section['title'] }}
                        </h2>

                        <div class="episode-carousel swiper" wire:ignore>
                            <div class="swiper-wrapper">
                                @foreach ($section['items'] as $episode)
                                    <div class="swiper-slide">
                                        <div
                                            class="group border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden transition hover:border-accent cursor-pointer"
                                            @click="showEp({
                                                title: {{ Js::from($episode->title) }},
                                                description: {{ Js::from($episode->description) }},
                                                sourceType: {{ Js::from($episode->source_type) }},
                                                videoUrl: {{ Js::from($episode->videoUrl()) }},
                                                youtubeEmbed: {{ Js::from($episode->youtubeEmbedUrl()) }},
                                                thumbnail: {{ Js::from($episode->thumbnailUrl()) }},
                                                instagram: {{ Js::from($episode->instagram_url) }},
                                                youtube: {{ Js::from($episode->youtube_link) }},
                                                tiktok: {{ Js::from($episode->tiktok_url) }},
                                                twitter: {{ Js::from($episode->twitter_url) }},
                                                ageRestricted: {{ Js::from((bool) $episode->age_restricted) }},
                                            })"
                                        >
                                            <div class="relative aspect-video bg-zinc-800 overflow-hidden">
                                                @if ($episode->thumbnailUrl())
                                                    <img src="{{ $episode->thumbnailUrl() }}" alt="{{ $episode->title }}" class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-zinc-700">
                                                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                    </div>
                                                @endif
                                                <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition">
                                                    <svg class="w-8 h-8 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                </div>
                                            </div>
                                            <div class="p-2">
                                                <h3 class="text-xs font-bold uppercase tracking-wider truncate">{{ $episode->title }}</h3>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endforeach

        {{-- "View All" button --}}
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if ($latestEpisodes->isNotEmpty() || $latestShorts->isNotEmpty() || $latestMinis->isNotEmpty())
                <div class="text-center mt-2 mb-8">
                    <a href="{{ route('episodes.index') }}" class="inline-flex items-center bg-accent text-black px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:brightness-90">
                        {{ __('Bekijk Alle Afleveringen') }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @else
                <div class="py-8 md:py-12">
                    <div class="border border-zinc-800 bg-zinc-900 rounded-sm p-8 md:p-12 flex items-center justify-center min-h-[150px]">
                        <p class="text-zinc-600 text-xl uppercase tracking-widest">{{ __('Binnenkort') }}</p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Episode Popup Modal --}}
        <template x-teleport="body">
            <div
                x-show="epOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-start md:items-center justify-center bg-black/95 p-0 md:p-8 overflow-y-auto"
                @click.self="closeEp()"
                style="display: none;"
            >
                <button @click="closeEp()" class="absolute top-2 right-2 md:top-4 md:right-4 z-10 text-white hover:text-accent transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="w-full max-w-5xl md:max-h-full" @click.stop>
                    {{-- Age Gate Overlay --}}
                    <template x-if="ep?.ageRestricted && !ageConfirmed">
                        <div class="aspect-video bg-zinc-900 rounded-sm overflow-hidden mb-4 flex flex-col items-center justify-center text-center p-8">
                            <div class="text-5xl font-bold text-red-500 mb-4">18+</div>
                            <p class="text-white text-lg mb-6 max-w-md">{{ $ageGate?->message ?? 'Ben je 18 jaar of ouder?' }}</p>
                            <div class="flex gap-4">
                                <button @click="confirmAge()" class="bg-accent text-black px-6 py-3 text-sm font-bold uppercase tracking-wider hover:brightness-90 transition">
                                    {{ $ageGate?->confirm_text ?? 'Ja, ik ben 18+' }}
                                </button>
                                <button @click="closeEp()" class="bg-zinc-800 text-white px-6 py-3 text-sm font-bold uppercase tracking-wider hover:bg-zinc-700 transition">
                                    {{ $ageGate?->deny_text ?? 'Nee' }}
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Video Player (shown after age confirmation or if not restricted) --}}
                    <template x-if="!ep?.ageRestricted || ageConfirmed">
                        <div class="aspect-video bg-black rounded-none md:rounded-sm overflow-hidden mb-0 md:mb-4">
                            <template x-if="ep?.sourceType === 'youtube' && ep?.youtubeEmbed">
                                <iframe :src="ep.youtubeEmbed" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </template>
                            <template x-if="ep?.sourceType === 'upload' && ep?.videoUrl">
                                <video controls autoplay class="w-full h-full" :src="ep.videoUrl"></video>
                            </template>
                            <template x-if="(!ep?.youtubeEmbed && !ep?.videoUrl) && ep?.thumbnail">
                                <img :src="ep.thumbnail" class="w-full h-full object-cover">
                            </template>
                        </div>
                    </template>

                    {{-- Episode Info --}}
                    <div class="text-white px-4 py-4 md:px-0 md:py-0">
                        <h2 class="text-2xl md:text-3xl font-bold uppercase tracking-wider mb-2" x-text="ep?.title"></h2>
                        <template x-if="ep?.description">
                            <div class="prose prose-invert prose-sm prose-zinc font-description max-w-3xl content-block-text" x-html="ep.description"></div>
                        </template>

                        {{-- Social Links --}}
                        <div class="flex flex-wrap gap-3">
                            <template x-if="ep?.instagram">
                                <a :href="ep.instagram" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                    Instagram
                                </a>
                            </template>
                            <template x-if="ep?.youtube">
                                <a :href="ep.youtube" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                    YouTube
                                </a>
                            </template>
                            <template x-if="ep?.tiktok">
                                <a :href="ep.tiktok" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                    TikTok
                                </a>
                            </template>
                            <template x-if="ep?.twitter">
                                <a :href="ep.twitter" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                    <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                    Twitter
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- ============================================================
         CONTENT BLOCKS (BELOW EPISODES)
         ============================================================ --}}
    @if ($blocksBelow->isNotEmpty())
        <section class="py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                @foreach ($blocksBelow as $block)
                    @include('partials.content-block', ['block' => $block, 'loop' => $loop])
                @endforeach
            </div>
        </section>
    @endif


</div>
