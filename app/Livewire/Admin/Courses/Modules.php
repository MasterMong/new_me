<?php

namespace App\Livewire\Admin\Courses;

use App\Enums\ContentType;
use App\Models\ContentGroupAccess;
use App\Models\Course;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use Livewire\Component;

class Modules extends Component
{
    public Course $course;

    // ── UI state ────────────────────────────────────────────────
    public bool $showModuleModal = false;

    public bool $showContentModal = false;

    public ?int $editingModuleId = null;

    public ?int $editingContentId = null;

    public ?int $activeModuleId = null;

    // ── Module form ─────────────────────────────────────────────
    public string $moduleTitle = '';

    public string $moduleDescription = '';

    public bool $moduleIsRequired = true;

    public bool $moduleRequiresExpertReview = false;

    public int $moduleMaxTestAttempts = 3;

    // ── Content form ────────────────────────────────────────────
    public string $contentType = 'video';

    public string $contentTitle = '';

    public string $contentFileUrl = '';

    public string $contentDurationMinutes = '';

    public array $selectedGroupIds = [];

    public function mount(Course $course): void
    {
        $this->course = $course;
    }

    public function updatedShowModuleModal(bool $value): void
    {
        if (! $value) {
            $this->resetModuleForm();
        }
    }

    public function updatedShowContentModal(bool $value): void
    {
        if (! $value) {
            $this->resetContentForm();
        }
    }

    // ── Module CRUD ─────────────────────────────────────────────

    public function openCreateModule(): void
    {
        $this->resetModuleForm();
        $this->showModuleModal = true;
    }

    public function openEditModule(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        $this->editingModuleId = $moduleId;
        $this->moduleTitle = $module->title;
        $this->moduleDescription = $module->description ?? '';
        $this->moduleIsRequired = $module->is_required;
        $this->moduleRequiresExpertReview = $module->requires_expert_review;
        $this->moduleMaxTestAttempts = $module->max_test_attempts;
        $this->showModuleModal = true;
    }

    public function saveModule(): void
    {
        $this->validate($this->moduleRules(), $this->moduleMessages());

        if ($this->editingModuleId) {
            Module::findOrFail($this->editingModuleId)->update([
                'title' => $this->moduleTitle,
                'description' => $this->moduleDescription ?: null,
                'is_required' => $this->moduleIsRequired,
                'requires_expert_review' => $this->moduleRequiresExpertReview,
                'max_test_attempts' => $this->moduleMaxTestAttempts,
            ]);
        } else {
            $nextNumber = $this->course->modules()->count() + 1;
            $maxSort = $this->course->modules()->max('sort_order') ?? -10;

            $this->course->modules()->create([
                'module_number' => $nextNumber,
                'sort_order' => $maxSort + 10,
                'title' => $this->moduleTitle,
                'description' => $this->moduleDescription ?: null,
                'is_required' => $this->moduleIsRequired,
                'requires_expert_review' => $this->moduleRequiresExpertReview,
                'max_test_attempts' => $this->moduleMaxTestAttempts,
            ]);
        }

        $this->showModuleModal = false;
    }

    public function deleteModule(int $moduleId): void
    {
        Module::findOrFail($moduleId)->delete();
    }

    // ── Content CRUD ────────────────────────────────────────────

    public function openCreateContent(int $moduleId): void
    {
        $this->resetContentForm();
        $this->activeModuleId = $moduleId;
        $this->showContentModal = true;
    }

