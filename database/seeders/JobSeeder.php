<?php

namespace Database\Seeders;

use App\Models\Job;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = [
            'Accounting',
            'Content Creator',
            'Student',
            'Sales',
            'Installation',
            'Cleaning',
            'PM',
            'Client',
            'External',
            'Copy-writer',
            'CEO',
            'Management',
            'Designer',
            'Developer',
            'HR',
            'Onthaal',
            'Account manager',
            'Photographer',
        ];

        foreach ($jobs as $title) {
            Job::firstOrCreate(
                ['title' => $title],
                ['description' => null]
            );
        }
    }
}
