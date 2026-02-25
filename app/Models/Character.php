<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Character extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'nick_name',
        'age',
        'job_id',
        'bio',
        'profile_image_path',
        'full_body_image_path',
        'sort_order',
    ];

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function characterImages()
    {
        return $this->hasMany(CharacterImage::class);
    }

    public function episodes()
    {
        return $this->belongsToMany(Episode::class);
    }
}
