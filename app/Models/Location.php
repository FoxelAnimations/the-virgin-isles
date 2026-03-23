<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Location extends Model
{
    protected $fillable = [
        'title',
        'description',
        'hidden_description',
        'image_path',
        'latitude',
        'longitude',
        'address',
        'button_1_label',
        'button_1_url',
        'button_2_label',
        'button_2_url',
        'is_visible',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_visible' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(LocationCategory::class, 'category_location');
    }

    public function beacons(): BelongsToMany
    {
        return $this->belongsToMany(Beacon::class, 'beacon_location');
    }

    public function revealedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'location_user')
            ->withPivot('revealed_at')
            ->withCasts(['revealed_at' => 'datetime']);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeHidden($query)
    {
        return $query->where('is_visible', false);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
