<?php

namespace App\Models;

use App\Enums\TestAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TestAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'assessment_id', 'attempt_number', 'total_score', 'max_score',
        'score_pct', 'star_rating', 'status', 'started_at', 'submitted_at',
        'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'status' => TestAttemptStatus::class,
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TestAnswer::class, 'attempt_id');
    }

    public function expertReview(): HasOne
    {
        return $this->hasOne(ExpertReview::class, 'attempt_id');
    }
}
