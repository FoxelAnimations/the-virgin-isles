<?php

namespace App\Livewire\Admin;

use App\Models\CameraSlotSetting;
use App\Models\SiteSetting;
use Livewire\Component;

class CameraSettings extends Component
{
    public array $slots = [];
    public bool $weatherEnabled = true;

    public function mount(): void
    {
        $this->loadSlots();
        $siteSettings = SiteSetting::first();
        $this->weatherEnabled = $siteSettings?->weather_enabled ?? true;
    }

    protected function loadSlots(): void
    {
        $settings = CameraSlotSetting::orderBy('sort_order')->get();

        $this->slots = $settings->map(function ($setting) {
            return [
                'id'            => $setting->id,
                'slot_key'      => $setting->slot_key,
                'label'         => $setting->label,
                'start_time'    => $setting->start_time,
                'end_time'      => $setting->end_time === '24:00' ? '24:00' : $setting->end_time,
                'bg_color'      => $setting->bg_color ?? '#000000',
                'overlay_color' => $setting->overlay_color ?? '#00000000',
                'is_transition' => (bool) $setting->is_transition,
            ];
        })->toArray();
    }

    /**
     * Circular cascade: when an end_time changes, update the next slot's start_time.
     * Last slot's end_time wraps to first slot's start_time.
     */
    public function updatedSlots($value, $key): void
    {
        if (! str_ends_with($key, '.end_time')) {
            return;
        }

        $index = (int) explode('.', $key)[0];
        $count = count($this->slots);
        $nextIndex = ($index + 1) % $count;

        $endTime = $this->slots[$index]['end_time'];

        // 24:00 normalizes to 00:00 as a start_time
        $this->slots[$nextIndex]['start_time'] = ($endTime === '24:00') ? '00:00' : $endTime;
    }

    public function save(): void
    {
        $this->validate([
            'slots.*.label'         => ['required', 'string', 'max:50'],
            'slots.*.end_time'      => ['required', 'regex:/^\d{2}:\d{2}$/'],
            'slots.*.bg_color'      => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'slots.*.overlay_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6,8}$/'],
        ], [
            'slots.*.label.required'         => 'Label is verplicht.',
            'slots.*.end_time.required'      => 'Eindtijd is verplicht.',
            'slots.*.end_time.regex'         => 'Ongeldig tijdformaat (HH:MM).',
            'slots.*.bg_color.required'      => 'Achtergrondkleur is verplicht.',
            'slots.*.bg_color.regex'         => 'Ongeldig kleurformaat (#RRGGBB).',
            'slots.*.overlay_color.required' => 'Overlay kleur is verplicht.',
            'slots.*.overlay_color.regex'    => 'Ongeldig kleurformaat (#RRGGBB of #RRGGBBAA).',
        ]);

        $count = count($this->slots);

        // Circular cascade: each slot's start = previous slot's end
        for ($i = 0; $i < $count; $i++) {
            $prevIndex = ($i - 1 + $count) % $count;
            $prevEnd = $this->slots[$prevIndex]['end_time'];
            $this->slots[$i]['start_time'] = ($prevEnd === '24:00') ? '00:00' : $prevEnd;
        }

        // Validate each slot has positive duration (handles midnight wrap automatically)
        foreach ($this->slots as $slot) {
            $startMin = $this->timeToMinutes($slot['start_time']);
            $endMin = $slot['end_time'] === '24:00' ? 1440 : $this->timeToMinutes($slot['end_time']);

            // Duration handles wrap: if end <= start, it crosses midnight
            $duration = ($endMin > $startMin)
                ? $endMin - $startMin
                : (1440 - $startMin) + $endMin;

            if ($duration <= 0) {
                $this->addError('slots', $slot['label'] . ': dagdeel heeft geen duur.');
                return;
            }
        }

        // Save
        foreach ($this->slots as $slot) {
            CameraSlotSetting::where('id', $slot['id'])->update([
                'label'         => $slot['label'],
                'start_time'    => $slot['start_time'],
                'end_time'      => $slot['end_time'],
                'bg_color'      => $slot['bg_color'],
                'overlay_color' => $slot['overlay_color'],
                'is_transition' => $slot['is_transition'] ?? false,
            ]);
        }

        CameraSlotSetting::clearCache();

        // Save site settings
        $siteSettings = SiteSetting::first();
        if ($siteSettings) {
            $siteSettings->update([
                'weather_enabled' => $this->weatherEnabled,
            ]);
        }

        session()->flash('status', 'Dagdeel-instellingen opgeslagen.');
    }

    private function timeToMinutes(string $time): int
    {
        return (int) substr($time, 0, 2) * 60 + (int) substr($time, 3, 2);
    }

    public function render()
    {
        return view('livewire.admin.camera-settings')->layout('layouts.admin');
    }
}
