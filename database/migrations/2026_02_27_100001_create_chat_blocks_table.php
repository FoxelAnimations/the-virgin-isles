<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_blocks', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45);
            $table->uuid('visitor_uuid')->nullable();
            $table->string('reason');
            $table->foreignId('blocked_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('ip_address');
            $table->index('visitor_uuid');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_blocks');
    }
};
