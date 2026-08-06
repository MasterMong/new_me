<?php

namespace App\Livewire\Admin\Courses;

use App\Livewire\Concerns\CleansUpPublicStorage;
use App\Models\Course;
use App\Models\CourseGroupAccess;
use App\Models\CourseImage;
use App\Models\LearnerGroup;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Edit extends Component
{
    use CleansUpPublicStorage, WithFileUploads;

    public Course $course;

    public string $title = '';

    public string $description = '';

    public ?string $durationHours = null;

    public int $passingScorePct = 70;

    public bool $hasTest = false;

    public bool $requireReview = false;

    public bool $isPublished = false;

    public $thumbnail;

    public string $thumbnailUrl = '';

    public $gallery = [];

    public array $existingGallery = [];

    public array $selectedGroupIds = [];

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
        $this->thumbnailUrl = $course->thumbnail_url ?? '';
        $this->existingGallery = $course->images()->get()->toArray();
        $this->selectedGroupIds = $course->groupAccess()
            ->pluck('group_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
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
            'thumbnail' => ['nullable', 'image', 'max:2048'],
            'gallery.*' => ['image', 'max:2048'],
            'selectedGroupIds' => ['array'],
            'selectedGroupIds.*' => ['integer', 'exists:learner_groups,id'],
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

        if ($this->thumbnail) {
            $this->deleteOldPublicFile($this->course->thumbnail_url);
            $path = $this->thumbnail->store('courses/thumbnails', 'public');
            $thumbnailUrl = Storage::disk('public')->url($path);
        } else {
            $thumbnailUrl = $this->course->thumbnail_url;
        }

        $this->course->update([
            'title' => $this->title,
            'description' => $this->description ?: null,
            'thumbnail_url' => $thumbnailUrl,
            'duration_hours' => $this->durationHours ?: null,
            'passing_score_pct' => $this->passingScorePct,
            'has_test' => $this->hasTest,
            'require_review' => $this->hasTest && $this->requireReview,
            'is_published' => $this->isPublished,
        ]);

        if ($this->gallery) {
            foreach ($this->gallery as $image) {
                $path = $image->store('courses/gallery', 'public');
                $this->course->images()->create([
                    'image_url' => Storage::disk('public')->url($path),
                    'sort_order' => $this->course->images()->max('sort_order') + 10,
                ]);
            }
        }

        $this->course->groupAccess()->delete();
        foreach ($this->selectedGroupIds as $groupId) {
            CourseGroupAccess::create([
                'course_id' => $this->course->id,
                'group_id' => (int) $groupId,
            ]);
        }

        session()->flash('status', 'บันทึกคอร์สเรียบร้อยแล้ว');

        $this->redirect(route('admin.courses.index'), navigate: true);
    }

    public function removeGalleryImage(int $imageId): void
    {
        $image = CourseImage::findOrFail($imageId);
        $this->deleteOldPublicFile($image->image_url);
        $image->delete();
        $this->existingGallery = $this->course->images()->get()->toArray();
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
            'certificateCount' => $this->course->certificates()->count(),
            'allGroups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'แก้ไขคอร์ส']);
    }
}
