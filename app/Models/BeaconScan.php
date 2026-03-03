<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BeaconScan extends Model
{
    protected $fillable = [
        'scanned_at',
        'guid',
        'beacon_id',
        'is_known',
        'hashed_ip',
        'user_agent',
        'referrer',
        'requested_url',
        'redirect_url_used',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'utm_term',
        'utm_content',
        'rate_limited',
        'meta_json',
    ];

    protected function casts(): array
    {
        return [
            'scanned_at' => 'datetime',
            'is_known' => 'boolean',
            'rate_limited' => 'boolean',
            'meta_json' => 'array',
        ];
    }

    public function beacon(): BelongsTo
    {
        return $this->belongsTo(Beacon::class, 'beacon_id');
    }

    public function getShortHashedIpAttribute(): string
    {
        return substr($this->hashed_ip, 0, 12) . '…';
    }

    public function getShortUserAgentAttribute(): string
    {
        if (!$this->user_agent) {
            return '—';
        }
        return Str::limit($this->user_agent, 60);
    }

    public function getDeviceTypeAttribute(): string
    {
        $ua = strtolower($this->user_agent ?? '');
        if (preg_match('/tablet|ipad|playbook|silk/i', $ua)) {
            return 'tablet';
        }
        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry/i', $ua)) {
            return 'mobile';
        }
        return 'desktop';
    }

    public function scopeKnown($query)
    {
        return $query->where('is_known', true);
    }

    public function scopeUnknown($query)
    {
        return $query->where('is_known', false);
    }
}
