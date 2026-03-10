<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Camera extends Model
{
    protected $fillable = [
        'name',
        'description',
        'is_offline',
        'is_hidden',
        'sort_order',
        'background_path',
        'static_enabled',
        'static_intensity',
        'rain_volume',
        'wind_volume',
    ];

    protected function casts(): array
    {
        return [
            'is_offline' => 'boolean',
            'is_hidden' => 'boolean',
            'sort_order' => 'integer',
            'static_enabled' => 'boolean',
            'static_intensity' => 'integer',
            'rain_volume' => 'integer',
            'wind_volume' => 'integer',
        ];
    }

    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false)->orderBy('sort_order');
    }

    public function scopeOnline($query)
    {
        return $query->where('is_offline', false);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(CameraVideo::class)->orderBy('sort_order');
    }

    public function defaultBlocks(): HasMany
    {
        return $this->hasMany(CameraDefaultBlock::class);
    }

    public function scheduledVideos(): HasMany
    {
        return $this->hasMany(CameraScheduledVideo::class);
    }

    public function defaultSounds(): HasMany
    {
        return $this->hasMany(CameraDefaultSound::class);
    }

    public function backgroundUrl(): ?string
    {
        return $this->background_path ? Storage::url($this->background_path) : null;
    }

    public function backgroundIsVideo(): bool
    {
        if (!$this->background_path) {
            return false;
        }

        return str_ends_with(strtolower($this->background_path), '.webm');
    }
}
