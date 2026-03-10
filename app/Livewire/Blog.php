<?php

namespace App\Livewire;

use App\Models\BlogPost;
use Livewire\Component;

class Blog extends Component
{
    public function render()
    {
        return view('livewire.blog', [
            'posts' => BlogPost::published()->get(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
