<?php

namespace App\Models;

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'module_number', 'title', 'description', 'thumbnail_url',
        'is_required', 'requires_expert_review', 'max_test_attempts', 'is_sequential', 'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'requires_expert_review' => 'boolean',
        'is_sequential' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(ModuleContent::class)->orderBy('sort_order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(ModulePrerequisite::class, 'module_id');
    }

    public function dependentPrerequisites(): HasMany
    {
        return $this->hasMany(ModulePrerequisite::class, 'prerequisite_module_id');
    }

    public function expertAssignments(): HasMany
    {
        return $this->hasMany(ModuleExpertAssignment::class);
    }

    public function assignedExperts(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'module_expert_assignments', 'module_id', 'expert_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    /**
     * A module with no expert assignments is open to any expert.
     * Once assigned, only the assigned experts may review it.
     */
    public function isAssignedTo(User $expert): bool
    {
        if (! $this->relationLoaded('expertAssignments')) {
            $this->load('expertAssignments');
        }

        if ($this->expertAssignments->isEmpty()) {
            return true;
        }

        return $this->expertAssignments->contains('expert_id', $expert->id);
    }

    /**
     * Content completion percentage for the given user, excluding content
     * restricted to a group they don't belong to. A module with no content
     * is trivially 100% (nothing to complete).
     * Expects `contents.views` (and ideally `contents.groupAccess`) eager-loaded.
     */
    public function progressPercentFor(User $user): int
    {
        $visibleContents = $this->contents->filter(fn (ModuleContent $content) => $content->isVisibleTo($user));

        $total = $visibleContents->count();
        if ($total === 0) {
            return 100;
        }

        $completed = $visibleContents->filter(function (ModuleContent $content) use ($user) {
            return $content->views->where('user_id', $user->id)->where('is_completed', true)->isNotEmpty();
        })->count();

        return (int) round(($completed / $total) * 100);
    }

    /**
     * Whether this module's own post-test (if any) has been passed by the given
     * user. A module with no post-test is trivially considered passed.
     * Expects `assessments.attempts` eager-loaded for accuracy/performance.
     */
    public function postTestPassedFor(User $user): bool
    {
        $postTest = $this->assessments->firstWhere('type', AssessmentType::PostTest);

        if (! $postTest) {
            return true;
        }

        return $postTest->attempts->where('user_id', $user->id)
            ->contains(fn ($attempt) => $attempt->status === TestAttemptStatus::Passed);
    }

    /**
     * A module is "completed" for a user once all its visible content is
     * watched and its own post-test (if any) has been passed.
     */
    public function isCompletedFor(User $user): bool
    {
        return $this->progressPercentFor($user) === 100 && $this->postTestPassedFor($user);
    }
}
