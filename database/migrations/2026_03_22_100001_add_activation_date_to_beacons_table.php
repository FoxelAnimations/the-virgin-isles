<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beacons', function (Blueprint $table) {
            $table->date('activation_date')->nullable()->after('is_out_of_action');
        });
    }

    public function down(): void
    {
        Schema::table('beacons', function (Blueprint $table) {
            $table->dropColumn('activation_date');
        });
    }
};
