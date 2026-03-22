<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CameraLiveMessage extends Model
{
    protected $fillable = [
        'camera_id',
        'user_id',
        'body',
    ];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
