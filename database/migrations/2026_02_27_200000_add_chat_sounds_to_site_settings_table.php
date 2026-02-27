<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('chat_blocked_sound')->nullable()->after('default_chat_character_id');
            $table->string('chat_notification_sound')->nullable()->after('chat_blocked_sound');
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn(['chat_blocked_sound', 'chat_notification_sound']);
        });
    }
};
