<?php

namespace App\Livewire;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UserDashboard extends Component
{
    public string $userName = '';
    public bool $editingName = false;

    public function mount(): void
    {
        $this->userName = Auth::user()->name;
    }

    public function startEditingName(): void
    {
        $this->editingName = true;
    }

    public function cancelEditingName(): void
    {
        $this->userName = Auth::user()->name;
        $this->editingName = false;
    }

    public function saveName(): void
    {
        $this->validate([
            'userName' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->update(['name' => $this->userName]);
        $this->editingName = false;
    }

    public function dismissNews(): void
    {
        Auth::user()->update(['news_dismissed_at' => now()]);
    }

    public function render()
    {
        $user = Auth::user();

        $collectedBeacons = $user
            ->collectedBeacons()
            ->where('is_collectible', true)
            ->orderByPivot('collected_at', 'desc')
            ->get();

        $earnedBadges = $user
            ->badges()
            ->orderByPivot('collected_at', 'desc')
            ->get();

        $settings = SiteSetting::first();
        $newsItems = $settings?->dashboard_news_items ?? [];
        $showNews = false;

        if (!empty($newsItems)) {
            $showNews = is_null($user->news_dismissed_at)
                || ($settings->dashboard_news_updated_at && $settings->dashboard_news_updated_at->gt($user->news_dismissed_at));
        }

        return view('livewire.user-dashboard', [
            'collectedBeacons' => $collectedBeacons,
            'earnedBadges' => $earnedBadges,
            'welcomeTitle' => $settings?->dashboard_welcome_title,
            'welcomeText' => $settings?->dashboard_welcome_text,
            'newsItems' => $newsItems,
            'showNews' => $showNews,
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
