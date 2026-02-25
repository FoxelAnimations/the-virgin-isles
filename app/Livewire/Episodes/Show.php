<?php

namespace App\Livewire\Episodes;

use App\Models\Episode;
use Livewire\Component;

class Show extends Component
{
    public function render()
    {
        return view('livewire.episodes.show', [
            'episodes' => Episode::with('characters.job')->orderBy('sort_order')->get(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
