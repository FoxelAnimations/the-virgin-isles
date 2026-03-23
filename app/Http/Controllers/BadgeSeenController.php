<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BadgeSeenController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'badge_id' => ['required', 'integer', 'exists:badges,id'],
        ]);

        DB::table('badge_user')
            ->where('user_id', Auth::id())
            ->where('badge_id', $request->badge_id)
            ->whereNull('seen_at')
            ->update(['seen_at' => now()]);

        return response()->noContent();
    }
}
