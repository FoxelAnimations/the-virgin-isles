<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CameraSlotSetting extends Model
{
    protected $fillable = [
        'slot_key',
        'label',
        'start_time',
        'end_time',
        'bg_color',
        'overlay_color',
        'cloud_color',
        'is_transition',
        'rain_enabled',
        'wind_enabled',
        'sort_order',
    ];

    protected $casts = [
        'is_transition' => 'boolean',
        'rain_enabled' => 'boolean',
        'wind_enabled' => 'boolean',
    ];

    /**
     * Get all slots as an associative array, cached for performance.
     * Returns the same format as the old CameraDefaultBlock::SLOTS constant.
     */
    public static function getSlots(): array
    {
        return Cache::remember('camera_slot_settings', 3600, function () {
            $settings = static::orderBy('sort_order')->get();

            if ($settings->isEmpty()) {
                // Fallback to defaults if table is empty
                return [
                    'nacht'   => ['start' => '00:00', 'end' => '06:00', 'label' => 'Nacht',   'bg_color' => '#0B1026', 'overlay_color' => '#0000001A', 'cloud_color' => '#FFFFFF66', 'is_transition' => false, 'rain_enabled' => false, 'wind_enabled' => false],
                    'ochtend' => ['start' => '06:00', 'end' => '08:00', 'label' => 'Ochtend', 'bg_color' => '#F4845F', 'overlay_color' => '#FF8C0030', 'cloud_color' => '#FFFFFF66', 'is_transition' => true,  'rain_enabled' => false, 'wind_enabled' => false],
                    'dag'     => ['start' => '08:00', 'end' => '18:00', 'label' => 'Dag',     'bg_color' => '#87CEEB', 'overlay_color' => '#FFFFFF10', 'cloud_color' => '#FFFFFF66', 'is_transition' => false, 'rain_enabled' => false, 'wind_enabled' => false],
                    'avond'   => ['start' => '18:00', 'end' => '24:00', 'label' => 'Avond',   'bg_color' => '#D4621A', 'overlay_color' => '#FF450030', 'cloud_color' => '#FFFFFF66', 'is_transition' => true,  'rain_enabled' => false, 'wind_enabled' => false],
                ];
            }

            return $settings->mapWithKeys(function ($setting) {
                return [$setting->slot_key => [
                    'start'         => $setting->start_time,
                    'end'           => $setting->end_time,
                    'label'         => $setting->label,
                    'bg_color'      => $setting->bg_color,
                    'overlay_color' => $setting->overlay_color,
                    'cloud_color'   => $setting->cloud_color ?? '#FFFFFF66',
                    'is_transition' => (bool) $setting->is_transition,
                    'rain_enabled'  => (bool) $setting->rain_enabled,
                    'wind_enabled'  => (bool) $setting->wind_enabled,
                ]];
            })->toArray();
        });
    }

    /**
     * Clear the cached slots when settings are updated.
     */
    public static function clearCache(): void
    {
        Cache::forget('camera_slot_settings');
    }
}
