<?php

namespace App\Models;

use App\Enums\AssessmentType;
use App\Enums\EnrollmentStatus;
use App\Enums\TestAttemptStatus;
use App\Notifications\CertificateIssued;
use App\Services\CertificatePdfService;
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

    /**
     * Whether this enrollment's user has met all 5 certificate conditions:
     * required modules completed, required assessments passed, a course
     * review submitted, and (if the course has a course-wide post-test)
     * a passing post-test score. Does not check whether a certificate has
     * already been issued — see issueCertificateIfEligible() for that.
     */
    public function isEligibleForCertificate(): bool
    {
        $course = $this->course;
        $user = $this->user;

        foreach ($course->modules->where('is_required', true) as $module) {
            if (! $module->isCompletedFor($user)) {
                return false;
            }
        }

        foreach ($course->assessments->where('is_required_for_cert', true) as $assessment) {
            $passed = $assessment->attempts->where('user_id', $user->id)
                ->contains(fn ($attempt) => $attempt->status === TestAttemptStatus::Passed);

            if (! $passed) {
                return false;
            }
        }

        if (! CourseReview::where('user_id', $user->id)->where('course_id', $course->id)->exists()) {
            return false;
        }

        $postTest = $this->courseWidePostTest();

        if ($postTest) {
            $bestScore = $postTest->attempts->where('user_id', $user->id)->max('score_pct');

            if ($bestScore === null || $bestScore < $course->passing_score_pct) {
                return false;
            }
        }

        return true;
    }

    /**
     * Issue a certificate if the user is eligible and doesn't already have
     * one for this course. Idempotent: returns the existing certificate if
     * already issued, without re-evaluating eligibility.
     */
    public function issueCertificateIfEligible(): ?Certificate
    {
        $existing = Certificate::where('user_id', $this->user_id)->where('course_id', $this->course_id)->first();
        if ($existing) {
            return $existing;
        }

        if (! $this->isEligibleForCertificate()) {
            return null;
        }

        $postTest = $this->courseWidePostTest();
        $finalScore = $postTest
            ? $postTest->attempts->where('user_id', $this->user_id)->max('score_pct')
            : 100.0;

        $certificate = Certificate::create([
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'certificate_number' => sprintf('MEL-%s-%04d-%04d', now()->format('Y'), $this->course_id, $this->user_id),
            'full_name_on_cert' => $this->user->fullName(),
            'final_score_pct' => $finalScore,
            'issued_date' => now()->toDateString(),
        ]);

        $this->update([
            'status' => EnrollmentStatus::Certified->value,
            'completed_at' => $this->completed_at ?? now(),
        ]);

        app(CertificatePdfService::class)->generate($certificate);

        $this->user->notify(new CertificateIssued($certificate));

        return $certificate;
    }

    /**
     * The course's own post-test, excluding any post-test scoped to a
     * specific module (Assessment.module_id) — those are separate, per-module
     * checks and must not be mistaken for the course-wide final post-test.
     */
    protected function courseWidePostTest(): ?Assessment
    {
        return $this->course->assessments->first(
            fn (Assessment $assessment) => $assessment->type === AssessmentType::PostTest && $assessment->module_id === null
        );
    }
}
