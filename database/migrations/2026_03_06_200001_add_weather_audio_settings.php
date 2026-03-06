<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->boolean('rain_enabled')->default(false)->after('is_transition');
            $table->boolean('wind_enabled')->default(false)->after('rain_enabled');
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->unsignedTinyInteger('rain_volume')->default(50)->after('static_intensity');
            $table->unsignedTinyInteger('wind_volume')->default(50)->after('rain_volume');
        });
    }

    public function down(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->dropColumn(['rain_enabled', 'wind_enabled']);
        });

        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn(['rain_volume', 'wind_volume']);
        });
    }
};
