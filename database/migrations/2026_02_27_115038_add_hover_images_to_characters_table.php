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
        Schema::table('characters', function (Blueprint $table) {
            $table->string('profile_image_hover_path')->nullable()->after('profile_image_path');
            $table->string('full_body_image_hover_path')->nullable()->after('full_body_image_path');
            $table->string('profile_photo_hover_path')->nullable()->after('profile_photo_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'profile_image_hover_path',
                'full_body_image_hover_path',
                'profile_photo_hover_path',
            ]);
        });
    }
};
