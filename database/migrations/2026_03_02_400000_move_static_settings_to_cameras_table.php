<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cameras', function (Blueprint $table) {
            $table->boolean('static_enabled')->default(true)->after('background_path');
            $table->unsignedTinyInteger('static_intensity')->default(15)->after('static_enabled');
        });

        // Copy current global values to all existing cameras
        $siteSettings = DB::table('site_settings')->first();
        if ($siteSettings) {
            DB::table('cameras')->update([
                'static_enabled' => $siteSettings->static_enabled ?? true,
                'static_intensity' => $siteSettings->static_intensity ?? 15,
            ]);
        }

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['static_enabled', 'static_intensity']);
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('static_enabled')->default(true);
            $table->unsignedTinyInteger('static_intensity')->default(15);
        });

        // Copy first camera's values back to global
        $camera = DB::table('cameras')->first();
        if ($camera) {
            DB::table('site_settings')->update([
                'static_enabled' => $camera->static_enabled ?? true,
                'static_intensity' => $camera->static_intensity ?? 15,
            ]);
        }

        Schema::table('cameras', function (Blueprint $table) {
            $table->dropColumn(['static_enabled', 'static_intensity']);
        });
    }
};
