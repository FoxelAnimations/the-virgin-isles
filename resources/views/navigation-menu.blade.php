<nav x-data="{ open: false }" class="bg-black border-b border-black">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a  class="block h-20 w-auto py-3"   href="{{ route('home') }}">
                        <x-application-mark/>
                    </a>
                </div>

                <!-- Navigation Links -->

            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <div class="flex items-center space-x-4  text-gray-100">
                    <a href="{{ route('home') }}" class="hover:text-prim transition">{{ __('Thus') }}</a>
                    <a href="{{ route('characters.index') }}" class="hover:text-prim transition">{{ __('Characters') }}</a>
                    @auth
                        <a href="{{ route('characters.create') }}" class="hover:text-prim transition">{{ __('Create Character') }}</a>
                        <a href="{{ route('jobs.create') }}" class="hover:text-prim transition">{{ __('Create Job') }}</a>
                    @endauth
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link href="{{ route('home') }}" :active="request()->routeIs('home')">
                {{ __('Home') }}
            </x-responsive-nav-link>

            <x-responsive-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>

            @auth
                <x-responsive-nav-link href="{{ route('characters.create') }}" :active="request()->routeIs('characters.create')">
                    {{ __('Create Character') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link href="{{ route('jobs.create') }}" :active="request()->routeIs('jobs.create')">
                    {{ __('Create Job') }}
                </x-responsive-nav-link>
            @endauth
        </div>
    </div>
</nav>
