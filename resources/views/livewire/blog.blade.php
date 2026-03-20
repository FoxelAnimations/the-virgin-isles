<div class="bg-black min-h-screen -mt-16 pt-16 text-white">

    {{-- Page Header --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 md:py-20">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold uppercase text-center tracking-wider mb-6">
            Blog
        </h1>
    </section>

    {{-- Blog Posts Grid --}}
    @if ($posts->isNotEmpty())
        <section class="pb-12 md:pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($posts as $post)
                        <a href="{{ route('blog.show', $post->slug) }}"
                           class="group bg-zinc-900 border border-zinc-800 rounded-sm overflow-hidden transition hover:border-zinc-700">
                            {{-- Featured image --}}
                            <div class="aspect-[16/10] overflow-hidden">
                                @if ($post->featured_image)
                                    <img src="{{ Storage::url($post->featured_image) }}"
                                         alt="{{ $post->title }}"
                                         class="w-full h-full object-cover transition group-hover:scale-105 duration-500">
                                @else
                                    <div class="w-full h-full bg-zinc-800 flex items-center justify-center p-8">
                                        <img src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}" class="max-h-full max-w-full object-contain opacity-30">
                                    </div>
                                @endif
                            </div>
                            {{-- Text --}}
                            <div class="p-5">
                                <h2 class="text-xl font-bold uppercase tracking-wider mb-2 group-hover:text-accent transition">
                                    {{ $post->title }}
                                </h2>
                                @if ($post->excerpt)
                                    <p class="text-zinc-400 text-sm leading-relaxed font-description line-clamp-3">
                                        {{ $post->excerpt }}
                                    </p>
                                @endif
                                <span class="inline-flex items-center mt-4 text-accent text-sm font-bold uppercase tracking-wider">
                                    {{ __('Lees meer') }}
                                    <svg class="w-4 h-4 ml-1 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @else
        <section class="py-12 md:py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-zinc-500 text-lg text-center">{{ __('Binnenkort beschikbaar.') }}</p>
            </div>
        </section>
    @endif
</div>
