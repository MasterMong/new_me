<?php

namespace App\Models;

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    protected $fillable = [
        'course_id', 'module_id', 'type', 'title', 'passing_score_pct',
        'max_attempts', 'grading_mode', 'is_required_for_cert', 'requires_expert_review',
    ];

    protected $casts = [
        'type' => AssessmentType::class,
        'grading_mode' => GradingMode::class,
        'is_required_for_cert' => 'boolean',
        'requires_expert_review' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('sort_order');
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function prerequisites(): HasMany
    {
        return $this->hasMany(ModulePrerequisite::class, 'prerequisite_assessment_id');
    }
}
