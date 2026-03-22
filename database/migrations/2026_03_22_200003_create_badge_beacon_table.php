<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badge_beacon', function (Blueprint $table) {
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->foreignId('beacon_id')->constrained()->cascadeOnDelete();
            $table->unique(['badge_id', 'beacon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('badge_beacon');
    }
};
