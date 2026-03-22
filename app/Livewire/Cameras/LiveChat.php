<?php

namespace App\Livewire\Cameras;

use App\Models\Camera;
use App\Models\CameraLiveMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;

class LiveChat extends Component
{
    public Camera $camera;
    public string $message = '';

    public function mount(Camera $camera): void
    {
        $this->camera = $camera;
        $this->sendHeartbeat();
    }

    public function getMessagesProperty(): Collection
    {
        return CameraLiveMessage::where('camera_id', $this->camera->id)
            ->with('user:id,name')
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();
    }

    public function getViewerCountProperty(): int
    {
        return (int) Cache::get("camera_viewers:{$this->camera->id}", 0);
    }

    public function sendHeartbeat(): void
    {
        $viewerId = auth()->id() ? 'u' . auth()->id() : 's' . session()->getId();
        $cameraId = $this->camera->id;

        $setKey = "camera_viewer_set:{$cameraId}";
        $viewers = Cache::get($setKey, []);

        $viewers[$viewerId] = now()->timestamp;

        // Remove expired viewers (older than 15s)
        $cutoff = now()->timestamp - 15;
        $viewers = array_filter($viewers, fn ($ts) => $ts > $cutoff);

        Cache::put($setKey, $viewers, 60);
        Cache::put("camera_viewers:{$cameraId}", count($viewers), 60);
    }

    public function sendMessage(): void
    {
        if (!auth()->check()) {
            return;
        }

        $user = auth()->user();

        if ($user->isAccountBlocked() || $user->isCommentBlocked()) {
            $this->addError('message', 'Je bent geblokkeerd om berichten te plaatsen.');
            return;
        }

        $this->validate([
            'message' => ['required', 'string', 'max:200'],
        ]);

        $body = strip_tags(trim($this->message));

        if ($body === '') {
            return;
        }

        // Rate limit: 5 messages per 60 seconds per user
        $rateLimitKey = "camera_chat_rate:{$user->id}";
        $attempts = (int) Cache::get($rateLimitKey, 0);
        if ($attempts >= 5) {
            $this->addError('message', 'Wacht even voordat je weer een bericht stuurt.');
            return;
        }
        Cache::put($rateLimitKey, $attempts + 1, 60);

        // Duplicate detection: same user + same text within 30s
        $dupeKey = "camera_chat_dupe:{$user->id}:" . md5($body);
        if (Cache::has($dupeKey)) {
            $this->addError('message', 'Dit bericht is al verstuurd.');
            return;
        }
        Cache::put($dupeKey, true, 30);

        CameraLiveMessage::create([
            'camera_id' => $this->camera->id,
            'user_id' => $user->id,
            'body' => $body,
        ]);

        $this->pruneMessages();
        $this->message = '';
    }

    private function pruneMessages(): void
    {
        $keepIds = CameraLiveMessage::where('camera_id', $this->camera->id)
            ->latest()
            ->take(20)
            ->pluck('id');

        CameraLiveMessage::where('camera_id', $this->camera->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    public function render()
    {
        return view('livewire.cameras.live-chat');
    }
}
