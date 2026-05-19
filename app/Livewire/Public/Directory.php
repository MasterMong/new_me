<?php

namespace App\Livewire\Public;

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.guest')]
#[Title('ทำเนียบนักติดตาม - ME-Learning')]
class Directory extends Component
{
    public function render()
    {
        return view('livewire.public.directory');
    }
}
