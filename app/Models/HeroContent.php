<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroContent extends Model
{
    protected $table = 'hero_content';

    protected $fillable = [
        'pre_title',
        'title',
        'description',
    ];
}
