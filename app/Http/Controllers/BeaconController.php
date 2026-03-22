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
            // Eager-load relations for badge/location processing
            $beacon->load(['badges' => fn ($q) => $q->active(), 'locations']);
            $this->processBadgesAndLocations($user, $beacon, $isNewCollection);
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

    private function processBadgesAndLocations(User $user, Beacon $beacon, bool $isNewCollection): void
    {
        if ($isNewCollection) {
            $badgePopups = $this->badgeScanService->processNewBeaconScan($user, $beacon);

            if (!empty($badgePopups)) {
                session()->flash('badge_popups', $badgePopups);
            }
        } else {
            $locationPopup = $this->badgeScanService->getDuplicateScanLocationPopup($beacon);

            if ($locationPopup) {
                session()->flash('location_popup', $locationPopup);
            }
        }
    }
}
