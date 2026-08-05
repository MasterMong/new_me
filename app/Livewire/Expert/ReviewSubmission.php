<?php

namespace App\Livewire\Expert;

use App\Models\ExpertReview;
use App\Models\TestAttempt;
use App\Notifications\ExpertReviewCompleted;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
class ReviewSubmission extends Component
{
    public TestAttempt $attempt;

    #[Validate('required|in:passed,revision_needed')]
    public string $status = '';

    #[Validate('nullable|numeric|min:0')]
    public ?float $score = null;

    #[Validate('required|string|min:5')]
    public string $feedback = '';

    public function mount(TestAttempt $attempt)
    {
        $this->attempt = $attempt->load(['user', 'assessment.module', 'answers.question']);

        // Prevent accessing attempts that are not ready for review
        abort_unless(in_array($this->attempt->status->value, ['pending_review', 'passed', 'failed', 'revision_needed']), 403);

        $module = $this->attempt->assessment->module;
        abort_unless(! $module || $module->isAssignedTo(auth()->user()), 403, 'คุณไม่ได้รับมอบหมายให้ตรวจโมดูลนี้');

        if ($this->attempt->expertReview) {
            $this->status = $this->attempt->expertReview->status->value;
            $this->score = $this->attempt->expertReview->score;
            $this->feedback = $this->attempt->expertReview->feedback;
        }
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
