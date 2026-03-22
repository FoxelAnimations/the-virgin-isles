<?php

namespace App\Http\Controllers;

use App\Models\Episode;
use App\Models\EpisodeView;
use App\Services\BeaconScanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EpisodeViewController extends Controller
{
    public function __construct(
        private BeaconScanService $scanService,
    ) {}

    public function store(Request $request, Episode $episode)
    {
        EpisodeView::create([
            'episode_id' => $episode->id,
            'user_id' => Auth::id(),
            'hashed_ip' => $this->scanService->hashIp($request->ip()),
            'user_agent' => Str::limit($request->userAgent() ?? '', 500),
            'device_type' => EpisodeView::detectDeviceType($request->userAgent()),
            'viewed_at' => now(),
        ]);

        return response()->noContent();
    }
}
