<div
    wire:poll.5s
    class="py-10"
    x-data="{
        tab: 'gesprekken',
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
        }
    }"
    @new-message.window="playPing()"
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">
                    {{ __('Chats') }}
                    @if ($totalUnread > 0)
                        <span class="inline-flex items-center justify-center ml-2 px-2 py-0.5 text-sm font-bold bg-red-500 text-white rounded-full">{{ $totalUnread }}</span>
                    @endif
                </h1>
            </div>
            <a href="{{ route('admin.chats.blocked') }}" class="inline-flex items-center gap-2 border border-yellow-900 text-yellow-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:bg-yellow-900/30">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                {{ __('Geblokkeerd') }}
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        @if (session('settings-status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('settings-status') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-1 mb-6">
            <button
                @click="tab = 'gesprekken'"
                :class="tab === 'gesprekken' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white'"
                class="px-5 py-2 text-sm font-semibold uppercase tracking-wider transition"
            >
                {{ __('Gesprekken') }}
            </button>
            <button
                @click="tab = 'instellingen'"
                :class="tab === 'instellingen' ? 'bg-accent text-black' : 'bg-zinc-800 text-zinc-400 hover:text-white'"
                class="px-5 py-2 text-sm font-semibold uppercase tracking-wider transition"
            >
                {{ __('Instellingen') }}
            </button>
        </div>

        {{-- Tab: Gesprekken --}}
        <div x-show="tab === 'gesprekken'" x-cloak>

        {{-- Character Online/Offline Bar --}}
        @if ($chatCharacters->count() > 0)
            <div class="mb-6 rounded-sm bg-zinc-900 border border-zinc-800 p-4">
                <div class="flex flex-wrap gap-4">
                    @foreach ($chatCharacters as $char)
                        <button
                            wire:click="toggleOnline({{ $char->id }})"
                            class="flex flex-col items-center gap-1.5 group transition"
                            title="{{ $char->full_name }} — klik om te wisselen"
                        >
                            <div class="relative">
                                @if ($char->profile_photo_path || $char->profile_image_path)
                                    <img
                                        src="{{ Storage::url($char->profile_photo_path ?? $char->profile_image_path) }}"
                                        alt="{{ $char->full_name }}"
                                        class="w-14 h-14 object-cover rounded-sm border-2 transition {{ $char->chat_online ? 'border-green-500' : 'border-zinc-700 grayscale opacity-50' }}"
                                    >
                                @else
                                    <div class="w-14 h-14 rounded-sm flex items-center justify-center text-lg font-bold transition {{ $char->chat_online ? 'bg-green-500/20 text-green-400 border-2 border-green-500' : 'bg-zinc-800 text-zinc-600 border-2 border-zinc-700' }}">
                                        {{ substr($char->first_name, 0, 1) }}
                                    </div>
                                @endif
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-zinc-900 {{ $char->chat_online ? 'bg-green-500' : 'bg-zinc-600' }}"></div>
                            </div>
                            <span class="text-xs font-medium {{ $char->chat_online ? 'text-green-400' : 'text-zinc-600' }}">
                                {{ $char->chat_online ? 'Online' : 'Offline' }}
                            </span>
                            <span class="text-[10px] text-zinc-500 -mt-1">{{ $char->first_name }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Conversations Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-zinc-800 text-xs font-semibold uppercase tracking-wider text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">{{ __('Character') }}</th>
                        <th class="px-4 py-3">{{ __('Bezoeker') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Berichten') }}</th>
                        <th class="px-4 py-3">{{ __('Laatste bericht') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('Acties') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    @forelse ($conversations as $conversation)
                        <tr class="hover:bg-zinc-800/50 transition {{ $conversation->unread_count > 0 && !$conversation->is_blocked ? 'bg-accent/5' : '' }} {{ $conversation->is_blocked ? 'opacity-60' : '' }}" wire:key="conv-{{ $conversation->id }}">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <div class="relative">
                                        @if ($conversation->character?->profile_photo_path || $conversation->character?->profile_image_path)
                                            <img src="{{ Storage::url($conversation->character->profile_photo_path ?? $conversation->character->profile_image_path) }}" class="w-8 h-8 rounded-full object-cover border border-zinc-700" alt="">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-zinc-700 flex items-center justify-center text-xs font-bold text-zinc-400">
                                                {{ substr($conversation->character?->first_name ?? '?', 0, 1) }}
                                            </div>
                                        @endif
                                        @if ($conversation->is_blocked && $conversation->blocked_attempt_at && $conversation->blocked_attempt_at->gt(now()->subSeconds(10)))
                                            <span class="absolute -top-1 -right-1 flex h-3 w-3">
                                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-white font-medium">{{ $conversation->character?->full_name ?? 'Verwijderd' }}</span>
                                        <span class="text-xs text-zinc-500">{{ $conversation->character?->chat_mode === 'manual' ? '(manueel)' : '(AI)' }}</span>
                                        @if ($conversation->is_blocked)
                                            <span class="text-[10px] font-semibold uppercase tracking-wider text-red-400 border border-red-900 px-1.5 py-0.5">Geblokkeerd</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                @if (isset($editingName[$conversation->id]))
                                    <form wire:submit="updateVisitorName({{ $conversation->id }})" class="flex items-center gap-1">
                                        <input
                                            type="text"
                                            wire:model="editingName.{{ $conversation->id }}"
                                            class="bg-zinc-800 border border-zinc-600 text-white text-xs px-2 py-1 rounded-sm w-32 focus:border-accent focus:ring-accent"
                                            placeholder="Naam..."
                                            autofocus
                                        >
                                        <button type="submit" class="text-accent hover:text-white transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                        <button type="button" wire:click="$set('editingName', [])" class="text-zinc-500 hover:text-white transition">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <button
                                        wire:click="$set('editingName.{{ $conversation->id }}', {{ Js::from($conversation->visitor_name ?? '') }})"
                                        class="group flex items-center gap-1 text-sm hover:text-accent transition"
                                    >
                                        @if ($conversation->visitor_name)
                                            <span class="text-white">{{ $conversation->visitor_name }}</span>
                                            <span class="text-zinc-600 text-xs font-mono">({{ substr($conversation->visitor_uuid, 0, 8) }})</span>
                                        @else
                                            <span class="text-zinc-400 font-mono text-xs">{{ substr($conversation->visitor_uuid, 0, 8) }}...</span>
                                        @endif
                                        <svg class="w-3 h-3 text-zinc-600 opacity-0 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </button>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm text-zinc-400">{{ $conversation->messages_count }}</span>
                                @if ($conversation->unread_count > 0)
                                    <span class="ml-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold bg-accent text-black rounded-full">{{ $conversation->unread_count }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500">
                                {{ $conversation->last_message_at?->diffForHumans() ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.chats.view', $conversation) }}" class="text-xs text-accent border border-accent/30 px-2 py-1 hover:bg-accent/10 transition">
                                        {{ __('Open') }}
                                    </a>
                                    <button wire:click="openBlockModal({{ $conversation->id }})" class="text-xs text-yellow-400 border border-yellow-900 px-2 py-1 hover:bg-yellow-900/30 transition" title="Blokkeren">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                    <button wire:click="deleteConversation({{ $conversation->id }})" wire:confirm="Gesprek permanent verwijderen?" class="text-xs text-red-400 border border-red-900 px-2 py-1 hover:bg-red-900/30 transition" title="Verwijderen">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500">
                                {{ __('Geen gesprekken gevonden.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    </div>{{-- End Tab: Gesprekken --}}

    {{-- Tab: Instellingen --}}
    <div x-show="tab === 'instellingen'" x-cloak>
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-6 space-y-8">

            {{-- Notification Sound --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-3">{{ __('Notificatiegeluid') }}</h3>
                <p class="text-xs text-zinc-500 mb-3">{{ __('Dit geluid wordt afgespeeld wanneer een bezoeker een nieuw bericht stuurt.') }}</p>

                @if ($settings?->chat_notification_sound)
                    <div class="flex items-center gap-3 mb-3">
                        <audio controls class="h-8">
                            <source src="{{ Storage::url($settings->chat_notification_sound) }}">
                        </audio>
                        <button wire:click="removeNotificationSound" wire:confirm="Notificatiegeluid verwijderen?" class="text-xs text-red-400 border border-red-900 px-3 py-1 hover:bg-red-900/30 transition">
                            {{ __('Verwijderen') }}
                        </button>
                    </div>
                @else
                    <p class="text-xs text-zinc-600 mb-3">{{ __('Geen geluid ingesteld — standaard pieptoon wordt gebruikt.') }}</p>
                @endif

                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <input
                            type="file"
                            wire:model="newNotificationSound"
                            accept=".mp3,.wav,.ogg,.m4a"
                            class="block w-full text-xs text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:border file:border-zinc-700 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-zinc-800 file:text-zinc-300 hover:file:bg-zinc-700 file:transition file:cursor-pointer"
                        >
                        @error('newNotificationSound') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @if ($newNotificationSound)
                        <button wire:click="saveNotificationSound" class="inline-flex items-center bg-accent text-black px-4 py-1.5 text-xs font-semibold uppercase tracking-wider transition hover:brightness-90 shrink-0">
                            {{ __('Opslaan') }}
                        </button>
                    @endif
                </div>
            </div>

            <div class="border-t border-zinc-800"></div>

            {{-- Blocked Sound --}}
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-zinc-400 mb-3">{{ __('Geblokkeerd geluid') }}</h3>
                <p class="text-xs text-zinc-500 mb-3">{{ __('Dit geluid wordt afgespeeld wanneer een geblokkeerde bezoeker op "verstuur" klikt.') }}</p>

                @if ($settings?->chat_blocked_sound)
                    <div class="flex items-center gap-3 mb-3">
                        <audio controls class="h-8">
                            <source src="{{ Storage::url($settings->chat_blocked_sound) }}">
                        </audio>
                        <button wire:click="removeBlockedSound" wire:confirm="Geblokkeerd geluid verwijderen?" class="text-xs text-red-400 border border-red-900 px-3 py-1 hover:bg-red-900/30 transition">
                            {{ __('Verwijderen') }}
                        </button>
                    </div>
                @else
                    <p class="text-xs text-zinc-600 mb-3">{{ __('Geen geluid ingesteld — standaard pieptoon wordt gebruikt.') }}</p>
                @endif

                <div class="flex items-end gap-3">
                    <div class="flex-1">
                        <input
                            type="file"
                            wire:model="newBlockedSound"
                            accept=".mp3,.wav,.ogg,.m4a"
                            class="block w-full text-xs text-zinc-400 file:mr-3 file:py-1.5 file:px-3 file:border file:border-zinc-700 file:text-xs file:font-semibold file:uppercase file:tracking-wider file:bg-zinc-800 file:text-zinc-300 hover:file:bg-zinc-700 file:transition file:cursor-pointer"
                        >
                        @error('newBlockedSound') <span class="text-red-400 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                    @if ($newBlockedSound)
                        <button wire:click="saveBlockedSound" class="inline-flex items-center bg-accent text-black px-4 py-1.5 text-xs font-semibold uppercase tracking-wider transition hover:brightness-90 shrink-0">
                            {{ __('Opslaan') }}
                        </button>
                    @endif
                </div>
            </div>

        </div>
    </div>{{-- End Tab: Instellingen --}}

    </div>

    {{-- Block Modal --}}
    @if ($blockingConversationId)
        @php $blockConv = $conversations->firstWhere('id', $blockingConversationId) @endphp
        @if ($blockConv)
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
                                @if ($blockConv->visitor_ip)
                                    <p class="text-white font-mono text-sm">{{ $blockConv->visitor_ip }}</p>
                                @else
                                    <p class="text-yellow-400 text-sm">Nog geen IP bekend. De bezoeker moet eerst een nieuw bericht sturen.</p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Bezoeker</label>
                                <p class="text-white text-sm">{{ $blockConv->visitor_name ?? substr($blockConv->visitor_uuid, 0, 8) . '...' }}</p>
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
    @endif
</div>
