<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episode_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('episode_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('hashed_ip', 64);
            $table->string('user_agent', 500)->nullable();
            $table->string('device_type', 20)->nullable();
            $table->timestamp('viewed_at')->useCurrent();

            $table->index('episode_id');
            $table->index(['episode_id', 'hashed_ip']);
            $table->index('viewed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episode_views');
    }
};
