<?php

namespace App\Livewire\Admin\Courses;

use App\Models\Course;
use Livewire\Component;

class Edit extends Component
{
    public Course $course;

    public string $title = '';

    public string $description = '';

    public ?string $durationHours = null;

    public int $passingScorePct = 70;

    public bool $hasTest = false;

    public bool $requireReview = false;

    public bool $isPublished = false;

    public function mount(Course $course): void
    {
        $this->course = $course;
        $this->title = $course->title;
        $this->description = $course->description ?? '';
        $this->durationHours = $course->duration_hours ? (string) $course->duration_hours : null;
        $this->passingScorePct = $course->passing_score_pct;
        $this->hasTest = $course->has_test;
        $this->requireReview = $course->require_review;
        $this->isPublished = $course->is_published;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'durationHours' => ['nullable', 'numeric', 'min:0.5', 'max:9999'],
            'passingScorePct' => ['required', 'integer', 'min:0', 'max:100'],
            'hasTest' => ['boolean'],
            'requireReview' => ['boolean'],
            'isPublished' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'กรุณากรอกชื่อคอร์ส',
            'title.max' => 'ชื่อคอร์สต้องไม่เกิน 255 ตัวอักษร',
            'durationHours.numeric' => 'ระยะเวลาต้องเป็นตัวเลข',
            'durationHours.min' => 'ระยะเวลาต้องมากกว่า 0',
            'passingScorePct.required' => 'กรุณากรอกเกณฑ์ผ่าน',
            'passingScorePct.min' => 'เกณฑ์ผ่านต้องอยู่ระหว่าง 0-100',
            'passingScorePct.max' => 'เกณฑ์ผ่านต้องอยู่ระหว่าง 0-100',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $this->course->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'duration_hours' => $this->durationHours ?: null,
            'passing_score_pct' => $this->passingScorePct,
            'has_test' => $this->hasTest,
            'require_review' => $this->hasTest && $this->requireReview,
            'is_published' => $this->isPublished,
        ]);

        session()->flash('status', 'บันทึกคอร์สเรียบร้อยแล้ว');

        $this->redirect(route('admin.courses.index'), navigate: true);
    }

    public function delete(): void
    {
        $this->course->delete();

        session()->flash('status', 'ลบคอร์สเรียบร้อยแล้ว');

        $this->redirect(route('admin.courses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.courses.edit', [
            'enrollmentCount' => $this->course->enrollments()->count(),
            'moduleCount' => $this->course->modules()->count(),
        ])->layout('layouts.app', ['title' => 'แก้ไขคอร์ส']);
    }
}
