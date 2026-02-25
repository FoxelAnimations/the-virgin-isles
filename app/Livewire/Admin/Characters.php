<?php

namespace App\Livewire\Admin;

use App\Models\Character;
use App\Models\Job;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Characters extends Component
{
    // Job editing
    public ?int $editingJobId = null;
    public string $editingJobTitle = '';
    public string $editingJobDescription = '';

    public function updateCharacterOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Character::where('id', $id)->update(['sort_order' => $index]);
        }
    }

    public function delete(int $characterId): void
    {
        DB::transaction(function () use ($characterId) {
            $character = Character::findOrFail($characterId);
            $character->delete();
        });

        session()->flash('status', 'Character deleted successfully.');
    }

    public function editJob(int $id): void
    {
        $job = Job::findOrFail($id);
        $this->editingJobId = $job->id;
        $this->editingJobTitle = $job->title;
        $this->editingJobDescription = $job->description ?? '';
    }

    public function updateJob(): void
    {
        $this->validate([
            'editingJobTitle' => ['required', 'string', 'max:255', 'unique:character_jobs,title,' . $this->editingJobId],
            'editingJobDescription' => ['nullable', 'string'],
        ]);

        $job = Job::findOrFail($this->editingJobId);
        $job->update([
            'title' => $this->editingJobTitle,
            'description' => $this->editingJobDescription,
        ]);

        $this->cancelEditJob();
        session()->flash('status', 'Job updated successfully.');
    }

    public function cancelEditJob(): void
    {
        $this->reset(['editingJobId', 'editingJobTitle', 'editingJobDescription']);
    }

    public function deleteJob(int $id): void
    {
        $job = Job::findOrFail($id);
        $job->delete();

        session()->flash('status', 'Job deleted successfully.');
    }

    public function render()
    {
        return view('livewire.admin.characters', [
            'characters' => Character::with('job')->orderBy('sort_order')->get(),
            'jobs' => Job::withCount('characters')->orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
