<?php

namespace App\Livewire;

use App\Models\Location;
use App\Models\LocationCategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class MapPage extends Component
{
    public string $filterCategory = '';
    public string $filterStatus = '';
    public array $mapLocations = [];
    public array $revealedLocationIds = [];

    protected $queryString = [
        'filterCategory' => ['except' => ''],
        'filterStatus' => ['except' => ''],
    ];

    public function updatedFilterCategory(): void
    {
        $this->loadLocations();
    }

    public function updatedFilterStatus(): void
    {
        $this->loadLocations();
    }

    public function mount(): void
    {
        if (Auth::check()) {
            $this->revealedLocationIds = Auth::user()
                ->revealedLocations()->pluck('locations.id')->toArray();
        }
        $this->loadLocations();
    }

    public function loadLocations(): void
    {
        $query = Location::with('categories');

        if ($this->filterCategory) {
            $query->whereHas('categories', fn ($q) => $q->where('location_categories.id', $this->filterCategory));
        }

        if (Auth::check() && $this->filterStatus === 'revealed') {
            $query->whereIn('id', $this->revealedLocationIds);
        } elseif (Auth::check() && $this->filterStatus === 'unrevealed') {
            $query->whereNotIn('id', $this->revealedLocationIds);
        }

        $locations = $query->get();
        $revealed = $this->revealedLocationIds;

        $this->mapLocations = $locations->map(function ($location) use ($revealed) {
            $isRevealed = in_array($location->id, $revealed);
            $isHidden = !$location->is_visible;
            $showFull = !$isHidden || $isRevealed;

            return [
                'id' => $location->id,
                'lat' => (float) $location->latitude,
                'lng' => (float) $location->longitude,
                'title' => $showFull ? $location->title : 'Hidden Location',
                'description' => $showFull ? ($location->description ?? '') : ($location->hidden_description ?? ''),
                'image' => $showFull && $location->image_path ? Storage::url($location->image_path) : null,
                'address' => $showFull ? $location->address : null,
                'button_1_label' => $showFull ? $location->button_1_label : null,
                'button_1_url' => $showFull ? $location->button_1_url : null,
                'button_2_label' => $showFull ? $location->button_2_label : null,
                'button_2_url' => $showFull ? $location->button_2_url : null,
                'is_hidden' => $isHidden,
                'is_revealed' => $isRevealed,
                'is_scanned' => $isRevealed,
            ];
        })->toArray();
    }

    public function render()
    {
        return view('livewire.map-page', [
            'categories' => LocationCategory::orderBy('name')->get(),
            'isLoggedIn' => Auth::check(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
