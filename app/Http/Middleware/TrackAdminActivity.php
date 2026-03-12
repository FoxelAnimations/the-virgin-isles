<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class TrackAdminActivity
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user?->is_admin) {
            // Throttle DB writes to once per minute
            $cacheKey = 'admin_active:' . $user->id;
            if (!Cache::has($cacheKey)) {
                $user->forceFill(['last_active_at' => now()])->saveQuietly();
                Cache::put($cacheKey, true, 60);
            }
        }

        return $next($request);
    }
}
