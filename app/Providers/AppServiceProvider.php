<?php

namespace App\Providers;

use App\Models\ChatConversation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('chat', function ($request) {
            return Limit::perMinute(15)->by($request->ip());
        });

        // Stricter limit specifically for sending messages (max 5 per minute per IP)
        RateLimiter::for('chat-send', function ($request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        View::composer('layouts.admin', function ($view) {
            if (auth()->check() && auth()->user()->is_admin) {
                $view->with('unreadChatCount', ChatConversation::open()->sum('unread_count'));
            }
        });
    }
}
