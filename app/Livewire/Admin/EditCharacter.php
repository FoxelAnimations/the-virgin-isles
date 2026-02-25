<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditCharacter extends Component
{
    use WithFileUploads;

    public Character $character;

    public $first_name = '';
    public $last_name = '';
    public $nick_name = '';
    public $age;
    public $job_id;
    public $bio = '';
    public $profile_image;
    public $full_body_image;

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
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        DB::transaction(function () use ($validated) {
            $this->character->first_name = $validated['first_name'];
            $this->character->last_name = $validated['last_name'] ?? null;
            $this->character->nick_name = $validated['nick_name'] ?? null;
            $this->character->age = $validated['age'] ?? null;
            $this->character->job_id = $validated['job_id'] ?? null;
            $this->character->bio = $validated['bio'] ?? null;

            if ($this->profile_image) {
                $this->character->profile_image_path = $this->profile_image->store('characters/profile', 'public');
            }

            if ($this->full_body_image) {
                $this->character->full_body_image_path = $this->full_body_image->store('characters/full-body', 'public');
            }

            $this->character->save();
        });

        session()->flash('status', 'Character updated successfully.');
    }

    public function mount(Character $character): void
    {
        $this->character = $character;
        $this->first_name = $character->first_name;
        $this->last_name = $character->last_name;
        $this->nick_name = $character->nick_name;
        $this->age = $character->age;
        $this->job_id = $character->job_id;
        $this->bio = $character->bio;
    }

    public function render()
    {
        return view('livewire.admin.edit-character', [
            'jobs' => Job::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
