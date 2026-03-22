<div wire:poll.3s="sendHeartbeat" class="flex flex-col h-full bg-zinc-900/50">
    {{-- Header: viewer count --}}
    <div class="px-3 py-2 bg-zinc-800/80 border-b border-zinc-700/50 flex items-center justify-between shrink-0">
        <span class="text-xs uppercase tracking-wider font-semibold text-zinc-300">Live Chat</span>
        <span class="flex items-center gap-1.5 text-zinc-400 text-xs">
            <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
            <span>{{ $this->viewerCount }} {{ $this->viewerCount === 1 ? 'kijker' : 'kijkers' }}</span>
        </span>
    </div>

    {{-- Messages area --}}
    <div class="flex-1 overflow-y-auto px-3 py-2 space-y-1 min-h-0"
         x-data="{ autoScroll: true }"
         x-ref="chatScroll"
         @scroll="autoScroll = ($el.scrollTop + $el.clientHeight >= $el.scrollHeight - 30)"
         x-effect="if(autoScroll) $nextTick(() => { if($refs.chatScroll) $refs.chatScroll.scrollTop = $refs.chatScroll.scrollHeight })">

        @foreach ($this->messages as $msg)
            <div class="text-sm leading-relaxed" wire:key="msg-{{ $msg->id }}">
                <span class="font-semibold text-accent text-xs">{{ $msg->user->name }}</span>
                <span class="text-white/80">{{ $msg->body }}</span>
            </div>
        @endforeach

        @if ($this->messages->isEmpty())
            <p class="text-zinc-600 text-xs text-center py-8">Nog geen berichten.</p>
        @endif
    </div>

    {{-- Input area --}}
    @auth
        <form wire:submit="sendMessage" class="px-3 py-2 border-t border-zinc-700/50 shrink-0">
            <div class="flex gap-2">
                <input type="text"
                    wire:model="message"
                    maxlength="200"
                    placeholder="Typ een bericht..."
                    autocomplete="off"
                    class="flex-1 bg-zinc-800 border border-zinc-700 text-white text-sm px-3 py-1.5 rounded-sm focus:border-accent focus:ring-accent"
                >
                <button type="submit"
                    class="bg-accent text-black px-3 py-1.5 rounded-sm text-sm font-semibold uppercase tracking-wider hover:brightness-90 transition shrink-0"
                    wire:loading.attr="disabled">
                    Stuur
                </button>
            </div>
            @error('message')
                <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
            @enderror
        </form>
    @else
        <div class="px-3 py-3 border-t border-zinc-700/50 text-center shrink-0">
            <p class="text-zinc-500 text-xs">
                <a href="{{ route('login') }}" class="text-accent hover:brightness-90 transition">Log in</a> om mee te chatten.
            </p>
        </div>
    @endauth
</div>
