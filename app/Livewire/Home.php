<?php

namespace App\Livewire;

use App\Models\Character;
use App\Models\Episode;
use App\Models\HeroContent;
use App\Models\HeroVideo;
use App\Models\SocialLink;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'characters' => Character::with('job')->orderBy('sort_order')->get(),
            'heroVideo' => HeroVideo::latest()->first(),
            'heroContent' => HeroContent::first(),
            'latestEpisodes' => Episode::with('characters')->orderBy('sort_order')->take(4)->get(),
            'socialLinks' => SocialLink::whereNotNull('url')->where('url', '!=', '')->orderBy('sort_order')->get(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
