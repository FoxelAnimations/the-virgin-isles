<?php

namespace App\Livewire\Episodes;

use App\Models\AgeGate;
use App\Models\Episode;
use Livewire\Component;

class Show extends Component
{
    public function render()
    {
        return view('livewire.episodes.show', [
            'episodes' => Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'episode')->where('visible', true)->orderBy('sort_order')->get(),
            'shorts' => Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'short')->where('visible', true)->orderBy('sort_order')->get(),
            'minis' => Episode::with(['characters.job', 'characters.socialLinks'])->where('category', 'mini')->where('visible', true)->orderBy('sort_order')->get(),
            'ageGate' => AgeGate::first(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
