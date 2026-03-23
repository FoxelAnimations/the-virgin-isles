<?php

namespace App\Services;

use App\Models\Beacon;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadgeScanService
{
    /**
     * Process badges and locations for a new beacon collection.
     * Returns array of badge popup data to flash to session.
     */
    public function processNewBeaconScan(User $user, Beacon $beacon): array
    {
        $badgePopups = [];

        $badges = $beacon->badges()->active()->get();

        if ($badges->isEmpty()) {
            $this->revealLocations($user, $beacon);
            return $badgePopups;
        }

        // Pre-load all existing badge-user records in a single query
        $existingBadges = DB::table('badge_user')
            ->where('user_id', $user->id)
            ->whereIn('badge_id', $badges->pluck('id'))
            ->get()
            ->keyBy('badge_id');

        $toInsert = [];

        foreach ($badges as $badge) {
            // Badge can only be earned once — skip if already owned
            if ($existingBadges->has($badge->id)) {
                continue;
            }

            $toInsert[] = [
                'badge_id' => $badge->id,
                'user_id' => $user->id,
                'count' => 1,
                'collected_at' => now(),
                'updated_at' => now(),
            ];

            $badgePopups[] = $badge->toPopupArray();
        }

        // Bulk insert new badge-user records
        if (!empty($toInsert)) {
            DB::table('badge_user')->insert($toInsert);
        }

        $this->revealLocations($user, $beacon);

        return $badgePopups;
    }

    /**
     * Reveal hidden locations linked to a beacon for a user.
     */
    private function revealLocations(User $user, Beacon $beacon): void
    {
        $locationIds = $beacon->locations()->pluck('locations.id');

        if ($locationIds->isEmpty()) {
            return;
        }

        // Get already-revealed locations in one query
        $alreadyRevealed = $user->revealedLocations()
            ->whereIn('locations.id', $locationIds)
            ->pluck('locations.id');

        $toReveal = $locationIds->diff($alreadyRevealed);

        if ($toReveal->isNotEmpty()) {
            $user->revealedLocations()->attach(
                $toReveal->mapWithKeys(fn ($id) => [$id => ['revealed_at' => now()]])->toArray()
            );
        }
    }
}
