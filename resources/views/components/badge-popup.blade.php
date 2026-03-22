@php
    $badgePopups = session('badge_popups', []);
    $locationPopup = session('location_popup');
@endphp

@if (!empty($badgePopups) || $locationPopup)
@php $popupTimeout = \App\Models\SiteSetting::first()?->badge_popup_timeout ?? 5; @endphp
<div x-data="{
        popups: {{ Js::from($badgePopups) }},
        locationPopup: {{ Js::from($locationPopup) }},
        current: 0,
        show: true,
        isLocation: false,
        timeout: {{ $popupTimeout }} * 1000,
        timer: null,
        progress: 100,
        progressInterval: null,
        init() {
            if (this.popups.length > 0) {
                this.isLocation = false;
                this.startTimer();
            } else if (this.locationPopup) {
                this.isLocation = true;
                this.startTimer();
            } else {
                this.show = false;
            }
        },
        startTimer() {
            this.progress = 100;
            clearInterval(this.progressInterval);
            clearTimeout(this.timer);

            const step = 50;
            const decrement = (step / this.timeout) * 100;
            this.progressInterval = setInterval(() => {
                this.progress = Math.max(0, this.progress - decrement);
            }, step);

            this.timer = setTimeout(() => this.advance(), this.timeout);
        },
        advance() {
            clearInterval(this.progressInterval);
            clearTimeout(this.timer);

            if (!this.isLocation && this.current < this.popups.length - 1) {
                this.current++;
                this.startTimer();
            } else if (!this.isLocation && this.locationPopup) {
                this.isLocation = true;
                this.startTimer();
            } else {
                this.show = false;
            }
        },
        close() {
            clearInterval(this.progressInterval);
            clearTimeout(this.timer);
            this.show = false;
        }
    }"
    x-show="show"
    x-transition.opacity
    class="fixed inset-0 z-[60] flex items-center justify-center bg-black/85 p-4"
    @click.self="advance()"
    style="display: none;"
>
    {{-- Badge Popup --}}
    <template x-if="!isLocation && popups.length > 0">
        <div class="bg-zinc-900 border border-zinc-800 rounded-sm w-full max-w-sm text-center overflow-hidden" @click.stop>
            {{-- Progress bar --}}
            <div class="h-1 bg-zinc-800">
                <div class="h-full bg-accent transition-all duration-100 ease-linear" :style="'width: ' + progress + '%'"></div>
            </div>

            <div class="p-6">
                {{-- Badge image --}}
                <template x-if="popups[current]?.image">
                    <img :src="popups[current].image" :alt="popups[current].title"
                        class="w-28 h-28 mx-auto mb-4 object-contain rounded-full border-2 border-accent/30 bg-zinc-800 p-1">
                </template>

                {{-- Title + count --}}
                <h3 class="text-xl font-bold uppercase tracking-wider text-white mb-1" x-text="popups[current]?.title"></h3>
                <template x-if="popups[current]?.count > 1">
                    <span class="inline-block text-sm font-semibold text-accent mb-3" x-text="'x' + popups[current].count"></span>
                </template>

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

                {{-- Next/Close button --}}
                <button @click="advance()"
                    class="px-6 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                    <span x-text="current < popups.length - 1 || locationPopup ? 'Next' : 'Close'"></span>
                </button>
            </div>
        </div>
    </template>

    {{-- Location Popup --}}
    <template x-if="isLocation && locationPopup">
        <div class="bg-zinc-900 border border-zinc-800 rounded-sm w-full max-w-sm overflow-hidden" @click.stop>
            {{-- Progress bar --}}
            <div class="h-1 bg-zinc-800">
                <div class="h-full bg-accent transition-all duration-100 ease-linear" :style="'width: ' + progress + '%'"></div>
            </div>

            {{-- Location image --}}
            <template x-if="locationPopup.image">
                <img :src="locationPopup.image" :alt="locationPopup.title"
                    class="w-full h-40 object-cover">
            </template>

            <div class="p-6 text-center">
                <h3 class="text-xl font-bold uppercase tracking-wider text-white mb-2" x-text="locationPopup.title"></h3>
                <div class="text-sm text-zinc-400 mb-4 prose prose-invert prose-sm max-w-none" x-html="locationPopup.description"></div>

                <button @click="close()"
                    class="px-6 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                    Close
                </button>
            </div>
        </div>
    </template>
</div>
@endif
