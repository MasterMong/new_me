<?php

namespace App\Models;

use App\Enums\ContentType;
use App\Enums\TestAttemptStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleContent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'module_id', 'content_type', 'assessment_id', 'title', 'file_url', 'duration_minutes', 'sort_order',
    ];

    protected $casts = [
        'content_type' => ContentType::class,
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function groupAccess(): HasMany
    {
        return $this->hasMany(ContentGroupAccess::class, 'content_id');
    }

    public function views(): HasMany
    {
        return $this->hasMany(ContentView::class, 'content_id');
    }

    /**
     * Content with no group_access rows is visible to everyone.
     * Content with rows is visible only to users belonging to one of those groups.
     */
    public function isVisibleTo(User $user): bool
    {
        if (! $this->relationLoaded('groupAccess')) {
            $this->load('groupAccess');
        }

        if ($this->groupAccess->isEmpty()) {
            return true;
        }

        $userGroupIds = $user->groups()->pluck('learner_groups.id');

        return $this->groupAccess->pluck('group_id')->intersect($userGroupIds)->isNotEmpty();
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $userGroupIds = $user->groups()->pluck('learner_groups.id');

        return $query->where(function (Builder $q) use ($userGroupIds) {
            $q->whereDoesntHave('groupAccess')
                ->orWhereHas('groupAccess', function (Builder $q2) use ($userGroupIds) {
                    $q2->whereIn('group_id', $userGroupIds);
                });
        });
    }

    /**
     * Whether the given user has completed this content item.
     * For video/document/link content, completion is signaled by a
     * completed ContentView row. For test content, there is no
     * ContentView — completion instead means the linked assessment has
     * a passed TestAttempt.
     * Expects `views` (or `assessment.attempts`) eager-loaded for accuracy/performance.
     */
    public function isCompletedFor(User $user): bool
    {
        if ($this->content_type === ContentType::Test) {
            if (! $this->assessment_id) {
                return false;
            }

            if ($this->relationLoaded('assessment') && $this->assessment?->relationLoaded('attempts')) {
                return $this->assessment->attempts
                    ->where('user_id', $user->id)
                    ->contains(fn (TestAttempt $attempt) => $attempt->status === TestAttemptStatus::Passed);
            }

            return TestAttempt::where('user_id', $user->id)
                ->where('assessment_id', $this->assessment_id)
                ->where('status', TestAttemptStatus::Passed)
                ->exists();
        }

        if (! $this->relationLoaded('views')) {
            $this->load('views');
        }

        return $this->views->where('user_id', $user->id)->where('is_completed', true)->isNotEmpty();
    }

    /**
     * Whether the given user may currently access this content item.
     * Non-sequential modules place no ordering restriction. Sequential
     * modules require every earlier, visible sibling to be completed first.
     * Expects `module.contents.views` and `module.contents.assessment.attempts` eager-loaded.
     */
    public function isAccessibleFor(User $user): bool
    {
        if (! $this->module->is_sequential) {
            return true;
        }

        $previousContents = $this->module->contents
            ->filter(fn (ModuleContent $content) => $content->sort_order < $this->sort_order)
            ->filter(fn (ModuleContent $content) => $content->isVisibleTo($user));

        return $previousContents->every(fn (ModuleContent $content) => $content->isCompletedFor($user));
    }
}
