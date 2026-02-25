@php
    $ageGate = \App\Models\AgeGate::first();
@endphp

@if($ageGate && $ageGate->enabled)
<div
    x-data="{
        show: false,
        init() {
            if (!document.cookie.split('; ').some(c => c.startsWith('age_verified='))) {
                this.show = true;
                document.body.classList.add('overflow-hidden');
            }
        },
        confirm() {
            document.cookie = 'age_verified=true; max-age=' + (365 * 24 * 60 * 60) + '; path=/; SameSite=Lax';
            this.show = false;
            document.body.classList.remove('overflow-hidden');
        },
        deny() {
            window.location.href = {{ Js::from($ageGate->deny_url) }};
        }
    }"
    x-show="show"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 p-4"
    style="display: none;"
>
    <div class="bg-zinc-900 border border-zinc-800 rounded-sm w-full max-w-md p-8 text-center">
        {{-- Logo --}}
        <div class="mb-6">
            <img src="{{ asset('img/TVI-Logo-inline.png') }}" alt="{{ config('app.name') }}" class="h-12 mx-auto opacity-80">
        </div>

        {{-- Message --}}
        <p class="text-white text-lg leading-relaxed mb-8">
            {{ $ageGate->message }}
        </p>

        {{-- Buttons --}}
        <div class="flex flex-col gap-3">
            <button
                @click="confirm()"
                class="w-full bg-accent text-black px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:brightness-90"
            >
                {{ $ageGate->confirm_text }}
            </button>
            <a
                href="{{ $ageGate->deny_url }}"
                @click.prevent="deny()"
                class="w-full border border-zinc-700 text-zinc-400 px-6 py-3 text-lg font-bold uppercase tracking-wider transition hover:border-red-500 hover:text-red-400 block"
            >
                {{ $ageGate->deny_text }}
            </a>
        </div>
    </div>
</div>
@endif
