<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeaconImage extends Model
{
    protected $fillable = [
        'beacon_id',
        'image_path',
        'sort_order',
    ];

    public function beacon(): BelongsTo
    {
        return $this->belongsTo(Beacon::class);
    }
}
