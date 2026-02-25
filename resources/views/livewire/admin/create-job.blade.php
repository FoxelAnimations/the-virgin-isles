<div class="py-10">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="mb-8">
            <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
            <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Create Job') }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-6">
            <form wire:submit.prevent="save" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Title') }}</label>
                    <input type="text" wire:model.defer="title" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent" />
                    @error('title')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Description') }}</label>
                    <textarea wire:model.defer="description" rows="4" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                        <span wire:loading.remove>{{ __('Save Job') }}</span>
                        <span wire:loading>{{ __('Saving...') }}</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
