@php
    use Illuminate\Support\Str;
@endphp

<section class="py-12">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-10 text-center">
            <h1 class="text-3xl font-semibold text-gray-900">{{ __('Characters') }}</h1>
            <p class="mt-2 text-gray-600">{{ __('Meet all characters and their roles.') }}</p>
        </div>

        @if ($characters->isEmpty())
            <p class="text-center text-gray-500">{{ __('No characters found yet.') }}</p>
        @else
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($characters as $character)
                    <div class="rounded-lg border border-gray-200 bg-white shadow-sm">
                        @if ($character->profile_image_path)
                            <img src="{{ Storage::url($character->profile_image_path) }}" alt="{{ $character->first_name }}" class="h-60 w-full rounded-t-lg object-cover">
                        @else
                            <div class="h-60 w-full rounded-t-lg bg-gray-100 flex items-center justify-center text-gray-400">
                                {{ __('No profile image') }}
                            </div>
                        @endif

                        <div class="p-5 space-y-3">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900">
                                    {{ $character->first_name }} {{ $character->last_name }}
                                </h2>
                                @if ($character->nick_name)
                                    <p class="text-sm text-gray-500">{{ __('AKA') }} {{ $character->nick_name }}</p>
                                @endif
                            </div>

                            <p class="text-sm text-gray-600">
                                {{ $character->job?->title ?? __('No job assigned') }}
                            </p>

                            @if ($character->bio)
                                <p class="text-sm text-gray-700 line-clamp-3">{{ Str::limit($character->bio, 150) }}</p>
                            @endif

                            @if ($character->full_body_image_path)
                                <div>
                                    <p class="text-xs font-semibold uppercase text-gray-500 mb-1">{{ __('Full Body') }}</p>
                                    <img src="{{ Storage::url($character->full_body_image_path) }}" alt="{{ $character->first_name }} full body" class="h-48 w-full rounded-md object-cover">
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</section>
