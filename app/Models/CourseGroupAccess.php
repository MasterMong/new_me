<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseGroupAccess extends Model
{
    protected $table = 'course_group_access';

    protected $fillable = ['course_id', 'group_id'];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(LearnerGroup::class, 'group_id');
    }
}
