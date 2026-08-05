<?php

namespace App\Models;

use App\Enums\EnrollmentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'course_id', 'status', 'enrolled_at', 'completed_at'];

    protected $casts = [
        'status' => EnrollmentStatus::class,
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Course-wide content completion percentage for this enrollment's user,
     * excluding content restricted to a group the user doesn't belong to.
     * Expects `course.modules.contents.views` (and ideally `.groupAccess`) eager-loaded.
     */
    public function calculateProgressPercent(): int
    {
        $visibleContents = $this->course->modules->flatMap(
            fn (Module $module) => $module->contents->filter(
                fn (ModuleContent $content) => $content->isVisibleTo($this->user)
            )
        );

        $total = $visibleContents->count();
        if ($total === 0) {
            return 0;
        }

        $completed = $visibleContents->filter(function (ModuleContent $content) {
            return $content->views->where('user_id', $this->user_id)->where('is_completed', true)->isNotEmpty();
        })->count();

        return (int) round(($completed / $total) * 100);
    }
}
