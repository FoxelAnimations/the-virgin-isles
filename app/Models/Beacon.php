<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Beacon extends Model
{
    protected $fillable = [
        'guid',
        'title',
        'description',
        'image_path',
        'amount',
        'latitude',
        'longitude',
        'type_id',
        'redirect_url',
        'is_online',
        'is_out_of_action',
        'is_collectible',
        'badge_image_path',
        'activation_date',
        'out_of_action_mode',
        'out_of_action_redirect_url',
        'out_of_action_message',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_online' => 'boolean',
            'is_out_of_action' => 'boolean',
            'is_collectible' => 'boolean',
            'activation_date' => 'date',
        ];
    }

    public const OUT_OF_ACTION_MODES = [
        'redirect' => 'Redirect (normal)',
        'redirectCustom' => 'Redirect (custom URL)',
        'showPage' => 'Show out-of-action page',
        'block' => 'Block (410 Gone)',
    ];

    protected static function booted(): void
    {
        static::creating(function (Beacon $beacon) {
            if (empty($beacon->guid)) {
                do {
                    $code = strtoupper(Str::random(10));
                } while (static::where('guid', $code)->exists());

                $beacon->guid = $code;
            }
        });
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(BeaconType::class, 'type_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BeaconImage::class)->orderBy('sort_order');
    }

    public function collectors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'beacon_user')->withPivot('collected_at');
    }

    public function scans(): HasMany
    {
        return $this->hasMany(BeaconScan::class, 'beacon_id')->orderByDesc('scanned_at');
    }

    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }

    public function getPublicUrlAttribute(): string
    {
        return url("/beacon/{$this->guid}");
    }

    public function resolveRedirectUrl(): string
    {
        if ($this->redirect_url) {
            return $this->redirect_url;
        }
        return '/';
    }

    public function scopeOnline($query)
    {
        return $query->where('is_online', true);
    }

    public function scopeOffline($query)
    {
        return $query->where('is_online', false);
    }

    public function scopeOutOfAction($query)
    {
        return $query->where('is_out_of_action', true);
    }

    public function badges(): BelongsToMany
    {
        return $this->belongsToMany(Badge::class, 'badge_beacon');
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, 'beacon_location');
    }

    public function isBeforeActivation(): bool
    {
        return $this->activation_date && $this->activation_date->isFuture();
    }
}
