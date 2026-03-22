<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Header --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS — Locations</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">Location Categories</h1>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.locations') }}" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white hover:border-zinc-500">
                    Back
                </a>
                <button wire:click="openCreate" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    New Category
                </button>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overview</p>
                <h2 class="text-lg font-semibold uppercase tracking-wider">All Categories</h2>
            </div>

            @if ($categories->isEmpty())
                <div class="p-8 text-center text-zinc-600">
                    No location categories yet.
                </div>
            @else
                <table class="min-w-full divide-y divide-zinc-800">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-zinc-500">
                            <th class="px-4 py-3 text-left">Name</th>
                            <th class="px-4 py-3 text-left">Slug</th>
                            <th class="px-4 py-3 text-center">Locations</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800">
                        @foreach ($categories as $category)
                            <tr class="hover:bg-zinc-800/50 transition">
                                <td class="px-4 py-4 text-white font-medium">{{ $category->name }}</td>
                                <td class="px-4 py-4"><code class="text-xs text-zinc-500 font-mono">{{ $category->slug }}</code></td>
                                <td class="px-4 py-4 text-center text-zinc-400 text-sm">{{ $category->locations_count }}</td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button wire:click="openEdit({{ $category->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-accent text-black rounded-sm transition hover:brightness-90 uppercase tracking-wider">
                                            Edit
                                        </button>
                                        <button wire:click="delete({{ $category->id }})"
                                            wire:confirm="Delete this category?"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold bg-red-900/30 text-red-400 border border-red-800 rounded-sm transition hover:bg-red-900/50 uppercase tracking-wider">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 p-4 md:p-8"
            x-data
            @keydown.escape.window="$wire.closeModal()"
        >
            <div class="absolute inset-0" wire:click="closeModal"></div>

            <div class="relative bg-zinc-900 border border-zinc-800 w-full max-w-md" @click.stop>
                <div class="sticky top-0 z-10 bg-zinc-800 text-accent px-5 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                    <span>{{ $editingId ? 'Edit Category' : 'New Category' }}</span>
                    <button wire:click="closeModal" class="text-zinc-400 hover:text-white transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="p-5">
                    <form wire:submit="save">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-zinc-400 mb-1">Name *</label>
                            <input type="text" wire:model="name"
                                class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm"
                                placeholder="e.g. Restaurant, Park, Landmark">
                            @error('name') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="flex gap-3 justify-end">
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
