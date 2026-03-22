<div class="bg-black min-h-screen -mt-16 pt-16 text-white"
     x-data="{ videoOpen: false }">
    <article class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">

        {{-- Back link --}}
        <a href="{{ route('collabs') }}" class="inline-flex items-center text-zinc-400 hover:text-accent text-sm font-bold uppercase tracking-wider transition mb-8">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
            {{ __('Terug naar collabs') }}
        </a>

        {{-- Title --}}
        <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold uppercase tracking-wider leading-none mb-6">
            {{ $collab->title }}
        </h1>

        {{-- Character tag --}}
        @if ($collab->character)
            <div class="mb-6">
                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-zinc-900 border border-zinc-800 rounded-sm">
                    @if ($collab->character->profile_image_path)
                        <img src="{{ Storage::url($collab->character->profile_image_path) }}" alt="" class="w-6 h-6 rounded-full object-cover">
                    @endif
                    <span class="text-sm font-semibold text-accent uppercase tracking-wider">{{ $collab->character->full_name }}</span>
                </span>
            </div>
        @endif

        {{-- Image + Video side by side --}}
        @if ($collab->featured_image || $collab->episode)
            <div class="grid grid-cols-1 {{ $collab->featured_image && $collab->episode ? 'md:grid-cols-2' : '' }} gap-4 mb-8">
                {{-- Featured image --}}
                @if ($collab->featured_image)
                    <div class="rounded-sm overflow-hidden border border-zinc-800">
                        <img src="{{ Storage::url($collab->featured_image) }}"
                             alt="{{ $collab->title }}"
                             class="w-full object-cover max-h-[400px]">
                    </div>
                @endif

                {{-- Video thumbnail (click to open popup) --}}
                @if ($collab->episode)
                    <div class="rounded-sm overflow-hidden border border-zinc-800 relative cursor-pointer group"
                         @click="videoOpen = true">
                        @if ($collab->episode->thumbnailUrl())
                            <img src="{{ $collab->episode->thumbnailUrl() }}"
                                 alt="{{ $collab->episode->title }}"
                                 class="w-full object-cover max-h-[400px] h-full transition group-hover:scale-105 duration-500">
                        @else
                            <div class="w-full h-full min-h-[200px] bg-zinc-900 flex items-center justify-center">
                                <svg class="w-16 h-16 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        @endif
                        {{-- Play overlay --}}
                        <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition">
                            <div class="w-16 h-16 rounded-full bg-accent/90 flex items-center justify-center group-hover:scale-110 transition">
                                <svg class="w-7 h-7 text-black ml-1" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Content --}}
        @if ($collab->content)
            <div class="prose prose-invert prose-lg prose-zinc font-description max-w-none content-block-text">
                {!! $collab->content !!}
            </div>
        @endif

        {{-- Links --}}
        @if ($collab->hasLink1() || $collab->hasLink2())
            <div class="flex flex-wrap gap-4 mt-10">
                @if ($collab->hasLink1())
                    <a href="{{ $collab->link1_url }}"
                       @if($collab->link1_new_tab) target="_blank" rel="noopener" @endif
                       class="inline-flex items-center bg-accent text-black px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:brightness-90">
                        {{ $collab->link1_label }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                @endif
                @if ($collab->hasLink2())
                    <a href="{{ $collab->link2_url }}"
                       @if($collab->link2_new_tab) target="_blank" rel="noopener" @endif
                       class="inline-flex items-center border border-accent text-accent px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:bg-accent hover:text-black">
                        {{ $collab->link2_label }}
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                @endif
            </div>
        @endif

        {{-- Separator --}}
        <div class="mt-12 md:mt-16 flex justify-center">
            <div class="w-24 h-1 rounded-full bg-accent"></div>
        </div>

        {{-- Back to collabs --}}
        <div class="mt-8 text-center">
            <a href="{{ route('collabs') }}" class="inline-flex items-center text-zinc-400 hover:text-accent text-sm font-bold uppercase tracking-wider transition">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                {{ __('Alle collabs') }}
            </a>
        </div>
    </article>

    {{-- Video Popup Modal --}}
    @if ($collab->episode)
        <template x-teleport="body">
            <div
                x-show="videoOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-50 flex items-center justify-center bg-black md:bg-black/95 p-0 md:p-8"
                @click.self="videoOpen = false"
                @keydown.escape.window="videoOpen = false"
                style="display: none;"
            >
                <button @click="videoOpen = false" class="absolute top-2 right-2 md:top-4 md:right-4 z-20 text-white hover:text-accent transition">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>

                <div class="w-full md:max-w-5xl md:max-h-full" @click.stop>
                    <div class="aspect-video bg-black rounded-none md:rounded-sm overflow-hidden">
                        @if ($collab->episode->isYoutube() && $collab->episode->youtubeEmbedUrl())
                            <template x-if="videoOpen">
                                <iframe src="{{ $collab->episode->youtubeEmbedUrl() }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                            </template>
                        @elseif ($collab->episode->videoUrl())
                            <template x-if="videoOpen">
                                <video controls autoplay class="w-full h-full" src="{{ $collab->episode->videoUrl() }}"></video>
                            </template>
                        @endif
                    </div>
                </div>
            </div>
        </template>
    @endif
</div>
