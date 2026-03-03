<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('nick_name')->nullable();
            $table->unsignedTinyInteger('age')->nullable();
            $table->foreignId('job_id')
                ->nullable()
                ->constrained('character_jobs')
                ->nullOnDelete();
            $table->text('bio')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('profile_image_path')->nullable();
            $table->string('profile_image_hover_path')->nullable();
            $table->string('full_body_image_path')->nullable();
            $table->string('full_body_image_hover_path')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('profile_photo_hover_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->string('chat_image_path')->nullable();
            $table->text('personality')->nullable();
            $table->text('speaking_style')->nullable();
            $table->text('backstory')->nullable();
            $table->text('example_phrases')->nullable();
            $table->text('chat_instructions')->nullable();
            $table->boolean('chat_enabled')->default(false);
            $table->string('chat_mode', 20)->default('ai');
            $table->boolean('chat_online')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
