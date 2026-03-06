<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Camera;
use App\Models\CameraDefaultBlock;
use App\Models\CameraSlotSetting;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class CameraController extends Controller
{
    public function schedule(): JsonResponse
    {
        $now = now('Europe/Brussels');
        $dayOfWeek = $now->dayOfWeekIso - 1; // 1=Mon→0, 7=Sun→6
        $currentTime = $now->format('H:i');

        $cameras = Camera::visible()
            ->with(['videos', 'defaultBlocks.video', 'scheduledVideos.video'])
            ->get();

        $result = $cameras->map(function (Camera $camera) use ($dayOfWeek, $currentTime, $now) {
            if ($camera->is_offline) {
                return [
                    'id' => $camera->id,
                    'name' => $camera->name,
                    'status' => 'offline',
                    'video_url' => null,
                    'audio_url' => null,
                    'background_url' => $camera->backgroundUrl(),
                    'background_is_video' => $camera->backgroundIsVideo(),
                    'static_enabled' => $camera->static_enabled,
                    'static_intensity' => $camera->static_intensity,
                    'rain_volume' => $camera->rain_volume,
                    'wind_volume' => $camera->wind_volume,
                    'next_check_seconds' => 300,
                ];
            }

            // Check scheduled videos first (overrides)
            $activeScheduled = $camera->scheduledVideos
                ->where('day_of_week', $dayOfWeek)
                ->filter(fn ($s) => substr($s->start_time, 0, 5) <= $currentTime && substr($s->end_time, 0, 5) > $currentTime)
                ->sortBy('start_time')
                ->first();

            $videoUrl = null;
            $audioUrl = null;
            $blockStartTime = null;
            $blockEndTime = null;
            $activeVideo = null;

            if ($activeScheduled) {
                $activeVideo = $activeScheduled->video;
                $videoUrl = $activeVideo?->videoUrl();
                $audioUrl = $activeVideo?->audioUrl();
                $blockStartTime = substr($activeScheduled->start_time, 0, 5);
                $blockEndTime = substr($activeScheduled->end_time, 0, 5);
            } else {
                // Fall back to default block
                $slot = CameraDefaultBlock::slotForTime($currentTime);
                $defaultBlock = $camera->defaultBlocks
                    ->where('day_of_week', $dayOfWeek)
                    ->where('time_slot', $slot)
                    ->first();

                $activeVideo = $defaultBlock?->video;
                $videoUrl = $activeVideo?->videoUrl();
                $audioUrl = $activeVideo?->audioUrl();
                $blockStartTime = CameraDefaultBlock::slots()[$slot]['start'];
                $blockEndTime = CameraDefaultBlock::slots()[$slot]['end'];
            }

            // Calculate seconds until current block ends (seconds-precise)
            $blockEndMinutes = $blockEndTime === '24:00' ? 1440 : $this->timeToMinutes($blockEndTime);
            $currentTotalSeconds = intval($now->format('H')) * 3600 + intval($now->format('i')) * 60 + intval($now->format('s'));

            // Calculate real-time playback offset
            $videoStartOffset = 0;
            if ($activeVideo?->behaviour_type === 'realtime' && $blockStartTime) {
                $blockStartSeconds = $this->timeToMinutes($blockStartTime) * 60;
                $videoStartOffset = max(0, $currentTotalSeconds - $blockStartSeconds);
                if ($activeVideo->duration_seconds) {
                    $videoStartOffset = min($videoStartOffset, $activeVideo->duration_seconds - 1);
                }
            }
            $blockEndSeconds = $blockEndMinutes * 60;
            $diff = $blockEndSeconds - $currentTotalSeconds;
            if ($diff <= 0) {
                $diff += 86400; // wrap around midnight
            }
            $secondsUntilEnd = max(5, $diff);

            // Check if a scheduled video starts sooner
            $nextScheduled = $camera->scheduledVideos
                ->where('day_of_week', $dayOfWeek)
                ->filter(fn ($s) => substr($s->start_time, 0, 5) > $currentTime)
                ->sortBy('start_time')
                ->first();

            if ($nextScheduled) {
                $nextStartSeconds = $this->timeToMinutes(substr($nextScheduled->start_time, 0, 5)) * 60;
                $secondsUntilNext = max(5, $nextStartSeconds - $currentTotalSeconds);
                $secondsUntilEnd = min($secondsUntilEnd, $secondsUntilNext);
            }

            return [
                'id' => $camera->id,
                'name' => $camera->name,
                'status' => 'online',
                'video_url' => $videoUrl,
                'audio_url' => $audioUrl,
                'behaviour_type' => $activeVideo?->behaviour_type ?? 'loop',
                'video_start_offset_seconds' => $videoStartOffset,
                'background_url' => $camera->backgroundUrl(),
                'background_is_video' => $camera->backgroundIsVideo(),
                'static_enabled' => $camera->static_enabled,
                'static_intensity' => $camera->static_intensity,
                'rain_volume' => $camera->rain_volume,
                'wind_volume' => $camera->wind_volume,
                'next_check_seconds' => $secondsUntilEnd,
            ];
        });

        $siteSettings = SiteSetting::first();

        return response()->json([
            'cameras' => $result->values(),
            'server_time' => $now->toIso8601String(),
            'slots' => CameraSlotSetting::getSlots(),
            'weather_enabled' => (bool) ($siteSettings?->weather_enabled ?? true),
        ]);
    }

    private function timeToMinutes(string $time): int
    {
        $parts = explode(':', $time);
        return intval($parts[0]) * 60 + intval($parts[1] ?? 0);
    }
}
