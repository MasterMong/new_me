<?php

namespace App\Livewire\Public\Courses;

use App\Models\Course;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $courses = Course::published()
            ->withCount('enrollments')
            ->withAvg('reviews', 'rating')
            ->with('modules')
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.public.courses.index', compact('courses'))
            ->layout('layouts.public', ['title' => 'หลักสูตรทั้งหมด']);
    }
}
