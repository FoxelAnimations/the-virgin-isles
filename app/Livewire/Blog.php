<?php

namespace App\Livewire;

use App\Models\ContentBlock;
use Livewire\Component;

class Blog extends Component
{
    public function render()
    {
        return view('livewire.blog', [
            'contentBlocks' => ContentBlock::active()->forBlog()->get(),
        ])->layoutData(['bgClass' => 'bg-black']);
    }
}
