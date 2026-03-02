<?php

namespace App\Livewire\Admin;

use App\Models\Camera;
use App\Models\CameraDefaultBlock;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class Cameras extends Component
{
    public bool $showModal = false;
    public string $name = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $camera = Camera::create([
            'name' => $this->name,
            'sort_order' => (Camera::max('sort_order') ?? -1) + 1,
        ]);

        // Seed 28 default blocks (7 days × 4 slots)
        $slots = array_keys(CameraDefaultBlock::slots());
        for ($day = 0; $day < 7; $day++) {
            foreach ($slots as $slot) {
                CameraDefaultBlock::create([
                    'camera_id' => $camera->id,
                    'camera_video_id' => null,
                    'day_of_week' => $day,
                    'time_slot' => $slot,
                ]);
            }
        }

        $this->resetForm();
        $this->showModal = false;
        session()->flash('status', 'Camera aangemaakt.');
    }

    public function delete(int $id): void
    {
        $camera = Camera::findOrFail($id);

        // Clean up background file
        if ($camera->background_path) {
            Storage::disk('public')->delete($camera->background_path);
        }

        // Clean up video and audio files from storage
        foreach ($camera->videos as $video) {
            if ($video->video_path) {
                Storage::disk('public')->delete($video->video_path);
            }
            if ($video->audio_path) {
                Storage::disk('public')->delete($video->audio_path);
            }
        }

        $camera->delete();
        session()->flash('status', 'Camera verwijderd.');
    }

    public function toggleOffline(int $id): void
    {
        $camera = Camera::findOrFail($id);
        $camera->update(['is_offline' => !$camera->is_offline]);
    }

    public function toggleHidden(int $id): void
    {
        $camera = Camera::findOrFail($id);
        $camera->update(['is_hidden' => !$camera->is_hidden]);
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Camera::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset(['name']);
    }

    public function render()
    {
        return view('livewire.admin.cameras', [
            'cameras' => Camera::orderBy('sort_order')->get(),
        ])->layout('layouts.admin');
    }
}
