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
            ['email' => 'deprauwemiel@gmail.com'],
            [
                'name' => 'Emiel',
                'password' => Hash::make('Solarius&Karen'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'tribalfusion_x@hotmail.com'],
            [
                'name' => 'Glenn',
                'password' => Hash::make('Solarius&Karen'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
