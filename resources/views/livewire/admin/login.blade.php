<x-authentication-card>
    <x-slot name="logo">
        <x-authentication-card-logo />
    </x-slot>

    <div class="mb-4 text-sm text-zinc-400 text-center uppercase tracking-wider">
        {{ __('Admin Login') }}
    </div>

    @if($errors->any())
        <div class="mb-4 font-medium text-sm text-red-400">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form wire:submit="authenticate">
        <div>
            <x-label for="email" value="{{ __('Email') }}" />
            <x-input id="email" class="block mt-1 w-full" type="email"
                wire:model="email" required autofocus autocomplete="username" />
        </div>

        <div class="mt-4">
            <x-label for="password" value="{{ __('Password') }}" />
            <x-input id="password" class="block mt-1 w-full" type="password"
                wire:model="password" required autocomplete="current-password" />
        </div>

        <div class="block mt-4">
            <label for="remember" class="flex items-center">
                <x-checkbox id="remember" wire:model="remember" />
                <span class="ms-2 text-sm text-zinc-400">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-button class="ms-4">
                {{ __('Log in') }}
            </x-button>
        </div>
    </form>
</x-authentication-card>
