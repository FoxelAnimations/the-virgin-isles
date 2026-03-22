<div>
    @if ($episodeId)
        {{-- Rating Section --}}
        <div class="mb-6">
            <div class="flex items-center gap-4">
                @auth
                    @if(auth()->user()->isCommentBlocked())
                        <div class="flex gap-0.5">
                            @for ($i = 1; $i <= 5; $i++)
                                <span class="text-2xl {{ $averageRating >= $i ? 'text-accent' : 'text-zinc-600' }}">&#9733;</span>
                            @endfor
                        </div>
                    @else
                        <div class="flex gap-1" x-data="{ hoverRating: 0 }">
                            @for ($i = 1; $i <= 5; $i++)
                                <button
                                    wire:click="rate({{ $i }})"
                                    @mouseenter="hoverRating = {{ $i }}"
                                    @mouseleave="hoverRating = 0"
                                    class="text-2xl transition cursor-pointer focus:outline-none"
                                    :class="(hoverRating > 0 ? hoverRating : {{ $userRating }}) >= {{ $i }} ? 'text-accent' : 'text-zinc-600 hover:text-zinc-400'"
                                >&#9733;</button>
                            @endfor
                        </div>
                    @endif
                @else
                    <div class="flex gap-0.5">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="text-2xl {{ $averageRating >= $i ? 'text-accent' : 'text-zinc-600' }}">&#9733;</span>
                        @endfor
                    </div>
                @endauth
                <span class="text-sm text-zinc-400">
                    {{ $averageRating }} / 5
                    @if ($ratingCount > 0)
                        ({{ $ratingCount }} {{ $ratingCount === 1 ? 'stem' : 'stemmen' }})
                    @endif
                </span>
            </div>
            @auth
                @if ($userRating > 0)
                    <p class="text-xs text-zinc-500 mt-1">Jouw beoordeling: {{ $userRating }} / 5</p>
                @endif
            @else
                <p class="text-xs text-zinc-500 mt-1">
                    <a href="{{ route('login') }}" class="text-accent hover:underline">Log in</a> om te beoordelen.
                </p>
            @endauth
        </div>

        <div class="border-t border-zinc-700/50 pt-4">
            {{-- Comment Form --}}
            @auth
                @if(auth()->user()->isCommentBlocked())
                    <p class="text-orange-400 text-sm mb-6">Je bent geblokkeerd om reacties te plaatsen.</p>
                @else
                    <form wire:submit="addComment" class="mb-6">
                        <textarea
                            wire:model="commentBody"
                            rows="3"
                            maxlength="1000"
                            placeholder="Schrijf een reactie..."
                            class="w-full bg-zinc-800 border border-zinc-700 text-white rounded-sm p-3 text-sm placeholder-zinc-500 focus:border-accent focus:ring-1 focus:ring-accent focus:outline-none resize-none"
                        ></textarea>
                        @error('commentBody')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <div class="flex justify-between items-center mt-2">
                            <span class="text-xs text-zinc-600" x-data x-text="$wire.commentBody.length + ' / 1000'"></span>
                            <button type="submit" class="bg-accent text-black px-4 py-2 text-xs font-bold uppercase tracking-wider hover:brightness-90 transition">
                                Reageer
                            </button>
                        </div>
                    </form>
                @endif
            @else
                <p class="text-zinc-400 text-sm mb-6">
                    <a href="{{ route('login') }}" class="text-accent hover:underline">Log in</a> om een reactie te plaatsen.
                </p>
            @endauth

            {{-- Comments List --}}
            <div class="space-y-3">
                @forelse ($comments as $comment)
                    <div class="bg-zinc-800/50 border border-zinc-700/30 rounded-sm p-4" wire:key="comment-{{ $comment->id }}">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <span class="text-sm font-semibold text-white">{{ $comment->user->name }}</span>
                                <span class="text-xs text-zinc-500 ml-2">{{ $comment->created_at->diffForHumans() }}</span>
                            </div>
                            @if (auth()->user()?->is_admin || auth()->id() === $comment->user_id)
                                <button
                                    wire:click="deleteComment({{ $comment->id }})"
                                    wire:confirm="Weet je zeker dat je deze reactie wilt verwijderen?"
                                    class="text-zinc-500 hover:text-red-400 transition shrink-0"
                                    title="Verwijder reactie"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            @endif
                        </div>
                        <p class="text-sm text-zinc-300 mt-2 font-description whitespace-pre-line break-words">{{ $comment->body }}</p>
                    </div>
                @empty
                    <p class="text-zinc-600 text-sm text-center py-4">Nog geen reacties.</p>
                @endforelse
            </div>
        </div>
    @endif
</div>
