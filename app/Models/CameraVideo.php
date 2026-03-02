<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CameraVideo extends Model
{
    protected $fillable = [
        'camera_id',
        'filename',
        'video_path',
        'audio_path',
        'sort_order',
    ];

    public function camera(): BelongsTo
    {
        return $this->belongsTo(Camera::class);
    }

    public function videoUrl(): ?string
    {
        return $this->video_path ? Storage::url($this->video_path) : null;
    }

    public function audioUrl(): ?string
    {
        return $this->audio_path ? Storage::url($this->audio_path) : null;
    }
}
