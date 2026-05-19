<?php

namespace App\Models;

use App\Enums\ExpertReviewStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExpertReview extends Model
{
    protected $fillable = ['attempt_id', 'expert_id', 'status', 'score', 'feedback', 'reviewed_at'];

    protected $casts = [
        'status' => ExpertReviewStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function expert(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expert_id');
    }
}
