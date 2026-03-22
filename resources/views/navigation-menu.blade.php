<nav x-data="{ open: false }" class="bg-black text-white">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <!-- Logo -->
            <div class="shrink-0 flex items-center">
                <a class="block h-20 w-auto py-3" href="{{ route('home') }}">
                    <x-application-mark/>
                </a>
            </div>

            <!-- Center Navigation Links -->
            <div class="hidden sm:flex sm:items-center sm:space-x-10">
                <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">THUS</a>
                <a href="{{ route('episodes.index') }}" class="{{ request()->routeIs('episodes.index') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">AFLEVERINGEN</a>
                <a href="{{ route('characters.index') }}" class="{{ request()->routeIs('characters.index') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">PERSONAGES</a>
                <a href="{{ route('blog') }}" class="{{ request()->routeIs('blog') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">BLOG</a>
                @if(\App\Models\SiteSetting::first()?->show_collabs)
                    <a href="{{ route('collabs') }}" class="{{ request()->routeIs('collabs*') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">COLLABS</a>
                @endif
                <a href="{{ route('cameras.index') }}" class="{{ request()->routeIs('cameras.*') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">CAMERA'S</a>
                <a href="{{ route('map') }}" class="{{ request()->routeIs('map') ? 'text-[#E7FF57]' : 'text-white' }} text-lg tracking-widest transition hover:text-[#E7FF57]">MAP</a>
                @auth
                    @can('access-admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-[#E7FF57] text-lg tracking-widest transition hover:brightness-90">ADMIN</a>
                    @endcan
                @endauth
            </div>

            <!-- Right Side -->
            @auth
                <div class="hidden sm:flex sm:items-center sm:space-x-4">
                    <a href="{{ route('dashboard') }}" class="bg-[#E7FF57] text-black text-lg tracking-widest px-6 py-2 transition hover:opacity-90">DASHBOARD</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="border border-white text-white text-lg tracking-widest px-6 py-2 transition hover:bg-white hover:text-black">UITLOGGEN</button>
                    </form>
                </div>
            @else
                @if(\App\Models\SiteSetting::first()?->login_enabled)
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <a href="{{ route('login') }}" class="text-white text-lg tracking-widest transition hover:text-[#E7FF57]">INLOGGEN</a>
                    </div>
                @endif
            @endauth

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-[#E7FF57] hover:text-white focus:outline-none transition duration-150 ease-in-out">
                    <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Full-Screen Mobile Menu Overlay -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         x-cloak
         class="fixed inset-0 z-50 bg-black flex flex-col sm:hidden"
    >
        <!-- Header: Logo + Close -->
        <div class="flex items-center justify-between px-4 h-16">
            <a href="{{ route('home') }}" class="block h-20 w-auto py-3">
                <x-application-mark/>
            </a>
            <button @click="open = false" class="inline-flex items-center justify-center p-2 text-[#E7FF57] hover:text-white transition">
                <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <div class="flex-1 flex flex-col items-center justify-center space-y-6">
            <a href="{{ route('home') }}" @click="open = false" class="{{ request()->routeIs('home') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">THUS</a>
            <a href="{{ route('episodes.index') }}" @click="open = false" class="{{ request()->routeIs('episodes.index') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">AFLEVERINGEN</a>
            <a href="{{ route('characters.index') }}" @click="open = false" class="{{ request()->routeIs('characters.index') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">PERSONAGES</a>
            <a href="{{ route('blog') }}" @click="open = false" class="{{ request()->routeIs('blog') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">BLOG</a>
            @if(\App\Models\SiteSetting::first()?->show_collabs)
                <a href="{{ route('collabs') }}" @click="open = false" class="{{ request()->routeIs('collabs*') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">COLLABS</a>
            @endif
            <a href="{{ route('cameras.index') }}" @click="open = false" class="{{ request()->routeIs('cameras.*') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">CAMERA'S</a>
            <a href="{{ route('map') }}" @click="open = false" class="{{ request()->routeIs('map') ? 'text-[#E7FF57]' : 'text-white' }} text-2xl tracking-widest hover:text-[#E7FF57] transition">MAP</a>
            @auth
                @can('access-admin')
                    <a href="{{ route('admin.dashboard') }}" @click="open = false" class="text-[#E7FF57] text-2xl tracking-widest hover:brightness-90 transition">ADMIN</a>
                @endcan
            @endauth
        </div>

        <!-- Bottom Actions -->
        <div class="px-6 pb-10 space-y-3" style="padding-bottom: max(2.5rem, env(safe-area-inset-bottom))">
            @auth
                <a href="{{ route('dashboard') }}" class="block bg-[#E7FF57] text-black text-lg tracking-widest px-6 py-3 text-center transition hover:opacity-90">DASHBOARD</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full border border-white text-white text-lg tracking-widest px-6 py-3 text-center transition hover:bg-white hover:text-black">UITLOGGEN</button>
                </form>
            @else
                @if(\App\Models\SiteSetting::first()?->login_enabled)
                    <a href="{{ route('login') }}" class="block text-center text-white text-lg tracking-widest hover:text-[#E7FF57] transition py-3">INLOGGEN</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
