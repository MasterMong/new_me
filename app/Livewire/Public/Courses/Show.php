<?php

namespace App\Livewire\Public\Courses;

use App\Enums\EnrollmentStatus;
use App\Models\Course;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Course $course;

    public function mount(Course $course): void
    {
        $this->course = $course;

        abort_unless($course->isVisibleTo(auth()->user()), 403);
    }

    public function render(): View
    {
        $course = $this->course->load([
            'modules.contents',
            'reviews.user',
            'enrollments',
        ]);

        $avgRating = $course->reviews->avg('rating');
        $enrollmentCount = $course->enrollments->count();
        $totalDurationMinutes = $course->modules->sum(function ($module) {
            return $module->contents->where('content_type', 'video')->sum('duration_minutes');
        });

        $isEnrolled = auth()->check() && $course->enrollments()->where('user_id', auth()->id())->exists();

        return view('livewire.public.courses.show', compact('course', 'avgRating', 'enrollmentCount', 'totalDurationMinutes', 'isEnrolled'))
            ->layout('layouts.public', ['title' => $course->title]);
    }

    public function enroll()
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        abort_unless($this->course->isVisibleTo(auth()->user()), 403);

        $this->course->enrollments()->firstOrCreate([
            'user_id' => auth()->id(),
        ], [
            'enrolled_at' => now(),
            'status' => EnrollmentStatus::InProgress->value,
        ]);

        return redirect()->route('learn.courses.show', $this->course);
    }
}
