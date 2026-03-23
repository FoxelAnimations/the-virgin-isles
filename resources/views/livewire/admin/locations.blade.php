<div class="py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Locations</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.location-categories') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Categories
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    New Location
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Tabs --}}
        <div class="flex gap-0 mb-6 border-b border-zinc-800">
            <button wire:click="$set('tab', 'locations')"
                class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $tab === 'locations' ? 'text-accent border-b-2 border-accent' : 'text-zinc-500 hover:text-white' }}">
                Locations
            </button>
            <button wire:click="$set('tab', 'settings')"
                class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $tab === 'settings' ? 'text-accent border-b-2 border-accent' : 'text-zinc-500 hover:text-white' }}">
                Settings
            </button>
        </div>

        @if ($tab === 'settings')
            {{-- Settings Tab --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
                <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider">
                    Map Settings
                </div>
                <div class="p-5 space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="showMap"
                            class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                        <span class="text-sm font-medium text-white">Show De Kaart</span>
                    </label>
                    <p class="text-xs text-zinc-500 ml-8">Shows the map page link in the navigation. When disabled, only admins can access the map.</p>

                    <button wire:click="saveSettings"
                        class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                        Save Settings
                    </button>
                </div>
            </div>
        @else

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm w-64"
                placeholder="Search locations...">
            <select wire:model.live="filterCategory"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterVisibility"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">Zichtbaar / Geheim</option>
                <option value="visible">Zichtbaar</option>
                <option value="hidden">Geheim</option>
            </select>
            <select wire:model.live="filterStatus"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">Actief / Inactief</option>
                <option value="active">Actief</option>
                <option value="inactive">Inactief</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            @if ($locations->isEmpty())
                <div class="p-8 text-center text-zinc-600">
                    No locations found.
                </div>
            @else
                <table class="min-w-full divide-y divide-zinc-800">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-zinc-500">
                            <th class="px-4 py-3 text-left">Location</th>
                            <th class="px-4 py-3 text-left">Categories</th>
                            <th class="px-4 py-3 text-center">Visibility</th>
                            <th class="px-4 py-3 text-center">Beacons</th>
                            <th class="px-4 py-3 text-left">Coordinates</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($locations as $location)
                            <tr class="hover:bg-zinc-800/50 transition">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded bg-zinc-800 overflow-hidden flex-shrink-0">
                                            @if ($location->image_path)
                                                <img src="{{ Storage::url($location->image_path) }}" alt="{{ $location->title }}" class="w-full h-full object-cover">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-zinc-600">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="text-white font-medium">{{ $location->title }}</span>
                                            @if ($location->address)
                                                <p class="text-xs text-zinc-500">{{ $location->address }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($location->categories as $cat)
                                            <span class="inline-flex px-2 py-0.5 text-xs bg-zinc-800 text-zinc-400 border border-zinc-700 rounded-sm">{{ $cat->name }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-center space-y-1">
                                    @if (!$location->is_active)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-red-900/30 text-red-400 border border-red-800">Inactief</span>
                                    @elseif ($location->is_visible)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-green-900/30 text-green-400 border border-green-800">Zichtbaar</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-amber-900/30 text-amber-400 border border-amber-800">Geheim</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center text-zinc-400 text-sm">{{ $location->beacons_count }}</td>
                                <td class="px-4 py-4 text-xs text-zinc-500 font-mono">{{ $location->latitude }}, {{ $location->longitude }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEdit({{ $location->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $location->id }})"
                                            wire:confirm="Delete this location? This cannot be undone."
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50 uppercase tracking-wider">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-zinc-800">
                    {{ $locations->links() }}
                </div>
            @endif
        </div>

        @endif
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center bg-black/80 p-4 md:p-8 overflow-y-auto"
            x-data
            @keydown.escape.window="$wire.closeModal()"
        >
            <div class="absolute inset-0" wire:click="closeModal"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-3xl my-8" @click.stop>
                <div class="bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $editingId ? 'Edit Location' : 'New Location' }}</span>
                    <button wire:click="closeModal" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5">
                    <form wire:submit="save" class="space-y-4">
                        {{-- Title --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Title *</label>
                            <input type="text" wire:model="title"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            @error('title') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description (WYSIWYG) --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                            <textarea wire:model="description" rows="4"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                placeholder="Full description (shown when location is revealed)"></textarea>
                        </div>

                        {{-- Hidden Description --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Hidden Description</label>
                            <textarea wire:model="hiddenDescription" rows="2"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                placeholder="Shown when location is hidden/unrevealed (teaser text)"></textarea>
                        </div>

                        {{-- Image --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Main Image</label>
                            @if ($existingImage && !$image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existingImage) }}" class="w-20 h-20 object-cover rounded bg-zinc-800">
                                </div>
                            @endif
                            @if ($image)
                                <div class="mb-2">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 object-cover rounded bg-zinc-800">
                                </div>
                            @endif
                            <input type="file" wire:model="image" accept="image/*"
                                class="w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-accent file:text-black file:cursor-pointer hover:file:brightness-90">
                            @error('image') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Coordinates with Map Picker --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Coordinates *</label>
                            <div class="grid grid-cols-2 gap-4 mb-2">
                                <div>
                                    <input type="text" wire:model="latitude" id="location-lat"
                                        class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                        placeholder="Latitude">
                                    @error('latitude') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                                <div>
                                    <input type="text" wire:model="longitude" id="location-lng"
                                        class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                        placeholder="Longitude">
                                    @error('longitude') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div id="admin-map-picker" class="w-full h-64 rounded-sm border border-zinc-700 bg-zinc-800" wire:ignore
                                x-data="{
                                    map: null,
                                    marker: null,
                                    init() {
                                        if (typeof L === 'undefined') return;
                                        this.waitForVisible();
                                    },
                                    waitForVisible() {
                                        const el = this.$el;
                                        const check = () => {
                                            if (el.offsetWidth > 0 && el.offsetHeight > 0) {
                                                this.initMap();
                                            } else {
                                                requestAnimationFrame(check);
                                            }
                                        };
                                        requestAnimationFrame(check);
                                    },
                                    initMap() {
                                        if (this.map) return;
                                        const lat = parseFloat($wire.latitude) || 51.05;
                                        const lng = parseFloat($wire.longitude) || 3.72;
                                        this.map = L.map(this.$el).setView([lat, lng], 13);
                                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                            attribution: '&copy; OpenStreetMap'
                                        }).addTo(this.map);
                                        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map);
                                        this.marker.on('dragend', () => {
                                            const pos = this.marker.getLatLng();
                                            $wire.set('latitude', pos.lat.toFixed(7));
                                            $wire.set('longitude', pos.lng.toFixed(7));
                                        });
                                        this.map.on('click', (e) => {
                                            this.marker.setLatLng(e.latlng);
                                            $wire.set('latitude', e.latlng.lat.toFixed(7));
                                            $wire.set('longitude', e.latlng.lng.toFixed(7));
                                        });
                                        this.$nextTick(() => this.map.invalidateSize());
                                    }
                                }"></div>
                        </div>

                        {{-- Address --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Address</label>
                            <input type="text" wire:model="address"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                placeholder="Street address">
                        </div>

                        {{-- Buttons --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-zinc-400">Button 1</label>
                                <input type="text" wire:model="button1Label"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="Label">
                                <input type="text" wire:model="button1Url"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="URL">
                            </div>
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-zinc-400">Button 2</label>
                                <input type="text" wire:model="button2Label"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="Label">
                                <input type="text" wire:model="button2Url"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="URL">
                            </div>
                        </div>

                        {{-- Status + Secret + Sort --}}
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Status</label>
                                    <label class="flex items-center gap-2 mt-2">
                                        <input type="checkbox" wire:model="isActive"
                                            class="rounded-sm bg-zinc-800 border-zinc-700 text-accent focus:ring-accent">
                                        <span class="text-sm text-zinc-300">Actief (zichtbaar op de kaart)</span>
                                    </label>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Geheim / Verborgen</label>
                                    <label class="flex items-center gap-2 mt-2">
                                        <input type="checkbox" wire:model="isHidden"
                                            class="rounded-sm bg-zinc-800 border-zinc-700 text-accent focus:ring-accent">
                                        <span class="text-sm text-zinc-300">Geheime locatie (toont ? op de kaart)</span>
                                    </label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Sort Order</label>
                                <input type="number" wire:model="sortOrder" min="0"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            </div>
                        </div>

                        {{-- Categories --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Categories</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach ($categories as $cat)
                                    <label class="flex items-center gap-1.5 py-1 px-2 bg-zinc-800 border border-zinc-700 rounded-sm cursor-pointer hover:border-zinc-500 transition">
                                        <input type="checkbox" wire:model="selectedCategoryIds" value="{{ $cat->id }}"
                                            class="rounded-sm bg-zinc-700 border-zinc-600 text-accent focus:ring-accent">
                                        <span class="text-sm text-zinc-300">{{ $cat->name }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        {{-- Linked Beacons --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Linked Beacons</label>
                            <div class="max-h-40 overflow-y-auto bg-zinc-800 border border-zinc-700 rounded-sm p-2 space-y-1">
                                @foreach ($beacons as $beacon)
                                    <label class="flex items-center gap-2 py-1 px-2 hover:bg-zinc-700/50 rounded-sm cursor-pointer">
                                        <input type="checkbox" wire:model="selectedBeaconIds" value="{{ $beacon->id }}"
                                            class="rounded-sm bg-zinc-700 border-zinc-600 text-accent focus:ring-accent">
                                        <span class="text-sm text-zinc-300">{{ $beacon->title }}</span>
                                        <code class="text-xs text-zinc-500 font-mono ml-auto">{{ $beacon->guid }}</code>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex gap-3 justify-end pt-2">
                            <button type="button" wire:click="closeModal"
                                class="px-4 py-2 text-sm font-semibold text-zinc-400 border border-zinc-700 uppercase tracking-wider transition hover:text-white hover:border-zinc-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                                {{ $editingId ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
