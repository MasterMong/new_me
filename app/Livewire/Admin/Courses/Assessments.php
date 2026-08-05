<?php

namespace App\Livewire\Admin\Courses;

use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;

class Assessments extends Component
{
    use WithFileUploads;

    public Course $course;

    public $assessments;

    // Assessment Form
    public bool $showAssessmentModal = false;

    public ?int $editingAssessmentId = null;

    public string $assessmentTitle = '';

    public string $assessmentType = 'module_test';

    public ?int $assessmentModuleId = null;

    public int $assessmentPassingScore = 60;

    public int $assessmentMaxAttempts = 3;

    public string $assessmentGradingMode = 'auto';

    public bool $assessmentIsTimed = false;

    public ?int $assessmentTimeLimitMinutes = null;

    // Question List
    public $questions = [];

    public bool $showQuestionListModal = false;

    // Question Editor
    public bool $showQuestionFormModal = false;

    public ?int $selectedAssessmentId = null;

    public ?int $editingQuestionId = null;

    public string $questionText = '';

    public string $questionType = 'multiple_choice';

    public int $questionPoints = 1;

    public array $choices = []; // [['text' => '', 'is_correct' => false]]

    public function mount(Course $course)
    {
        $this->course = $course;
        $this->loadAssessments();
    }

    public function loadAssessments()
    {
        $this->assessments = $this->course->assessments()->with('module')->withCount('questions')->get();
    }

    public function openCreateAssessment()
    {
        $this->resetAssessmentForm();
        $this->showAssessmentModal = true;
    }

    public function editAssessment(int $id)
    {
        $assessment = Assessment::findOrFail($id);
        $this->editingAssessmentId = $assessment->id;
        $this->assessmentTitle = $assessment->title;
        $this->assessmentType = $assessment->type->value;
        $this->assessmentModuleId = $assessment->module_id;
        $this->assessmentPassingScore = $assessment->passing_score_pct;
        $this->assessmentMaxAttempts = $assessment->max_attempts;
        $this->assessmentGradingMode = $assessment->grading_mode->value;
        $this->assessmentIsTimed = $assessment->is_timed;
        $this->assessmentTimeLimitMinutes = $assessment->time_limit_minutes;
        $this->showAssessmentModal = true;
    }

    public function saveAssessment()
    {
        $this->validate([
            'assessmentTitle' => 'required|min:3',
            'assessmentType' => ['required', Rule::in(array_column(AssessmentType::cases(), 'value'))],
            'assessmentModuleId' => [
                'nullable',
                Rule::exists('modules', 'id')->where('course_id', $this->course->id),
            ],
            'assessmentPassingScore' => 'required|integer|min:0|max:100',
            'assessmentTimeLimitMinutes' => 'nullable|required_if:assessmentIsTimed,true|integer|min:1|max:600',
        ]);

        $data = [
            'course_id' => $this->course->id,
            'module_id' => $this->assessmentModuleId,
            'title' => $this->assessmentTitle,
            'type' => $this->assessmentType,
            'passing_score_pct' => $this->assessmentPassingScore,
            'max_attempts' => $this->assessmentMaxAttempts,
            'grading_mode' => $this->assessmentGradingMode,
            'is_timed' => $this->assessmentIsTimed,
            'time_limit_minutes' => $this->assessmentIsTimed ? $this->assessmentTimeLimitMinutes : null,
        ];

        if ($this->editingAssessmentId) {
            Assessment::find($this->editingAssessmentId)->update($data);
        } else {
            Assessment::create($data);
        }

        $this->showAssessmentModal = false;
        $this->loadAssessments();
        $this->dispatch('notify', 'Assessment saved successfully.');
    }

    public function deleteAssessment(int $id)
    {
        Assessment::find($id)->delete();
        $this->loadAssessments();
    }

    // Question Logic
    public function manageQuestions(int $assessmentId)
    {
        $this->selectedAssessmentId = $assessmentId;
        $this->loadQuestions();
        $this->showQuestionListModal = true;
    }

    public function loadQuestions()
    {
        if ($this->selectedAssessmentId) {
            $this->questions = Question::where('assessment_id', $this->selectedAssessmentId)
                ->with('choices')
                ->orderBy('sort_order')
                ->get();
        }
    }

