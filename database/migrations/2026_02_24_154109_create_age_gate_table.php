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
        Schema::create('age_gate', function (Blueprint $table) {
            $table->id();
            $table->text('message');
            $table->string('confirm_text');
            $table->string('deny_text');
            $table->string('deny_url');
            $table->timestamps();
        });

        DB::table('age_gate')->insert([
            'message' => 'Deze website bevat content die alleen geschikt is voor bezoekers van 18 jaar en ouder. Ben je 18 of ouder?',
            'confirm_text' => 'Ja, ik ben 18+',
            'deny_text' => 'Nee',
            'deny_url' => 'https://www.google.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('age_gate');
    }
};
