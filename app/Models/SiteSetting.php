<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'login_enabled',
        'register_enabled',
    ];

    protected function casts(): array
    {
        return [
            'login_enabled' => 'boolean',
            'register_enabled' => 'boolean',
        ];
    }
}
