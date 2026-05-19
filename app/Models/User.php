<?php

namespace App\Models;

use App\Enums\UserExperience;
use App\Enums\UserPrefix;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'prefix', 'first_name', 'last_name', 'email', 'password',
        'position_id', 'position_other', 'affiliation_id', 'school_name',
        'experience', 'phone', 'role', 'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'prefix' => UserPrefix::class,
            'experience' => UserExperience::class,
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    public function fullName(): string
    {
        return ($this->prefix?->value ?? '').' '.$this->first_name.' '.$this->last_name;
    }

    public function initials(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name, 0, 1));
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function affiliation(): BelongsTo
    {
        return $this->belongsTo(Affiliation::class);
    }

    public function groupMemberships(): HasMany
    {
        return $this->hasMany(UserGroupMembership::class);
    }

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(LearnerGroup::class, 'user_group_memberships', 'user_id', 'group_id')
            ->withPivot('assigned_at', 'assigned_by')
            ->withTimestamps();
    }

    public function loginLogs(): HasMany
    {
        return $this->hasMany(LoginLog::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function moduleProgress(): HasMany
    {
        return $this->hasMany(ModuleProgress::class);
    }

    public function contentViews(): HasMany
    {
        return $this->hasMany(ContentView::class);
    }

    public function testAttempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function expertReviews(): HasMany
    {
        return $this->hasMany(ExpertReview::class, 'expert_id');
    }

    public function courseReviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(LearnerNotification::class);
    }

    public function createdCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }
}
