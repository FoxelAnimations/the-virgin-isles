@props(['redirectUrl' => '/beacon', 'readerId' => 'qr-reader', 'mode' => 'redirect'])

<div x-data="{
    show: false,
    scanning: false,
    error: null,
    result: null,
    scanner: null,
    badgePopups: [],
    currentPopup: 0,
    showingBadge: false,

    async openScanner() {
        this.show = true;
        this.error = null;
        this.result = null;
        this.badgePopups = [];
        this.currentPopup = 0;
        this.showingBadge = false;
        await this.$nextTick();
        if (typeof Html5Qrcode === 'undefined') {
            await new Promise((resolve, reject) => {
                const s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js';
                s.onload = resolve;
                s.onerror = () => { this.error = '{{ __('Scanner kon niet geladen worden.') }}'; reject(); };
                document.head.appendChild(s);
            });
        }
        setTimeout(() => this.startCamera(), 150);
    },

    async closeScanner() {
        await this.stopCamera();
        this.show = false;
        this.showingBadge = false;
    },

    async startCamera() {
        try {
            this.scanner = new Html5Qrcode('{{ $readerId }}');
            await this.scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                (decodedText) => this.onScan(decodedText),
                () => {}
            );
            this.scanning = true;
        } catch (err) {
            this.error = '{{ __('Camera niet beschikbaar. Geef toegang tot je camera.') }}';
            this.scanning = false;
        }
    },

    async stopCamera() {
        if (this.scanner) {
            try {
                if (this.scanning) await this.scanner.stop();
                this.scanner.clear();
            } catch (e) {}
        }
        this.scanner = null;
        this.scanning = false;
    },

    onScan(text) {
        this.stopCamera();
        let guid = null;
        try {
            const url = new URL(text);
            const match = url.pathname.match(/\/beacon\/([A-Za-z0-9]+)/);
            if (match) guid = match[1];
        } catch {
            if (/^[A-Za-z0-9]{10}$/.test(text)) guid = text;
        }
        if (!guid) {
            this.error = '{{ __('Geen geldige beacon QR-code.') }}';
            setTimeout(() => { this.error = null; this.startCamera(); }, 1500);
            return;
        }

        @if($mode === 'api')
            this.handleApiScan(guid);
        @else
            this.result = '{{ __('Beacon gevonden! Even geduld…') }}';
            window.location.href = '{{ url($redirectUrl) }}/' + encodeURIComponent(guid);
        @endif
    },

    async handleApiScan(guid) {
        this.result = '{{ __('Beacon gevonden! Verwerken…') }}';
        try {
            const res = await fetch('/beacon/' + encodeURIComponent(guid) + '/scan', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                    'Accept': 'application/json',
                },
            });
            const data = await res.json();

            if (!res.ok) {
                this.result = null;
                this.error = data.error || '{{ __('Er ging iets mis.') }}';
                setTimeout(() => { this.error = null; this.startCamera(); }, 2000);
                return;
            }

            if (data.badge_popups && data.badge_popups.length > 0) {
                this.badgePopups = data.badge_popups;
                this.currentPopup = 0;
                this.showingBadge = true;
                this.result = null;
                if (window.confetti) {
                    window.confetti({ particleCount: 60, spread: 55, origin: { y: 0.6 }, disableForReducedMotion: true });
                }
            } else {
                this.result = data.is_new
                    ? '{{ __('Beacon verzameld!') }}'
                    : '{{ __('Beacon gescand.') }}';
                setTimeout(() => { window.location.reload(); }, 1500);
            }
        } catch (e) {
            this.result = null;
            this.error = '{{ __('Verbindingsfout. Probeer opnieuw.') }}';
            setTimeout(() => { this.error = null; this.startCamera(); }, 2000);
        }
    },

    async nextBadge() {
        const badge = this.badgePopups[this.currentPopup];
        if (badge?.id) {
            await window.axios.post('{{ route('badge.seen') }}', { badge_id: badge.id }).catch(() => {});
        }

        if (this.currentPopup < this.badgePopups.length - 1) {
            this.currentPopup++;
        } else {
            window.location.reload();
        }
    }
}" @open-scanner.window="openScanner()" @keydown.escape.window="if (show) closeScanner()"
   x-show="show" x-cloak
   class="fixed inset-0 z-[55] flex items-center justify-center bg-black/80 backdrop-blur-sm">

    <div class="bg-zinc-900 border border-zinc-700 rounded-lg w-full max-w-md mx-4 overflow-hidden" @click.outside="closeScanner()">
        <div class="flex items-center justify-between px-5 py-4 border-b border-zinc-800">
            <h3 class="text-white text-sm font-semibold tracking-widest uppercase">{{ __('Scan Beacon') }}</h3>
            <button @click="closeScanner()" class="text-zinc-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Camera view --}}
        <div class="p-5" x-show="!showingBadge">
            <div id="{{ $readerId }}" class="w-full rounded overflow-hidden bg-black"></div>

            <p x-show="error" x-text="error" x-cloak class="mt-3 text-red-400 text-sm text-center"></p>
            <p x-show="result" x-text="result" x-cloak class="mt-3 text-accent text-sm text-center font-semibold"></p>
            <p x-show="scanning && !error && !result" x-cloak class="mt-3 text-zinc-400 text-sm text-center">
                {{ __('Richt je camera op een beacon QR-code') }}
            </p>
        </div>

        {{-- Badge popup (inline) --}}
        <div x-show="showingBadge" x-cloak class="p-6 text-center">
            <template x-if="badgePopups[currentPopup]?.image">
                <img :src="badgePopups[currentPopup].image" :alt="badgePopups[currentPopup].title"
                    class="w-28 h-28 mx-auto mb-4 object-contain rounded-full border-2 border-accent/30 bg-zinc-800 p-1">
            </template>

            <h3 class="text-xl font-bold uppercase tracking-wider text-white mb-3"
                x-text="badgePopups[currentPopup]?.title"></h3>

            <p class="text-sm text-zinc-400 mb-4"
                x-text="badgePopups[currentPopup]?.popup_text"></p>

            <template x-if="badgePopups.length > 1">
                <div class="flex justify-center gap-1.5 mb-4">
                    <template x-for="(_, i) in badgePopups" :key="i">
                        <div class="w-2 h-2 rounded-full transition"
                            :class="i === currentPopup ? 'bg-accent' : 'bg-zinc-700'"></div>
                    </template>
                </div>
            </template>

            <button @click="nextBadge()"
                class="px-6 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                <span x-text="currentPopup < badgePopups.length - 1 ? '{{ __('Volgende') }}' : '{{ __('Sluiten') }}'"></span>
            </button>
        </div>
    </div>
</div>
