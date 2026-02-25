<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\Episode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Episodes extends Component
{
    use WithFileUploads;

    public $title = '';
    public $description = '';
    public $source_type = 'upload';
    public $video;
    public $youtube_url = '';
    public $thumbnail;
    public $instagram_url = '';
    public $youtube_link = '';
    public $tiktok_url = '';
    public $twitter_url = '';
    public $selectedCharacters = [];

    public ?int $editingId = null;
    public bool $showModal = false;

    protected function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_type' => ['required', 'in:upload,youtube'],
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'youtube_link' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'selectedCharacters' => ['array'],
            'selectedCharacters.*' => ['exists:characters,id'],
        ];

        if ($this->source_type === 'upload') {
            $rules['video'] = $this->editingId
                ? ['nullable', 'mimes:mp4,webm,mov']
                : ['required', 'mimes:mp4,webm,mov'];
            $rules['youtube_url'] = ['nullable'];
        } else {
            $rules['video'] = ['nullable'];
            $rules['youtube_url'] = ['required', 'url', 'max:255'];
        }

        return $rules;
    }

    protected $messages = [
        'video.required' => 'Please upload a video file.',
        'youtube_url.required' => 'Please enter a YouTube URL.',
    ];

    public function updateEpisodeOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Episode::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $videoPath = null;
            if ($this->source_type === 'upload' && $this->video) {
                $videoPath = $this->video->store('episodes/videos', 'public');
            }

            $thumbnailPath = null;
            if ($this->thumbnail) {
                $thumbnailPath = $this->thumbnail->store('episodes/thumbnails', 'public');
            }

            $episode = Episode::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'source_type' => $validated['source_type'],
                'video_path' => $videoPath,
                'youtube_url' => $this->source_type === 'youtube' ? $validated['youtube_url'] : null,
                'thumbnail_path' => $thumbnailPath,
                'instagram_url' => $validated['instagram_url'] ?: null,
                'youtube_link' => $validated['youtube_link'] ?: null,
                'tiktok_url' => $validated['tiktok_url'] ?: null,
                'twitter_url' => $validated['twitter_url'] ?: null,
            ]);

            $episode->characters()->sync($this->selectedCharacters);
        });

        $this->resetForm();
        session()->flash('status', 'Episode created successfully.');
        $this->dispatch('episode-saved');
    }

    public function edit(int $id): void
    {
        $episode = Episode::findOrFail($id);

        $this->editingId = $episode->id;
        $this->title = $episode->title;
        $this->description = $episode->description ?? '';
        $this->source_type = $episode->source_type;
        $this->youtube_url = $episode->youtube_url ?? '';
        $this->instagram_url = $episode->instagram_url ?? '';
        $this->youtube_link = $episode->youtube_link ?? '';
        $this->tiktok_url = $episode->tiktok_url ?? '';
        $this->twitter_url = $episode->twitter_url ?? '';
        $this->selectedCharacters = $episode->characters->pluck('id')->toArray();
        $this->video = null;
        $this->thumbnail = null;
        $this->showModal = true;
    }

    public function update(): void
    {
        $validated = $this->validate();

        $episode = Episode::findOrFail($this->editingId);

        DB::transaction(function () use ($validated, $episode) {
            $data = [
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'source_type' => $validated['source_type'],
                'instagram_url' => $validated['instagram_url'] ?: null,
                'youtube_link' => $validated['youtube_link'] ?: null,
                'tiktok_url' => $validated['tiktok_url'] ?: null,
                'twitter_url' => $validated['twitter_url'] ?: null,
            ];

            if ($this->source_type === 'youtube') {
                $data['youtube_url'] = $validated['youtube_url'];
                if ($episode->video_path) {
                    Storage::disk('public')->delete($episode->video_path);
                    $data['video_path'] = null;
                }
            } elseif ($this->video) {
                if ($episode->video_path) {
                    Storage::disk('public')->delete($episode->video_path);
                }
                $data['video_path'] = $this->video->store('episodes/videos', 'public');
                $data['youtube_url'] = null;
            }

            if ($this->thumbnail) {
                if ($episode->thumbnail_path) {
                    Storage::disk('public')->delete($episode->thumbnail_path);
                }
                $data['thumbnail_path'] = $this->thumbnail->store('episodes/thumbnails', 'public');
            }

            $episode->update($data);
            $episode->characters()->sync($this->selectedCharacters);
        });

        $this->resetForm();
        session()->flash('status', 'Episode updated successfully.');
        $this->dispatch('episode-saved');
    }

    public function delete(int $id): void
    {
        $episode = Episode::findOrFail($id);

        if ($episode->video_path) {
            Storage::disk('public')->delete($episode->video_path);
        }
        if ($episode->thumbnail_path) {
            Storage::disk('public')->delete($episode->thumbnail_path);
        }

        $episode->characters()->detach();
        $episode->delete();

        session()->flash('status', 'Episode deleted successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->reset([
            'title', 'description', 'source_type', 'video', 'youtube_url',
            'thumbnail', 'instagram_url', 'youtube_link', 'tiktok_url',
            'twitter_url', 'selectedCharacters', 'editingId', 'showModal',
        ]);
        $this->source_type = 'upload';
    }

    public function render()
    {
        return view('livewire.admin.episodes', [
            'episodes' => Episode::with('characters')->orderBy('sort_order')->get(),
            'characters' => Character::orderBy('first_name')->get(),
        ])->layout('layouts.admin');
    }
}
