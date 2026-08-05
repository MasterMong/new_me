<?php

namespace App\Livewire\Learner;

use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Enums\TestAttemptStatus;
use App\Models\Assessment;
use App\Models\TestAnswer;
use App\Models\TestAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class AssessmentPlayer extends Component
{
    use WithFileUploads;

    public Assessment $assessment;

    public $questions;

    public $currentIndex = 0;

    public $answers = [];

    public array $essayAnswers = [];

    public array $uploadedFiles = [];

    public array $existingFileUrls = [];

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
            // Load existing answers (including previously saved drafts)
            $existingAnswers = TestAnswer::where('attempt_id', $this->currentAttempt->id)->get();
            foreach ($existingAnswers as $answer) {
                $this->answers[$answer->question_id] = $answer->selected_choice_id;
                $this->essayAnswers[$answer->question_id] = $answer->essay_text;
                $this->existingFileUrls[$answer->question_id] = $answer->uploaded_file_url;
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

    public function saveDraft()
    {
        $this->persistAllAnswers();

        $this->dispatch('notify', 'บันทึกคำตอบเรียบร้อยแล้ว คุณสามารถกลับมาทำต่อได้ภายหลัง');
    }

    protected function persistAllAnswers(): void
    {
        foreach ($this->questions as $question) {
            $this->persistAnswer($question);
        }
    }

    protected function persistAnswer($question): void
    {
        match ($question->question_type) {
            QuestionType::MultipleChoice => null, // saved instantly by selectChoice()
            QuestionType::Essay, QuestionType::ShortAnswer => $this->persistTextAnswer($question),
            QuestionType::FileUpload => $this->persistFileAnswer($question),
        };
    }

    protected function persistTextAnswer($question): void
    {
        $text = $this->essayAnswers[$question->id] ?? null;

        if ($text === null || $text === '') {
            return;
        }

        TestAnswer::updateOrCreate(
            ['attempt_id' => $this->currentAttempt->id, 'question_id' => $question->id],
            ['essay_text' => $text]
        );
    }

    protected function persistFileAnswer($question): void
    {
        $file = $this->uploadedFiles[$question->id] ?? null;

        if (! $file) {
            return;
        }

        $this->validate([
            'uploadedFiles.'.$question->id => 'file|max:10240',
        ]);

        $path = $file->store('worksheets/'.$this->currentAttempt->id, 'public');

        TestAnswer::updateOrCreate(
            ['attempt_id' => $this->currentAttempt->id, 'question_id' => $question->id],
            ['uploaded_file_url' => Storage::disk('public')->url($path)]
        );

        $this->existingFileUrls[$question->id] = Storage::disk('public')->url($path);
        unset($this->uploadedFiles[$question->id]);
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
        $this->persistAllAnswers();

        $totalPoints = $this->questions->sum('points');
        $requiresExpertReview = $this->questions->contains(fn ($q) => $q->grading_mode === GradingMode::Manual);

        if ($requiresExpertReview) {
            $this->currentAttempt->update([
                'max_score' => $totalPoints,
                'status' => TestAttemptStatus::PendingReview,
                'submitted_at' => now(),
            ]);

            $this->score = null;
            $this->isFinished = true;

            return;
        }

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
