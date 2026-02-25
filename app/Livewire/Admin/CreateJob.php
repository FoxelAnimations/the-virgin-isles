<?php

namespace App\Livewire\Admin;

use App\Models\Job;
use Livewire\Component;

class CreateJob extends Component
{
    public $title = '';
    public $description = '';

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255', 'unique:character_jobs,title'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        Job::create($validated);

        $this->reset(['title', 'description']);

        session()->flash('status', 'Job created successfully.');
    }

    public function render()
    {
        return view('livewire.admin.create-job')->layout('layouts.admin');
    }
}
