<?php

namespace App\Livewire\Admin;

use App\Models\Beacon;
use App\Models\Location;
use App\Models\LocationCategory;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Locations extends Component
{
    use WithPagination, WithFileUploads;

    // Tab
    public string $tab = 'locations';

    // Settings
    public bool $showMap = false;

    // Filters
    public string $filterCategory = '';
    public string $filterVisibility = '';
    public string $filterStatus = '';
    public string $search = '';

    // Create/Edit modal
    public bool $showModal = false;
    public ?int $editingId = null;
    public string $title = '';
    public string $description = '';
    public string $hiddenDescription = '';
    public string $latitude = '';
    public string $longitude = '';
    public string $address = '';
    public string $button1Label = '';
    public string $button1Url = '';
    public string $button2Label = '';
    public string $button2Url = '';
    public bool $isHidden = false;
    public bool $isActive = true;
    public int $sortOrder = 0;
    public $image = null;
    public ?string $existingImage = null;
    public array $selectedCategoryIds = [];
    public array $selectedBeaconIds = [];

    protected $queryString = [
        'tab' => ['except' => 'locations'],
        'filterCategory' => ['except' => ''],
        'filterVisibility' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount(): void
    {
        $settings = SiteSetting::first();
        $this->showMap = $settings?->show_map ?? false;
    }

    public function saveSettings(): void
    {
        SiteSetting::first()?->update([
            'show_map' => $this->showMap,
        ]);

        session()->flash('status', 'Settings updated.');
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'hiddenDescription' => ['nullable', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string', 'max:255'],
            'button1Label' => ['nullable', 'string', 'max:255'],
            'button1Url' => ['nullable', 'string', 'max:2048'],
            'button2Label' => ['nullable', 'string', 'max:255'],
            'button2Url' => ['nullable', 'string', 'max:2048'],
            'isHidden' => ['boolean'],
            'isActive' => ['boolean'],
            'sortOrder' => ['integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'selectedCategoryIds' => ['array'],
            'selectedBeaconIds' => ['array'],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterCategory(): void
    {
        $this->resetPage();
    }

    public function updatedFilterVisibility(): void
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
        $location = Location::with(['categories', 'beacons'])->findOrFail($id);
        $this->editingId = $location->id;
        $this->title = $location->title;
        $this->description = $location->description ?? '';
        $this->hiddenDescription = $location->hidden_description ?? '';
        $this->latitude = (string) $location->latitude;
        $this->longitude = (string) $location->longitude;
        $this->address = $location->address ?? '';
        $this->button1Label = $location->button_1_label ?? '';
        $this->button1Url = $location->button_1_url ?? '';
        $this->button2Label = $location->button_2_label ?? '';
        $this->button2Url = $location->button_2_url ?? '';
        $this->isHidden = !$location->is_visible;
        $this->isActive = $location->is_active;
        $this->sortOrder = $location->sort_order;
        $this->existingImage = $location->image_path;
        $this->selectedCategoryIds = $location->categories->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->selectedBeaconIds = $location->beacons->pluck('id')->map(fn ($id) => (string) $id)->toArray();
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
            $imagePath = $this->image->store('locations', 'public');
        }

        $data = [
            'title' => $this->title,
            'description' => $this->description ?: null,
            'hidden_description' => $this->hiddenDescription ?: null,
            'image_path' => $imagePath,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'address' => $this->address ?: null,
            'button_1_label' => $this->button1Label ?: null,
            'button_1_url' => $this->button1Url ?: null,
            'button_2_label' => $this->button2Label ?: null,
            'button_2_url' => $this->button2Url ?: null,
            'is_visible' => !$this->isHidden,
            'is_active' => $this->isActive,
            'sort_order' => $this->sortOrder,
        ];

        if ($this->editingId) {
            $location = Location::findOrFail($this->editingId);
            $location->update($data);
        } else {
            $location = Location::create($data);
        }

        $location->categories()->sync($this->selectedCategoryIds);
        $location->beacons()->sync($this->selectedBeaconIds);

        session()->flash('status', $this->editingId ? 'Location updated.' : 'Location created.');

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $location = Location::findOrFail($id);
        if ($location->image_path) {
            Storage::disk('public')->delete($location->image_path);
        }
        $location->categories()->detach();
        $location->beacons()->detach();
        $location->delete();
        session()->flash('status', 'Location deleted.');
    }

    public function closeModal(): void
    {
        $this->resetForm();
        $this->showModal = false;
    }

    protected function resetForm(): void
    {
        $this->reset([
            'editingId', 'title', 'description', 'hiddenDescription',
            'latitude', 'longitude', 'address',
            'button1Label', 'button1Url', 'button2Label', 'button2Url',
            'isHidden', 'isActive', 'sortOrder', 'image', 'existingImage',
            'selectedCategoryIds', 'selectedBeaconIds',
        ]);
        $this->isHidden = false;
        $this->isActive = true;
    }

    private function getFilteredQuery()
    {
        $query = Location::with('categories')->withCount('beacons');

        if ($this->filterCategory) {
            $query->whereHas('categories', fn ($q) => $q->where('location_categories.id', $this->filterCategory));
        }

        if ($this->filterVisibility === 'visible') {
            $query->where('is_visible', true);
        } elseif ($this->filterVisibility === 'hidden') {
            $query->where('is_visible', false);
        }

        if ($this->filterStatus === 'active') {
            $query->where('is_active', true);
        } elseif ($this->filterStatus === 'inactive') {
            $query->where('is_active', false);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('address', 'like', "%{$this->search}%");
            });
        }

        return $query->orderBy('sort_order')->orderByDesc('created_at');
    }

    public function render()
    {
        return view('livewire.admin.locations', [
            'locations' => $this->getFilteredQuery()->paginate(25),
            'categories' => LocationCategory::orderBy('name')->get(),
            'beacons' => Beacon::orderBy('title')->get(),
        ])->layout('layouts.admin');
    }
}
