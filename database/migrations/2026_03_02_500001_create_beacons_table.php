<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beacons', function (Blueprint $table) {
            $table->id();
            $table->string('guid', 10)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('type_id')->nullable()->constrained('beacon_types')->nullOnDelete();
            $table->string('redirect_url')->nullable();
            $table->boolean('is_online')->default(true);
            $table->boolean('is_out_of_action')->default(false);
            $table->string('out_of_action_mode')->default('showPage');
            $table->string('out_of_action_redirect_url')->nullable();
            $table->text('out_of_action_message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beacons');
    }
};
