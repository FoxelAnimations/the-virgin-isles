<?php

namespace App\Livewire;

use App\Models\AgeGate;
use App\Models\Episode;
use App\Models\HeroContent;
use App\Models\HeroVideo;
use App\Models\ContentBlock;
use App\Models\Character;
use App\Models\SiteSetting;
use App\Models\SocialLink;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        $settings = SiteSetting::first();

        return view('livewire.home', [
            'characters' => Character::with(['job', 'socialLinks'])
                ->whereNotNull('full_body_image_path')
                ->orderBy('sort_order')
                ->get(),
            'carouselTitle' => $settings?->carousel_title,
            'heroVideo' => HeroVideo::latest()->first(),
            'heroContent' => HeroContent::first(),
            'latestEpisodes' => ($settings?->show_episodes ?? true) ? Episode::with('characters')->where('category', 'episode')->where('visible', true)->latest()->take(5)->get() : collect(),
            'latestShorts' => ($settings?->show_shorts ?? true) ? Episode::with('characters')->where('category', 'short')->where('visible', true)->latest()->take(5)->get() : collect(),
            'latestMinis' => ($settings?->show_minis ?? true) ? Episode::with('characters')->where('category', 'mini')->where('visible', true)->latest()->take(5)->get() : collect(),
            'socialLinks' => SocialLink::whereNotNull('url')->where('url', '!=', '')->orderBy('sort_order')->get(),
            'contentBlocks' => ContentBlock::active()->forHome()->get(),
            'ageGate' => AgeGate::first(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
