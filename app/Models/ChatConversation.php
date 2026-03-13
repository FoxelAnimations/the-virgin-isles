<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChatConversation extends Model
{
    protected $fillable = [
        'visitor_uuid',
        'visitor_name',
        'visitor_ip',
        'user_agent',
        'character_id',
        'status',
        'unread_count',
        'last_message_at',
        'blocked_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'last_message_at' => 'datetime',
            'blocked_attempt_at' => 'datetime',
            'unread_count' => 'integer',
        ];
    }

    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ChatMessage::class)->orderBy('created_at');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeWithUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    public function getParsedUserAgentAttribute(): ?array
    {
        if (!$this->user_agent) {
            return null;
        }

        $ua = $this->user_agent;

        // Detect OS
        $os = 'unknown';
        $osVersion = '';
        if (preg_match('/Windows NT ([\d.]+)/', $ua, $m)) {
            $os = 'windows';
            $osVersion = match (true) {
                str_starts_with($m[1], '10') => '10/11',
                str_starts_with($m[1], '6.3') => '8.1',
                str_starts_with($m[1], '6.2') => '8',
                str_starts_with($m[1], '6.1') => '7',
                default => $m[1],
            };
        } elseif (preg_match('/Mac OS X ([\d_]+)/', $ua, $m)) {
            $os = 'macos';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/Android ([\d.]+)/', $ua, $m)) {
            $os = 'android';
            $osVersion = $m[1];
        } elseif (preg_match('/iPhone OS ([\d_]+)/', $ua, $m)) {
            $os = 'ios';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (preg_match('/iPad.*OS ([\d_]+)/', $ua, $m)) {
            $os = 'ios';
            $osVersion = str_replace('_', '.', $m[1]);
        } elseif (str_contains($ua, 'Linux')) {
            $os = 'linux';
        } elseif (str_contains($ua, 'CrOS')) {
            $os = 'chromeos';
        }

        // Detect browser
        $browser = 'unknown';
        $browserVersion = '';
        if (preg_match('/Edg(?:e|A|iOS)?\/([\d.]+)/', $ua, $m)) {
            $browser = 'edge';
            $browserVersion = explode('.', $m[1])[0];
        } elseif (preg_match('/OPR\/([\d.]+)/', $ua, $m)) {
            $browser = 'opera';
            $browserVersion = explode('.', $m[1])[0];
        } elseif (preg_match('/Chrome\/([\d.]+)/', $ua) && !str_contains($ua, 'Edg')) {
            preg_match('/Chrome\/([\d.]+)/', $ua, $m);
            $browser = 'chrome';
            $browserVersion = explode('.', $m[1])[0];
        } elseif (preg_match('/Safari\/([\d.]+)/', $ua) && preg_match('/Version\/([\d.]+)/', $ua, $m)) {
            $browser = 'safari';
            $browserVersion = explode('.', $m[1])[0];
        } elseif (preg_match('/Firefox\/([\d.]+)/', $ua, $m)) {
            $browser = 'firefox';
            $browserVersion = explode('.', $m[1])[0];
        } elseif (str_contains($ua, 'SamsungBrowser')) {
            $browser = 'samsung';
            preg_match('/SamsungBrowser\/([\d.]+)/', $ua, $m);
            $browserVersion = $m[1] ?? '';
        }

        // Detect device type
        $device = 'desktop';
        if (preg_match('/Mobile|Android.*(?!Tablet)|iPhone/', $ua)) {
            $device = 'mobile';
        } elseif (preg_match('/Tablet|iPad/', $ua)) {
            $device = 'tablet';
        }

        return [
            'os' => $os,
            'os_version' => $osVersion,
            'browser' => $browser,
            'browser_version' => $browserVersion,
            'device' => $device,
        ];
    }
}
