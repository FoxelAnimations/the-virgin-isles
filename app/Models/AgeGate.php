<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgeGate extends Model
{
    protected $table = 'age_gate';

    protected $fillable = [
        'enabled',
        'message',
        'confirm_text',
        'deny_text',
        'deny_url',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];
}
