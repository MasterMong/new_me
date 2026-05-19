<?php

namespace App\Livewire\Admin\Courses;

use App\Models\Course;
use Livewire\Component;

class Create extends Component
{
    public string $title = '';

    public string $description = '';

    public ?string $durationHours = null;

    public int $passingScorePct = 70;

    public bool $hasTest = false;

    public bool $requireReview = false;

    public bool $isPublished = false;

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

        Course::create([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'duration_hours' => $this->durationHours ?: null,
            'passing_score_pct' => $this->passingScorePct,
            'has_test' => $this->hasTest,
            'require_review' => $this->hasTest && $this->requireReview,
            'is_published' => $this->isPublished,
            'created_by' => auth()->id(),
        ]);

        session()->flash('status', 'สร้างคอร์สใหม่เรียบร้อยแล้ว');

        $this->redirect(route('admin.courses.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.courses.create')
            ->layout('layouts.app', ['title' => 'เพิ่มคอร์สใหม่']);
    }
}
