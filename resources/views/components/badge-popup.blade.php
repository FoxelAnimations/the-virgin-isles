@php
    $badgePopups = [];
    if (auth()->check()) {
        $badgePopups = auth()->user()
            ->badges()
            ->wherePivotNull('seen_at')
            ->get()
            ->map(fn ($badge) => $badge->toPopupArray())
            ->values()
            ->toArray();
    }
@endphp

@if (!empty($badgePopups))
<div x-data="{
        popups: {{ Js::from($badgePopups) }},
        current: 0,
        show: true,
        init() {
            if (this.popups.length > 0) {
                if (window.confetti) {
                    window.confetti({
                        particleCount: 60,
                        spread: 55,
                        origin: { y: 0.6 },
                        disableForReducedMotion: true,
                    });
                }
            } else {
                this.show = false;
            }
        },
        confirm() {
            const badge = this.popups[this.current];
            if (badge?.id) {
                window.axios.post('{{ route('badge.seen') }}', { badge_id: badge.id }).catch(() => {});
            }

            if (this.current < this.popups.length - 1) {
                this.current++;
            } else {
                this.show = false;
            }
        }
    }"
    x-show="show"
    x-transition.opacity
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4"
    style="display: none;"
>
    {{-- Badge Popup --}}
    <template x-if="popups.length > 0">
        <div class="bg-zinc-900 border border-zinc-800 rounded-sm w-full max-w-sm text-center overflow-hidden" @click.stop>
            <div class="p-6">
                {{-- Badge image --}}
                <template x-if="popups[current]?.image">
                    <img :src="popups[current].image" :alt="popups[current].title"
                        class="w-28 h-28 mx-auto mb-4 object-contain rounded-full border-2 border-accent/30 bg-zinc-800 p-1">
                </template>

                {{-- Title --}}
                <h3 class="text-xl font-bold uppercase tracking-wider text-white mb-3" x-text="popups[current]?.title"></h3>

                {{-- Popup text --}}
                <p class="text-sm text-zinc-400 mb-4" x-text="popups[current]?.popup_text"></p>

                {{-- Dots --}}
                <template x-if="popups.length > 1">
                    <div class="flex justify-center gap-1.5 mb-4">
                        <template x-for="(_, i) in popups" :key="i">
                            <div class="w-2 h-2 rounded-full transition"
                                :class="i === current ? 'bg-accent' : 'bg-zinc-700'"></div>
                        </template>
                    </div>
                </template>

                {{-- Confirm button --}}
                <button @click="confirm()"
                    class="px-6 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                    <span x-text="current < popups.length - 1 ? 'Volgende' : 'Sluiten'"></span>
                </button>
            </div>
        </div>
    </template>

</div>
@endif
