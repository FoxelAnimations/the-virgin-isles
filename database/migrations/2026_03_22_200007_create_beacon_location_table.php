<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beacon_location', function (Blueprint $table) {
            $table->foreignId('beacon_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->unique(['beacon_id', 'location_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beacon_location');
    }
};
