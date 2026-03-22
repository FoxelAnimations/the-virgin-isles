<?php

namespace App\Livewire\Admin;

use App\Models\Episode;
use App\Models\EpisodeView;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class EpisodeAnalytics extends Component
{
    public string $period = '30';

    public function render()
    {
        $days = (int) $this->period;

        // Summary cards (single query)
        $summary = DB::table('episode_views')->selectRaw("
            COUNT(*) as total_views,
            COUNT(DISTINCT hashed_ip) as unique_viewers,
            SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as views_today,
            SUM(CASE WHEN viewed_at >= ? THEN 1 ELSE 0 END) as views_this_week
        ", [now()->startOfDay(), now()->subDays(7)])->first();

        $totalViews = (int) $summary->total_views;
        $uniqueViewers = (int) $summary->unique_viewers;
        $viewsToday = (int) $summary->views_today;
        $viewsThisWeek = (int) $summary->views_this_week;

        // Views per day (last N days)
        $viewsPerDay = EpisodeView::where('viewed_at', '>=', now()->subDays($days))
            ->select(DB::raw('DATE(viewed_at) as date'), DB::raw('COUNT(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('count', 'date')
            ->toArray();

        // Fill missing days with 0
        $filledDays = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $filledDays[$date] = $viewsPerDay[$date] ?? 0;
        }

        // Top 10 episodes by views
        $topEpisodes = Episode::withCount('views')
            ->withAvg('ratings', 'rating')
            ->withCount(['views as unique_viewers' => function ($q) {
                $q->select(DB::raw('COUNT(DISTINCT hashed_ip)'));
            }])
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        // Views by category
        $viewsByCategory = Episode::join('episode_views', 'episodes.id', '=', 'episode_views.episode_id')
            ->select('episodes.category', DB::raw('COUNT(episode_views.id) as view_count'))
            ->groupBy('episodes.category')
            ->orderByDesc('view_count')
            ->get();

        // Device breakdown
        $devices = EpisodeView::whereNotNull('device_type')
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();

        // Peak hours (0-23)
        $peakHours = EpisodeView::select(
                DB::raw('HOUR(viewed_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->pluck('count', 'hour')
            ->toArray();

        // Fill all 24 hours
        $filledHours = [];
        for ($h = 0; $h < 24; $h++) {
            $filledHours[$h] = $peakHours[$h] ?? 0;
        }

        return view('livewire.admin.episode-analytics', [
            'totalViews' => $totalViews,
            'uniqueViewers' => $uniqueViewers,
            'viewsToday' => $viewsToday,
            'viewsThisWeek' => $viewsThisWeek,
            'viewsPerDay' => $filledDays,
            'topEpisodes' => $topEpisodes,
            'viewsByCategory' => $viewsByCategory,
            'devices' => $devices,
            'peakHours' => $filledHours,
        ])->layout('layouts.admin');
    }
}
