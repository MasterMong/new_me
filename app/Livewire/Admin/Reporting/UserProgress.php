<?php

namespace App\Livewire\Admin\Reporting;

use App\Enums\AssessmentType;
use App\Enums\UserRole;
use App\Models\Enrollment;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserProgress extends Component
{
    use WithPagination;

    public string $search = '';

    public string $groupId = '';

    public string $enrolledFrom = '';

    public string $enrolledTo = '';

    public ?int $selectedUserId = null;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingEnrolledFrom(): void
    {
        $this->resetPage();
    }

    public function updatingEnrolledTo(): void
    {
        $this->resetPage();
    }

    public function clearDateFilter(): void
    {
        $this->enrolledFrom = '';
        $this->enrolledTo = '';
        $this->resetPage();
    }

    public function selectUser(?int $id): void
    {
        $this->selectedUserId = $id;
        $this->resetPage();
    }

    public function render()
    {
        if ($this->selectedUserId) {
            return $this->renderUserDetails();
        }

        $users = User::query()
            ->where('role', UserRole::Learner)
            ->withCount(['enrollments'])
            ->when($this->search, function ($q) {
                $q->where(function ($sq) {
                    $sq->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('email', 'like', "%{$this->search}%");
                });
            })
            ->when($this->groupId, function ($q) {
                $q->whereHas('groupMemberships', fn ($sq) => $sq->where('group_id', $this->groupId));
            })
            ->when($this->enrolledFrom || $this->enrolledTo, function ($q) {
                $q->whereHas('enrollments', function ($sq) {
                    $sq->when($this->enrolledFrom, fn ($ssq) => $ssq->whereDate('enrolled_at', '>=', $this->enrolledFrom))
                        ->when($this->enrolledTo, fn ($ssq) => $ssq->whereDate('enrolled_at', '<=', $this->enrolledTo));
                });
            })
            ->latest()
            ->paginate(15);

        return view('livewire.admin.reporting.user-progress', [
            'users' => $users,
            'groups' => LearnerGroup::where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'รายงานความก้าวหน้าตามรายบุคคล']);
    }

    protected function renderUserDetails()
    {
        $user = User::with([
            'enrollments.course.modules.contents.views',
            'enrollments.course.modules.contents.groupAccess',
            'enrollments.course.modules.assessments.attempts',
            'groupMemberships.group',
        ])->findOrFail($this->selectedUserId);

        $user->enrollments->each(function ($enrollment) use ($user) {
            $enrollment->setRelation('user', $user);
            $enrollment->progress_percent = $enrollment->calculateProgressPercent();
            $enrollment->module_breakdown = $this->moduleBreakdownFor($enrollment, $user);
        });

        return view('livewire.admin.reporting.user-details', [
            'user' => $user,
        ])->layout('layouts.app', ['title' => 'รายละเอียดผู้เรียน: '.$user->fullName()]);
    }

    protected function moduleBreakdownFor(Enrollment $enrollment, User $user)
    {
        $modules = $enrollment->course->modules->values();

        return $modules->map(function (Module $module, int $index) use ($modules, $user) {
            $progress = $module->progressPercentFor($user);
            $completed = $module->isCompletedFor($user);
            $previousCompleted = $index === 0 || $modules[$index - 1]->isCompletedFor($user);

            $status = match (true) {
                $completed => 'completed',
                ! $previousCompleted => 'locked',
                $progress > 0 => 'in_progress',
                default => 'not_started',
            };

            return [
                'module' => $module,
                'progress_percent' => $progress,
                'status' => $status,
                'pre_test' => $module->assessments->firstWhere('type', AssessmentType::PreTest),
                'post_test' => $module->assessments->firstWhere('type', AssessmentType::PostTest),
                'post_test_passed' => $module->postTestPassedFor($user),
            ];
        });
    }
}
