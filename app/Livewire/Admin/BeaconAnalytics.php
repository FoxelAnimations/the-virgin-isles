<?php

namespace App\Livewire\Admin;

use App\Models\Beacon;
use App\Models\BeaconScan;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BeaconAnalytics extends Component
{
    public string $period = '30';

    public function render()
    {
        $days = (int) $this->period;

        $totalScans7 = BeaconScan::where('scanned_at', '>=', now()->subDays(7))->count();
        $totalScans30 = BeaconScan::where('scanned_at', '>=', now()->subDays(30))->count();
        $totalScans90 = BeaconScan::where('scanned_at', '>=', now()->subDays(90))->count();

        // Scans per day (last N days)
        $scansPerDay = BeaconScan::where('scanned_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(scanned_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing days with 0
        $filledDays = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $filledDays[$date] = $scansPerDay[$date] ?? 0;
        }

        // Top 10 beacons by scans (last 30 days)
        $topBeacons = BeaconScan::where('scanned_at', '>=', now()->subDays(30))
            ->whereNotNull('beacon_id')
            ->select('beacon_id', DB::raw('COUNT(*) as scan_count'))
            ->groupBy('beacon_id')
            ->orderByDesc('scan_count')
            ->limit(10)
            ->get();

        // Eager-load the beacon relationship after grouping
        $beaconIds = $topBeacons->pluck('beacon_id')->filter();
        $beaconsMap = Beacon::whereIn('id', $beaconIds)->get()->keyBy('id');
        $topBeacons->each(function ($item) use ($beaconsMap) {
            $item->setRelation('beacon', $beaconsMap->get($item->beacon_id));
        });

        // Top referrers (last 30 days)
        $topReferrers = BeaconScan::where('scanned_at', '>=', now()->subDays(30))
            ->whereNotNull('referrer')
            ->where('referrer', '!=', '')
            ->select('referrer', DB::raw('COUNT(*) as count'))
            ->groupBy('referrer')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Device breakdown (last 30 days)
        $allScans30 = BeaconScan::where('scanned_at', '>=', now()->subDays(30))->get(['user_agent']);
        $devices = ['mobile' => 0, 'desktop' => 0, 'tablet' => 0];
        foreach ($allScans30 as $scan) {
            $type = $scan->device_type;
            $devices[$type] = ($devices[$type] ?? 0) + 1;
        }

        // Known vs unknown breakdown
        $knownCount = BeaconScan::where('scanned_at', '>=', now()->subDays(30))->where('is_known', true)->count();
        $unknownCount = BeaconScan::where('scanned_at', '>=', now()->subDays(30))->where('is_known', false)->count();

        return view('livewire.admin.beacon-analytics', [
            'totalScans7' => $totalScans7,
            'totalScans30' => $totalScans30,
            'totalScans90' => $totalScans90,
            'scansPerDay' => $filledDays,
            'topBeacons' => $topBeacons,
            'topReferrers' => $topReferrers,
            'devices' => $devices,
            'knownCount' => $knownCount,
            'unknownCount' => $unknownCount,
        ])->layout('layouts.admin');
    }
}
