<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('show_episodes')->default(true)->after('weather_enabled');
            $table->boolean('show_shorts')->default(true)->after('show_episodes');
            $table->boolean('show_minis')->default(true)->after('show_shorts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['show_episodes', 'show_shorts', 'show_minis']);
        });
    }
};
