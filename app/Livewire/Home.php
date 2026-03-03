<?php

namespace App\Livewire;

use App\Models\AgeGate;
use App\Models\Episode;
use App\Models\HeroContent;
use App\Models\HeroVideo;
use App\Models\ContentBlock;
use App\Models\Character;
use App\Models\SocialLink;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'characters' => Character::whereNotNull('full_body_image_path')
                ->orderBy('sort_order')
                ->get(['id', 'first_name', 'last_name', 'full_body_image_path']),
            'heroVideo' => HeroVideo::latest()->first(),
            'heroContent' => HeroContent::first(),
            'latestEpisodes' => Episode::with('characters')->latest()->take(5)->get(),
            'socialLinks' => SocialLink::whereNotNull('url')->where('url', '!=', '')->orderBy('sort_order')->get(),
            'contentBlocks' => ContentBlock::active()->forHome()->get(),
            'ageGate' => AgeGate::first(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
