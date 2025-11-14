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
        'character_beast_id',
        'first_name',
        'last_name',
        'nick_name',
        'age',
        'job_id',
        'bio',
        'deleted_at',
    ];

    // add cast to pre convert  data to the eqiested  format 
    // protected $casts = [
    //     'starting_time' => 'datetime:H:i:s',
    // ];

    // add use \OwenIt\Auditing\Auditable;
    // (this looks wor changes and who made the changes )

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function characterImages()
    {
        return $this->hasMany(CharacterImage::class);
    }
}
