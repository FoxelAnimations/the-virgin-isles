<?php

namespace App\Livewire\Admin;

use App\Models\LocationCategory;
use Livewire\Component;

class LocationCategories extends Component
{
    public bool $showModal = false;
    public string $name = '';
    public ?int $editingId = null;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:location_categories,name' . ($this->editingId ? ",{$this->editingId}" : '')],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $category = LocationCategory::findOrFail($id);
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId) {
            $category = LocationCategory::findOrFail($this->editingId);
            $category->update(['name' => $this->name]);
            session()->flash('status', 'Category updated.');
        } else {
            LocationCategory::create(['name' => $this->name]);
            session()->flash('status', 'Category created.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        LocationCategory::findOrFail($id)->delete();
        session()->flash('status', 'Category deleted.');
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
        return view('livewire.admin.location-categories', [
            'categories' => LocationCategory::withCount('locations')->orderBy('sort_order')->orderBy('name')->get(),
        ])->layout('layouts.admin');
    }
}
