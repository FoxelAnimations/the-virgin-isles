<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Convert existing placement values to new position values
        DB::table('content_blocks')->update(['placement' => 'below_episodes']);

        // Change the enum values
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropColumn('placement');
        });

        Schema::table('content_blocks', function (Blueprint $table) {
            $table->enum('placement', ['above_episodes', 'below_episodes'])->default('below_episodes')->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropColumn('placement');
        });

        Schema::table('content_blocks', function (Blueprint $table) {
            $table->enum('placement', ['home', 'blog', 'both'])->default('home')->after('is_active');
        });
    }
};
