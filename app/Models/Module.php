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
     * Expects `contents.views`, `contents.groupAccess`, and
     * `contents.assessment.attempts` eager-loaded.
     */
    public function progressPercentFor(User $user): int
    {
        $visibleContents = $this->contents->filter(fn (ModuleContent $content) => $content->isVisibleTo($user));

        $total = $visibleContents->count();
        if ($total === 0) {
            return 100;
        }

        $completed = $visibleContents->filter(fn (ModuleContent $content) => $content->isCompletedFor($user))->count();

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

    /**
     * Whether this module's own post-test is exhausted for this user — every
     * attempt used, none passed. Once locked out, the learner can't retake
     * the test; they must resetProgressFor() and redo the module from the
     * Outline. A module with no post-test, or one with unlimited attempts
     * (max_attempts <= 0), can never lock out this way.
     * Expects `assessments.attempts` eager-loaded, filtered to this user.
     */
    public function isLockedOutFor(User $user): bool
    {
        $postTest = $this->assessments->firstWhere('type', AssessmentType::PostTest);

        if (! $postTest || $postTest->max_attempts <= 0) {
            return false;
        }

        $attempts = $postTest->attempts->where('user_id', $user->id);

        if ($attempts->count() < $postTest->max_attempts) {
            return false;
        }

        return ! $attempts->contains(fn ($attempt) => $attempt->status === TestAttemptStatus::Passed);
    }

    /**
     * Wipe this user's progress in this module — watched content, and
     * attempts on the module's own pre-test and post-test (answers and any
     * expert review cascade with the attempt) — so they restart from the
     * Outline with a fresh set of post-test attempts. Only meaningful once
     * isLockedOutFor() is true; the worksheet/assignment (if any) and its
     * expert review are left untouched, since exhausting the post-test is
     * the only thing that triggers this reset.
     */
    public function resetProgressFor(User $user): void
    {
        $contentIds = $this->contents()->pluck('id');
        ContentView::where('user_id', $user->id)->whereIn('content_id', $contentIds)->delete();

        $assessmentIds = $this->assessments()
            ->whereIn('type', [AssessmentType::PreTest->value, AssessmentType::PostTest->value])
            ->pluck('id');

        TestAttempt::where('user_id', $user->id)->whereIn('assessment_id', $assessmentIds)->delete();
    }
}
