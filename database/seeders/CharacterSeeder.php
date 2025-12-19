<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\CharacterImage;
use App\Models\Job;
use Illuminate\Database\Seeder;

class CharacterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ceoJob = Job::firstOrCreate(['title' => 'CEO']);

        $character = Character::updateOrCreate(
            ['first_name' => 'Luc', 'last_name' => 'de Kaasrander'],
            [
                'job_id' => $ceoJob->id,
                'bio' => 'Luc is een strenge maar speciale baas die niet goed snapt dat mensen van een ander ras ook gewoon mensen zijn en hij heeft daarbij de mentaliteit van een opa van 90 jaar op dat vlak.',
                'profile_image_path' => 'characters/profile/luc.png',
                'full_body_image_path' => 'characters/full-body/luc.png',
            ]
        );

        CharacterImage::firstOrCreate(
            ['character_id' => $character->id, 'image_path' => 'characters/gallery/luc-1.png'],
            ['sort_order' => 0]
        );
    }
}
