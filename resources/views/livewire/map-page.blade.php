<div class="bg-black min-h-screen -mt-16 pt-16 text-white">
    {{-- Leaflet CSS from CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Header --}}
        <div class="mb-6">
            <p class="text-sm tracking-[0.3em] uppercase text-zinc-500 mb-1">{{ __('Explore') }}</p>
            <h1 class="text-3xl font-bold uppercase tracking-wider">{{ __('Locations') }}</h1>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <select wire:model.live="filterCategory"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">{{ __('All Categories') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @auth
                <select wire:model.live="filterStatus"
                    class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                    <option value="">{{ __('All') }}</option>
                    <option value="revealed">{{ __('Revealed') }}</option>
                    <option value="unrevealed">{{ __('Not Yet Revealed') }}</option>
                </select>
            @endauth
        </div>

        {{-- Map Container --}}
        <div class="rounded-sm border border-zinc-800 overflow-hidden"
            x-data="mapComponent()"
            x-init="initMap()"
            wire:ignore
        >
            <div id="location-map" class="w-full" style="height: 70vh; min-height: 400px;"></div>
        </div>
    </div>

    {{-- Location Detail Modal --}}
    <div x-data="{ open: false, location: null }"
        @open-location.window="location = $event.detail; open = true"
        x-show="open"
        x-transition.opacity
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/85 p-4"
        @click.self="open = false"
        @keydown.escape.window="open = false"
        style="display: none;"
    >
        <div class="bg-zinc-900 border border-zinc-800 rounded-sm w-full max-w-lg overflow-hidden max-h-[85vh] overflow-y-auto" @click.stop>
            {{-- Image --}}
            <template x-if="location?.image">
                <img :src="location.image" :alt="location.title" class="w-full h-48 object-cover">
            </template>

            {{-- Hidden location placeholder --}}
            <template x-if="location?.is_hidden && !location?.is_revealed && !location?.image">
                <div class="w-full h-48 bg-zinc-800 flex items-center justify-center">
                    <span class="text-6xl font-bold text-zinc-600">?</span>
                </div>
            </template>

            <div class="p-6">
                <h2 class="text-2xl font-bold uppercase tracking-wider text-white mb-2" x-text="location?.title"></h2>

                <template x-if="location?.address">
                    <p class="text-sm text-zinc-500 mb-3" x-text="location.address"></p>
                </template>

                <template x-if="location?.description">
                    <div class="text-sm text-zinc-400 prose prose-invert prose-sm max-w-none mb-4" x-html="location.description"></div>
                </template>

                {{-- Buttons --}}
                <div class="flex flex-wrap gap-3 mt-4">
                    <template x-if="location?.button_1_label && location?.button_1_url">
                        <a :href="location.button_1_url" target="_blank" rel="noopener"
                            class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90"
                            x-text="location.button_1_label"></a>
                    </template>
                    <template x-if="location?.button_2_label && location?.button_2_url">
                        <a :href="location.button_2_url" target="_blank" rel="noopener"
                            class="inline-flex items-center border border-zinc-700 text-zinc-300 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500"
                            x-text="location.button_2_label"></a>
                    </template>
                </div>

                <button @click="open = false"
                    class="mt-4 px-4 py-2 text-sm font-semibold border border-zinc-700 text-zinc-400 uppercase tracking-wider transition hover:text-white hover:border-zinc-500">
                    {{ __('Close') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Leaflet JS from CDN --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function mapComponent() {
            return {
                map: null,
                markers: [],
                accentColor: null,
                stylesAdded: false,

                initMap() {
                    const defaultLat = 51.05;
                    const defaultLng = 3.72;
                    const defaultZoom = 13;

                    this.map = L.map('location-map').setView([defaultLat, defaultLng], defaultZoom);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
                        className: 'grayscale-tiles'
                    }).addTo(this.map);

                    if (!this.stylesAdded) {
                        const style = document.createElement('style');
                        style.textContent = '.grayscale-tiles { filter: grayscale(100%) !important; -webkit-filter: grayscale(100%) !important; } .custom-marker { background: none !important; border: none !important; }';
                        document.head.appendChild(style);
                        this.stylesAdded = true;
                    }

                    this.accentColor = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#f59e0b';

                    // Initial render
                    this.updateMarkers(this.$wire.mapLocations);

                    // Watch for Livewire property changes
                    this.$wire.$watch('mapLocations', (newLocations) => {
                        this.updateMarkers(newLocations);
                    });
                },

                updateMarkers(locations) {
                    // Remove existing markers
                    this.markers.forEach(m => m.remove());
                    this.markers = [];

                    const bounds = [];

                    locations.forEach(loc => {
                        const isHiddenUnrevealed = loc.is_hidden && !loc.is_revealed;
                        const hasCheckmark = loc.is_scanned;

                        const icon = this.createMarkerIcon(
                            isHiddenUnrevealed ? '?' : 'pin',
                            hasCheckmark
                        );

                        const marker = L.marker([loc.lat, loc.lng], { icon }).addTo(this.map);
                        bounds.push([loc.lat, loc.lng]);

                        marker.on('click', () => {
                            window.dispatchEvent(new CustomEvent('open-location', { detail: loc }));
                        });

                        this.markers.push(marker);
                    });

                    if (bounds.length > 0) {
                        this.map.fitBounds(bounds, { padding: [50, 50], maxZoom: 15 });
                    }
                },

                createMarkerIcon(symbol, hasCheckmark) {
                    const c = this.accentColor;
                    const checkSvg = hasCheckmark
                        ? `<circle cx="28" cy="8" r="7" fill="${c}" stroke="#000" stroke-width="1"/><path d="M24 8 L27 11 L32 5" fill="none" stroke="#000" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>`
                        : '';

                    const svgContent = symbol === '?'
                        ? `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="44" viewBox="0 0 36 44"><path d="M18 42 C18 42 2 26 2 16 C2 7.16 9.16 0 18 0 S34 7.16 34 16 C34 26 18 42 18 42Z" fill="${c}" stroke="#000" stroke-width="1.5"/><text x="18" y="22" text-anchor="middle" font-size="18" font-weight="bold" fill="#000">?</text>${checkSvg}</svg>`
                        : `<svg xmlns="http://www.w3.org/2000/svg" width="36" height="44" viewBox="0 0 36 44"><path d="M18 42 C18 42 2 26 2 16 C2 7.16 9.16 0 18 0 S34 7.16 34 16 C34 26 18 42 18 42Z" fill="${c}" stroke="#000" stroke-width="1.5"/><circle cx="18" cy="16" r="6" fill="#000" opacity="0.3"/><circle cx="18" cy="16" r="4" fill="#fff"/>${checkSvg}</svg>`;

                    return L.divIcon({
                        html: svgContent,
                        className: 'custom-marker',
                        iconSize: [36, 44],
                        iconAnchor: [18, 44],
                        popupAnchor: [0, -44]
                    });
                }
            };
        }
    </script>
</div>
