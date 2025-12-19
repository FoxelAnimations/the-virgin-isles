<?php

namespace App\Livewire\Characters;

use App\Models\Character;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;

    public $first_name = '';
    public $last_name = '';
    public $nick_name = '';
    public $age;
    public $job_id;
    public $bio = '';
    public $profile_image;
    public $full_body_image;
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
            'profile_image' => ['nullable', 'image', 'max:2048'],
            'full_body_image' => ['nullable', 'image', 'max:2048'],
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

            $fullBodyImagePath = $this->full_body_image
                ? $this->full_body_image->store('characters/full-body', 'public')
                : null;

            $character = Character::create([
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'] ?? null,
                'nick_name' => $validated['nick_name'] ?? null,
                'age' => $validated['age'] ?? null,
                'job_id' => $validated['job_id'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'profile_image_path' => $profileImagePath,
                'full_body_image_path' => $fullBodyImagePath,
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
            'full_body_image',
            'images',
        ]);

        session()->flash('status', 'Character created successfully.');
    }

    public function render()
    {
        return view('livewire.characters.create', [
            'jobs' => Job::orderBy('title')->get(),
        ])->layout('layouts.app');
    }
}
