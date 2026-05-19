<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestAnswer extends Model
{
    protected $fillable = [
        'attempt_id', 'question_id', 'selected_choice_id', 'essay_text',
        'uploaded_file_url', 'score', 'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class, 'attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public function selectedChoice(): BelongsTo
    {
        return $this->belongsTo(QuestionChoice::class, 'selected_choice_id');
    }
}
