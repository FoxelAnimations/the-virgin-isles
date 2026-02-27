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
}
