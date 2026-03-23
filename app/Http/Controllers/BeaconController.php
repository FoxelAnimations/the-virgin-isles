<?php

namespace App\Http\Controllers;

use App\Models\Beacon;
use App\Models\User;
use App\Services\BadgeScanService;
use App\Services\BeaconScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class BeaconController extends Controller
{
    public function __construct(
        private BeaconScanService $scanService,
        private BadgeScanService $badgeScanService,
    ) {}

    public function scan(Request $request, string $guid)
    {
        $rateLimitKey = 'beacon-scan:' . $this->scanService->hashIp($request->ip());
        $maxAttempts = config('beacon.rate_limit_per_minute', 60);
        $isRateLimited = RateLimiter::tooManyAttempts($rateLimitKey, $maxAttempts);

        if (!$isRateLimited) {
            RateLimiter::hit($rateLimitKey, 60);
        }

        $beacon = Beacon::where('guid', $guid)->first();

        if (!$beacon) {
            $redirectUrl = config('beacon.default_redirect', '/');
            $this->scanService->logScan($request, $guid, null, $redirectUrl, $isRateLimited);
            return redirect($redirectUrl);
        }

        if ($beacon->isBeforeActivation()) {
            $redirectUrl = config('beacon.default_redirect', '/');
            $this->scanService->logScan($request, $guid, $beacon, $redirectUrl, $isRateLimited);
            return redirect($redirectUrl);
        }

        if ($beacon->is_out_of_action) {
            $behavior = $this->scanService->resolveOutOfActionBehavior($beacon);
            $redirectUrl = $behavior['url'] ?? '/';

            $this->scanService->logScan($request, $guid, $beacon, $redirectUrl, $isRateLimited);

            $this->collectForUser(Auth::user(), $beacon);

            return match ($behavior['type']) {
                'redirect' => redirect($redirectUrl),
                'page' => response()->view('beacon.out-of-action', [
                    'beacon' => $beacon,
                    'message' => $behavior['message'] ?? null,
                ]),
                'block' => response()->view('beacon.out-of-action', [
                    'beacon' => $beacon,
                    'message' => $behavior['message'] ?? 'This beacon is no longer active.',
                ], 410),
                default => redirect('/'),
            };
        }

        $user = Auth::user();
        $isNewCollection = $this->collectForUser($user, $beacon);

        if ($user) {
            if ($isNewCollection) {
                $earnedBadges = $this->badgeScanService->processNewBeaconScan($user, $beacon);

                if (!empty($earnedBadges)) {
                    $dashboardUrl = route('dashboard');
                    $this->scanService->logScan($request, $guid, $beacon, $dashboardUrl, $isRateLimited);
                    return redirect($dashboardUrl);
                }
            } elseif ($beacon->locations()->exists()) {
                // Repeat scan (beacon already collected) with linked location → redirect to map
                $mapUrl = route('map');
                $this->scanService->logScan($request, $guid, $beacon, $mapUrl, $isRateLimited);
                return redirect($mapUrl);
            }
        }

        $redirectUrl = $this->scanService->resolveRedirectUrl($beacon);
        $this->scanService->logScan($request, $guid, $beacon, $redirectUrl, $isRateLimited);

        return redirect($redirectUrl);
    }

    private function collectForUser(?User $user, Beacon $beacon): bool
    {
        if (!$user || !$beacon->is_collectible) {
            return false;
        }

        $alreadyCollected = $user->collectedBeacons()->where('beacon_id', $beacon->id)->exists();

        if (!$alreadyCollected) {
            $user->collectedBeacons()->attach($beacon->id, ['collected_at' => now()]);
            return true;
        }

        return false;
    }

}
