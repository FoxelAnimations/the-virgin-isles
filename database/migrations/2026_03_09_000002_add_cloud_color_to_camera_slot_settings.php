<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->string('cloud_color', 9)->default('#FFFFFF66')->after('overlay_color');
        });
    }

    public function down(): void
    {
        Schema::table('camera_slot_settings', function (Blueprint $table) {
            $table->dropColumn('cloud_color');
        });
    }
};
