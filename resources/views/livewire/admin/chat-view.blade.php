<div wire:poll.3s class="h-screen flex flex-col overflow-hidden">
    {{-- Header --}}
    <div class="bg-zinc-900 border-b border-zinc-800 px-4 py-3 shrink-0">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.chats') }}" class="inline-flex items-center gap-1 text-sm text-zinc-400 hover:text-accent transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    {{ __('Terug') }}
                </a>
                <div class="flex items-center gap-3">
                    @if ($character?->profile_photo_path || $character?->profile_image_path)
                        <img src="{{ Storage::url($character->profile_photo_path ?? $character->profile_image_path) }}" class="w-8 h-8 rounded-full object-cover border border-zinc-600" alt="">
                    @else
                        <div class="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-sm font-bold text-zinc-400">
                            {{ substr($character?->first_name ?? '?', 0, 1) }}
                        </div>
                    @endif
                    <div>
                        <h2 class="text-sm font-bold text-white">{{ $character?->full_name ?? 'Verwijderd personage' }}</h2>
                        <div class="flex items-center gap-2 text-xs text-zinc-500">
                            @if ($editingName)
                                <form wire:submit="saveVisitorName" class="flex items-center gap-1">
                                    <input
                                        type="text"
                                        wire:model="visitorName"
                                        class="bg-zinc-800 border border-zinc-600 text-white text-xs px-2 py-0.5 rounded-sm w-28 focus:border-accent focus:ring-accent"
                                        placeholder="Naam..."
                                        autofocus
                                    >
                                    <button type="submit" class="text-accent hover:text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </button>
                                    <button type="button" wire:click="$set('editingName', false)" class="text-zinc-500 hover:text-white transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            @else
                                <button wire:click="$set('editingName', true)" class="group flex items-center gap-1 hover:text-accent transition">
                                    @if ($conversation->visitor_name)
                                        <span class="text-zinc-300">{{ $conversation->visitor_name }}</span>
                                        <span class="font-mono text-zinc-600">({{ substr($conversation->visitor_uuid, 0, 8) }})</span>
                                    @else
                                        <span class="font-mono">{{ substr($conversation->visitor_uuid, 0, 8) }}...</span>
                                    @endif
                                    <svg class="w-3 h-3 text-zinc-600 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </button>
                            @endif
                            <span>&middot;</span>
                            <span>{{ $character?->chat_mode === 'manual' ? 'Manueel' : 'AI' }}</span>
                            @if ($conversation->visitor_ip)
                                <span>&middot;</span>
                                <span class="font-mono text-zinc-600">{{ $conversation->visitor_ip }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if ($isBlocked)
                    <button wire:click="unblockVisitor" class="text-xs text-green-400 border border-green-900 px-3 py-1.5 hover:bg-green-900/30 transition">
                        {{ __('Deblokkeren') }}
                    </button>
                @else
                    <button wire:click="openBlockModal" class="text-xs text-yellow-400 border border-yellow-900 px-3 py-1.5 hover:bg-yellow-900/30 transition">
                        {{ __('Blokkeren') }}
                    </button>
                @endif
                <button wire:click="deleteConversation" wire:confirm="Gesprek permanent verwijderen?" class="text-xs text-red-400 border border-red-900 px-3 py-1.5 hover:bg-red-900/30 transition">
                    {{ __('Verwijderen') }}
                </button>
            </div>
        </div>
    </div>

    @if (session('status'))
        <div class="bg-accent/10 border-b border-accent/30 px-4 py-2 text-sm text-accent text-center shrink-0">
            {{ session('status') }}
        </div>
    @endif

    {{-- Messages --}}
    <div
        class="flex-1 overflow-hidden"
        x-data="{
            showBlockedPing: false,
            scrollToBottom() {
                const el = $refs.msgs;
                if (el) {
                    this.$nextTick(() => { el.scrollTop = el.scrollHeight; });
                }
            },
            playPing() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    ctx.resume().then(() => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.type = 'triangle';
                        osc.frequency.setValueAtTime(520, ctx.currentTime);
                        osc.frequency.setValueAtTime(440, ctx.currentTime + 0.15);
                        gain.gain.setValueAtTime(0.4, ctx.currentTime);
                        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                        osc.start(ctx.currentTime);
                        osc.stop(ctx.currentTime + 0.4);
                    });
                } catch (e) {}
            },
            flashBlockedPing() {
                this.showBlockedPing = true;
                setTimeout(() => this.showBlockedPing = false, 3000);
            }
        }"
        x-init="scrollToBottom()"
        @scroll-chat.window="scrollToBottom()"
        @new-message.window="playPing()"
        @blocked-ping.window="flashBlockedPing()"
    >
        <div x-ref="msgs" class="h-full overflow-y-auto p-4 space-y-3">
            <div x-ref="msgList" class="max-w-4xl mx-auto space-y-3">
                @if ($hasMore)
                    <div class="text-center py-2">
                        <button wire:click="loadMore" class="text-xs text-accent border border-accent/30 px-4 py-1.5 hover:bg-accent/10 transition">
                            {{ __('Laad meer berichten') }}
                        </button>
                    </div>
                @endif

                @forelse ($messages as $msg)
                    <div class="flex {{ $msg->sender === 'visitor' ? 'justify-start' : 'justify-end' }}">
                        <div class="{{ $msg->sender === 'visitor'
                            ? 'bg-zinc-800 text-white rounded-sm rounded-bl-none border border-zinc-700'
                            : 'bg-accent text-black rounded-sm rounded-br-none' }} px-3 py-2 max-w-[80%] overflow-hidden">
                            <p class="text-sm whitespace-pre-wrap break-words">{{ $msg->content }}</p>
                            <div class="flex items-center gap-1 mt-1 {{ $msg->sender === 'visitor' ? 'text-zinc-500' : 'text-black/50' }}">
                                <span class="text-xs">{{ $msg->created_at->format('H:i') }}</span>
                                @if ($msg->sender === 'character' && $msg->is_ai)
                                    <span class="text-xs font-semibold ml-1">AI</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-zinc-500 text-sm py-8">
                        {{ __('Nog geen berichten.') }}
                    </div>
                @endforelse

                {{-- Blocked visitor ping --}}
                <div x-show="showBlockedPing" x-transition class="flex justify-start">
                    <div class="flex items-center gap-2 bg-red-500/10 border border-red-900/50 rounded-sm px-3 py-1.5 text-xs text-red-400">
                        <span class="flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-red-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                        </span>
                        {{ __('Geblokkeerde bezoeker probeert te versturen...') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Reply Form --}}
    <div class="bg-zinc-900 border-t border-zinc-800 px-4 py-3 shrink-0">
        <div class="max-w-4xl mx-auto">
            <form wire:submit="sendReply">
                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Reageer als') }} {{ $character?->full_name }}</label>
                <div class="flex items-stretch gap-3">
                    <div class="flex-1">
                        <textarea
                            wire:model="reply"
                            rows="2"
                            maxlength="2000"
                            placeholder="{{ __('Typ je antwoord...') }}"
                            class="w-full h-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                            @keydown.enter.prevent="if (!$event.shiftKey) $wire.sendReply()"
                        ></textarea>
                    </div>
                    <button type="submit" class="inline-flex items-center bg-accent text-black px-4 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90 shrink-0 rounded-sm" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendReply">{{ __('Verstuur') }}</span>
                        <span wire:loading wire:target="sendReply">{{ __('Bezig...') }}</span>
                    </button>
                </div>
                @error('reply') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
            </form>
            <p class="text-xs text-zinc-600 mt-1">{{ __('Enter om te versturen, Shift+Enter voor nieuwe regel') }}</p>
        </div>
    </div>

    {{-- Block Modal --}}
    @if ($showBlockModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4" x-data @keydown.escape.window="$wire.closeBlockModal()">
            <div class="absolute inset-0" wire:click="closeBlockModal"></div>
            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-md" @click.stop>
                <div class="bg-zinc-800 text-yellow-400 px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>Bezoeker blokkeren</span>
                    <button wire:click="closeBlockModal" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="p-5">
                    <form wire:submit="blockVisitor" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">IP-adres</label>
                            @if ($conversation->visitor_ip)
                                <p class="text-white font-mono text-sm">{{ $conversation->visitor_ip }}</p>
                            @else
                                <p class="text-yellow-400 text-sm">Nog geen IP bekend. De bezoeker moet eerst een nieuw bericht sturen.</p>
                            @endif
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Reden *</label>
                            <textarea wire:model="blockReason" rows="3" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Reden voor blokkering..."></textarea>
                            @error('blockReason') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-2">Duur</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="blockDuration" value="day" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                    <span class="text-sm text-white">24 uur</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" wire:model="blockDuration" value="indefinite" class="border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                    <span class="text-sm text-white">Onbeperkt</span>
                                </label>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 pt-2">
                            <button type="submit" class="inline-flex items-center bg-yellow-500 text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                                Blokkeren
                            </button>
                            <button type="button" wire:click="closeBlockModal" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white">
                                Annuleren
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
