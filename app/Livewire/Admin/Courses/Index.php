<?php

namespace App\Livewire\Admin\Courses;

use App\Models\Course;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function togglePublish(int $courseId): void
    {
        $course = Course::findOrFail($courseId);
        $course->update(['is_published' => ! $course->is_published]);
    }

    public function deleteCourse(int $courseId): void
    {
        Course::findOrFail($courseId)->delete();
    }

    public function render()
    {
        $courses = Course::query()
            ->with('creator')
            ->withCount('enrollments')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->statusFilter !== '', fn ($q) => $q->where('is_published', (bool) $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.admin.courses.index', ['courses' => $courses])
            ->layout('layouts.app', ['title' => 'จัดการคอร์ส']);
    }
}
