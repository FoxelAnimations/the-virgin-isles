<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('hero_content', function (Blueprint $table) {
            $table->id();
            $table->string('pre_title')->default('');
            $table->string('title')->default('');
            $table->text('description')->default('');
            $table->timestamps();
        });

        $now = now();
        DB::table('hero_content')->insert([
            'pre_title' => 'Info / Intro',
            'title' => 'The Visual Identity',
            'description' => 'A creative universe of original characters, stories, and content. Follow us on socials to stay up to date.',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hero_content');
    }
};
