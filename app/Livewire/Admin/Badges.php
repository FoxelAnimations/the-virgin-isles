<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\BadgeType;
use App\Models\Beacon;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Badges extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public string $filterType = '';
    public string $filterStatus = '';
    public string $search = '';

    // Create/Edit modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $description = '';
    public string $popupTextFirst = '';
    public string $popupTextRepeat = '';
    public ?int $typeId = null;
    public bool $isActive = true;
    public int $sortOrder = 0;
    public $image = null;
    public ?string $existingImage = null;
    public array $selectedBeaconIds = [];

    protected $queryString = [
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'popupTextFirst' => ['nullable', 'string'],
            'popupTextRepeat' => ['nullable', 'string'],
            'typeId' => ['nullable', 'exists:badge_types,id'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
            'image' => $this->editingId ? ['nullable', 'image', 'max:4096'] : ['required', 'image', 'max:4096'],
            'selectedBeaconIds' => ['array'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $badge = Badge::with('beacons')->findOrFail($id);
        $this->editingId = $badge->id;
        $this->title = $badge->title;
        $this->description = $badge->description ?? '';
        $this->popupTextFirst = $badge->popup_text_first ?? '';
        $this->popupTextRepeat = $badge->popup_text_repeat ?? '';
        $this->typeId = $badge->type_id;
        $this->isActive = $badge->is_active;
        $this->sortOrder = $badge->sort_order;
        $this->existingImage = $badge->image_path;
        $this->selectedBeaconIds = $badge->beacons->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $imagePath = $this->existingImage;
        if ($this->image) {
            if ($this->existingImage) {
                Storage::disk('public')->delete($this->existingImage);
            }
            $imagePath = $this->image->store('badges', 'public');
        }

        $data = [
            'title' => $this->title,
            'image_path' => $imagePath,
            'description' => $this->description ?: null,
            'popup_text_first' => $this->popupTextFirst ?: null,
            'popup_text_repeat' => $this->popupTextRepeat ?: null,
            'type_id' => $this->typeId ?: null,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId) {
            $badge = Badge::findOrFail($this->editingId);
            $badge->update($data);
            $badge->beacons()->sync($this->selectedBeaconIds);
            session()->flash('status', 'Badge updated.');
        } else {
            $badge = Badge::create($data);
            $badge->beacons()->sync($this->selectedBeaconIds);
            session()->flash('status', 'Badge created.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $badge = Badge::findOrFail($id);
        $badge->delete(); // Soft delete
        session()->flash('status', 'Badge deleted.');
    }

    public function restore(int $id): void
    {
        $badge = Badge::withTrashed()->findOrFail($id);
        $badge->restore();
        session()->flash('status', 'Badge restored.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset(['editingId', 'title', 'description', 'popupTextFirst', 'popupTextRepeat', 'typeId', 'isActive', 'sortOrder', 'image', 'existingImage', 'selectedBeaconIds']);
        $this->isActive = true;
    }

    private function getFilteredQuery()
    {
        $query = Badge::with('type')->withCount(['beacons', 'users']);

        if ($this->filterType) {
            $query->where('type_id', $this->filterType);
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        } elseif ($this->filterStatus === 'deleted') {
            $query->onlyTrashed();
        }

        if ($this->search) {
            $query->where('title', 'like', "%{$this->search}%");
        }

        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function render()
    {
        return view('livewire.admin.badges', [
            'badges' => $this->getFilteredQuery()->paginate(25),
            'types' => BadgeType::orderBy('name')->get(),
            'beacons' => Beacon::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
