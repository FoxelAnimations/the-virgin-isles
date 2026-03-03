<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('pre_title')->nullable();
            $table->string('title')->nullable();
            $table->text('text')->nullable();

            $table->enum('media_type', ['image', 'video', 'youtube'])->nullable();
            $table->string('image_path')->nullable();
            $table->string('video_path')->nullable();
            $table->string('youtube_url')->nullable();

            $table->string('button_label')->nullable();
            $table->string('button_url')->nullable();
            $table->string('button_color')->nullable();
            $table->boolean('button_new_tab')->default(false);

            $table->string('separator_color')->nullable();

            $table->boolean('is_active')->default(true);
            $table->enum('placement', ['home', 'blog', 'both'])->default('home');
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_blocks');
    }
};
