<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateCharacter extends Component
{
    use WithFileUploads;

    public $first_name = '';
    public $last_name = '';
    public $nick_name = '';
    public $age;
    public $job_id;
    public $bio = '';
    public $personality = '';
    public $speaking_style = '';
    public $backstory = '';
    public $example_phrases = '';
    public $chat_instructions = '';
    public bool $chat_enabled = false;
    public string $chat_mode = 'ai';
    public bool $chat_online = true;
    public $profile_image;
    public $profile_image_hover;
    public $full_body_image;
    public $full_body_image_hover;
    public $profile_photo;
    public $profile_photo_hover;
    public $chat_image;
    public $background_image;
    public $images = [];

    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'nick_name' => ['nullable', 'string', 'max:255'],
            'age' => ['nullable', 'integer', 'between:0,255'],
            'job_id' => ['nullable', 'exists:character_jobs,id'],
            'bio' => ['nullable', 'string'],
            'personality' => ['nullable', 'string', 'max:2000'],
            'speaking_style' => ['nullable', 'string', 'max:2000'],
            'backstory' => ['nullable', 'string', 'max:2000'],
            'example_phrases' => ['nullable', 'string', 'max:2000'],
            'chat_instructions' => ['nullable', 'string', 'max:2000'],
            'chat_enabled' => ['boolean'],
            'chat_mode' => ['required', 'string', 'in:ai,manual'],
            'chat_online' => ['boolean'],
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'profile_image_hover' => ['nullable', 'image', 'max:2048'],
            'full_body_image' => ['nullable', 'image', 'max:2048'],
            'full_body_image_hover' => ['nullable', 'image', 'max:2048'],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
            'profile_photo_hover' => ['nullable', 'image', 'max:2048'],
            'chat_image' => ['nullable', 'image', 'max:2048'],
            'background_image' => ['nullable', 'image', 'max:4096'],
            'images.*' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $profileImagePath = $this->profile_image
                ? $this->profile_image->store('characters/profile', 'public')
                : null;

            $profileImageHoverPath = $this->profile_image_hover
                ? $this->profile_image_hover->store('characters/profile', 'public')
                : null;

            $fullBodyImagePath = $this->full_body_image
                ? $this->full_body_image->store('characters/full-body', 'public')
                : null;

            $fullBodyImageHoverPath = $this->full_body_image_hover
                ? $this->full_body_image_hover->store('characters/full-body', 'public')
                : null;

            $profilePhotoPath = $this->profile_photo
                ? $this->profile_photo->store('characters/profile-photo', 'public')
                : null;

            $profilePhotoHoverPath = $this->profile_photo_hover
                ? $this->profile_photo_hover->store('characters/profile-photo', 'public')
                : null;

            $chatImagePath = $this->chat_image
                ? $this->chat_image->store('characters/chat', 'public')
                : null;

            $backgroundImagePath = $this->background_image
                ? $this->background_image->store('characters/background', 'public')
                : null;

            $character = Character::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'nick_name' => $validated['nick_name'] ?? null,
                'age' => $validated['age'] ?? null,
                'job_id' => $validated['job_id'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'personality' => $validated['personality'] ?? null,
                'speaking_style' => $validated['speaking_style'] ?? null,
                'backstory' => $validated['backstory'] ?? null,
                'example_phrases' => $validated['example_phrases'] ?? null,
                'chat_instructions' => $validated['chat_instructions'] ?? null,
                'chat_enabled' => $validated['chat_enabled'],
                'chat_mode' => $validated['chat_mode'],
                'chat_online' => $validated['chat_online'],
                'profile_image_path' => $profileImagePath,
                'profile_image_hover_path' => $profileImageHoverPath,
                'full_body_image_path' => $fullBodyImagePath,
                'full_body_image_hover_path' => $fullBodyImageHoverPath,
                'profile_photo_path' => $profilePhotoPath,
                'profile_photo_hover_path' => $profilePhotoHoverPath,
                'chat_image_path' => $chatImagePath,
                'background_image_path' => $backgroundImagePath,
            ]);

            foreach ($this->images as $index => $image) {
                if (!$image) {
                    continue;
                }

                $path = $image->store('characters', 'public');

                $character->characterImages()->create([
                    'image_path' => $path,
                    'sort_order' => $index,
                ]);
            }
        });

        $this->reset([
            'first_name',
            'last_name',
            'nick_name',
            'age',
            'job_id',
            'bio',
            'profile_image',
            'profile_image_hover',
            'full_body_image',
            'full_body_image_hover',
            'profile_photo',
            'profile_photo_hover',
            'chat_image',
            'background_image',
            'images',
        ]);

        session()->flash('status', 'Character created successfully.');
    }

    public function render()
    {
        return view('livewire.admin.create-character', [
            'jobs' => Job::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
