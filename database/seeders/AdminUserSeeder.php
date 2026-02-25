<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@in-cc.be'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Password1'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
