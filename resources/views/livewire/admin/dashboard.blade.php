<div class="py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Page Title --}}
        <div class="mb-8">
            <p class="text-sm uppercase tracking-[0.3em] text-zinc-500">CMS</p>
            <h1 class="text-4xl font-bold uppercase tracking-wider">{{ __('Home Page') }}</h1>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-sm bg-accent/10 border border-accent/30 px-4 py-3 text-sm text-accent">
                {{ session('status') }}
            </div>
        @endif

        {{-- Hero Section CMS --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mb-6">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider">{{ __('Hero Section') }}</div>
            <div class="p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Left: Video --}}
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-3">{{ __('Video') }}</h3>
                        @if ($heroVideo)
                            <div class="mb-3">
                                <video class="w-full max-h-48 rounded-sm bg-black" controls>
                                    <source src="{{ Storage::url($heroVideo->video_path) }}" type="video/mp4">
                                </video>
                            </div>
                            <div class="flex items-center justify-between mb-3">
                                <span class="text-xs text-zinc-500">{{ __('Current hero video') }}</span>
                                <button
                                    wire:click="removeVideo"
                                    wire:confirm="Are you sure you want to remove the hero video?"
                                    class="inline-flex items-center border border-red-900 px-2 py-1 text-xs font-semibold text-red-400 hover:bg-red-900/30 transition"
                                >
                                    {{ __('Remove') }}
                                </button>
                            </div>
                        @else
                            <p class="text-sm text-zinc-500 mb-3">{{ __('No hero video set. The homepage will show the logo placeholder.') }}</p>
                        @endif

                        <form wire:submit="uploadVideo">
                            <label class="block text-xs font-medium text-zinc-500 mb-1">
                                {{ $heroVideo ? __('Replace video') : __('Upload video') }}
                            </label>
                            <input type="file" wire:model="video" accept="video/mp4,video/webm,video/quicktime" class="block w-full text-sm text-zinc-400 file:mr-4 file:py-2 file:px-3 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-zinc-800 file:text-white hover:file:bg-zinc-700">
                            @error('video') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror

                            <div class="mt-3 flex items-center gap-3">
                                <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="uploadVideo">{{ __('Upload') }}</span>
                                    <span wire:loading wire:target="uploadVideo">{{ __('Uploading...') }}</span>
                                </button>
                                <div wire:loading wire:target="video" class="text-sm text-zinc-500">{{ __('Processing file...') }}</div>
                            </div>
                        </form>
                    </div>

                    {{-- Right: Copy --}}
                    <div>
                        <h3 class="text-sm font-semibold text-zinc-400 uppercase tracking-wider mb-3">{{ __('Copy') }}</h3>
                        <form wire:submit="saveHeroContent" class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Pre-title') }}</label>
                                <input type="text" wire:model="heroPreTitle" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Info / Intro">
                                @error('heroPreTitle') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Title') }}</label>
                                <input type="text" wire:model="heroTitle" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="The Visual Identity">
                                @error('heroTitle') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Description') }}</label>
                                <textarea wire:model="heroDescription" rows="4" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="A creative universe..."></textarea>
                                @error('heroDescription') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                            </div>
                            <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                                {{ __('Save Copy') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Social Links CMS --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider flex items-center justify-between">
                <span>{{ __('Social Links') }}</span>
                <button wire:click="addSocialLink" class="text-xs text-zinc-400 border border-zinc-700 px-2 py-1 hover:text-accent hover:border-accent transition">+ {{ __('Add') }}</button>
            </div>
            <div class="p-4">
                <p class="text-sm text-zinc-500 mb-4">{{ __('These buttons appear on the homepage. Leave the URL empty to hide a link.') }}</p>

                <div class="space-y-3">
                    @foreach($socialLinks as $index => $link)
                        <div class="flex items-center gap-3" wire:key="social-{{ $link['id'] }}">
                            <div class="w-40">
                                <input type="text" wire:model="socialLinks.{{ $index }}.label" placeholder="{{ __('Label') }}" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            </div>
                            <div class="flex-1">
                                <input type="url" wire:model="socialLinks.{{ $index }}.url" placeholder="https://..." class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm">
                            </div>
                            <button wire:click="removeSocialLink({{ $link['id'] }})" wire:confirm="Remove this social link?" class="text-red-400 hover:text-red-300 transition p-1 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                        @error("socialLinks.{$index}.label") <p class="text-red-400 text-xs ml-1">{{ $message }}</p> @enderror
                        @error("socialLinks.{$index}.url") <p class="text-red-400 text-xs ml-1">{{ $message }}</p> @enderror
                    @endforeach
                </div>

                @if(count($socialLinks) > 0)
                    <div class="mt-4">
                        <button wire:click="saveSocialLinks" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                            {{ __('Save Social Links') }}
                        </button>
                    </div>
                @endif
            </div>
        </div>

        {{-- Site Settings CMS --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mt-6">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider">{{ __('Site Settings') }}</div>
            <div class="p-4">
                <form wire:submit="saveSiteSettings" class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="loginEnabled" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                        <span class="text-sm font-medium text-white">{{ __('Login available') }}</span>
                    </label>
                    <p class="text-sm text-zinc-500">{{ __('When unchecked, the public login page returns a 404. Admins can still log in via /admin/login.') }}</p>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="registerEnabled" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                        <span class="text-sm font-medium text-white">{{ __('Register available') }}</span>
                    </label>
                    <p class="text-sm text-zinc-500">{{ __('When unchecked, the public register page returns a 404.') }}</p>

                    <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                        {{ __('Save Site Settings') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Age Gate CMS --}}
        <div class="rounded-sm bg-zinc-900 border border-zinc-800 overflow-hidden mt-6">
            <div class="bg-zinc-800 text-accent px-4 py-3 text-sm font-semibold uppercase tracking-wider">{{ __('Age Gate (18+)') }}</div>
            <div class="p-4">
                <form wire:submit="saveAgeGate" class="space-y-4">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" wire:model.live="ageGateEnabled" class="rounded-sm border-zinc-600 bg-zinc-800 text-accent focus:ring-accent">
                        <span class="text-sm font-medium text-white">{{ __('Enable age verification') }}</span>
                    </label>
                    <p class="text-sm text-zinc-500">{{ __('When enabled, visitors must confirm they are 18+ before accessing the site.') }}</p>

                    <div class="{{ $ageGateEnabled ? '' : 'opacity-50 pointer-events-none' }}">
                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Message') }}</label>
                        <textarea wire:model="ageGateMessage" rows="3" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Ben je 18 of ouder?"></textarea>
                        @error('ageGateMessage') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Confirm Button Text') }}</label>
                            <input type="text" wire:model="ageGateConfirmText" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Ja, ik ben 18+">
                            @error('ageGateConfirmText') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Deny Button Text') }}</label>
                            <input type="text" wire:model="ageGateDenyText" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="Nee">
                            @error('ageGateDenyText') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-500 mb-1">{{ __('Deny Redirect URL') }}</label>
                        <input type="url" wire:model="ageGateDenyUrl" class="w-full bg-zinc-800 border border-zinc-700 text-white px-3 py-2 text-sm focus:border-accent focus:ring-accent rounded-sm" placeholder="https://www.google.com">
                        @error('ageGateDenyUrl') <span class="text-red-400 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>
                    </div>

                    <button type="submit" class="inline-flex items-center bg-accent text-black px-4 py-2 text-sm font-semibold tracking-wider uppercase transition hover:brightness-90">
                        {{ __('Save Age Gate') }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
