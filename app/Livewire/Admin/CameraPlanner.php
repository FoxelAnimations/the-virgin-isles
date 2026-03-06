<?php

namespace App\Livewire\Admin;

use App\Models\Camera;
use App\Models\CameraDefaultBlock;
use App\Models\CameraScheduledVideo;
use App\Models\CameraVideo;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class CameraPlanner extends Component
{
    use WithFileUploads;

    public Camera $camera;

    // Video upload (used by Add Video modal)
    public $videoUpload = null;
    public $newAudioUpload = null;
    public string $newVideoType = 'loop';
    public ?int $newVideoDurationSeconds = null;

    // Audio uploads map (videoId => file) — for editing existing videos
    public array $audioUploads = [];
    public $backgroundUpload = null;

    // Default video selections (slot_key => video_id|null)
    public array $defaultSelections = [];

    // Snap precision
    public int $snapMinutes = 15;

    // Add Video Modal
    public bool $showAddVideoModal = false;

    // Edit Video Modal
    public bool $showEditVideoModal = false;
    public ?int $editingVideoId = null;
    public string $editVideoName = '';
    public string $editVideoType = 'loop';

    // Schedule modal
    public bool $showScheduleModal = false;
    public ?int $editingScheduleId = null;
    public ?int $scheduleVideoId = null;
    public int $scheduleDayOfWeek = 0;
    public string $scheduleStartTime = '08:00';
    public string $scheduleEndTime = '09:00';

    // Static effect
    public bool $staticEnabled = true;
    public int $staticIntensity = 15;

    public function mount(Camera $camera): void
    {
        $this->camera = $camera;
        $this->staticEnabled = $camera->static_enabled ?? true;
        $this->staticIntensity = $camera->static_intensity ?? 15;
        $this->loadDefaultSelections();
    }

    public function updateName(string $name): void
    {
        $name = trim($name);
        if ($name === '' || $name === $this->camera->name) {
            return;
        }

        $this->camera->update(['name' => $name]);
        $this->camera->refresh();
    }

    protected function loadDefaultSelections(): void
    {
        $defaults = $this->camera->defaultBlocks()->get();

        $this->defaultSelections = [];
        foreach (CameraDefaultBlock::slots() as $slot => $bounds) {
            $block = $defaults->where('time_slot', $slot)->first();
            $this->defaultSelections[$slot] = $block?->camera_video_id ? (string) $block->camera_video_id : '';
        }
    }

    public function updatedSnapMinutes(): void
    {
        $this->dispatch('snap-updated', snap: $this->snapMinutes);
    }

    // ─── Add Video Modal ─────────────────────────────────────

    public function openAddVideoModal(): void
    {
        $this->reset('videoUpload', 'newAudioUpload', 'newVideoDurationSeconds');
        $this->newVideoType = 'loop';
        $this->showAddVideoModal = true;
    }

    public function closeAddVideoModal(): void
    {
        $this->reset('videoUpload', 'newAudioUpload', 'newVideoDurationSeconds');
        $this->newVideoType = 'loop';
        $this->showAddVideoModal = false;
    }

    public function uploadVideo(): void
    {
        $filename = $this->videoUpload->getClientOriginalName();
        $videoPath = $this->videoUpload->store("cameras/{$this->camera->id}/videos", 'public');

        $audioPath = null;
        if ($this->newAudioUpload) {
            $audioPath = $this->newAudioUpload->store("cameras/{$this->camera->id}/audio", 'public');
        }

        CameraVideo::create([
            'camera_id' => $this->camera->id,
            'filename' => $filename,
            'video_path' => $videoPath,
            'audio_path' => $audioPath,
            'behaviour_type' => $this->newVideoType,
            'duration_seconds' => $this->newVideoType === 'realtime' ? $this->newVideoDurationSeconds : null,
            'sort_order' => (CameraVideo::where('camera_id', $this->camera->id)->max('sort_order') ?? -1) + 1,
        ]);

        $this->closeAddVideoModal();
        session()->flash('status', 'Video geüpload.');
    }

    // ─── Edit Video Modal ─────────────────────────────────────

    public function openEditVideoModal(int $id): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($id);
        $this->editingVideoId = $id;
        $this->editVideoName = $video->filename;
        $this->editVideoType = $video->behaviour_type ?? 'loop';
        $this->showEditVideoModal = true;
    }

    public function saveEditVideo(): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($this->editingVideoId);
        $video->update([
            'filename' => trim($this->editVideoName),
            'behaviour_type' => $this->editVideoType,
            // When switching to loop, clear duration; keep existing duration when realtime
            'duration_seconds' => $this->editVideoType === 'loop' ? null : $video->duration_seconds,
        ]);

        $this->closeEditVideoModal();
        $this->dispatchScheduleUpdate();
        session()->flash('status', 'Video bijgewerkt.');
    }

    public function closeEditVideoModal(): void
    {
        $this->editingVideoId = null;
        $this->editVideoName = '';
        $this->editVideoType = 'loop';
        $this->showEditVideoModal = false;
    }

    // ─── Audio ───────────────────────────────────────────────

    public function uploadAudio(int $videoId): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($videoId);

        if ($video->audio_path) {
            Storage::disk('public')->delete($video->audio_path);
        }

        $path = $this->audioUploads[$videoId]->store("cameras/{$this->camera->id}/audio", 'public');
        $video->update(['audio_path' => $path]);

        unset($this->audioUploads[$videoId]);
        session()->flash('status', 'Audio geüpload.');
    }

    public function removeAudio(int $videoId): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($videoId);

        if ($video->audio_path) {
            Storage::disk('public')->delete($video->audio_path);
            $video->update(['audio_path' => null]);
        }

        session()->flash('status', 'Audio verwijderd.');
    }

    // ─── Background ─────────────────────────────────────────

    public function uploadBackground(): void
    {
        if ($this->camera->background_path) {
            Storage::disk('public')->delete($this->camera->background_path);
        }

        $path = $this->backgroundUpload->store(
            "cameras/{$this->camera->id}/backgrounds",
            'public'
        );

        $this->camera->update(['background_path' => $path]);

        $this->reset('backgroundUpload');
        session()->flash('status', 'Achtergrond geüpload.');
    }

    public function removeBackground(): void
    {
        if ($this->camera->background_path) {
            Storage::disk('public')->delete($this->camera->background_path);
            $this->camera->update(['background_path' => null]);
        }

        session()->flash('status', 'Achtergrond verwijderd.');
    }

    // ─── Static Effect ────────────────────────────────────────

    public function saveStaticSettings(): void
    {
        $this->camera->update([
            'static_enabled' => $this->staticEnabled,
            'static_intensity' => max(0, min(100, $this->staticIntensity)),
        ]);

        $this->camera->refresh();
        session()->flash('status', 'Camera effect instellingen opgeslagen.');
    }

    public function deleteVideo(int $id): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($id);

        if ($video->video_path) {
            Storage::disk('public')->delete($video->video_path);
        }
        if ($video->audio_path) {
            Storage::disk('public')->delete($video->audio_path);
        }

        $video->delete();
        $this->closeEditVideoModal();
        session()->flash('status', 'Video verwijderd.');
    }

    // ─── Default Videos ──────────────────────────────────────

    public function saveDefaults(): void
    {
        foreach ($this->defaultSelections as $slot => $videoId) {
            $videoId = $videoId ? (int) $videoId : null;

            for ($day = 0; $day < 7; $day++) {
                CameraDefaultBlock::updateOrCreate(
                    [
                        'camera_id' => $this->camera->id,
                        'day_of_week' => $day,
                        'time_slot' => $slot,
                    ],
                    [
                        'camera_video_id' => $videoId,
                    ]
                );
            }
        }

        session()->flash('status', 'Standaard video\'s opgeslagen.');
    }

    // ─── Scheduled Videos ────────────────────────────────────

    public function openScheduleCreate(int $dayOfWeek, string $startTime, string $endTime): void
    {
        $this->resetScheduleForm();
        $this->scheduleDayOfWeek = $dayOfWeek;
        $this->scheduleStartTime = $startTime;
        $this->scheduleEndTime = $endTime;
        $this->showScheduleModal = true;
    }

    public function openScheduleEdit(int $id): void
    {
        $scheduled = CameraScheduledVideo::where('camera_id', $this->camera->id)->findOrFail($id);

        $this->editingScheduleId = $scheduled->id;
        $this->scheduleVideoId = $scheduled->camera_video_id;
        $this->scheduleDayOfWeek = $scheduled->day_of_week;
        $this->scheduleStartTime = substr($scheduled->start_time, 0, 5);
        $this->scheduleEndTime = substr($scheduled->end_time, 0, 5);
        $this->showScheduleModal = true;
    }

    public function saveSchedule(): void
    {
        // For realtime videos, auto-calculate end time from duration
        $video = CameraVideo::find($this->scheduleVideoId);
        if ($video && $video->behaviour_type === 'realtime' && $video->duration_seconds) {
            $startMinutes = $this->timeStringToMinutes($this->scheduleStartTime);
            $endMinutes = min(1440, $startMinutes + (int) ceil($video->duration_seconds / 60));
            $this->scheduleEndTime = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);
        }

        $this->validate([
            'scheduleVideoId' => ['required', 'exists:camera_videos,id'],
            'scheduleDayOfWeek' => ['required', 'integer', 'between:0,6'],
            'scheduleStartTime' => ['required', 'date_format:H:i'],
            'scheduleEndTime' => ['required', 'date_format:H:i', 'after:scheduleStartTime'],
        ]);

        if ($this->hasOverlap($this->scheduleDayOfWeek, $this->scheduleStartTime, $this->scheduleEndTime, $this->editingScheduleId)) {
            session()->flash('status', 'Overlap met een ander gepland blok.');
            return;
        }

        $data = [
            'camera_id' => $this->camera->id,
            'camera_video_id' => $this->scheduleVideoId,
            'day_of_week' => $this->scheduleDayOfWeek,
            'start_time' => $this->scheduleStartTime,
            'end_time' => $this->scheduleEndTime,
        ];

        if ($this->editingScheduleId) {
            $scheduled = CameraScheduledVideo::where('camera_id', $this->camera->id)
                ->findOrFail($this->editingScheduleId);
            $scheduled->update($data);
            session()->flash('status', 'Gepland blok bijgewerkt.');
        } else {
            CameraScheduledVideo::create($data);
            session()->flash('status', 'Gepland blok aangemaakt.');
        }

        $this->closeScheduleModal();
        $this->dispatchScheduleUpdate();
    }

    public function updateScheduledPosition(int $id, int $dayOfWeek, string $startTime, string $endTime): void
    {
        $scheduled = CameraScheduledVideo::where('camera_id', $this->camera->id)->findOrFail($id);

        // For realtime videos, lock the duration (recalculate end from start + duration)
        if ($scheduled->video?->behaviour_type === 'realtime' && $scheduled->video?->duration_seconds) {
            $startMinutes = $this->timeStringToMinutes($startTime);
            $durationMinutes = (int) ceil($scheduled->video->duration_seconds / 60);
            $endMinutes = min(1440, $startMinutes + $durationMinutes);
            $endTime = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);
        }

        if ($this->hasOverlap($dayOfWeek, $startTime, $endTime, $id)) {
            $this->dispatchScheduleUpdate(); // Reset client to DB state
            return;
        }

        $scheduled->update([
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->dispatchScheduleUpdate();
    }

    public function deleteScheduledVideo(int $id): void
    {
        CameraScheduledVideo::where('camera_id', $this->camera->id)->findOrFail($id)->delete();
        $this->closeScheduleModal();
        $this->dispatchScheduleUpdate();
        session()->flash('status', 'Gepland blok verwijderd.');
    }

    public function createFromDrop(int $videoId, int $dayOfWeek, string $startTime, string $endTime): void
    {
        $video = CameraVideo::where('camera_id', $this->camera->id)->findOrFail($videoId);

        // For realtime videos, enforce duration-based end time
        if ($video->behaviour_type === 'realtime' && $video->duration_seconds) {
            $startMinutes = $this->timeStringToMinutes($startTime);
            $endMinutes = min(1440, $startMinutes + (int) ceil($video->duration_seconds / 60));
            $endTime = sprintf('%02d:%02d', intdiv($endMinutes, 60), $endMinutes % 60);
        }

        if ($this->hasOverlap($dayOfWeek, $startTime, $endTime)) {
            $this->dispatchScheduleUpdate(); // Reset client to DB state
            return;
        }

        CameraScheduledVideo::create([
            'camera_id' => $this->camera->id,
            'camera_video_id' => $videoId,
            'day_of_week' => $dayOfWeek,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        $this->dispatchScheduleUpdate();
    }

    protected function hasOverlap(int $dayOfWeek, string $startTime, string $endTime, ?int $excludeId = null): bool
    {
        return CameraScheduledVideo::where('camera_id', $this->camera->id)
            ->where('day_of_week', $dayOfWeek)
            ->where('start_time', '<', $endTime)
            ->where('end_time', '>', $startTime)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
            ->exists();
    }

    public function closeScheduleModal(): void
    {
        $this->resetScheduleForm();
        $this->showScheduleModal = false;
    }

    protected function dispatchScheduleUpdate(): void
    {
        $scheduled = $this->camera->scheduledVideos()->with('video')->get();

        $data = $scheduled->map(function ($item) {
            return [
                'id' => $item->id,
                'video_id' => $item->camera_video_id,
                'video_name' => $item->video?->filename,
                'behaviour_type' => $item->video?->behaviour_type ?? 'loop',
                'duration_seconds' => $item->video?->duration_seconds,
                'day_of_week' => $item->day_of_week,
                'start_time' => substr($item->start_time, 0, 5),
                'end_time' => substr($item->end_time, 0, 5),
            ];
        })->values()->toArray();

        $this->dispatch('schedule-updated', scheduled: $data);
    }

    protected function resetScheduleForm(): void
    {
        $this->reset(['editingScheduleId', 'scheduleVideoId', 'scheduleDayOfWeek', 'scheduleStartTime', 'scheduleEndTime']);
        $this->scheduleStartTime = '08:00';
        $this->scheduleEndTime = '09:00';
    }

    protected function timeStringToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return intval($parts[0]) * 60 + intval($parts[1] ?? 0);
    }

    // ─── Render ──────────────────────────────────────────────

    public function render()
    {
        $videos = $this->camera->videos()->orderBy('sort_order')->get();
        $defaults = $this->camera->defaultBlocks()->with('video')->get();
        $scheduled = $this->camera->scheduledVideos()->with('video')->get();

        $videosMeta = $videos->mapWithKeys(fn ($v) => [
            $v->id => [
                'id' => $v->id,
                'behaviour_type' => $v->behaviour_type ?? 'loop',
                'duration_seconds' => $v->duration_seconds,
            ]
        ])->toArray();

        // Build schedule data for Alpine.js
        $scheduleData = [
            'defaults' => $defaults->groupBy('day_of_week')->map(function ($dayBlocks) {
                return $dayBlocks->mapWithKeys(function ($block) {
                    return [$block->time_slot => [
                        'id' => $block->id,
                        'video_id' => $block->camera_video_id,
                        'video_name' => $block->video?->filename,
                        'video_url' => $block->video?->videoUrl(),
                    ]];
                });
            }),
            'scheduled' => $scheduled->map(function ($item) {
                return [
                    'id' => $item->id,
                    'video_id' => $item->camera_video_id,
                    'video_name' => $item->video?->filename,
                    'behaviour_type' => $item->video?->behaviour_type ?? 'loop',
                    'duration_seconds' => $item->video?->duration_seconds,
                    'day_of_week' => $item->day_of_week,
                    'start_time' => substr($item->start_time, 0, 5),
                    'end_time' => substr($item->end_time, 0, 5),
                ];
            })->values(),
            'slots' => CameraDefaultBlock::slots(),
            'days' => CameraDefaultBlock::DAY_LABELS,
        ];

        return view('livewire.admin.camera-planner', [
            'videos' => $videos,
            'defaults' => $defaults,
            'scheduled' => $scheduled,
            'scheduleData' => $scheduleData,
            'videosMeta' => $videosMeta,
        ])->layout('layouts.admin');
    }
}
