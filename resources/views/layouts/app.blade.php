<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=montserrat:400,500,600,700&display=swap" rel="stylesheet" />
        <link href="https://fonts.bunny.net/css?family=bebas-neue:400&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased overflow-x-hidden">
        <x-banner />

        <div class="min-h-screen {{ $bgClass ?? 'bg-gray-100' }}">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="relative z-[40] bg-zinc-900 border-t border-zinc-800 text-zinc-500">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" style="padding-bottom: max(2rem, env(safe-area-inset-bottom))">
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                        <p class="text-sm tracking-wider">&copy; {{ date('Y') }} IN-CC. {{ __('Alle rechten voorbehouden.') }}</p>
                        <p class="text-sm tracking-wider">Business contact: <a href="mailto:wuk@in-cc.be" class="text-accent hover:brightness-90 transition">wuk@in-cc.be</a></p>
                    </div>
                </div>
            </footer>
        </div>

        @stack('modals')

        <x-badge-popup />
        <x-age-gate />
        @if (!request()->routeIs('map') && !request()->routeIs('cameras.show'))
            <x-character-chat />
        @endif

        @livewireScripts
    </body>
</html>
