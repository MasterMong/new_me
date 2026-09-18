<?php

namespace App\Livewire\Expert;

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Models\Module;
use App\Models\TestAttempt;
use App\Models\User;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class IndividualReport extends Component
{
    public User $user;

    public function mount(User $user)
    {
        abort_unless($user->role === UserRole::Learner, 404);
        $this->user = $user;
    }

    public function render()
    {
        return view('livewire.expert.individual-report', [
            'modules' => Module::where('requires_expert_review', true)->get(),
            'attempts' => $this->attempts(),
        ]);
    }

    public function exportCsv()
    {
        $attempts = $this->attempts();
        $statusLabels = [
            TestAttemptStatus::PendingReview->value => 'รอตรวจ',
            TestAttemptStatus::Passed->value => 'ผ่าน',
            TestAttemptStatus::RevisionNeeded->value => 'รอแก้ไข',
            TestAttemptStatus::Failed->value => 'ไม่ผ่าน',
            TestAttemptStatus::Submitted->value => 'ส่งแล้ว',
            TestAttemptStatus::InProgress->value => 'กำลังทำ',
        ];
        $typeLabels = [
            AssessmentType::PreTest->value => 'แบบทดสอบก่อนเรียน',
            AssessmentType::PostTest->value => 'แบบทดสอบหลังเรียน',
            AssessmentType::ModuleTest->value => 'แบบทดสอบประจำโมดูล',
            AssessmentType::Assignment->value => 'ใบงาน',
        ];

        return response()->streamDownload(function () use ($attempts, $statusLabels, $typeLabels) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM so Thai text displays correctly when opened in Excel
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['วันที่ส่ง', 'โมดูล', 'ประเภท', 'ครั้งที่', 'คะแนน', 'คะแนนเต็ม', 'สถานะ']);

            foreach ($attempts as $attempt) {
                fputcsv($handle, [
                    $attempt->submitted_at?->format('d/m/Y H:i') ?? '-',
                    $attempt->assessment->module->title,
                    $typeLabels[$attempt->assessment->type->value] ?? $attempt->assessment->type->value,
                    $attempt->attempt_number,
                    $attempt->total_score ?? '-',
                    $attempt->max_score ?? '-',
                    $statusLabels[$attempt->status->value] ?? $attempt->status->value,
                ]);
            }

            fclose($handle);
        }, 'ผลการเรียน-'.str_replace(' ', '_', $this->user->fullName()).'-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Test attempts for this learner across every module the current expert
     * is responsible for (pre-test, post-test, and worksheet/assignment).
     */
    protected function attempts(): Collection
    {
        $moduleIds = Module::where('requires_expert_review', true)->pluck('id');

        return TestAttempt::with(['assessment.module', 'expertReview'])
            ->where('user_id', $this->user->id)
            ->whereHas('assessment', function ($query) use ($moduleIds) {
                $query->whereIn('module_id', $moduleIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
