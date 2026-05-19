<?php

namespace App\Livewire\Admin\Reporting;

use App\Models\Course;
use App\Models\LearnerGroup;
use Livewire\Component;
use Livewire\WithPagination;

class CourseProgress extends Component
{
    use WithPagination;

    public string $search = '';

    public string $groupId = '';

    public ?int $selectedCourseId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function selectCourse(?int $id): void
    {
        $this->selectedCourseId = $id;
        $this->resetPage();
    }

    public function render()
    {
        if ($this->selectedCourseId) {
            return $this->renderCourseDetails();
        }

        $courses = Course::query()
            ->withCount(['enrollments'])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest()
            ->paginate(12);

        return view('livewire.admin.reporting.course-progress', [
            'courses' => $courses,
        ])->layout('layouts.app', ['title' => 'รายงานความก้าวหน้าตามหลักสูตร']);
    }

    protected function renderCourseDetails()
    {
        $course = Course::with(['modules'])->findOrFail($this->selectedCourseId);

        $enrollments = $course->enrollments()
            ->with(['user.groupMemberships.group'])
            ->when($this->groupId, function ($q) {
                $q->whereHas('user.groupMemberships', fn ($sq) => $sq->where('group_id', $this->groupId));
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.reporting.course-details', [
            'course' => $course,
            'enrollments' => $enrollments,
            'groups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'รายละเอียด: '.$course->title]);
    }
}
