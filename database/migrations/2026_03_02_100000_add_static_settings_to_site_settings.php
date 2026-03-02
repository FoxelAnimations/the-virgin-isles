<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('static_enabled')->default(true)->after('weather_enabled');
            $table->unsignedTinyInteger('static_intensity')->default(15)->after('static_enabled'); // 0-100
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['static_enabled', 'static_intensity']);
        });
    }
};
