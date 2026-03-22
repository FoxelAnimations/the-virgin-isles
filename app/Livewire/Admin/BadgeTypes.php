<?php

namespace App\Livewire\Admin;

use App\Models\BadgeType;
use Livewire\Component;

class BadgeTypes extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:badge_types,name' . ($this->editingId ? ",{$this->editingId}" : '')],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $type = BadgeType::findOrFail($id);
        $this->editingId = $type->id;
        $this->name = $type->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $type = BadgeType::findOrFail($this->editingId);
            $type->update(['name' => $this->name]);
            session()->flash('status', 'Type updated.');
        } else {
            BadgeType::create(['name' => $this->name]);
            session()->flash('status', 'Type created.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        BadgeType::findOrFail($id)->delete();
        session()->flash('status', 'Type deleted.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset(['name', 'editingId']);
    }

    public function render()
    {
        return view('livewire.admin.badge-types', [
            'types' => BadgeType::withCount('badges')->orderBy('sort_order')->orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
