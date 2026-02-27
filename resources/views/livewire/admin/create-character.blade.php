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

        <div class="rounded-sm bg-zinc-900 border border-zinc-800 p-6">
            <form wire:submit="save" class="space-y-6" enctype="multipart/form-data">
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

                <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Face Image') }}</label>
                        <input type="file" wire:model="profile_image" class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-2 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-700" />
                        @error('profile_image')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($profile_image)
                            <div class="mt-4 rounded-sm border border-zinc-700 p-2">
                                <img src="{{ $profile_image->temporaryUrl() }}" alt="{{ __('Face preview') }}" class="h-40 w-full object-cover">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Body Image') }}</label>
                        <input type="file" wire:model="full_body_image" class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-2 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-700" />
                        @error('full_body_image')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($full_body_image)
                            <div class="mt-4 rounded-sm border border-zinc-700 p-2">
                                <img src="{{ $full_body_image->temporaryUrl() }}" alt="{{ __('Body preview') }}" class="h-40 w-full object-cover">
                            </div>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-400">{{ __('Profile Photo') }}</label>
                        <input type="file" wire:model="profile_photo" class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-2 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-700" />
                        <p class="mt-1 text-xs text-zinc-600">{{ __('Used in chat & slider.') }}</p>
                        @error('profile_photo')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror

                        @if ($profile_photo)
                            <div class="mt-4 rounded-sm border border-zinc-700 p-2">
                                <img src="{{ $profile_photo->temporaryUrl() }}" alt="{{ __('Profile photo preview') }}" class="h-40 w-full object-cover">
                            </div>
                        @endif
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-400">{{ __('Additional Character Images') }}</label>
                    <input type="file" wire:model="images" multiple class="mt-1 block w-full text-sm text-zinc-400 file:mr-4 file:rounded-sm file:border-0 file:bg-zinc-800 file:py-2 file:px-4 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-700" />
                    <p class="mt-1 text-xs text-zinc-600">{{ __('You can upload up to 2MB per image.') }}</p>
                    @error('images.*')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                @if ($images)
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ($images as $preview)
                            <div class="rounded-sm border border-zinc-700 p-2">
                                <img src="{{ $preview->temporaryUrl() }}" alt="{{ __('Image preview') }}" class="h-32 w-full object-cover">
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- Chat Personality Section --}}
                <div class="col-span-full rounded-sm bg-zinc-800/50 border border-zinc-700 overflow-hidden">
                    <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                        <span>{{ __('Chat Personality (GPT)') }}</span>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="chat_online" class="rounded-sm border-zinc-600 bg-zinc-800 text-green-500 focus:ring-green-500">
                                <span class="text-xs normal-case tracking-normal {{ $chat_online ? 'text-green-400' : 'text-zinc-500' }}">{{ $chat_online ? __('Online') : __('Offline') }}</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="chat_enabled" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                                <span class="text-xs text-zinc-400 normal-case tracking-normal">{{ __('Enable in chat') }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="p-4 space-y-4 {{ $chat_enabled ? '' : 'opacity-50 pointer-events-none' }}">
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
</div>
