<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Geblokkeerde bezoekers') }}</h1>
            </div>
            <a href="{{ route('admin.chats') }}" class="inline-flex items-center gap-2 border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                {{ __('Terug naar chats') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Active Blocks --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mb-8">
            <div class="bg-zinc-800 px-4 py-3">
                <h2 class="text-sm font-semibold uppercase tracking-wider text-yellow-400">
                    {{ __('Actieve blokkeringen') }}
                    <span class="text-zinc-500 ml-1">({{ $activeBlocks->count() }})</span>
                </h2>
            </div>

            @forelse ($activeBlocks as $block)
                <div x-data="{ open: true }" wire:key="block-{{ $block->id }}" class="border-b border-zinc-800 last:border-b-0">
                    {{-- Clickable row --}}
                    <div @click="open = !open" class="flex items-center gap-4 px-4 py-3 cursor-pointer hover:bg-zinc-800/50 transition select-none">
                        <svg class="w-4 h-4 text-zinc-500 transition-transform shrink-0" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>

                        <div class="flex-1 grid grid-cols-5 gap-4 items-center min-w-0">
                            <div>
                                <span class="font-mono text-sm text-white">{{ $block->ip_address }}</span>
                                @if ($block->visitor_uuid)
                                    <span class="text-zinc-600 text-xs font-mono ml-1">({{ substr($block->visitor_uuid, 0, 8) }})</span>
                                @endif
                            </div>
                            <div class="text-sm text-zinc-300 truncate">{{ $block->reason ?: '-' }}</div>
                            <div class="text-sm text-zinc-400">{{ $block->blockedByUser?->name ?? '-' }}</div>
                            <div class="text-sm text-zinc-500">{{ $block->created_at->format('d-m-Y H:i') }}</div>
                            <div class="text-sm">
                                @if ($block->expires_at)
                                    <span class="text-yellow-400">{{ $block->expires_at->diffForHumans() }}</span>
                                @else
                                    <span class="text-red-400 font-semibold">{{ __('Nooit') }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0" @click.stop>
                            <button
                                wire:click="unblock({{ $block->id }})"
                                class="text-xs text-green-400 border border-green-900 px-3 py-1 hover:bg-green-900/30 transition"
                            >
                                {{ __('Deblokkeren') }}
                            </button>
                            <button
                                wire:click="deleteBlock({{ $block->id }})"
                                wire:confirm="Weet je zeker dat je deze blokkering wilt verwijderen?"
                                class="text-xs text-red-400 border border-red-900 px-3 py-1 hover:bg-red-900/30 transition"
                            >
                                {{ __('Verwijder') }}
                            </button>
                        </div>
                    </div>

                    {{-- Expanded detail --}}
                    <div x-show="open" x-collapse>
                        <div class="bg-zinc-950/50 border-t border-zinc-800 px-4 py-4">
                            @if ($block->conversations->count() > 0)
                                <p class="text-xs uppercase tracking-wider text-zinc-500 mb-3">{{ __('Gesprekken') }} ({{ $block->conversations->count() }})</p>
                                <div class="space-y-2">
                                    @foreach ($block->conversations as $conversation)
                                        <a href="{{ route('admin.chats.view', $conversation) }}" class="flex items-center gap-4 px-3 py-2 rounded-sm hover:bg-zinc-800/70 transition group" wire:key="conv-{{ $conversation->id }}">
                                            @if ($conversation->character?->profile_photo_path || $conversation->character?->profile_image_path)
                                                <img src="{{ Storage::url($conversation->character->profile_photo_path ?? $conversation->character->profile_image_path) }}" class="w-8 h-8 rounded-full object-cover border border-zinc-700" alt="">
                                            @else
                                                <div class="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-xs font-bold text-zinc-400">
                                                    {{ substr($conversation->character?->first_name ?? '?', 0, 1) }}
                                                </div>
                                            @endif
                                            <div class="flex-1 min-w-0">
                                                <span class="text-sm text-white font-medium">{{ $conversation->character?->full_name ?? 'Verwijderd' }}</span>
                                                @if ($conversation->visitor_name)
                                                    <span class="text-zinc-500 text-sm ml-2">{{ $conversation->visitor_name }}</span>
                                                @endif
                                            </div>
                                            <span class="text-xs text-zinc-500">{{ $conversation->messages_count }} {{ __('berichten') }}</span>
                                            <span class="text-xs text-zinc-600">{{ $conversation->last_message_at?->diffForHumans() ?? '-' }}</span>
                                            <span class="text-xs text-accent opacity-0 group-hover:opacity-100 transition">{{ __('Open') }} &rarr;</span>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-sm text-zinc-600">{{ __('Geen gesprekken gevonden voor deze bezoeker.') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-4 py-8 text-center text-sm text-zinc-500">
                    {{ __('Geen actieve blokkeringen.') }}
                </div>
            @endforelse
        </div>

        {{-- Expired Blocks --}}
        @if ($expiredBlocks->count() > 0)
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden" x-data="{ open: false }">
                <button @click="open = !open" class="w-full bg-zinc-800 px-4 py-3 flex items-center justify-between hover:bg-zinc-700/50 transition">
                    <h2 class="text-sm font-semibold uppercase tracking-wider text-zinc-500">
                        {{ __('Verlopen blokkeringen') }}
                        <span class="ml-1">({{ $expiredBlocks->count() }})</span>
                    </h2>
                    <svg class="w-4 h-4 text-zinc-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="open" x-collapse>
                    @foreach ($expiredBlocks as $block)
                        <div class="flex items-center gap-4 px-4 py-3 border-t border-zinc-800 opacity-50" wire:key="expired-{{ $block->id }}">
                            <div class="w-4 shrink-0"></div>
                            <div class="flex-1 grid grid-cols-5 gap-4 items-center min-w-0">
                                <div>
                                    <span class="font-mono text-sm text-white">{{ $block->ip_address }}</span>
                                    @if ($block->visitor_uuid)
                                        <span class="text-zinc-600 text-xs font-mono ml-1">({{ substr($block->visitor_uuid, 0, 8) }})</span>
                                    @endif
                                </div>
                                <div class="text-sm text-zinc-300 truncate">{{ $block->reason ?: '-' }}</div>
                                <div class="text-sm text-zinc-400">{{ $block->blockedByUser?->name ?? '-' }}</div>
                                <div class="text-sm text-zinc-500">{{ $block->created_at->format('d-m-Y H:i') }}</div>
                                <div class="text-sm text-zinc-500">{{ $block->expires_at->format('d-m-Y H:i') }}</div>
                            </div>
                            <div class="shrink-0">
                                <button
                                    wire:click="deleteBlock({{ $block->id }})"
                                    wire:confirm="Weet je zeker dat je deze blokkering wilt verwijderen?"
                                    class="text-xs text-red-400 border border-red-900 px-3 py-1 hover:bg-red-900/30 transition"
                                >
                                    {{ __('Verwijder') }}
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
