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
                <a href="{{ route('home') }}" class="text-white text-lg tracking-widest transition hover:text-[#E7FF57]">THUS</a>
                <a href="{{ route('episodes.index') }}" class="text-white text-lg tracking-widest transition hover:text-[#E7FF57]">AFLEVERINGEN</a>
                <a href="{{ route('home') }}#characters" class="text-white text-lg tracking-widest transition hover:text-[#E7FF57]">PERSONAGES</a>
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

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-black">
        <div class="pt-2 pb-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-4 py-2 text-white text-lg tracking-widest hover:text-[#E7FF57] transition">THUS</a>
            <a href="{{ route('episodes.index') }}" class="block px-4 py-2 text-white text-lg tracking-widest hover:text-[#E7FF57] transition">AFLEVERINGEN</a>
            <a href="{{ route('home') }}#characters" class="block px-4 py-2 text-white text-lg tracking-widest hover:text-[#E7FF57] transition">PERSONAGES</a>
            @auth
                @can('access-admin')
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-[#E7FF57] text-lg tracking-widest hover:brightness-90 transition">ADMIN</a>
                @endcan
                <a href="{{ route('dashboard') }}" class="block mx-4 mt-2 bg-[#E7FF57] text-black text-lg tracking-widest px-6 py-2 text-center transition hover:opacity-90">DASHBOARD</a>
                <form method="POST" action="{{ route('logout') }}" class="px-4 mt-2">
                    @csrf
                    <button type="submit" class="w-full border border-white text-white text-lg tracking-widest px-6 py-2 text-center transition hover:bg-white hover:text-black">UITLOGGEN</button>
                </form>
            @else
                @if(\App\Models\SiteSetting::first()?->login_enabled)
                    <a href="{{ route('login') }}" class="block px-4 py-2 text-white text-lg tracking-widest hover:text-[#E7FF57] transition">INLOGGEN</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
