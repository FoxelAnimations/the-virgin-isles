<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->text('personality')->nullable()->after('bio');
            $table->text('speaking_style')->nullable()->after('personality');
            $table->text('backstory')->nullable()->after('speaking_style');
            $table->text('example_phrases')->nullable()->after('backstory');
            $table->text('chat_instructions')->nullable()->after('example_phrases');
            $table->boolean('chat_enabled')->default(false)->after('chat_instructions');
        });
    }

    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'personality',
                'speaking_style',
                'backstory',
                'example_phrases',
                'chat_instructions',
                'chat_enabled',
            ]);
        });
    }
};
