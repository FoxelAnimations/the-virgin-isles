<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpisodeView extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'episode_id',
        'user_id',
        'hashed_ip',
        'user_agent',
        'device_type',
        'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'viewed_at' => 'datetime',
        ];
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDeviceTypeAttribute(): string
    {
        if ($this->attributes['device_type']) {
            return $this->attributes['device_type'];
        }

        return self::detectDeviceType($this->user_agent);
    }

    public static function detectDeviceType(?string $userAgent): string
    {
        if (!$userAgent) return 'desktop';
        $ua = strtolower($userAgent);
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) return 'tablet';
        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry/i', $ua)) return 'mobile';
        return 'desktop';
    }
}
