<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BeaconType extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    protected static function booted(): void
    {
        static::creating(function (BeaconType $type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name);
            }
        });

        static::updating(function (BeaconType $type) {
            if ($type->isDirty('name')) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function beacons(): HasMany
    {
        return $this->hasMany(Beacon::class, 'type_id');
    }
}
