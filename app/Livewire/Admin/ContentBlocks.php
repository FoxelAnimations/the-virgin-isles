<?php

namespace App\Livewire\Admin;

use App\Models\ContentBlock;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ContentBlocks extends Component
{
    use WithFileUploads;

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $pre_title = '';
    public string $title = '';
    public string $text = '';
    public ?string $media_type = null;
    public $image_upload = null;
    public $video_upload = null;
    public string $youtube_url = '';
    public string $button_label = '';
    public string $button_url = '';
    public bool $button_new_tab = false;
    public bool $is_active = true;
    public string $placement = 'below_episodes';

    public ?string $existing_image_path = null;
    public ?string $existing_video_path = null;

    protected function rules(): array
    {
        return [
            'pre_title' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'text' => ['nullable', 'string', 'max:15000'],
            'media_type' => ['nullable', 'string', 'in:image,video,youtube'],
            'image_upload' => ['nullable', 'image', 'max:4096'],
            'video_upload' => ['nullable', 'mimes:mp4,webm,mov', 'max:51200'],
            'youtube_url' => ['nullable', 'url', 'max:500'],
            'button_label' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'url', 'max:500'],
            'button_new_tab' => ['boolean'],
            'is_active' => ['boolean'],
            'placement' => ['required', 'string', 'in:above_episodes,below_episodes'],
        ];
    }

    public function openCreateForm(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function openEditForm(int $id): void
    {
        $block = ContentBlock::findOrFail($id);
        $this->editingId = $block->id;
        $this->pre_title = $block->pre_title ?? '';
        $this->title = $block->title ?? '';
        $this->text = $block->text ?? '';
        $this->media_type = $block->media_type;
        $this->youtube_url = $block->youtube_url ?? '';
        $this->button_label = $block->button_label ?? '';
        $this->button_url = $block->button_url ?? '';
        $this->button_new_tab = $block->button_new_tab;
        $this->is_active = $block->is_active;
        $this->placement = $block->placement ?? 'below_episodes';
        $this->existing_image_path = $block->image_path;
        $this->existing_video_path = $block->video_path;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        // Sanitize HTML: only allow safe tags
        $cleanText = $this->text ? strip_tags($this->text, '<p><br><strong><em><u><h2><h3><ul><ol><li>') : null;
        // Remove empty editor content
        if ($cleanText && trim(strip_tags($cleanText)) === '') {
            $cleanText = null;
        }

        $data = [
            'pre_title' => $this->pre_title ?: null,
            'title' => $this->title ?: null,
            'text' => $cleanText,
            'media_type' => $this->media_type ?: null,
            'youtube_url' => $this->media_type === 'youtube' ? ($this->youtube_url ?: null) : null,
            'button_label' => $this->button_label ?: null,
            'button_url' => $this->button_url ?: null,
            'button_new_tab' => $this->button_new_tab,
            'is_active' => $this->is_active,
            'placement' => $this->placement,
        ];

        // Handle image upload
        if ($this->media_type === 'image') {
            if ($this->image_upload) {
                // Delete old image if replacing
                if ($this->existing_image_path) {
                    Storage::disk('public')->delete($this->existing_image_path);
                }
                $data['image_path'] = $this->image_upload->store('content-blocks', 'public');
            } else {
                $data['image_path'] = $this->existing_image_path;
            }
            $data['video_path'] = null;
        } elseif ($this->media_type === 'video') {
            if ($this->video_upload) {
                // Delete old video if replacing
                if ($this->existing_video_path) {
                    Storage::disk('public')->delete($this->existing_video_path);
                }
                $data['video_path'] = $this->video_upload->store('content-blocks/videos', 'public');
            } else {
                $data['video_path'] = $this->existing_video_path;
            }
            $data['image_path'] = null;
        } else {
            $data['image_path'] = null;
            $data['video_path'] = null;
        }

        if ($this->editingId) {
            $block = ContentBlock::findOrFail($this->editingId);
            // Clean up old files when switching media type
            if ($block->image_path && $data['image_path'] !== $block->image_path) {
                Storage::disk('public')->delete($block->image_path);
            }
            if ($block->video_path && $data['video_path'] !== $block->video_path) {
                Storage::disk('public')->delete($block->video_path);
            }
            $block->update($data);
            session()->flash('status', 'Content block bijgewerkt.');
        } else {
            $data['sort_order'] = (ContentBlock::max('sort_order') ?? -1) + 1;
            ContentBlock::create($data);
            session()->flash('status', 'Content block aangemaakt.');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function toggleActive(int $id): void
    {
        $block = ContentBlock::findOrFail($id);
        $block->update(['is_active' => !$block->is_active]);
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            ContentBlock::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function delete(int $id): void
    {
        $block = ContentBlock::findOrFail($id);

        if ($block->image_path) {
            Storage::disk('public')->delete($block->image_path);
        }
        if ($block->video_path) {
            Storage::disk('public')->delete($block->video_path);
        }

        $block->delete();
        session()->flash('status', 'Content block verwijderd.');
    }

    public function cancelForm(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'pre_title', 'title', 'text', 'media_type',
            'image_upload', 'video_upload', 'youtube_url',
            'button_label', 'button_url', 'button_new_tab', 'placement',
            'existing_image_path', 'existing_video_path',
        ]);
        $this->is_active = true;
        $this->placement = 'below_episodes';
    }

    public function render()
    {
        return view('livewire.admin.content-blocks', [
            'blocks' => ContentBlock::orderBy('sort_order')->get(),
        ])->layout('layouts.admin');
    }
}
