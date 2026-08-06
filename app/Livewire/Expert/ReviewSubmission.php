<?php

namespace App\Livewire\Expert;

use App\Models\Enrollment;
use App\Models\ExpertReview;
use App\Models\TestAttempt;
use App\Notifications\ExpertReviewCompleted;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReviewSubmission extends Component
{
    public TestAttempt $attempt;

    public Collection $previousAttempts;

    public string $status = '';

    public ?float $score = null;

    public string $feedback = '';

    protected function rules(): array
    {
        return [
            'status' => 'required|in:passed,revision_needed',
            'score' => $this->attempt->max_score !== null
                ? 'nullable|numeric|min:0|max:'.$this->attempt->max_score
                : 'nullable|numeric|min:0',
            'feedback' => 'required|string|min:5',
        ];
    }

    public function mount(TestAttempt $attempt): void
    {
        $this->attempt = $attempt->load([
            'user.position', 'user.affiliation',
            'assessment.module', 'answers.question',
        ]);

        // Prevent accessing attempts that are not ready for review
        abort_unless(in_array($this->attempt->status->value, ['pending_review', 'passed', 'failed', 'revision_needed']), 403);

        $module = $this->attempt->assessment->module;
        abort_unless(! $module || $module->isAssignedTo(auth()->user()), 403, 'คุณไม่ได้รับมอบหมายให้ตรวจโมดูลนี้');

        if ($this->attempt->expertReview) {
            $this->status = $this->attempt->expertReview->status->value;
            $this->score = $this->attempt->expertReview->score;
            $this->feedback = $this->attempt->expertReview->feedback ?? '';
        }

        $this->previousAttempts = TestAttempt::where('user_id', $this->attempt->user_id)
            ->where('assessment_id', $this->attempt->assessment_id)
            ->where('id', '!=', $this->attempt->id)
            ->with('expertReview')
            ->orderByDesc('attempt_number')
            ->get();
    }

    public function submitReview()
    {
        $this->validate();

        $review = ExpertReview::updateOrCreate(
            ['attempt_id' => $this->attempt->id],
            [
                'expert_id' => auth()->id(),
                'status' => $this->status,
                'score' => $this->score,
                'feedback' => $this->feedback,
                'reviewed_at' => now(),
            ]
        );

        // Update the overall attempt status
        $this->attempt->update([
            'status' => $this->status === 'passed' ? 'passed' : 'revision_needed',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'total_score' => $this->score,
            'score_pct' => $this->score !== null && $this->attempt->max_score > 0
                ? ($this->score / $this->attempt->max_score) * 100
                : null,
        ]);

        // Send Notification to User
        $this->attempt->user->notify(new ExpertReviewCompleted($review));

        if ($this->status === 'passed') {
            Enrollment::where('user_id', $this->attempt->user_id)
                ->where('course_id', $this->attempt->assessment->course_id)
                ->first()
                ?->issueCertificateIfEligible();
        }

        session()->flash('toast', [
            'type' => 'success',
            'message' => 'บันทึกผลการตรวจเรียบร้อยแล้ว',
        ]);

        return $this->redirectRoute('expert.submissions.index', $this->attempt->assessment->module_id, navigate: true);
    }

    public function render()
    {
        return view('livewire.expert.review-submission');
    }
}
