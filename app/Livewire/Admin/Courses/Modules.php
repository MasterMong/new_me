<?php

namespace App\Livewire\Admin\Courses;

use App\Enums\ContentType;
use App\Enums\UserRole;
use App\Models\ContentGroupAccess;
use App\Models\Course;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\ModuleExpertAssignment;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Modules extends Component
{
    use WithFileUploads;

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

    public bool $moduleIsSequential = false;

    public $moduleThumbnail;

    public string $moduleThumbnailUrl = '';

    // ── Content form ────────────────────────────────────────────
    public string $contentType = 'video';

    public string $contentTitle = '';

    public string $contentFileUrl = '';

    public string $contentDurationMinutes = '';

    public ?int $contentAssessmentId = null;

    public array $selectedGroupIds = [];

    // ── Expert assignment ───────────────────────────────────────
    public bool $showExpertModal = false;

    public ?int $expertModalModuleId = null;

    public array $selectedExpertIds = [];

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

    public function updatedShowExpertModal(bool $value): void
    {
        if (! $value) {
            $this->resetExpertForm();
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
        $this->moduleIsSequential = $module->is_sequential ?? false;
        $this->moduleThumbnailUrl = $module->thumbnail_url ?? '';
        $this->showModuleModal = true;
    }

    public function saveModule(): void
    {
        $this->validate($this->moduleRules(), $this->moduleMessages());

        if ($this->moduleThumbnail) {
            $path = $this->moduleThumbnail->store('modules/thumbnails', 'public');
            $thumbnailUrl = Storage::disk('public')->url($path);
        } else {
            $thumbnailUrl = $this->editingModuleId
                ? Module::findOrFail($this->editingModuleId)->thumbnail_url
                : null;
        }

        if ($this->editingModuleId) {
            Module::findOrFail($this->editingModuleId)->update([
                'title' => $this->moduleTitle,
                'description' => $this->moduleDescription ?: null,
                'thumbnail_url' => $thumbnailUrl,
                'is_required' => $this->moduleIsRequired,
                'requires_expert_review' => $this->moduleRequiresExpertReview,
                'max_test_attempts' => $this->moduleMaxTestAttempts,
                'is_sequential' => $this->moduleIsSequential,
            ]);
        } else {
            $nextNumber = $this->course->modules()->count() + 1;
            $maxSort = $this->course->modules()->max('sort_order') ?? -10;

            $this->course->modules()->create([
                'module_number' => $nextNumber,
                'sort_order' => $maxSort + 10,
                'title' => $this->moduleTitle,
                'description' => $this->moduleDescription ?: null,
                'thumbnail_url' => $thumbnailUrl,
                'is_required' => $this->moduleIsRequired,
                'requires_expert_review' => $this->moduleRequiresExpertReview,
                'max_test_attempts' => $this->moduleMaxTestAttempts,
                'is_sequential' => $this->moduleIsSequential,
            ]);
        }

        $this->showModuleModal = false;
    }

    public function deleteModule(int $moduleId): void
    {
        Module::findOrFail($moduleId)->delete();
        $this->course->load('modules');
        $this->reorderModules();
    }

    public function moveModule(int $moduleId, string $direction): void
    {
        $modules = $this->course->modules()->orderBy('sort_order')->get();
        $index = $modules->search(fn ($m) => $m->id === $moduleId);

        if ($index === false) {
            return;
        }

        if ($direction === 'up' && $index > 0) {
            $prev = $modules[$index - 1];
            $curr = $modules[$index];

            $temp = $prev->sort_order;
            $prev->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        } elseif ($direction === 'down' && $index < $modules->count() - 1) {
            $next = $modules[$index + 1];
            $curr = $modules[$index];

            $temp = $next->sort_order;
            $next->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        }

        $this->reorderModules();
    }

    protected function reorderModules(): void
    {
        $modules = $this->course->modules()->orderBy('sort_order')->get();
        foreach ($modules as $i => $module) {
            $module->update([
                'sort_order' => ($i + 1) * 10,
                'module_number' => $i + 1,
            ]);
        }
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
        $this->contentAssessmentId = $content->assessment_id;
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
            'assessment_id' => $this->contentType === 'test' ? $this->contentAssessmentId : null,
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
        $content = ModuleContent::findOrFail($contentId);
        $moduleId = $content->module_id;
        $content->delete();
        $this->reorderContents($moduleId);
    }

    public function moveContent(int $contentId, string $direction): void
    {
        $content = ModuleContent::findOrFail($contentId);
        $moduleId = $content->module_id;
        $contents = ModuleContent::where('module_id', $moduleId)->orderBy('sort_order')->get();
        $index = $contents->search(fn ($c) => $c->id === $contentId);

        if ($index === false) {
            return;
        }

        if ($direction === 'up' && $index > 0) {
            $prev = $contents[$index - 1];
            $curr = $contents[$index];

            $temp = $prev->sort_order;
            $prev->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        } elseif ($direction === 'down' && $index < $contents->count() - 1) {
            $next = $contents[$index + 1];
            $curr = $contents[$index];

            $temp = $next->sort_order;
            $next->update(['sort_order' => $curr->sort_order]);
            $curr->update(['sort_order' => $temp]);
        }

        $this->reorderContents($moduleId);
    }

    protected function reorderContents(int $moduleId): void
    {
        $contents = ModuleContent::where('module_id', $moduleId)->orderBy('sort_order')->get();
        foreach ($contents as $i => $content) {
            $content->update(['sort_order' => ($i + 1) * 10]);
        }
    }

    // ── Expert assignment ───────────────────────────────────────

    public function openExpertAssignment(int $moduleId): void
    {
        $module = Module::findOrFail($moduleId);
        $this->expertModalModuleId = $moduleId;
        $this->selectedExpertIds = $module->assignedExperts()
            ->pluck('users.id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
        $this->showExpertModal = true;
    }

    public function saveExpertAssignments(): void
    {
        $this->validate([
            'selectedExpertIds' => ['array'],
            'selectedExpertIds.*' => ['integer', 'exists:users,id'],
        ]);

        $module = Module::findOrFail($this->expertModalModuleId);

        $module->expertAssignments()->delete();
        foreach ($this->selectedExpertIds as $expertId) {
            ModuleExpertAssignment::create([
                'module_id' => $module->id,
                'expert_id' => (int) $expertId,
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }

        $this->showExpertModal = false;
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
            'moduleIsSequential' => ['boolean'],
            'moduleThumbnail' => ['nullable', 'image', 'max:2048'],
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
            'contentType' => ['required', 'in:video,document,link,test'],
            'contentTitle' => ['required', 'string', 'max:500'],
            'contentFileUrl' => ['nullable', 'string', 'max:1000'],
            'contentDurationMinutes' => ['nullable', 'numeric', 'min:0'],
            'contentAssessmentId' => ['nullable', 'required_if:contentType,test', 'exists:assessments,id'],
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
        $this->moduleIsSequential = false;
        $this->moduleThumbnail = null;
        $this->moduleThumbnailUrl = '';
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
        $this->contentAssessmentId = null;
        $this->selectedGroupIds = [];
        $this->resetValidation();
    }

    protected function resetExpertForm(): void
    {
        $this->expertModalModuleId = null;
        $this->selectedExpertIds = [];
        $this->resetValidation();
    }

    public function render()
    {
        $modules = $this->course->modules()
            ->with(['contents.groupAccess.group', 'assignedExperts'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.admin.courses.modules', [
            'modules' => $modules,
            'contentTypes' => ContentType::cases(),
            'courseAssessments' => $this->course->assessments()->orderBy('title')->get(),
            'allGroups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
            'allExperts' => User::where('role', UserRole::Expert->value)->orderBy('first_name')->get(),
        ])->layout('layouts.app', ['title' => 'โมดูล: '.$this->course->title]);
    }
}
