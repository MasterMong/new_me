<?php

use App\Enums\AssessmentType;
use App\Enums\TestAttemptStatus;
use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\CertificateTemplate;
use App\Models\Course;
use App\Models\CourseReview;
use App\Models\Enrollment;
use App\Models\Module;
use App\Models\ModuleContent;
use App\Models\TestAttempt;
use App\Models\User;
use App\Services\CertificatePdfService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

function fakeTemplateImage(string $path, int $width = 1200, int $height = 800): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefill($image, 0, 0, imagecolorallocate($image, 230, 230, 250));
    ob_start();
    imagejpeg($image);
    $data = ob_get_clean();
    imagedestroy($image);

    Storage::disk('public')->put($path, $data);

    return Storage::disk('public')->url($path);
}

test('generates a fallback pdf when the course has no certificate template', function () {
    Storage::fake('public');

    $course = Course::factory()->create();
    $certificate = Certificate::factory()->create(['course_id' => $course->id]);

    $url = (new CertificatePdfService)->generate($certificate);

    $relative = Str::after($url, '/storage/');
    Storage::disk('public')->assertExists($relative);
    expect($certificate->fresh()->pdf_url)->toBe($url);
});

test('generates a template-based pdf when the course has a certificate template', function () {
    Storage::fake('public');

    $course = Course::factory()->create();
    $imageUrl = fakeTemplateImage('certificates/templates/test.jpg');

    CertificateTemplate::create([
        'course_id' => $course->id,
        'template_image_url' => $imageUrl,
        'name_x' => 600,
        'name_y' => 400,
        'date_x' => 600,
        'date_y' => 600,
    ]);

    $certificate = Certificate::factory()->create(['course_id' => $course->id]);

    $url = (new CertificatePdfService)->generate($certificate);

    $relative = Str::after($url, '/storage/');
    Storage::disk('public')->assertExists($relative);
    expect($certificate->fresh()->pdf_url)->toBe($url);
});

test('falls back to the plain design if the template image file cannot be read', function () {
    Storage::fake('public');

    $course = Course::factory()->create();

    CertificateTemplate::create([
        'course_id' => $course->id,
        'template_image_url' => Storage::disk('public')->url('certificates/templates/missing.jpg'),
        'name_x' => 100,
        'name_y' => 100,
        'date_x' => 100,
        'date_y' => 100,
    ]);

    $certificate = Certificate::factory()->create(['course_id' => $course->id]);

    // Should not throw even though the referenced image file was never written.
    $url = (new CertificatePdfService)->generate($certificate);

    expect($certificate->fresh()->pdf_url)->toBe($url);
});

test('issuing a certificate populates pdf_url end to end', function () {
    Storage::fake('public');

    $user = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create(['passing_score_pct' => 60]);
    $module = Module::factory()->create(['course_id' => $course->id, 'is_required' => true]);

    $content = ModuleContent::factory()->create(['module_id' => $module->id]);
    $content->views()->create(['user_id' => $user->id, 'is_completed' => true, 'viewed_at' => now()]);

    $postTest = Assessment::factory()->create([
        'course_id' => $course->id,
        'module_id' => null,
        'type' => AssessmentType::PostTest->value,
    ]);
    TestAttempt::factory()->create([
        'user_id' => $user->id,
        'assessment_id' => $postTest->id,
        'status' => TestAttemptStatus::Passed->value,
        'score_pct' => 80,
    ]);

    CourseReview::create(['user_id' => $user->id, 'course_id' => $course->id, 'rating' => 5]);

    $enrollment = Enrollment::factory()->create(['user_id' => $user->id, 'course_id' => $course->id]);

    $certificate = $enrollment->issueCertificateIfEligible();

    expect($certificate->pdf_url)->not->toBeNull();
    Storage::disk('public')->assertExists(Str::after($certificate->pdf_url, '/storage/'));
});
