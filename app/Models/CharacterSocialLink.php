<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CharacterSocialLink extends Model
{
    protected $fillable = [
        'character_id',
        'title',
        'url',
        'sort_order',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
