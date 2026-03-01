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
            'episodes' => Episode::with('characters.job')->orderBy('sort_order')->get(),
            'ageGate' => AgeGate::first(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