    public function openCreateQuestion()
    {
        $this->resetQuestionForm();
        $this->showQuestionFormModal = true;
    }

    public function editQuestion(int $id)
    {
        $question = Question::with('choices')->findOrFail($id);
        $this->editingQuestionId = $question->id;
        $this->questionText = $question->question_text;
        $this->questionType = $question->question_type->value;
        $this->questionPoints = $question->points;

        $this->choices = $question->choices->map(fn ($c) => [
            'id' => $c->id,
            'text' => $c->choice_text,
            'is_correct' => $c->is_correct,
        ])->toArray();

        $this->showQuestionFormModal = true;
    }

    public function saveQuestion()
    {
        $this->validate([
            'questionText' => 'required|min:3',
            'questionPoints' => 'required|integer|min:1',
            'choices.*.text' => 'required_if:questionType,multiple_choice',
        ]);

        $questionData = [
            'assessment_id' => $this->selectedAssessmentId,
            'question_text' => $this->questionText,
            'question_type' => $this->questionType,
            'points' => $this->questionPoints,
            'grading_mode' => $this->questionType === 'multiple_choice' ? 'auto' : 'manual',
            'sort_order' => $this->editingQuestionId ? Question::find($this->editingQuestionId)->sort_order : (Question::where('assessment_id', $this->selectedAssessmentId)->max('sort_order') + 10),
        ];

        if ($this->editingQuestionId) {
            $question = Question::find($this->editingQuestionId);
            $question->update($questionData);
        } else {
            $question = Question::create($questionData);
        }

        // Handle Choices
        if ($this->questionType === 'multiple_choice') {
            // Remove old choices if editing
            if ($this->editingQuestionId) {
                $question->choices()->delete();
            }

            foreach ($this->choices as $index => $choiceData) {
                $question->choices()->create([
                    'choice_text' => $choiceData['text'],
                    'is_correct' => $choiceData['is_correct'],
                    'sort_order' => ($index + 1) * 10,
                ]);
            }
        }

        $this->showQuestionFormModal = false;
        $this->loadQuestions();
        $this->loadAssessments();
        $this->dispatch('notify', 'Question saved successfully.');
    }

    public function deleteQuestion(int $id)
    {
        Question::find($id)->delete();
        $this->loadQuestions();
        $this->loadAssessments();
    }

    public function moveQuestion(int $id, string $direction)
    {
        $questions = Question::where('assessment_id', $this->selectedAssessmentId)->orderBy('sort_order')->get();
        $index = $questions->search(fn ($q) => $q->id === $id);

        if ($index === false) {
            return;
        }

        if ($direction === 'up' && $index > 0) {
            $prev = $questions[$index - 1];
            $curr = $questions[$index];
            $temp = $prev->sort_order;
            $prev->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        } elseif ($direction === 'down' && $index < $questions->count() - 1) {
            $next = $questions[$index + 1];
            $curr = $questions[$index];
            $temp = $next->sort_order;
            $next->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        }

        $this->loadQuestions();
    }

    public function addChoice()
    {
        $this->choices[] = ['text' => '', 'is_correct' => false];
    }

    public function removeChoice(int $index)
    {
        unset($this->choices[$index]);
        $this->choices = array_values($this->choices);
    }

    public function render()
    {
        return view('livewire.admin.courses.assessments', [
            'assessmentTypes' => AssessmentType::cases(),
            'courseModules' => $this->course->modules()->orderBy('sort_order')->get(),
        ])->layout('layouts.app', ['title' => 'จัดการแบบทดสอบ - '.$this->course->title]);
    }

    protected function resetQuestionForm()
    {
        $this->editingQuestionId = null;
        $this->questionText = '';
        $this->questionType = 'multiple_choice';
        $this->questionPoints = 1;
        $this->choices = [
            ['text' => '', 'is_correct' => false],
            ['text' => '', 'is_correct' => false],
        ];
    }

    protected function resetAssessmentForm()
    {
        $this->editingAssessmentId = null;
        $this->assessmentTitle = '';
        $this->assessmentType = 'module_test';
        $this->assessmentModuleId = null;
        $this->assessmentPassingScore = 60;
        $this->assessmentMaxAttempts = 3;
        $this->assessmentGradingMode = 'auto';
        $this->assessmentIsTimed = false;
        $this->assessmentTimeLimitMinutes = null;
    }
}
