<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('blocked_until')->nullable()->after('is_blocked');
            $table->string('block_reason', 500)->nullable()->after('blocked_until');
            $table->foreignId('blocked_by')->nullable()->constrained('users')->nullOnDelete()->after('block_reason');
            $table->boolean('is_comment_blocked')->default(false)->after('blocked_by');
            $table->timestamp('comment_blocked_until')->nullable()->after('is_comment_blocked');
            $table->string('comment_block_reason', 500)->nullable()->after('comment_blocked_until');
            $table->foreignId('comment_blocked_by')->nullable()->constrained('users')->nullOnDelete()->after('comment_block_reason');
            $table->timestamp('news_dismissed_at')->nullable()->after('comment_blocked_by');
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->string('dashboard_welcome_title')->nullable();
            $table->text('dashboard_welcome_text')->nullable();
            $table->json('dashboard_news_items')->nullable();
            $table->timestamp('dashboard_news_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['blocked_by']);
            $table->dropForeign(['comment_blocked_by']);
            $table->dropColumn([
                'blocked_until', 'block_reason', 'blocked_by',
                'is_comment_blocked', 'comment_blocked_until', 'comment_block_reason', 'comment_blocked_by',
                'news_dismissed_at',
            ]);
        });

        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'dashboard_welcome_title', 'dashboard_welcome_text',
                'dashboard_news_items', 'dashboard_news_updated_at',
            ]);
        });
    }
};
