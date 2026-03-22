<?php

namespace App\Livewire;

use App\Models\EpisodeComment;
use App\Models\EpisodeRating;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class EpisodeInteractions extends Component
{
    #[Locked]
    public ?int $episodeId = null;

    public string $commentBody = '';

    #[Locked]
    public int $userRating = 0;

    #[On('open-episode-interactions')]
    public function loadEpisode(int $id): void
    {
        $this->episodeId = $id;
        $this->commentBody = '';
        $this->resetValidation();

        $this->userRating = Auth::check()
            ? (int) (EpisodeRating::where('episode_id', $id)->where('user_id', Auth::id())->value('rating') ?? 0)
            : 0;
    }

    #[On('close-episode-interactions')]
    public function closeEpisode(): void
    {
        $this->episodeId = null;
        $this->commentBody = '';
        $this->userRating = 0;
        $this->resetValidation();
    }

    public function rate(int $rating): void
    {
        if (! Auth::check() || ! $this->episodeId) {
            return;
        }

        if (Auth::user()->isCommentBlocked()) {
            return;
        }

        $rating = max(1, min(5, $rating));

        EpisodeRating::updateOrCreate(
            ['user_id' => Auth::id(), 'episode_id' => $this->episodeId],
            ['rating' => $rating]
        );

        $this->userRating = $rating;
    }

    public function addComment(): void
    {
        if (! Auth::check() || ! $this->episodeId) {
            return;
        }

        if (Auth::user()->isCommentBlocked()) {
            $this->addError('commentBody', 'Je bent geblokkeerd om reacties te plaatsen.');
            return;
        }

        $this->validate([
            'commentBody' => 'required|string|min:2|max:1000',
        ]);

        $key = 'episode-comment:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            $this->addError('commentBody', "Wacht nog {$seconds} seconden voordat je weer kunt reageren.");
            return;
        }

        RateLimiter::hit($key, 60);

        EpisodeComment::create([
            'user_id' => Auth::id(),
            'episode_id' => $this->episodeId,
            'body' => strip_tags($this->commentBody),
        ]);

        $this->commentBody = '';
    }

    public function deleteComment(int $commentId): void
    {
        $comment = EpisodeComment::findOrFail($commentId);

        if (Auth::user()?->is_admin || $comment->user_id === Auth::id()) {
            $comment->delete();
        }
    }

    public function render()
    {
        $comments = collect();
        $averageRating = 0;
        $ratingCount = 0;

        if ($this->episodeId) {
            $comments = EpisodeComment::with('user')
                ->where('episode_id', $this->episodeId)
                ->latest()
                ->take(50)
                ->get();

            $ratingData = EpisodeRating::where('episode_id', $this->episodeId)
                ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as total')
                ->first();

            $averageRating = round($ratingData->avg_rating ?? 0, 1);
            $ratingCount = $ratingData->total ?? 0;
        }

        return view('livewire.episode-interactions', [
            'comments' => $comments,
            'averageRating' => $averageRating,
            'ratingCount' => $ratingCount,
        ]);
    }
}
