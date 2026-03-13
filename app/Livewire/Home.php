<?php

namespace App\Livewire;

use App\Models\AgeGate;
use App\Models\Collab;
use App\Models\Quote;
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
    protected function getRandomQuote()
    {
        $lastQuoteId = session('last_quote_id');

        $query = Quote::with(['character.job', 'character.socialLinks'])->active();

        if ($lastQuoteId) {
            $quote = $query->where('id', '!=', $lastQuoteId)->inRandomOrder()->first();

            // Fall back to the last quote if it's the only active one
            if (!$quote) {
                $quote = Quote::with(['character.job', 'character.socialLinks'])->active()->inRandomOrder()->first();
            }
        } else {
            $quote = $query->inRandomOrder()->first();
        }

        if ($quote) {
            session(['last_quote_id' => $quote->id]);
        }

        return $quote;
    }

    public function render()
    {
        $settings = SiteSetting::first();

        return view('livewire.home', [
            'characters' => Character::with(['job', 'socialLinks'])
                ->whereNotNull('full_body_image_path')
                ->where('show_in_slider', true)
                ->orderBy('sort_order')
                ->get(),
            'carouselTitle' => $settings?->carousel_title,
            'heroVideo' => HeroVideo::latest()->first(),
            'heroContent' => HeroContent::first(),
            'latestEpisodes' => ($settings?->show_episodes ?? true) ? Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'episode')->where('visible', true)->latest()->take(5)->get() : collect(),
            'latestShorts' => ($settings?->show_shorts ?? true) ? Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'short')->where('visible', true)->latest()->take(5)->get() : collect(),
            'latestMinis' => ($settings?->show_minis ?? true) ? Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'mini')->where('visible', true)->latest()->take(5)->get() : collect(),
            'latestSpecials' => ($settings?->show_specials ?? true) ? Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'special')->where('visible', true)->latest()->take(5)->get() : collect(),
            'socialLinks' => SocialLink::whereNotNull('url')->where('url', '!=', '')->orderBy('sort_order')->get(),
            'blocksAbove' => ContentBlock::active()->aboveEpisodes()->get(),
            'blocksBelow' => ContentBlock::active()->belowEpisodes()->get(),
            'collabLogos' => ($settings?->show_collabs ?? false) ? Collab::where('show_on_homepage', true)->where('is_published', true)->where('is_visible', true)->whereNotNull('logo_image')->orderBy('sort_order')->get() : collect(),
            'showCollabs' => $settings?->show_collabs ?? false,
            'randomQuote' => ($settings?->show_quotes ?? true) ? $this->getRandomQuote() : null,
            'ageGate' => AgeGate::first(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
