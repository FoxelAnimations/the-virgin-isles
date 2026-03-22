<?php

namespace App\Http\Controllers;

use App\Models\Beacon;
use App\Services\BadgeScanService;
use App\Services\BeaconScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BeaconScanApiController extends Controller
{
    public function __construct(
        private BeaconScanService $scanService,
        private BadgeScanService $badgeScanService,
    ) {}

    /**
     * Process a beacon scan via AJAX (internal scanner).
     * Returns badge popup data as JSON instead of redirecting.
     */
    public function scan(Request $request, string $guid): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Niet ingelogd.'], 401);
        }

        $beacon = Beacon::where('guid', $guid)->first();

        if (!$beacon) {
            return response()->json(['error' => 'Beacon niet gevonden.'], 404);
        }

        if ($beacon->isBeforeActivation()) {
            return response()->json(['error' => 'Deze beacon is nog niet actief.'], 403);
        }

        if ($beacon->is_out_of_action) {
            return response()->json(['error' => 'Deze beacon is buiten gebruik.'], 410);
        }

        // Log the scan
        $this->scanService->logScan($request, $guid, $beacon, 'api-scan', false);

        // Collect beacon
        $isNewCollection = false;
        if ($beacon->is_collectible) {
            $alreadyCollected = $user->collectedBeacons()->where('beacon_id', $beacon->id)->exists();
            if (!$alreadyCollected) {
                $user->collectedBeacons()->attach($beacon->id, ['collected_at' => now()]);
                $isNewCollection = true;
            }
        }

        // Process badges
        $badgePopups = [];
        if ($isNewCollection) {
            $badgePopups = $this->badgeScanService->processNewBeaconScan($user, $beacon);
        }

        return response()->json([
            'success' => true,
            'is_new' => $isNewCollection,
            'badge_popups' => $badgePopups,
            'beacon_title' => $beacon->title,
        ]);
    }
}
