<?php

namespace App\Models;

use App\Enums\ContentType;
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
}
