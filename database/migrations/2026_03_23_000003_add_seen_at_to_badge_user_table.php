<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->timestamp('seen_at')->nullable()->after('collected_at');
        });

        // Mark all existing badges as seen so they don't trigger popups
        DB::table('badge_user')->whereNull('seen_at')->update([
            'seen_at' => DB::raw('collected_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('badge_user', function (Blueprint $table) {
            $table->dropColumn('seen_at');
        });
    }
};
