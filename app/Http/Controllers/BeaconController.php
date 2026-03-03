<?php

namespace App\Http\Controllers;

use App\Models\Beacon;
use App\Services\BeaconScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class BeaconController extends Controller
{
    public function __construct(
        private BeaconScanService $scanService,
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
            // Unknown GUID — log and redirect to homepage
            $redirectUrl = config('beacon.default_redirect', '/');
            $this->scanService->logScan($request, $guid, null, $redirectUrl, $isRateLimited);
            return redirect($redirectUrl);
        }

        // Known beacon — always log
        if ($beacon->is_out_of_action) {
            $behavior = $this->scanService->resolveOutOfActionBehavior($beacon);
            $redirectUrl = $behavior['url'] ?? '/';

            $this->scanService->logScan($request, $guid, $beacon, $redirectUrl, $isRateLimited);

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

        // Normal flow: online or offline — both redirect
        $redirectUrl = $this->scanService->resolveRedirectUrl($beacon);
        $this->scanService->logScan($request, $guid, $beacon, $redirectUrl, $isRateLimited);

        return redirect($redirectUrl);
    }
}
