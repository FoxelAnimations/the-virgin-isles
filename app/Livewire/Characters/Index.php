<?php

namespace App\Livewire\Characters;

use App\Models\Character;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.characters.index', [
            'characters' => Character::with('job')->latest()->get(),
        ])->layout('layouts.guest');
    }
}
