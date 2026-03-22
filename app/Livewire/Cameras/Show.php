<?php

namespace App\Livewire\Cameras;

use App\Models\Camera;
use Livewire\Component;

class Show extends Component
{
    public Camera $camera;

    public function mount(Camera $camera): void
    {
        if ($camera->is_hidden) {
            abort(404);
        }

        $this->camera = $camera;
    }

    public function render()
    {
        return view('livewire.cameras.show')
            ->layoutData(['bgClass' => 'bg-black']);
    }
}
