<?php

namespace App\Services;

use App\Models\Beacon;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            $existing = $existingBadges->get($badge->id);

            if (!$existing) {
                $toInsert[] = [
                    'badge_id' => $badge->id,
                    'user_id' => $user->id,
                    'count' => 1,
                    'collected_at' => now(),
                    'updated_at' => now(),
                ];

                $badgePopups[] = [
                    'title' => $badge->title,
                    'image' => $badge->image_path ? Storage::url($badge->image_path) : null,
                    'popup_text' => $badge->popup_text_first,
                    'count' => 1,
                    'is_first' => true,
                ];
            } else {
                $newCount = $existing->count + 1;

                $user->badges()->updateExistingPivot($badge->id, [
                    'count' => $newCount,
                    'updated_at' => now(),
                ]);

                $badgePopups[] = [
                    'title' => $badge->title,
                    'image' => $badge->image_path ? Storage::url($badge->image_path) : null,
                    'popup_text' => $badge->popup_text_repeat,
                    'count' => $newCount,
                    'is_first' => false,
                ];
            }
        }

        // Bulk insert new badge-user records
        if (!empty($toInsert)) {
            DB::table('badge_user')->insert($toInsert);
        }

        $this->revealLocations($user, $beacon);

        return $badgePopups;
    }

    /**
     * Get location popup data for a duplicate beacon scan.
     */
    public function getDuplicateScanLocationPopup(Beacon $beacon): ?array
    {
        $location = $beacon->locations()->first();

        if (!$location) {
            return null;
        }

        return [
            'title' => $location->title,
            'description' => $location->description,
            'image' => $location->image_path ? Storage::url($location->image_path) : null,
        ];
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
