<?php

namespace App\Livewire\Learner;

use App\Models\Enrollment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('ผลการเรียนรู้')]
class Results extends Component
{
    public function downloadPdf($enrollmentId)
    {
        $enrollment = Enrollment::with([
            'course.modules.assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            },
            'course.assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            },
        ])
            ->where('user_id', Auth::id())
            ->findOrFail($enrollmentId);

        $pdf = Pdf::loadView('pdf.learner-result', ['enrollment' => $enrollment, 'user' => Auth::user()]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, 'ผลการเรียนรู้_'.$enrollment->course->title.'.pdf');
    }

    public function render()
    {
        $enrollments = Enrollment::with([
            'course.modules.assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            },
            'course.assessments.attempts' => function ($query) {
                $query->where('user_id', Auth::id());
            },
        ])
            ->where('user_id', Auth::id())
            ->latest('enrolled_at')
            ->get();

        return view('livewire.learner.results', compact('enrollments'));
    }
}
