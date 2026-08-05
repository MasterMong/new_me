<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'description', 'thumbnail_url', 'duration_hours', 'passing_score_pct',
        'has_test', 'require_review', 'is_published', 'created_by',
    ];

    protected $casts = [
        'has_test' => 'boolean',
        'require_review' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function instructors(): HasMany
    {
        return $this->hasMany(CourseInstructor::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('sort_order');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function certificateTemplate(): HasOne
    {
        return $this->hasOne(CertificateTemplate::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(CourseImage::class)->orderBy('sort_order');
    }

    public function groupAccess(): HasMany
    {
        return $this->hasMany(CourseGroupAccess::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * A course with no group_access rows is visible to everyone, including
     * guests. Once restricted, only users belonging to one of the allowed
     * groups may see or enroll in it — guests never qualify.
     */
    public function isVisibleTo(?User $user): bool
    {
        if (! $this->relationLoaded('groupAccess')) {
            $this->load('groupAccess');
        }

        if ($this->groupAccess->isEmpty()) {
            return true;
        }

        if (! $user) {
            return false;
        }

        $userGroupIds = $user->groups()->pluck('learner_groups.id');

        return $this->groupAccess->pluck('group_id')->intersect($userGroupIds)->isNotEmpty();
    }

    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            $q->whereDoesntHave('groupAccess');

            if ($user) {
                $userGroupIds = $user->groups()->pluck('learner_groups.id');
                $q->orWhereHas('groupAccess', function (Builder $q2) use ($userGroupIds) {
                    $q2->whereIn('group_id', $userGroupIds);
                });
            }
        });
    }
}
