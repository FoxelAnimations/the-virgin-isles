<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="flex items-center justify-between mb-8">
            <div>
                <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
                <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Quotes') }}</h1>
            </div>
            @unless($showForm)
                <button wire:click="openCreateForm"
                    class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                    {{ __('Nieuwe quote') }}
                </button>
            @endunless
        </div>

        {{-- Status flash --}}
        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- CREATE/EDIT FORM --}}
        @if($showForm)
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mb-6">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                <span>{{ $editingId ? __('Quote bewerken') : __('Nieuwe quote') }}</span>
                <button wire:click="cancelForm" class="text-xs text-zinc-400 border border-zinc-700 px-2 py-1 hover:text-accent hover:border-accent transition">
                    {{ __('Annuleren') }}
                </button>
            </div>
            <div class="p-4 space-y-6">
                <form wire:submit="save" class="space-y-6">

                    {{-- Active toggle --}}
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="is_active" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                        <span class="text-sm font-medium text-white">{{ __('Actief') }}</span>
                    </label>

                    {{-- Character --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Character') }} *</label>
                        <select wire:model="character_id" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            <option value="">{{ __('Selecteer een character...') }}</option>
                            @foreach($characters as $character)
                                <option value="{{ $character->id }}">{{ $character->full_name }}</option>
                            @endforeach
                        </select>
                        @error('character_id') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Quote text --}}
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Quote tekst') }} *</label>
                        <textarea wire:model="text" rows="3" maxlength="1000" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="De quote tekst..."></textarea>
                        @error('text') <span class="text-red-400 text-xs">{{ $message }}</span> @enderror
                    </div>

                    {{-- Submit --}}
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="save">{{ $editingId ? __('Opslaan') : __('Aanmaken') }}</span>
                            <span wire:loading wire:target="save">{{ __('Bezig...') }}</span>
                        </button>
                        <button type="button" wire:click="cancelForm" class="inline-flex items-center border border-zinc-700 text-zinc-400 px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:text-white">
                            {{ __('Annuleren') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @endif

        {{-- QUOTES LIST --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="px-4 py-4 border-b border-zinc-800">
                <p class="text-xs uppercase tracking-[0.3em] text-zinc-500">Overzicht</p>
                <h2 class="text-lg font-semibold uppercase tracking-wider">{{ __('Quotes') }}</h2>
            </div>

            @if($quotes->isEmpty())
                <div class="p-8 text-center text-zinc-600">{{ __('Nog geen quotes.') }}</div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-800">
                        <thead class="bg-zinc-800/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Quote') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Character') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Status') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-zinc-500 uppercase tracking-wider">{{ __('Acties') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-800">
                            @foreach($quotes as $quote)
                                <tr class="hover:bg-zinc-800/50 transition {{ !$quote->is_active ? 'opacity-50' : '' }}" wire:key="quote-{{ $quote->id }}">
                                    <td class="px-6 py-4">
                                        <div class="text-white text-sm max-w-md truncate italic">"{{ Str::limit($quote->text, 80) }}"</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="text-accent text-sm font-semibold">{{ $quote->character?->full_name ?? '—' }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button wire:click="toggleActive({{ $quote->id }})"
                                            class="inline-flex items-center px-2 py-1 text-xs font-semibold rounded-sm transition {{ $quote->is_active ? 'bg-green-900/30 text-green-400 border border-green-800' : 'bg-zinc-800 text-zinc-500 border border-zinc-700' }}">
                                            {{ $quote->is_active ? __('Actief') : __('Inactief') }}
                                        </button>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        <button wire:click="openEditForm({{ $quote->id }})"
                                            class="inline-flex items-center border border-zinc-700 px-3 py-1.5 text-xs font-semibold text-zinc-300 hover:border-accent hover:text-accent transition">
                                            {{ __('Bewerken') }}
                                        </button>
                                        <button wire:click="delete({{ $quote->id }})"
                                            wire:confirm="{{ __('Quote permanent verwijderen?') }}"
                                            class="inline-flex items-center border border-red-900 px-3 py-1.5 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition">
                                            {{ __('Verwijderen') }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
