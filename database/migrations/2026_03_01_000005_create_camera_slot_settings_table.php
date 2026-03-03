<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('camera_slot_settings', function (Blueprint $table) {
            $table->id();
            $table->string('slot_key')->unique();
            $table->string('label');
            $table->string('start_time', 5);
            $table->string('end_time', 5);
            $table->string('bg_color', 7)->default('#000000');
            $table->string('overlay_color', 9)->default('#00000000');
            $table->boolean('is_transition')->default(false);
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->timestamps();
        });

        DB::table('camera_slot_settings')->insert([
            ['slot_key' => 'ochtend', 'label' => 'Ochtend', 'start_time' => '06:00', 'end_time' => '08:00', 'bg_color' => '#F4845F', 'overlay_color' => '#FF8C0030', 'is_transition' => true,  'sort_order' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['slot_key' => 'dag',     'label' => 'Dag',     'start_time' => '08:00', 'end_time' => '18:00', 'bg_color' => '#87CEEB', 'overlay_color' => '#FFFFFF10', 'is_transition' => false, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['slot_key' => 'avond',   'label' => 'Avond',   'start_time' => '18:00', 'end_time' => '24:00', 'bg_color' => '#D4621A', 'overlay_color' => '#FF450030', 'is_transition' => true,  'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['slot_key' => 'nacht',   'label' => 'Nacht',   'start_time' => '00:00', 'end_time' => '06:00', 'bg_color' => '#0B1026', 'overlay_color' => '#0000001A', 'is_transition' => false, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('camera_slot_settings');
    }
};
