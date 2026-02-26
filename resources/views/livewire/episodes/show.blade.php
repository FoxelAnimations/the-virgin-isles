<div class="bg-black min-h-screen -mt-16 pt-16 text-white"
    x-data="{
        open: false,
        episode: null,
        charOpen: false,
        char: null,
        openEpisode(ep) {
            this.episode = ep;
            this.open = true;
            document.body.classList.add('overflow-hidden');
        },
        close() {
            this.open = false;
            this.episode = null;
            document.body.classList.remove('overflow-hidden');
        },
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
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-10">
            Afleveringen
        </h1>

        @if ($episodes->isEmpty())
            <p class="text-center text-zinc-600 text-lg">{{ __('Nog geen afleveringen.') }}</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($episodes as $episode)
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
                            <span class="absolute top-2 right-2 px-2 py-0.5 text-xs font-semibold uppercase tracking-wider {{ $episode->isYoutube() ? 'bg-red-600 text-white' : 'bg-accent text-black' }}">
                                {{ $episode->source_type }}
                            </span>
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
                                <p class="text-sm text-zinc-500 line-clamp-2">{{ Illuminate\Support\Str::limit($episode->description, 120) }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/95 p-4 md:p-8"
            @click.self="close()"
            style="display: none;"
        >
            <button @click="close()" class="absolute top-4 right-4 z-10 text-white hover:text-accent transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="w-full max-w-5xl max-h-full overflow-y-auto" @click.stop>
                {{-- Video Player --}}
                <div class="aspect-video bg-black rounded-sm overflow-hidden mb-4">
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

                {{-- Episode Info --}}
                <div class="text-white" x-show="episode">
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

                    <p class="text-zinc-400 text-sm mb-4 max-w-3xl" x-text="episode?.description" x-show="episode?.description"></p>

                    {{-- Social Links --}}
                    <div class="flex flex-wrap gap-3">
                        <template x-if="episode?.instagram">
                            <a :href="episode.instagram" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition">Instagram</a>
                        </template>
                        <template x-if="episode?.youtube">
                            <a :href="episode.youtube" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition">YouTube</a>
                        </template>
                        <template x-if="episode?.tiktok">
                            <a :href="episode.tiktok" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition">TikTok</a>
                        </template>
                        <template x-if="episode?.twitter">
                            <a :href="episode.twitter" target="_blank" rel="noopener" class="inline-flex items-center gap-1.5 bg-accent text-black px-3 py-1.5 text-xs font-semibold uppercase tracking-wider hover:brightness-90 transition">Twitter</a>
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
                            <p class="text-zinc-400 text-sm leading-relaxed" x-text="char?.bio"></p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
