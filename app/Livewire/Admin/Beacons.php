<?php

namespace App\Livewire\Admin;

use App\Models\Beacon;
use App\Models\BeaconType;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Beacons extends Component
{
    use WithPagination, WithFileUploads;

    // Filters
    public string $filterType = '';
    public string $filterStatus = '';
    public string $search = '';

    // Create modal
    public bool $showCreateModal = false;
    public string $newTitle = '';
    public string $newDescription = '';
    public ?int $newTypeId = null;
    public int $newAmount = 0;
    public string $newRedirectUrl = '';
    public $newImage = null;

    // Bulk actions
    public array $selected = [];
    public bool $selectAll = false;

    protected $queryString = [
        'filterType' => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function updatedSelectAll(bool $value): void
    {
        if ($value) {
            $this->selected = $this->getFilteredBeaconsQuery()->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        } else {
            $this->selected = [];
        }
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
        $this->resetCreateForm();
        $this->showCreateModal = true;
    }

    public function closeCreate(): void
    {
        $this->showCreateModal = false;
        $this->resetCreateForm();
    }

    public function createBeacon(): void
    {
        $this->validate([
            'newTitle' => ['required', 'string', 'max:255'],
            'newDescription' => ['nullable', 'string'],
            'newTypeId' => ['nullable', 'exists:beacon_types,id'],
            'newAmount' => ['required', 'integer', 'min:0'],
            'newRedirectUrl' => ['nullable', 'string', 'max:2048'],
            'newImage' => ['nullable', 'image', 'max:4096'],
        ]);

        $imagePath = null;
        if ($this->newImage) {
            $imagePath = $this->newImage->store('beacons', 'public');
        }

        $beacon = Beacon::create([
            'title' => $this->newTitle,
            'description' => $this->newDescription ?: null,
            'type_id' => $this->newTypeId ?: null,
            'amount' => $this->newAmount,
            'redirect_url' => $this->newRedirectUrl ?: null,
            'image_path' => $imagePath,
        ]);

        $this->closeCreate();
        session()->flash('status', "Beacon \"{$beacon->title}\" created. GUID: {$beacon->guid}");
    }

    public function deleteBeacon(int $id): void
    {
        $beacon = Beacon::findOrFail($id);
        if ($beacon->image_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($beacon->image_path);
        }
        $beacon->scans()->delete();
        $beacon->delete();
        session()->flash('status', 'Beacon deleted.');
    }

    public function toggleOnline(int $id): void
    {
        $beacon = Beacon::findOrFail($id);
        $beacon->update(['is_online' => !$beacon->is_online]);
    }

    public function toggleOutOfAction(int $id): void
    {
        $beacon = Beacon::findOrFail($id);
        $beacon->update(['is_out_of_action' => !$beacon->is_out_of_action]);
    }

    // Bulk actions
    public function bulkSetOnline(): void
    {
        Beacon::whereIn('id', $this->selected)->update(['is_online' => true]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('status', 'Selected beacons set to online.');
    }

    public function bulkSetOffline(): void
    {
        Beacon::whereIn('id', $this->selected)->update(['is_online' => false]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('status', 'Selected beacons set to offline.');
    }

    public function bulkSetOutOfAction(): void
    {
        Beacon::whereIn('id', $this->selected)->update(['is_out_of_action' => true]);
        $this->selected = [];
        $this->selectAll = false;
        session()->flash('status', 'Selected beacons set to out of action.');
    }

    private function resetCreateForm(): void
    {
        $this->reset(['newTitle', 'newDescription', 'newTypeId', 'newAmount', 'newRedirectUrl', 'newImage']);
    }

    private function getFilteredBeaconsQuery()
    {
        $query = Beacon::with('type')->withCount('scans');

        if ($this->filterType) {
            $query->where('type_id', $this->filterType);
        }

        if ($this->filterStatus === 'online') {
            $query->where('is_online', true)->where('is_out_of_action', false);
        } elseif ($this->filterStatus === 'offline') {
            $query->where('is_online', false);
        } elseif ($this->filterStatus === 'out_of_action') {
            $query->where('is_out_of_action', true);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                  ->orWhere('guid', 'like', "%{$this->search}%");
            });
        }

        return $query->orderByDesc('created_at');
    }

    public function render()
    {
        return view('livewire.admin.beacons', [
            'beacons' => $this->getFilteredBeaconsQuery()->paginate(25),
            'types' => BeaconType::orderBy('name')->get(),
            'totalCount' => Beacon::count(),
        ])->layout('layouts.admin');
    }
}
