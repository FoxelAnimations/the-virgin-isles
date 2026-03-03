<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Beacons</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.beacon-types') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Types
                </a>
                <a href="{{ route('admin.beacon-logs') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Logs
                </a>
                <a href="{{ route('admin.beacon-analytics') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Analytics
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    New Beacon
                </button>
            </div>
        </div>

        {{-- Flash --}}
        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filters --}}
        <div class="mb-6 flex flex-wrap items-center gap-3">
            <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search title or GUID..."
                class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm w-64">

            <select wire:model.live="filterType" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Types</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterStatus" class="bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                <option value="">All Statuses</option>
                <option value="online">Online</option>
                <option value="offline">Offline</option>
                <option value="out_of_action">Out of Action</option>
            </select>

            <span class="text-zinc-500 text-sm ml-auto">{{ $totalCount }} total beacons</span>
        </div>

        {{-- Bulk Actions --}}
        @if (count($selected) > 0)
            <div class="mb-4 flex items-center gap-3 bg-zinc-900 border border-zinc-800 px-4 py-3 rounded-sm">
                <span class="text-sm text-zinc-400">{{ count($selected) }} selected</span>
                <button wire:click="bulkSetOnline" class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-green-900/30 text-green-400 border border-green-800 rounded-sm transition hover:bg-green-900/50 uppercase tracking-wider">
                    Set Online
                </button>
                <button wire:click="bulkSetOffline" class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-zinc-800 text-zinc-400 border border-zinc-700 rounded-sm transition hover:bg-zinc-700 uppercase tracking-wider">
                    Set Offline
                </button>
                <button wire:click="bulkSetOutOfAction" class="inline-flex items-center px-3 py-1 text-xs font-semibold bg-orange-900/30 text-orange-400 border border-orange-800 rounded-sm transition hover:bg-orange-900/50 uppercase tracking-wider">
                    Set Out of Action
                </button>
            </div>
        @endif

        {{-- Table --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overview</p>
                <h3 class="text-lg font-semibold uppercase tracking-wider">All Beacons</h3>
            </div>

            @if ($beacons->isEmpty())
                <div class="p-8 text-center text-zinc-600">
                    No beacons found.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead>
                            <tr class="text-xs uppercase tracking-wider text-zinc-500">
                                <th class="px-3 py-3 text-left w-10">
                                    <input type="checkbox" wire:model.live="selectAll" class="rounded-sm border-zinc-700 bg-zinc-800 text-accent focus:ring-accent">
                                </th>
                                <th class="px-4 py-3 text-left w-12"></th>
                                <th class="px-4 py-3 text-left">Title</th>
                                <th class="px-4 py-3 text-left">GUID</th>
                                <th class="px-4 py-3 text-center">Type</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Scans</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach ($beacons as $beacon)
                                <tr class="hover:bg-zinc-800/50 transition">
                                    <td class="px-3 py-4">
                                        <input type="checkbox" wire:model.live="selected" value="{{ $beacon->id }}" class="rounded-sm border-zinc-700 bg-zinc-800 text-accent focus:ring-accent">
                                    </td>
                                    <td class="px-4 py-4">
                                        @if ($beacon->image_path)
                                            <img src="{{ Storage::url($beacon->image_path) }}" alt="" class="w-10 h-10 object-cover rounded-sm border border-zinc-700">
                                        @else
                                            <div class="w-10 h-10 rounded-sm border border-zinc-700 bg-zinc-800 flex items-center justify-center">
                                                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <a href="{{ route('admin.beacon-detail', $beacon) }}" class="text-white font-medium hover:text-accent transition">
                                            {{ $beacon->title }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-4">
                                        <code class="text-xs text-zinc-500 font-mono">{{ $beacon->guid }}</code>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        @if ($beacon->type)
                                            <span class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-zinc-800 text-zinc-300 border border-zinc-700 rounded-sm">
                                                {{ $beacon->type->name }}
                                            </span>
                                        @else
                                            <span class="text-zinc-600 text-xs">—</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            @if ($beacon->is_out_of_action)
                                                <button wire:click="toggleOutOfAction({{ $beacon->id }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-orange-900/30 text-orange-400 border border-orange-800 rounded-sm transition hover:bg-orange-900/50">
                                                    Out of Action
                                                </button>
                                            @else
                                                <button wire:click="toggleOnline({{ $beacon->id }})"
                                                    class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $beacon->is_online ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-red-900/30 text-red-400 border border-red-800' }}">
                                                    {{ $beacon->is_online ? 'Online' : 'Offline' }}
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-zinc-400 text-sm">
                                        {{ $beacon->scans_count }}
                                    </td>
                                    <td class="px-4 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.beacon-detail', $beacon) }}"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                                Edit
                                            </a>
                                            <button wire:click="deleteBeacon({{ $beacon->id }})"
                                                wire:confirm="Are you sure you want to delete this beacon and all its scan logs?"
                                                class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50 uppercase tracking-wider">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 border-t border-zinc-800">
                    {{ $beacons->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            @keydown.escape.window="$wire.closeCreate()"
        >
            <div class="absolute inset-0" wire:click="closeCreate"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-lg" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>New Beacon</span>
                    <button wire:click="closeCreate" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5">
                    <form wire:submit="createBeacon">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Title *</label>
                                <input type="text" wire:model="newTitle" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Entrance plaque">
                                @error('newTitle') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Description</label>
                                <textarea wire:model="newDescription" rows="2" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Type</label>
                                    <select wire:model="newTypeId" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                        <option value="">— None —</option>
                                        @foreach ($types as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-zinc-400 mb-1">Amount in circulation</label>
                                    <input type="number" wire:model="newAmount" min="0" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                                    @error('newAmount') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Redirect URL</label>
                                <input type="text" wire:model="newRedirectUrl" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="/custom-page or https://...">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-zinc-400 mb-1">Image</label>
                                <input type="file" wire:model="newImage" accept="image/*" class="w-full text-sm text-zinc-400 file:mr-3 file:py-2 file:px-3 file:border-0 file:text-sm file:font-semibold file:bg-zinc-800 file:text-zinc-300 file:cursor-pointer hover:file:bg-zinc-700">
                                @error('newImage') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="flex gap-3 justify-end mt-6">
                            <button type="button" wire:click="closeCreate"
                                class="px-4 py-2 text-sm font-semibold text-zinc-400 border border-zinc-700 uppercase tracking-wider transition hover:text-white hover:border-zinc-500">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-4 py-2 text-sm font-semibold bg-accent text-black uppercase tracking-wider transition hover:brightness-90">
                                Create
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
