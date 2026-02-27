<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="font-size: 19px;">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }} — Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=bebas-neue:400&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-black text-white">
        <x-banner />

        {{-- Admin Navigation --}}
        <nav x-data="{ open: false }" class="bg-zinc-900 border-b border-zinc-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    {{-- Logo --}}
                    <div class="shrink-0 flex items-center">
                        <a class="block h-20 w-auto py-3" href="{{ route('home') }}">
                            <x-application-mark/>
                        </a>
                    </div>

                    {{-- Desktop Links --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-8">
                        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest transition hover:text-accent">DASHBOARD</a>
                        <a href="{{ route('admin.characters') }}" class="{{ request()->routeIs('admin.characters') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest transition hover:text-accent">CHARACTERS</a>
                        <a href="{{ route('admin.episodes') }}" class="{{ request()->routeIs('admin.episodes') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest transition hover:text-accent">EPISODES</a>
                        <a href="{{ route('admin.chats') }}" class="{{ request()->routeIs('admin.chats*') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest transition hover:text-accent relative">
                            CHATS
                            @if (($unreadChatCount ?? 0) > 0)
                                <span class="absolute -top-1.5 -right-3 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ $unreadChatCount }}</span>
                            @endif
                        </a>
                        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest transition hover:text-accent">USERS</a>
                    </div>

                    {{-- Right side --}}
                    <div class="hidden sm:flex sm:items-center sm:space-x-4">
                        <span class="text-zinc-500 text-sm tracking-wider">{{ Auth::user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="border border-zinc-700 text-zinc-400 text-lg tracking-widest px-5 py-1.5 transition hover:border-accent hover:text-accent">LOGOUT</button>
                        </form>
                    </div>

                    {{-- Mobile Hamburger --}}
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-accent hover:text-white focus:outline-none transition">
                            <svg class="size-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Mobile Menu --}}
            <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden bg-zinc-900 border-t border-zinc-800">
                <div class="pt-2 pb-3 space-y-1">
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 {{ request()->routeIs('admin.dashboard') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest hover:text-accent transition">DASHBOARD</a>
                    <a href="{{ route('admin.characters') }}" class="block px-4 py-2 {{ request()->routeIs('admin.characters') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest hover:text-accent transition">CHARACTERS</a>
                    <a href="{{ route('admin.episodes') }}" class="block px-4 py-2 {{ request()->routeIs('admin.episodes') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest hover:text-accent transition">EPISODES</a>
                    <a href="{{ route('admin.chats') }}" class="block px-4 py-2 {{ request()->routeIs('admin.chats*') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest hover:text-accent transition">
                        CHATS
                        @if (($unreadChatCount ?? 0) > 0)
                            <span class="ml-1 inline-flex items-center justify-center w-4 h-4 text-[10px] font-bold bg-red-500 text-white rounded-full">{{ $unreadChatCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.users') }}" class="block px-4 py-2 {{ request()->routeIs('admin.users') ? 'text-accent' : 'text-zinc-400' }} text-lg tracking-widest hover:text-accent transition">USERS</a>
                    <form method="POST" action="{{ route('logout') }}" class="px-4 mt-2">
                        @csrf
                        <button type="submit" class="w-full border border-zinc-700 text-zinc-400 text-lg tracking-widest px-6 py-2 text-center transition hover:border-accent hover:text-accent">LOGOUT</button>
                    </form>
                </div>
            </div>
        </nav>

        {{-- Page Content --}}
        <main>
            {{ $slot }}
        </main>

        @stack('modals')

        @livewireScripts
    </body>
</html>
