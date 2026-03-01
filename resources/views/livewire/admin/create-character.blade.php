<div class="py-10">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="mb-8">
            <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
            <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Create Character') }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">
            {{-- Basic Info --}}
            <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-6 space-y-6">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('First Name') }}</label>
                        <input type="text" wire:model="first_name" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent" />
                        @error('first_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Last Name') }}</label>
                        <input type="text" wire:model="last_name" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent" />
                        @error('last_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Job') }}</label>
                        <select wire:model="job_id" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent">
                            <option value="">{{ __('Select a job') }}</option>
                            @foreach ($jobs as $job)
                                <option value="{{ $job->id }}">{{ $job->title }}</option>
                            @endforeach
                        </select>
                        @error('job_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Nickname') }}</label>
                        <input type="text" wire:model="nick_name" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent" />
                        @error('nick_name')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Age') }}</label>
                        <input type="number" wire:model="age" min="0" max="255" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent" />
                        @error('age')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Biography') }}</label>
                    <textarea wire:model="bio" rows="5" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    @error('bio')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Images Toggle --}}
            <div x-data="{ open: false }" class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
                <button @click="open = !open" type="button"
                    class="w-full bg-zinc-800 px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between text-accent hover:bg-zinc-750 transition">
                    <span>{{ __('Images') }}</span>
                    <svg :class="open && 'rotate-180'" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="p-4 space-y-5">

                    {{-- Face Image --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-accent mb-2">{{ __('Face Image') }}</p>
                        <div class="h-px bg-accent/40 mb-3"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Default') }}</label>
                                <input type="file" wire:model="profile_image" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('profile_image') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($profile_image)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $profile_image->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('profile_image', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Hover') }}</label>
                                <input type="file" wire:model="profile_image_hover" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('profile_image_hover') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($profile_image_hover)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $profile_image_hover->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('profile_image_hover', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Body Image --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-accent mb-2">{{ __('Body Image') }}</p>
                        <div class="h-px bg-accent/40 mb-3"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Default') }}</label>
                                <input type="file" wire:model="full_body_image" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('full_body_image') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($full_body_image)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $full_body_image->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('full_body_image', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Hover') }}</label>
                                <input type="file" wire:model="full_body_image_hover" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('full_body_image_hover') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($full_body_image_hover)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $full_body_image_hover->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('full_body_image_hover', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Profile Photo --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-accent mb-2">{{ __('Profile Photo') }}</p>
                        <p class="text-[10px] text-zinc-600 -mt-1 mb-2">{{ __('Used in slider.') }}</p>
                        <div class="h-px bg-accent/40 mb-3"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Default') }}</label>
                                <input type="file" wire:model="profile_photo" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('profile_photo') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($profile_photo)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $profile_photo->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('profile_photo', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Hover') }}</label>
                                <input type="file" wire:model="profile_photo_hover" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                @error('profile_photo_hover') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($profile_photo_hover)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $profile_photo_hover->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('profile_photo_hover', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Chat Image & Background --}}
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-accent mb-2">{{ __('Other Images') }}</p>
                        <div class="h-px bg-accent/40 mb-3"></div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Chat Image') }}</label>
                                <input type="file" wire:model="chat_image" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                <p class="mt-1 text-[10px] text-zinc-600">{{ __('Falls back to profile/face.') }}</p>
                                @error('chat_image') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($chat_image)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $chat_image->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('chat_image', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <label class="block text-[11px] uppercase tracking-wider text-zinc-500 mb-1">{{ __('Background') }}</label>
                                <input type="file" wire:model="background_image" class="block w-full text-xs text-zinc-400 file:mr-2 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-1.5 file:px-3 file:text-xs file:font-medium file:text-white hover:file:bg-zinc-700" />
                                <p class="mt-1 text-[10px] text-zinc-600">{{ __('Behind all images. Max 4MB.') }}</p>
                                @error('background_image') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                                @if ($background_image)
                                    <div class="mt-2 relative rounded-sm border border-zinc-700 p-1">
                                        <img src="{{ $background_image->temporaryUrl() }}" class="h-28 w-full object-cover rounded-sm">
                                        <button type="button" wire:click="$set('background_image', null)" class="absolute top-0 right-0 bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] hover:bg-red-500">&times;</button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- Chat Personality Toggle --}}
            <div x-data="{ open: false }" class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
                <button @click="open = !open" type="button"
                    class="w-full bg-zinc-800 px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between text-accent hover:bg-zinc-750 transition">
                    <span>{{ __('Chat Personality') }}</span>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer" @click.stop>
                            <input type="checkbox" wire:model.live="chat_online" class="rounded-sm border-zinc-600 bg-zinc-800 text-green-500 focus:ring-green-500">
                            <span class="text-xs normal-case tracking-normal {{ $chat_online ? 'text-green-400' : 'text-zinc-500' }}">{{ $chat_online ? __('Online') : __('Offline') }}</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer" @click.stop>
                            <input type="checkbox" wire:model.live="chat_enabled" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                            <span class="text-xs text-zinc-400 normal-case tracking-normal">{{ __('Enable') }}</span>
                        </label>
                        <svg :class="open && 'rotate-180'" class="w-4 h-4 transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                    </div>
                </button>
                <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0" x-cloak class="p-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Chat Mode') }}</label>
                        <select wire:model="chat_mode" class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent">
                            <option value="ai">{{ __('AI (GPT auto-responds)') }}</option>
                            <option value="manual">{{ __('Manual (admin responds live)') }}</option>
                        </select>
                        <p class="mt-1 text-xs text-zinc-600">{{ __('In manual mode, visitor messages wait for an admin to reply as this character.') }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Personality') }}</label>
                        <textarea wire:model="personality" rows="2" placeholder="Sarcastisch, loyaal, straatslim..."
                            class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Speaking Style') }}</label>
                        <textarea wire:model="speaking_style" rows="2" placeholder="Gebruikt veel straattaal, korte zinnen..."
                            class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Backstory') }}</label>
                        <textarea wire:model="backstory" rows="2" placeholder="Opgegroeid op straat in Antwerpen..."
                            class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Example Phrases') }}</label>
                        <textarea wire:model="example_phrases" rows="2" placeholder="Zet elke zin op een nieuwe regel..."
                            class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Additional Chat Instructions') }}</label>
                        <textarea wire:model="chat_instructions" rows="2" placeholder="Extra instructies voor GPT-gedrag..."
                            class="mt-1 block w-full rounded-sm border-zinc-700 bg-zinc-800 text-white shadow-sm focus:border-accent focus:ring-accent"></textarea>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                    <span wire:loading.remove>{{ __('Save Character') }}</span>
                    <span wire:loading>{{ __('Saving...') }}</span>
                </button>
            </div>
        </form>
    </div>
</div>
