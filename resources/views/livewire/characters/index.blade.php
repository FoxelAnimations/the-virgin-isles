<div class="bg-black min-h-screen -mt-16 pt-16 text-white"
    x-data="{
        open: false,
        char: null,
        show(c) { this.char = c; this.open = true; document.body.classList.add('overflow-hidden'); },
        close() { this.open = false; this.char = null; document.body.classList.remove('overflow-hidden'); },
    }"
    @keydown.escape.window="close()"
>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-10">
            Personages
        </h1>

        @if ($characters->isEmpty())
            <p class="text-center text-zinc-600 text-lg">{{ __('Nog geen personages.') }}</p>
        @else
            <div class="grid gap-6 grid-cols-2 md:grid-cols-3 xl:grid-cols-5">
                @foreach ($characters as $character)
                    @php
                        $mainImg = $character->profile_photo_path ?? $character->profile_image_path;
                        $hoverImg = $character->profile_photo_path
                            ? $character->profile_photo_hover_path
                            : $character->profile_image_hover_path;
                    @endphp
                    <div
                        class="border border-zinc-800 bg-zinc-900 rounded-sm overflow-hidden group cursor-pointer transition hover:border-accent"
                        @click="show({
                            name: {{ Js::from($character->full_name) }},
                            nickname: {{ Js::from($character->nick_name) }},
                            age: {{ Js::from($character->age) }},
                            job: {{ Js::from($character->job?->title) }},
                            bio: {{ Js::from($character->bio) }},
                            image: {{ Js::from($mainImg ? Storage::url($mainImg) : null) }},
                            imageHover: {{ Js::from($hoverImg ? Storage::url($hoverImg) : null) }},
                            fullBody: {{ Js::from($character->full_body_image_path ? Storage::url($character->full_body_image_path) : null) }},
                            fullBodyHover: {{ Js::from($character->full_body_image_hover_path ? Storage::url($character->full_body_image_hover_path) : null) }},
                            background: {{ Js::from($character->background_image_path ? Storage::url($character->background_image_path) : null) }},
                            links: {{ Js::from($character->socialLinks->map(fn($l) => ['title' => $l->title, 'url' => $l->url])) }},
                        })"
                    >
                        {{-- Character Image --}}
                        <div class="w-full aspect-[3/4] overflow-hidden relative"
                            @if($character->background_image_path)
                                style="background-image: url('{{ Storage::url($character->background_image_path) }}'); background-size: cover; background-position: center;"
                            @else
                                class="bg-zinc-800"
                            @endif
                        >
                            @if ($mainImg)
                                <img
                                    src="{{ Storage::url($mainImg) }}"
                                    alt="{{ $character->full_name }}"
                                    class="w-full h-full object-cover object-top transition duration-300 relative z-[1] {{ $hoverImg ? 'group-hover:opacity-0' : 'group-hover:scale-105' }}"
                                >
                                @if ($hoverImg)
                                    <img
                                        src="{{ Storage::url($hoverImg) }}"
                                        alt="{{ $character->full_name }}"
                                        class="absolute inset-0 w-full h-full object-cover object-top z-[2] opacity-0 transition duration-300 group-hover:opacity-100"
                                    >
                                @endif
                            @elseif(!$character->background_image_path)
                                <div class="w-full h-full flex items-center justify-center text-zinc-600">
                                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </div>
                            @endif
                        </div>

                        {{-- Character Info --}}
                        <div class="p-4 sm:p-5">
                            <h2 class="text-base sm:text-xl font-bold uppercase tracking-wider">
                                {{ $character->full_name }}
                            </h2>
                            @if ($character->nick_name)
                                <p class="text-accent text-xs sm:text-sm uppercase tracking-wider">"{{ $character->nick_name }}"</p>
                            @endif
                            @if ($character->job)
                                <p class="text-zinc-500 text-xs sm:text-sm mt-1">{{ $character->job->title }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ============================================================
         CHARACTER POPUP MODAL
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
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            @click.self="close()"
            style="display: none;"
        >
            <button @click="close()" class="absolute top-4 right-4 z-10 text-white hover:text-accent transition">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

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
                        <template x-if="!char?.fullBody && !char?.image && !char?.background">
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
</div>
