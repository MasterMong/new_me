<?php

namespace App\Models;

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
}
