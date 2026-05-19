<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseInstructor extends Model
{
    public $timestamps = false;

    protected $fillable = ['course_id', 'name', 'position', 'photo_url', 'sort_order'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
