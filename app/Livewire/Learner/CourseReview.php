<?php

namespace App\Livewire\Learner;

use App\Models\Course;
use App\Models\CourseReview as ReviewModel;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('แบบประเมินความพึงพอใจ')]
class CourseReview extends Component
{
    public Course $course;

    public int $rating = 5;

    public string $comment = '';

    public function mount(Course $course)
    {
        $this->course = $course;

        // Redirect if already reviewed
        if (ReviewModel::where('user_id', Auth::id())->where('course_id', $this->course->id)->exists()) {
            $this->redirectRoute('learn.certificates.index');
        }
    }

    public function submitReview()
    {
        $this->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        ReviewModel::create([
            'user_id' => Auth::id(),
            'course_id' => $this->course->id,
            'rating' => $this->rating,
            'comment' => $this->comment,
        ]);

        $this->dispatch('toast', message: 'บันทึกแบบประเมินเรียบร้อยแล้ว ขอบคุณสำหรับความคิดเห็น', type: 'success');

        return redirect()->route('learn.certificates.index');
    }

    public function setRating($value)
    {
        $this->rating = $value;
    }

    public function render()
    {
        return view('livewire.learner.course-review');
    }
}
