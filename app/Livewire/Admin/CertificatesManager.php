<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use Livewire\Component;
use Livewire\WithPagination;

class CertificatesManager extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $certificates = Certificate::query()
            ->with(['user', 'course'])
            ->when($this->search, function ($q) {
                $q->where('certificate_number', 'like', "%{$this->search}%")
                    ->orWhere('full_name_on_cert', 'like', "%{$this->search}%")
                    ->orWhereHas('course', function ($q) {
                        $q->where('title', 'like', "%{$this->search}%");
                    });
            })
            ->latest('issued_date')
            ->paginate(20);

        return view('livewire.admin.certificates-manager', [
            'certificates' => $certificates,
        ])->layout('layouts.app', ['title' => 'จัดการเกียรติบัตร']);
    }
}
