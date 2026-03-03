<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('beacon_scans', function (Blueprint $table) {
            $table->id();
            $table->timestamp('scanned_at')->useCurrent();
            $table->string('guid')->index();
            $table->foreignId('beacon_id')->nullable()->constrained('beacons')->nullOnDelete();
            $table->boolean('is_known')->default(false);
            $table->string('hashed_ip', 64);
            $table->text('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->text('requested_url')->nullable();
            $table->text('redirect_url_used')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_term')->nullable();
            $table->string('utm_content')->nullable();
            $table->boolean('rate_limited')->default(false);
            $table->json('meta_json')->nullable();
            $table->timestamps();

            $table->index(['beacon_id', 'scanned_at']);
            $table->index(['guid', 'scanned_at']);
            $table->index('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('beacon_scans');
    }
};
