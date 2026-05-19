<?php

namespace App\Livewire\Learner;

use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AssessmentPlayer extends Component
{
    public Assessment $assessment;

    public $questions;

    public $currentIndex = 0;

    public $answers = [];

    public ?TestAttempt $currentAttempt = null;

    public bool $isFinished = false;

    public $score = 0;

    public $totalQuestions = 0;

    public function mount(Assessment $assessment)
    {
        $this->assessment = $assessment;
        $this->questions = $this->assessment->questions()->with('choices')->get();
        $this->totalQuestions = $this->questions->count();

        if ($this->totalQuestions === 0) {
            return redirect()->route('learn.courses.show', $this->assessment->course_id);
        }

        $this->startAttempt();
    }

    protected function startAttempt()
    {
        // Check if there's an in-progress attempt
        $this->currentAttempt = TestAttempt::where('user_id', Auth::id())
            ->where('assessment_id', $this->assessment->id)
            ->where('status', TestAttemptStatus::InProgress)
            ->first();

        if (! $this->currentAttempt) {
            // Check max attempts
            $attemptsCount = TestAttempt::where('user_id', Auth::id())
                ->where('assessment_id', $this->assessment->id)
                ->count();

            if ($this->assessment->max_attempts > 0 && $attemptsCount >= $this->assessment->max_attempts) {
                $this->isFinished = true;
                $this->currentAttempt = TestAttempt::where('user_id', Auth::id())
                    ->where('assessment_id', $this->assessment->id)
                    ->orderByDesc('score_pct')
                    ->first();

                return;
            }

            $this->currentAttempt = TestAttempt::create([
                'user_id' => Auth::id(),
                'assessment_id' => $this->assessment->id,
                'attempt_number' => $attemptsCount + 1,
                'status' => TestAttemptStatus::InProgress,
                'started_at' => now(),
            ]);
        } else {
            // Load existing answers
            $existingAnswers = TestAnswer::where('attempt_id', $this->currentAttempt->id)->get();
            foreach ($existingAnswers as $answer) {
                $this->answers[$answer->question_id] = $answer->selected_choice_id;
            }
        }
    }

    public function selectChoice($choiceId)
    {
        $questionId = $this->questions[$this->currentIndex]->id;
        $this->answers[$questionId] = $choiceId;

        // Auto-save answer
        TestAnswer::updateOrCreate(
            [
                'attempt_id' => $this->currentAttempt->id,
                'question_id' => $questionId,
            ],
            [
                'selected_choice_id' => $choiceId,
                'is_correct' => $this->questions[$this->currentIndex]->choices->firstWhere('id', $choiceId)->is_correct,
                'score' => $this->questions[$this->currentIndex]->choices->firstWhere('id', $choiceId)->is_correct ? $this->questions[$this->currentIndex]->points : 0,
            ]
        );
    }

    public function nextQuestion()
    {
        if ($this->currentIndex < $this->totalQuestions - 1) {
            $this->currentIndex++;
        }
    }

    public function prevQuestion()
    {
        if ($this->currentIndex > 0) {
            $this->currentIndex--;
        }
    }

    public function goToQuestion($index)
    {
        if ($index >= 0 && $index < $this->totalQuestions) {
            $this->currentIndex = $index;
        }
    }

    public function finish()
    {
        $totalPoints = $this->questions->sum('points');
        $earnedPoints = TestAnswer::where('attempt_id', $this->currentAttempt->id)->sum('score');
        $scorePct = $totalPoints > 0 ? ($earnedPoints / $totalPoints) * 100 : 0;

        $status = $scorePct >= $this->assessment->passing_score_pct ? TestAttemptStatus::Passed : TestAttemptStatus::Failed;

        $this->currentAttempt->update([
            'total_score' => $earnedPoints,
            'max_score' => $totalPoints,
            'score_pct' => $scorePct,
            'status' => $status,
            'submitted_at' => now(),
        ]);

        $this->score = $scorePct;
        $this->isFinished = true;
    }

    public function render()
    {
        return view('livewire.learner.assessment-player')
            ->title($this->assessment->title);
    }
}
