<?php

namespace App\Livewire\Admin;

use App\Models\Badge;
use App\Models\Beacon;
use App\Models\BeaconImage;
use App\Models\BeaconScan;
use App\Models\BeaconType;
use App\Models\Location;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class BeaconDetail extends Component
{
    use WithFileUploads, WithPagination;

    public Beacon $beacon;

    // Active tab
    public string $tab = 'details';

    // Editable fields
    public string $title = '';
    public string $description = '';
    public ?int $typeId = null;
    public int $amount = 0;
    public string $latitude = '';
    public string $longitude = '';
    public string $redirectUrl = '';
    public bool $isOnline = true;
    public bool $isOutOfAction = false;
    public ?string $activationDate = null;
    public bool $isCollectible = false;
    public $badgeImage = null;
    public string $outOfActionMode = 'showPage';
    public string $outOfActionRedirectUrl = '';
    public string $outOfActionMessage = '';
    public $image = null;
    public $newImages = [];
    public array $selectedBadgeIds = [];
    public array $selectedLocationIds = [];

    public function mount(Beacon $beacon): void
    {
        $this->beacon = $beacon;
        $this->loadFields();
    }

    private function loadFields(): void
    {
        $this->beacon->load(['badges', 'locations']);
        $this->selectedBadgeIds = $this->beacon->badges->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->selectedLocationIds = $this->beacon->locations->pluck('id')->map(fn ($id) => (string) $id)->toArray();
        $this->title = $this->beacon->title;
        $this->description = $this->beacon->description ?? '';
        $this->typeId = $this->beacon->type_id;
        $this->amount = $this->beacon->amount;
        $this->latitude = $this->beacon->latitude ?? '';
        $this->longitude = $this->beacon->longitude ?? '';
        $this->redirectUrl = $this->beacon->redirect_url ?? '';
        $this->isOnline = $this->beacon->is_online;
        $this->isOutOfAction = $this->beacon->is_out_of_action;
        $this->activationDate = $this->beacon->activation_date?->format('Y-m-d');
        $this->isCollectible = $this->beacon->is_collectible;
        $this->outOfActionMode = $this->beacon->out_of_action_mode ?? 'showPage';
        $this->outOfActionRedirectUrl = $this->beacon->out_of_action_redirect_url ?? '';
        $this->outOfActionMessage = $this->beacon->out_of_action_message ?? '';
    }

    public function save(): void
    {
        $this->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'typeId' => ['nullable', 'exists:beacon_types,id'],
            'amount' => ['required', 'integer', 'min:0'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'redirectUrl' => ['nullable', 'string', 'max:2048'],
            'isOnline' => ['boolean'],
            'isOutOfAction' => ['boolean'],
            'activationDate' => ['nullable', 'date'],
            'isCollectible' => ['boolean'],
            'badgeImage' => ['nullable', 'image', 'max:4096'],
            'outOfActionMode' => ['required', 'in:redirect,redirectCustom,showPage,block'],
            'outOfActionRedirectUrl' => ['nullable', 'string', 'max:2048'],
            'outOfActionMessage' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:4096'],
            'newImages' => ['nullable', 'array', 'max:10'],
            'newImages.*' => ['image', 'max:4096'],
        ]);

        if ($this->image) {
            if ($this->beacon->image_path) {
                Storage::disk('public')->delete($this->beacon->image_path);
            }
            $this->beacon->image_path = $this->image->store('beacons', 'public');
        }

        $badgeImagePath = $this->beacon->badge_image_path;
        if ($this->badgeImage) {
            if ($badgeImagePath) {
                Storage::disk('public')->delete($badgeImagePath);
            }
            $badgeImagePath = $this->badgeImage->store('beacons/badges', 'public');
        }

        $this->beacon->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'type_id' => $this->typeId ?: null,
            'amount' => $this->amount,
            'latitude' => $this->latitude !== '' ? $this->latitude : null,
            'longitude' => $this->longitude !== '' ? $this->longitude : null,
            'redirect_url' => $this->redirectUrl ?: null,
            'is_online' => $this->isOnline,
            'is_out_of_action' => $this->isOutOfAction,
            'activation_date' => $this->activationDate ?: null,
            'is_collectible' => $this->isCollectible,
            'badge_image_path' => $badgeImagePath,
            'out_of_action_mode' => $this->outOfActionMode,
            'out_of_action_redirect_url' => $this->outOfActionRedirectUrl ?: null,
            'out_of_action_message' => $this->outOfActionMessage ?: null,
            'image_path' => $this->beacon->image_path,
        ]);

        if (!empty($this->newImages)) {
            $maxSort = $this->beacon->images()->max('sort_order') ?? -1;
            foreach ($this->newImages as $newImage) {
                $path = $newImage->store('beacons', 'public');
                $this->beacon->images()->create([
                    'image_path' => $path,
                    'sort_order' => ++$maxSort,
                ]);
            }
        }

        $this->beacon->badges()->sync($this->selectedBadgeIds);
        $this->beacon->locations()->sync($this->selectedLocationIds);

        $this->beacon->refresh();
        $this->image = null;
        $this->badgeImage = null;
        $this->newImages = [];
        session()->flash('status', 'Beacon updated.');
    }

    public function removeImage(): void
    {
        if ($this->beacon->image_path) {
            Storage::disk('public')->delete($this->beacon->image_path);
            $this->beacon->update(['image_path' => null]);
            $this->beacon->refresh();
        }
    }

    public function removeBadgeImage(): void
    {
        if ($this->beacon->badge_image_path) {
            Storage::disk('public')->delete($this->beacon->badge_image_path);
            $this->beacon->update(['badge_image_path' => null]);
            $this->beacon->refresh();
        }
    }

    public function removeBeaconImage(int $imageId): void
    {
        $image = BeaconImage::where('id', $imageId)->where('beacon_id', $this->beacon->id)->first();
        if ($image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
            $this->beacon->refresh();
        }
    }

    public function deleteScan(int $scanId): void
    {
        BeaconScan::where('id', $scanId)->where('beacon_id', $this->beacon->id)->delete();
        session()->flash('status', 'Scan log entry deleted.');
    }

    public function clearScans(): void
    {
        $this->beacon->scans()->delete();
        session()->flash('status', 'All scan logs for this beacon cleared.');
    }

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
        $this->resetPage();
    }

    public function render()
    {
        $data = [
            'types' => BeaconType::orderBy('name')->get(),
            'outOfActionModes' => Beacon::OUT_OF_ACTION_MODES,
            'allBadges' => $this->tab === 'details' ? Badge::orderBy('title')->get() : collect(),
            'allLocations' => $this->tab === 'details' ? Location::orderBy('title')->get() : collect(),
        ];

        if ($this->tab === 'scans') {
            $data['scans'] = $this->beacon->scans()->paginate(25);
        }

        if ($this->tab === 'qr') {
            $data['qrSvg'] = $this->generateQrSvg();
        }

        return view('livewire.admin.beacon-detail', $data)->layout('layouts.admin');
    }

    private function generateQrSvg(): string
    {
        $url = $this->beacon->public_url;
        // Simple QR code generation using a pure PHP approach
        // We'll use a minimal SVG-based QR encoding
        return $this->buildQrSvg($url);
    }

    /**
     * Generate a QR code SVG string for the given data.
     * Uses a simple encoding approach — for production, consider chillerlan/php-qrcode.
     */
    private function buildQrSvg(string $data): string
    {
        // Use the endroid/qr-code or chillerlan package if installed,
        // otherwise fall back to a Google Charts-style embedded approach.
        // For now, generate an inline SVG placeholder that the blade can use
        // with a JS-based QR library for client-side rendering.
        return $data; // The blade template will use a JS QR library with this URL
    }
}