    public function openEditContent(int $contentId): void
    {
        $content = ModuleContent::findOrFail($contentId);
        $this->editingContentId = $contentId;
        $this->activeModuleId = $content->module_id;
        $this->contentType = $content->content_type->value;
        $this->contentTitle = $content->title;
        $this->contentFileUrl = $content->file_url ?? '';
        $this->contentDurationMinutes = $content->duration_minutes !== null
            ? (string) $content->duration_minutes
            : '';
        $this->selectedGroupIds = $content->groupAccess()
            ->pluck('group_id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
        $this->showContentModal = true;
    }

    public function saveContent(): void
    {
        $this->validate($this->contentRules(), $this->contentMessages());

        $data = [
            'content_type' => $this->contentType,
            'title' => $this->contentTitle,
            'file_url' => $this->contentFileUrl ?: null,
            'duration_minutes' => $this->contentDurationMinutes !== ''
                ? (float) $this->contentDurationMinutes
                : null,
        ];

        if ($this->editingContentId) {
            $content = ModuleContent::findOrFail($this->editingContentId);
            $content->update($data);
        } else {
            $module = Module::findOrFail($this->activeModuleId);
            $maxSort = $module->contents()->max('sort_order') ?? -10;

            $content = ModuleContent::create(array_merge($data, [
                'module_id' => $this->activeModuleId,
                'sort_order' => $maxSort + 10,
            ]));
        }

        $content->groupAccess()->delete();
        foreach ($this->selectedGroupIds as $groupId) {
            ContentGroupAccess::create([
                'content_id' => $content->id,
                'group_id' => (int) $groupId,
            ]);
        }

        $this->showContentModal = false;
    }

    public function deleteContent(int $contentId): void
    {
        ModuleContent::findOrFail($contentId)->delete();
    }

    // ── Validation ──────────────────────────────────────────────

    protected function moduleRules(): array
    {
        return [
            'moduleTitle' => ['required', 'string', 'max:500'],
            'moduleDescription' => ['nullable', 'string'],
            'moduleIsRequired' => ['boolean'],
            'moduleRequiresExpertReview' => ['boolean'],
            'moduleMaxTestAttempts' => ['required', 'integer', 'min:1', 'max:10'],
        ];
    }

    protected function moduleMessages(): array
    {
        return [
            'moduleTitle.required' => 'กรุณากรอกชื่อโมดูล',
            'moduleTitle.max' => 'ชื่อโมดูลต้องไม่เกิน 500 ตัวอักษร',
            'moduleMaxTestAttempts.required' => 'กรุณากรอกจำนวนครั้งสูงสุด',
            'moduleMaxTestAttempts.min' => 'ต้องมากกว่า 0',
            'moduleMaxTestAttempts.max' => 'ต้องไม่เกิน 10 ครั้ง',
        ];
    }

    protected function contentRules(): array
    {
        return [
            'contentType' => ['required', 'in:video,document,link'],
            'contentTitle' => ['required', 'string', 'max:500'],
            'contentFileUrl' => ['nullable', 'string', 'max:1000'],
            'contentDurationMinutes' => ['nullable', 'numeric', 'min:0'],
            'selectedGroupIds' => ['array'],
            'selectedGroupIds.*' => ['integer', 'exists:learner_groups,id'],
        ];
    }

    protected function contentMessages(): array
    {
        return [
            'contentTitle.required' => 'กรุณากรอกชื่อเนื้อหา',
            'contentType.required' => 'กรุณาเลือกประเภทเนื้อหา',
            'contentDurationMinutes.numeric' => 'ระยะเวลาต้องเป็นตัวเลข',
            'contentDurationMinutes.min' => 'ระยะเวลาต้องมากกว่า 0',
        ];
    }

    // ── Helpers ─────────────────────────────────────────────────

    protected function resetModuleForm(): void
    {
        $this->editingModuleId = null;
        $this->moduleTitle = '';
        $this->moduleDescription = '';
        $this->moduleIsRequired = true;
        $this->moduleRequiresExpertReview = false;
        $this->moduleMaxTestAttempts = 3;
        $this->resetValidation();
    }

    protected function resetContentForm(): void
    {
        $this->editingContentId = null;
        $this->activeModuleId = null;
        $this->contentType = 'video';
        $this->contentTitle = '';
        $this->contentFileUrl = '';
        $this->contentDurationMinutes = '';
        $this->selectedGroupIds = [];
        $this->resetValidation();
    }

    public function render()
    {
        $modules = $this->course->modules()
            ->with(['contents.groupAccess.group'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.courses.modules', [
            'modules' => $modules,
            'contentTypes' => ContentType::cases(),
            'allGroups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'โมดูล: '.$this->course->title]);
    }
}
