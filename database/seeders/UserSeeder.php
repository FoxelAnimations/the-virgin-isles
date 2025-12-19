<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'foxelanimations@gmail.com'],
            [
                'name' => 'emiel',
                'password' => 'Password1',
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
