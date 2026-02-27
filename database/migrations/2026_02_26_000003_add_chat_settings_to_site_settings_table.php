<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->boolean('chat_enabled')->default(false)->after('register_enabled');
            $table->foreignId('default_chat_character_id')
                ->nullable()
                ->after('chat_enabled')
                ->constrained('characters')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropForeign(['default_chat_character_id']);
            $table->dropColumn(['chat_enabled', 'default_chat_character_id']);
        });
    }
};
