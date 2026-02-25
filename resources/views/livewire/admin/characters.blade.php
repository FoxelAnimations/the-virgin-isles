<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Characters') }}</h1>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('characters.create') }}" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('Create Character') }}
                </a>
                <a href="{{ route('jobs.create') }}" class="inline-flex items-center border border-zinc-700 text-zinc-300 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:border-accent hover:text-accent">
                    {{ __('Create Job') }}
                </a>
            </div>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Characters --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800 flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Resource</p>
                    <h3 class="text-lg font-semibold uppercase tracking-wider">{{ __('Characters') }}</h3>
                </div>
                <a href="{{ route('characters.create') }}" class="inline-flex items-center bg-zinc-800 text-white px-3 py-2 text-sm font-semibold tracking-wider uppercase transition hover:bg-zinc-700">
                    {{ __('New Character') }}
                </a>
            </div>

            @if($characters->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('No characters yet. Create the first one!') }}</div>
            @else
                <div class="overflow-x-auto"
                    x-data
                    x-init="
                        Sortable.create($el.querySelector('#characters-sortable'), {
                            handle: '.drag-handle',
                            animation: 150,
                            ghostClass: 'opacity-30',
                            onEnd() {
                                const ids = [...$el.querySelectorAll('#characters-sortable tr[data-id]')].map(row => parseInt(row.dataset.id));
                                $wire.updateCharacterOrder(ids);
                            }
                        })
                    "
                >
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="w-10 px-3 py-3"></th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Name') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Job') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Bio') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody id="characters-sortable" class="divide-y divide-zinc-800">
                            @foreach($characters as $character)
                                <tr class="hover:bg-zinc-800/50 transition" data-id="{{ $character->id }}">
                                    <td class="px-3 py-4 drag-handle cursor-grab active:cursor-grabbing text-zinc-600 hover:text-zinc-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/></svg>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-semibold text-white">{{ $character->full_name }}</div>
                                        @if($character->nick_name)
                                            <div class="text-xs text-zinc-500">{{ __('AKA') }} {{ $character->nick_name }}</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-400">
                                        {{ $character->job?->title ?? __('Unassigned') }}
                                    </td>
                                    <td class="px-6 py-4 max-w-md text-sm text-zinc-500">
                                        {{ \Illuminate\Support\Str::limit($character->bio, 90) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <a href="{{ route('characters.edit', $character) }}" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">{{ __('Edit') }}</a>
                                        <button
                                            type="button"
                                            class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition"
                                            wire:click="delete({{ $character->id }})"
                                            wire:confirm="Are you sure you want to delete this character?"
                                        >
                                            {{ __('Delete') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Jobs --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mt-6" x-data="{ open: false }">
            <button @click="open = !open" class="w-full px-4 py-4 flex items-center justify-between hover:bg-zinc-800/50 transition">
                <div class="text-left">
                    <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Resource</p>
                    <h3 class="text-lg font-semibold uppercase tracking-wider">{{ __('Jobs') }}</h3>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('jobs.create') }}" @click.stop class="inline-flex items-center bg-zinc-800 text-white px-3 py-2 text-sm font-semibold tracking-wider uppercase transition hover:bg-zinc-700">
                        {{ __('New Job') }}
                    </a>
                    <svg class="w-5 h-5 text-zinc-500 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>
            </button>

            <div x-show="open" x-collapse>
                <div class="border-t border-zinc-800">
                    @if($jobs->isEmpty())
                        <div class="p-8 text-center text-zinc-600">{{ __('No jobs yet. Create the first one!') }}</div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-zinc-800">
                                <thead class="bg-zinc-800/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Title') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Description') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Characters') }}</th>
                                        <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-zinc-800">
                                    @foreach($jobs as $job)
                                        <tr class="hover:bg-zinc-800/50 transition">
                                            @if($editingJobId === $job->id)
                                                <td class="px-6 py-3" colspan="3">
                                                    <form wire:submit="updateJob" class="flex flex-col sm:flex-row gap-3">
                                                        <div class="flex-1">
                                                            <input type="text" wire:model="editingJobTitle" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-1.5 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="{{ __('Title') }}">
                                                            @error('editingJobTitle') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                                                        </div>
                                                        <div class="flex-1">
                                                            <input type="text" wire:model="editingJobDescription" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-1.5 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="{{ __('Description (optional)') }}">
                                                        </div>
                                                        <div class="flex gap-2 shrink-0">
                                                            <button type="submit" class="inline-flex items-center bg-accent text-black px-3 py-1.5 text-xs font-semibold tracking-wider uppercase transition hover:brightness-90">{{ __('Save') }}</button>
                                                            <button type="button" wire:click="cancelEditJob" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:text-white transition">{{ __('Cancel') }}</button>
                                                        </div>
                                                    </form>
                                                </td>
                                            @else
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="font-semibold text-white">{{ $job->title }}</div>
                                                </td>
                                                <td class="px-6 py-4 max-w-md text-sm text-zinc-500">
                                                    {{ $job->description ?? '—' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-zinc-400">
                                                    {{ $job->characters_count }}
                                                </td>
                                            @endif

                                            @if($editingJobId !== $job->id)
                                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                                    <button wire:click="editJob({{ $job->id }})" class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">{{ __('Edit') }}</button>
                                                    <button wire:click="deleteJob({{ $job->id }})" wire:confirm="{{ __('Are you sure? Characters with this job will become unassigned.') }}" class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition">{{ __('Delete') }}</button>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
