<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'login_enabled',
        'register_enabled',
        'chat_enabled',
        'default_chat_character_id',
        'chat_blocked_sound',
        'chat_notification_sound',
    ];

    protected function casts(): array
    {
        return [
            'login_enabled' => 'boolean',
            'register_enabled' => 'boolean',
            'chat_enabled' => 'boolean',
        ];
    }

    public function defaultChatCharacter()
    {
        return $this->belongsTo(Character::class, 'default_chat_character_id');
    }
}
