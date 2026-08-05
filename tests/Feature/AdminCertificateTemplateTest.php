<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\CertificateTemplate;
use App\Models\CertificateTemplate as CertificateTemplateModel;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('admin can view the certificate template page with no template yet', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $response = $this->actingAs($admin)->get(route('admin.courses.certificate-template', $course));

    $response->assertOk();
    $response->assertSee('เทมเพลตเกียรติบัตร');
});

test('admin can create a certificate template with an uploaded image and coordinates', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CertificateTemplate::class, ['course' => $course])
        ->set('templateImage', UploadedFile::fake()->image('template.jpg', 1200, 800))
        ->set('nameX', 500)
        ->set('nameY', 400)
        ->set('dateX', 500)
        ->set('dateY', 600)
        ->call('save')
        ->assertHasNoErrors();

    $template = CertificateTemplateModel::where('course_id', $course->id)->firstOrFail();

    expect($template->name_x)->toBe(500);
    expect($template->name_y)->toBe(400);
    expect($template->date_x)->toBe(500);
    expect($template->date_y)->toBe(600);
    expect($template->template_image_url)->not->toBeEmpty();
});

test('saving without ever providing an image is rejected', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    $this->actingAs($admin);

    Livewire::test(CertificateTemplate::class, ['course' => $course])
        ->set('nameX', 100)
        ->call('save')
        ->assertHasErrors(['templateImage']);

    expect(CertificateTemplateModel::where('course_id', $course->id)->exists())->toBeFalse();
});

test('admin can update an existing template without re-uploading the image', function () {
    Storage::fake('public');

    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create();

    CertificateTemplateModel::create([
        'course_id' => $course->id,
        'template_image_url' => 'https://example.com/template.jpg',
        'name_x' => 100,
        'name_y' => 100,
        'date_x' => 200,
        'date_y' => 200,
    ]);

    $this->actingAs($admin);

    Livewire::test(CertificateTemplate::class, ['course' => $course])
        ->assertSet('nameX', 100)
        ->assertSet('templateImageUrl', 'https://example.com/template.jpg')
        ->set('nameX', 750)
        ->set('nameY', 450)
        ->call('save');

    $template = CertificateTemplateModel::where('course_id', $course->id)->firstOrFail();

    expect($template->name_x)->toBe(750);
    expect($template->name_y)->toBe(450);
    expect($template->template_image_url)->toBe('https://example.com/template.jpg');
    expect(CertificateTemplateModel::where('course_id', $course->id)->count())->toBe(1);
});

test('a learner cannot access the certificate template admin page', function () {
    $learner = User::factory()->create(['role' => UserRole::Learner->value]);
    $course = Course::factory()->create();

    $response = $this->actingAs($learner)->get(route('admin.courses.certificate-template', $course));

    $response->assertForbidden();
});
