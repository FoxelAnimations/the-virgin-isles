<div class="bg-black min-h-screen -mt-16 pt-16 text-white">
    {{-- Leaflet CSS from CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">

        {{-- Header --}}
        <div class="mb-6">
            <p class="text-sm tracking-[0.3em] uppercase text-zinc-500 mb-1">{{ __('Ontdek') }}</p>
            <h1 class="text-3xl font-bold uppercase tracking-wider">{{ __('De Kaart') }}</h1>
        </div>

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 mb-4">
            <select wire:model.live="filterCategory"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">{{ __('Alle categorieën') }}</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            @auth
                <select wire:model.live="filterStatus"
                    class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                    <option value="">{{ __('Alles') }}</option>
                    <option value="revealed">{{ __('Ontdekt') }}</option>
                    <option value="unrevealed">{{ __('Nog niet ontdekt') }}</option>
                </select>
            @endauth
        </div>

        {{-- Map Container --}}
        <div class="relative z-0 rounded-sm border border-zinc-800 overflow-hidden"
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
                    {{ __('Sluiten') }}
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
                    // Default: Kortrijk
                    const defaultLat = 50.8279;
                    const defaultLng = 3.2654;
                    const defaultZoom = 14;

                    this.map = L.map('location-map', {
                        zoomControl: false
                    }).setView([defaultLat, defaultLng], defaultZoom);

                    L.control.zoom({ position: 'topright' }).addTo(this.map);

                    // CartoDB Positron — clean minimal style WITH labels (street names)
                    L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; <a href="https://carto.com/">CARTO</a>',
                        subdomains: 'abcd',
                        maxZoom: 19,
                        className: 'map-tiles'
                    }).addTo(this.map);

                    if (!this.stylesAdded) {
                        const style = document.createElement('style');
                        style.textContent = `
                            .map-tiles { filter: grayscale(100%) brightness(0.85) contrast(1.1) !important; }
                            .custom-marker { background: none !important; border: none !important; }
                            .custom-marker svg { filter: drop-shadow(0 2px 4px rgba(0,0,0,0.5)); transition: transform 0.15s ease; }
                            .custom-marker:hover svg { transform: scale(1.18) translateY(-3px); }
                            #location-map { background: #d4d4d8 !important; }
                            .leaflet-control-zoom a { background: #18181b !important; color: #a1a1aa !important; border-color: #27272a !important; }
                            .leaflet-control-zoom a:hover { color: #fff !important; }
                            .leaflet-control-attribution { background: rgba(0,0,0,0.6) !important; color: #52525b !important; font-size: 9px !important; }
                            .leaflet-control-attribution a { color: #71717a !important; }
                        `;
                        document.head.appendChild(style);
                        this.stylesAdded = true;
                    }

                    this.accentColor = getComputedStyle(document.documentElement).getPropertyValue('--color-accent').trim() || '#E7FF57';

                    this.updateMarkers(this.$wire.mapLocations);

                    this.$wire.$watch('mapLocations', (newLocations) => {
                        this.updateMarkers(newLocations);
                    });
                },

                updateMarkers(locations) {
                    this.markers.forEach(m => m.remove());
                    this.markers = [];
                    const bounds = [];

                    locations.forEach(loc => {
                        const isHiddenUnrevealed = loc.is_hidden && !loc.is_revealed;
                        const hasCheckmark = loc.is_scanned;
                        const icon = this.createMarkerIcon(isHiddenUnrevealed ? '?' : 'pin', hasCheckmark);

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

                    // Chunky rounded checkmark badge
                    const checkSvg = hasCheckmark
                        ? `<circle cx="32" cy="9" r="9" fill="${c}" stroke="#18181b" stroke-width="2.5"/>
                           <path d="M28 9 L31 12 L37 6" fill="none" stroke="#18181b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`
                        : '';

                    // Bigger, rounder, bolder — friendly cartoon pin
                    const svg = symbol === '?'
                        ? `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="52" viewBox="0 0 42 52">
                            <path d="M21 49 C21 49 3 30 3 18 C3 8.6 11.06 1 21 1 S39 8.6 39 18 C39 30 21 49 21 49Z" fill="${c}" stroke="#18181b" stroke-width="3" stroke-linejoin="round"/>
                            <circle cx="21" cy="18" r="13" fill="#18181b" opacity="0.15"/>
                            <text x="21" y="25" text-anchor="middle" font-size="22" font-weight="900" font-family="system-ui,sans-serif" fill="#18181b">?</text>
                            ${checkSvg}
                           </svg>`
                        : `<svg xmlns="http://www.w3.org/2000/svg" width="42" height="52" viewBox="0 0 42 52">
                            <path d="M21 49 C21 49 3 30 3 18 C3 8.6 11.06 1 21 1 S39 8.6 39 18 C39 30 21 49 21 49Z" fill="${c}" stroke="#18181b" stroke-width="3" stroke-linejoin="round"/>
                            <circle cx="21" cy="18" r="7" fill="#18181b" opacity="0.2"/>
                            <circle cx="21" cy="18" r="5" fill="#fff"/>
                            ${checkSvg}
                           </svg>`;

                    return L.divIcon({
                        html: svg,
                        className: 'custom-marker',
                        iconSize: [42, 52],
                        iconAnchor: [21, 52],
                        popupAnchor: [0, -52]
                    });
                }
            };
        }
    </script>
</div>
