<?php

namespace App\Livewire\Admin;

use App\Models\Certificate;
use App\Services\CertificatePdfService;
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

    public function regeneratePdf(int $certificateId): void
    {
        $certificate = Certificate::findOrFail($certificateId);

        try {
            app(CertificatePdfService::class)->generate($certificate);
            $this->dispatch('toast', message: 'สร้างไฟล์ PDF ใหม่เรียบร้อยแล้ว', type: 'success');
        } catch (\Throwable $e) {
            report($e);
            $this->dispatch('toast', message: 'ไม่สามารถสร้างไฟล์ PDF ได้ กรุณาลองใหม่อีกครั้ง', type: 'error');
        }
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
