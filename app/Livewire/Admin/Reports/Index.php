<?php

namespace App\Livewire\Admin\Reports;

use App\Enums\EnrollmentStatus;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\ExpertReview;
use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.reports.index', [
            'totalUsers' => User::count(),
            'totalEnrollments' => Enrollment::count(),
            'totalCertificates' => Certificate::count(),
            'pendingReviews' => ExpertReview::where('status', 'pending')->count(),
            'courseStats' => $this->courseStats(),
        ])->layout('layouts.app', ['title' => 'รายงาน']);
    }

    public function exportCsv()
    {
        $courseStats = $this->courseStats();

        return response()->streamDownload(function () use ($courseStats) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Thai text displays correctly when opened in Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['หลักสูตร', 'ลงทะเบียนแล้ว', 'สำเร็จการศึกษา', 'ได้รับเกียรติบัตร', 'อัตราสำเร็จ (%)']);

            foreach ($courseStats as $row) {
                fputcsv($handle, [
                    $row['title'],
                    $row['enrollments'],
                    $row['completed'],
                    $row['certificates'],
                    $row['rate'],
                ]);
            }

            fclose($handle);
        }, 'course-completion-report-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    protected function courseStats()
    {
        return Course::query()
            ->withCount(['enrollments', 'certificates'])
            ->with('enrollments')
            ->get()
            ->map(function (Course $course) {
                $completed = $course->enrollments
                    ->whereIn('status', [EnrollmentStatus::Completed, EnrollmentStatus::Certified])
                    ->count();

                return [
                    'title' => $course->title,
                    'thumbnail_url' => $course->thumbnail_url,
                    'enrollments' => $course->enrollments_count,
                    'completed' => $completed,
                    'certificates' => $course->certificates_count,
                    'rate' => $course->enrollments_count > 0
                        ? round($completed / $course->enrollments_count * 100)
                        : 0,
                ];
            });
    }
}
