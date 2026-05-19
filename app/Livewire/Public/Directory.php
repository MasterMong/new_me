<?php

namespace App\Livewire\Public;

use App\Models\Certificate;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Directory extends Component
{
    use WithPagination;

    public function render(): View
    {
        $certificates = Certificate::with(['user', 'course'])
            ->orderByDesc('issued_date')
            ->paginate(12);

        return view('livewire.public.directory', compact('certificates'))
            ->layout('layouts.public', ['title' => 'ทำเนียบนักติดตาม']);
    }
}
