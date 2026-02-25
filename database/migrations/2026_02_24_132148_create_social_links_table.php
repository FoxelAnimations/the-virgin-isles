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
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('url')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('social_links')->insert([
            ['label' => 'Instagram', 'url' => null, 'sort_order' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'YouTube', 'url' => null, 'sort_order' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'Twitter', 'url' => null, 'sort_order' => 2, 'created_at' => $now, 'updated_at' => $now],
            ['label' => 'TikTok', 'url' => null, 'sort_order' => 3, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
