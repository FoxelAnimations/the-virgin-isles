<div class="py-10">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Badges</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.badge-types') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Types
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    New Badge
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
            <button wire:click="$set('tab', 'badges')"
                class="px-4 py-2 text-sm font-semibold uppercase tracking-wider transition {{ $tab === 'badges' ? 'text-accent border-b-2 border-accent' : 'text-zinc-500 hover:text-white' }}">
                Badges
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
                    Badge Popup Settings
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400 mb-1">Badge Popup Timeout (seconds)</label>
                        <input type="number" wire:model="badgePopupTimeout" min="1" max="60"
                            class="w-24 bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                        <p class="text-xs text-zinc-500 mt-1">Auto-close duration for badge popups after scanning a beacon.</p>
                    </div>

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
                placeholder="Search badges...">
            <select wire:model.live="filterType"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="filterStatus"
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="deleted">Deleted</option>
            </select>
        </div>

        {{-- Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            @if ($badges->isEmpty())
                <div class="p-8 text-center text-zinc-600">
                    No badges found.
                </div>
            @else
                <table class="min-w-full divide-y divide-zinc-800">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-zinc-500">
                            <th class="px-4 py-3 text-left">Badge</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">Beacons</th>
                            <th class="px-4 py-3 text-center">Users</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($badges as $badge)
                            <tr class="hover:bg-zinc-800/50 transition {{ $badge->trashed() ? 'opacity-50' : '' }}">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-zinc-800 overflow-hidden flex-shrink-0">
                                            @if ($badge->image_path)
                                                <img src="{{ Storage::url($badge->image_path) }}" alt="{{ $badge->title }}" class="w-full h-full object-cover">
                                            @endif
                                        </div>
                                        <span class="text-white font-medium">{{ $badge->title }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-zinc-400 text-sm">{{ $badge->type?->name ?? '—' }}</td>
                                <td class="px-4 py-4 text-center">
                                    @if ($badge->trashed())
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-red-900/30 text-red-400 border border-red-800">Deleted</span>
                                    @elseif ($badge->is_active)
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-green-900/30 text-green-400 border border-green-800">Active</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-sm bg-zinc-800 text-zinc-500 border border-zinc-700">Inactive</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-center text-zinc-400 text-sm">{{ $badge->beacons_count }}</td>
                                <td class="px-4 py-4 text-center text-zinc-400 text-sm">{{ $badge->users_count }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if ($badge->trashed())
                                            <button wire:click="restore({{ $badge->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                                Restore
                                            </button>
                                        @else
                                            <button wire:click="openEdit({{ $badge->id }})"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $badge->id }})"
                                                wire:confirm="Delete this badge? It will be soft-deleted."
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50 uppercase tracking-wider">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                <div class="px-4 py-3 border-t border-zinc-800">
                    {{ $badges->links() }}
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

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-2xl my-8" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $editingId ? 'Edit Badge' : 'New Badge' }}</span>
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

                        {{-- Image --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Image {{ $editingId ? '' : '*' }}</label>
                            @if ($existingImage && !$image)
                                <div class="mb-2">
                                    <img src="{{ Storage::url($existingImage) }}" class="w-20 h-20 object-contain rounded bg-zinc-800 p-1">
                                </div>
                            @endif
                            @if ($image)
                                <div class="mb-2">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 object-contain rounded bg-zinc-800 p-1">
                                </div>
                            @endif
                            <input type="file" wire:model="image" accept="image/*"
                                class="w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-accent file:text-black file:cursor-pointer hover:file:brightness-90">
                            @error('image') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                            <textarea wire:model="description" rows="2"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"></textarea>
                        </div>

                        {{-- Popup texts --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Popup Text (First Scan)</label>
                                <textarea wire:model="popupTextFirst" rows="3"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="Shown when user earns this badge for the first time"></textarea>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Popup Text (Repeat Scan)</label>
                                <textarea wire:model="popupTextRepeat" rows="3"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                    placeholder="Shown when user earns this badge again from a new beacon"></textarea>
                            </div>
                        </div>

                        {{-- Type + Status + Sort --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Type</label>
                                <select wire:model="typeId"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    <option value="">No type</option>
                                    @foreach ($types as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Active</label>
                                <label class="flex items-center gap-2 mt-2">
                                    <input type="checkbox" wire:model="isActive"
                                        class="rounded-sm bg-zinc-800 border-zinc-700 text-accent focus:ring-accent">
                                    <span class="text-sm text-zinc-300">Badge is earnable</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Sort Order</label>
                                <input type="number" wire:model="sortOrder" min="0"
                                    class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
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
