<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class BadgeType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (BadgeType $type) {
            if (empty($type->slug)) {
                $type->slug = Str::slug($type->name);
            }
        });

        static::updating(function (BadgeType $type) {
            if ($type->isDirty('name')) {
                $type->slug = Str::slug($type->name);
            }
        });
    }

    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class, 'type_id');
    }
}
