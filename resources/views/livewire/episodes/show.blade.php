<div class="bg-black min-h-screen -mt-16 pt-16 text-white"
    x-data="{
        open: false,
        episode: null,
        ageConfirmed: false,
        charOpen: false,
        char: null,
        openEpisode(ep) {
            this.episode = ep;
            this.ageConfirmed = !ep.ageRestricted;
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            this.episode = null;
            this.ageConfirmed = false;
            document.body.classList.remove('overflow-hidden');
        },
        confirmAge() { this.ageConfirmed = true; },
        showChar(c) {
            this.char = c;
            this.charOpen = true;
        },
        closeChar() {
            this.charOpen = false;
            this.char = null;
        }
    }"
    @keydown.escape.window="charOpen ? closeChar() : close()"
>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20"
        x-data="{ tab: 'episodes' }"
    >
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-10">
            Afleveringen
        </h1>

        {{-- Tabs --}}
        <div class="flex justify-center gap-2 mb-10">
            <button @click="tab = 'episodes'"
                :class="tab === 'episodes' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white'"
                class="px-6 py-2.5 text-sm font-bold uppercase tracking-wider transition">
                Episodes
            </button>
            <button @click="tab = 'shorts'"
                :class="tab === 'shorts' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white'"
                class="px-6 py-2.5 text-sm font-bold uppercase tracking-wider transition">
                Shorts
            </button>
            <button @click="tab = 'minis'"
                :class="tab === 'minis' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white'"
                class="px-6 py-2.5 text-sm font-bold uppercase tracking-wider transition">
                Minis
            </button>
        </div>

        @foreach ([
            ['key' => 'episodes', 'items' => $episodes],
            ['key' => 'shorts', 'items' => $shorts],
            ['key' => 'minis', 'items' => $minis],
        ] as $section)
            <div x-show="tab === '{{ $section['key'] }}'" x-cloak>
                @if ($section['items']->isEmpty())
                    <p class="text-center text-zinc-600 text-lg">{{ __('Nog geen afleveringen.') }}</p>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($section['items'] as $episode)
                            <div
                                class="group cursor-pointer border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden transition hover:border-accent"
                                @click="openEpisode({
                                    id: {{ $episode->id }},
                                    title: {{ Js::from($episode->title) }},
                                    description: {{ Js::from($episode->description) }},
                                    isYoutube: {{ $episode->isYoutube() ? 'true' : 'false' }},
                                    embedUrl: {{ Js::from($episode->youtubeEmbedUrl()) }},
                                    videoUrl: {{ Js::from($episode->videoUrl()) }},
                                    characters: {{ Js::from($episode->characters->map(fn($c) => [
                                        'name' => $c->full_name,
                                        'nickname' => $c->nick_name,
                                        'job' => $c->job?->title,
                                        'bio' => $c->bio,
                                        'image' => $c->profile_image_path ? Storage::url($c->profile_image_path) : null,
                                        'fullBody' => $c->full_body_image_path ? Storage::url($c->full_body_image_path) : null,
                                    ])) }},
                                    instagram: {{ Js::from($episode->instagram_url) }},
                                    youtube: {{ Js::from($episode->youtube_link) }},
                                    tiktok: {{ Js::from($episode->tiktok_url) }},
                                    twitter: {{ Js::from($episode->twitter_url) }},
                                    ageRestricted: {{ Js::from((bool) $episode->age_restricted) }},
                                })"
                            >
                                {{-- Thumbnail --}}
                                <div class="relative aspect-video bg-zinc-800 overflow-hidden">
                                    @if ($episode->thumbnailUrl())
                                        <img src="{{ $episode->thumbnailUrl() }}" alt="{{ $episode->title }}" class="w-full h-full object-cover transition group-hover:scale-105 duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-zinc-700">
                                            <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        </div>
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/30 opacity-0 group-hover:opacity-100 transition">
                                        <svg class="w-14 h-14 text-accent" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                    <div class="absolute top-2 right-2 flex gap-1">
                                        @if ($episode->age_restricted)
                                            <span class="px-2 py-0.5 text-xs font-bold uppercase tracking-wider bg-red-600 text-white">18+</span>
                                        @endif
                                        <span class="px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $episode->isYoutube() ? 'bg-red-600 text-white' : 'bg-accent text-black' }}">
                                            {{ $episode->source_type }}
                                        </span>
                                    </div>
                                </div>

                                {{-- Info --}}
                                <div class="p-4">
                                    <h3 class="text-lg font-bold uppercase tracking-wider mb-1">{{ $episode->title }}</h3>
                                    @if ($episode->characters->isNotEmpty())
                                        <div class="flex flex-wrap gap-1 mb-2">
                                            @foreach ($episode->characters as $char)
                                                <span class="px-2 py-0.5 text-xs bg-zinc-800 text-accent rounded-sm">{{ $char->first_name }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if ($episode->description)
                                        <p class="text-sm text-zinc-500 line-clamp-2 font-description">{{ Illuminate\Support\Str::limit($episode->description, 120) }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach
    </section>

    {{-- ============================================================
         EPISODE LIGHTBOX MODAL
         ============================================================ --}}
    <template x-teleport="body">
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 flex items-start md:items-center justify-center bg-black/95 p-0 md:p-8 overflow-y-auto"
            @click.self="close()"
            style="display: none;"
        >
            <button @click="close()" class="absolute top-2 right-2 md:top-4 md:right-4 z-10 text-white hover:text-accent transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="w-full max-w-5xl md:max-h-full" @click.stop>
                {{-- Age Gate Overlay --}}
                <template x-if="episode?.ageRestricted && !ageConfirmed">
                    <div class="aspect-video bg-zinc-900 rounded-sm overflow-hidden mb-4 flex flex-col items-center justify-center text-center p-8">
                        <div class="text-5xl font-bold text-red-500 mb-4">18+</div>
                        <p class="text-white text-lg mb-6 max-w-md">{{ $ageGate?->message ?? 'Ben je 18 jaar of ouder?' }}</p>
                        <div class="flex gap-4">
                            <button @click="confirmAge()" class="bg-accent text-black px-6 py-3 text-sm font-bold uppercase tracking-wider hover:brightness-90 transition">
                                {{ $ageGate?->confirm_text ?? 'Ja, ik ben 18+' }}
                            </button>
                            <button @click="close()" class="bg-zinc-800 text-white px-6 py-3 text-sm font-bold uppercase tracking-wider hover:bg-zinc-700 transition">
                                {{ $ageGate?->deny_text ?? 'Nee' }}
                            </button>
                        </div>
                    </div>
                </template>

                {{-- Video Player (shown after age confirmation or if not restricted) --}}
                <template x-if="!episode?.ageRestricted || ageConfirmed">
                    <div class="aspect-video bg-black rounded-none md:rounded-sm overflow-hidden mb-0 md:mb-4">
                        <template x-if="episode && episode.isYoutube && episode.embedUrl">
                            <iframe
                                :src="episode.embedUrl"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                            ></iframe>
                        </template>
                        <template x-if="episode && !episode.isYoutube && episode.videoUrl">
                            <video controls autoplay class="w-full h-full" :src="episode.videoUrl"></video>
                        </template>
                    </div>
                </template>

                {{-- Episode Info --}}
                <div class="text-white px-4 py-4 md:px-0 md:py-0" x-show="episode">
                    <h2 class="text-2xl md:text-3xl font-bold uppercase tracking-wider mb-2" x-text="episode?.title"></h2>

                    {{-- Characters (clickable) --}}
                    <template x-if="episode?.characters?.length > 0">
                        <div class="flex flex-wrap gap-2 mb-3">
                            <template x-for="(c, i) in episode.characters" :key="i">
                                <button
                                    @click.stop="showChar(c)"
                                    class="px-2 py-1 text-xs bg-zinc-800 text-accent rounded-sm uppercase tracking-wider hover:bg-zinc-700 transition cursor-pointer"
                                    x-text="c.name"
                                ></button>
                            </template>
                        </div>
                    </template>

                    <div class="prose prose-invert prose-sm prose-zinc font-description max-w-3xl content-block-text" x-html="episode?.description" x-show="episode?.description"></div>

                    {{-- Social Links --}}
                    <div class="flex flex-wrap gap-3">
                        <template x-if="episode?.instagram">
                            <a :href="episode.instagram" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
                                Instagram
                            </a>
                        </template>
                        <template x-if="episode?.youtube">
                            <a :href="episode.youtube" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </a>
                        </template>
                        <template x-if="episode?.tiktok">
                            <a :href="episode.tiktok" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                TikTok
                            </a>
                        </template>
                        <template x-if="episode?.twitter">
                            <a :href="episode.twitter" target="_blank" rel="noopener" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition" style="gap: 12px;">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                Twitter
                            </a>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>

    {{-- ============================================================
         CHARACTER POPUP MODAL
         ============================================================ --}}
    <template x-teleport="body">
        <div
            x-show="charOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-[60] flex items-center justify-center bg-black/80 p-4 md:p-8"
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

                    <div class="p-6 md:p-8 flex flex-col justify-center">
                        <h2 class="text-3xl md:text-4xl font-bold uppercase tracking-wider text-white mb-1" x-text="char?.name"></h2>
                        <template x-if="char?.nickname">
                            <p class="text-accent text-sm uppercase tracking-wider mb-2">"<span x-text="char?.nickname"></span>"</p>
                        </template>
                        <template x-if="char?.job">
                            <p class="text-zinc-500 text-lg uppercase tracking-wider mb-6" x-text="char?.job"></p>
                        </template>
                        <template x-if="char?.bio">
                            <p class="text-zinc-400 text-sm leading-relaxed font-description" x-text="char?.bio"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
