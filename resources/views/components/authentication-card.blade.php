<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0">
    <div class="h-40">
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-zinc-900 border border-zinc-800 overflow-hidden sm:rounded-sm">
        {{ $slot }}
    </div>
</div>
