<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatBlock extends Model
{
    protected $fillable = [
        'ip_address',
        'visitor_uuid',
        'reason',
        'blocked_by',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
        ];
    }

    public function blockedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'blocked_by');
    }

    public function isActive(): bool
    {
        return $this->expires_at === null || $this->expires_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public static function isBlocked(?string $ip, ?string $uuid = null): bool
    {
        if (!$ip && !$uuid) {
            return false;
        }

        return static::active()
            ->where(function ($q) use ($ip, $uuid) {
                if ($ip) {
                    $q->where('ip_address', $ip);
                }
                if ($uuid) {
                    $q->orWhere('visitor_uuid', $uuid);
                }
            })
            ->exists();
    }
}
