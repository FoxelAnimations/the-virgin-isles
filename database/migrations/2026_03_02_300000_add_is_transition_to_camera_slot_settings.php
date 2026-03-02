<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->boolean('is_transition')->default(false)->after('overlay_color');
        });

        // Ochtend and Avond are transition periods by default
        DB::table('camera_slot_settings')
            ->whereIn('slot_key', ['ochtend', 'avond'])
            ->update(['is_transition' => true]);
    }

    public function down(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->dropColumn('is_transition');
        });
    }
};
