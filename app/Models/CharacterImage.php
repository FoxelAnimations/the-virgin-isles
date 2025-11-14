<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CharacterImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'character_id',
        'image_path',
        'alt_text',
        'sort_order',
    ];

    public function character()
    {
        return $this->belongsTo(Character::class);
    }
}
