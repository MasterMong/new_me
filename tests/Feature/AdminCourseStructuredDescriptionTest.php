<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Create;
use App\Livewire\Admin\Courses\Edit;
use App\Models\Course;
use App\Models\User;
use Livewire\Livewire;

test('admin can set the structured course description fields when creating a course', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);

    $this->actingAs($admin);

    Livewire::test(Create::class)
        ->set('title', 'หลักสูตรทดสอบ L2')
        ->set('passingScorePct', 60)
        ->set('targetAudience', "นักวิชาการศึกษา\nบุคลากรในสถานศึกษา")
        ->set('learningFormat', 'ศึกษาด้วยตนเอง ผ่าน e-Learning')
        ->set('completionCriteria', 'มีผลการเรียนมากกว่า 80%')
        ->set('instructorTeam', 'บันทึกคลิปการสอน และตรวจใบงานพร้อมเฉลย')
        ->set('certificationInfo', "การรับเกียรติบัตร\nทำเนียบนักติดตามฯ ระดับพื้นฐาน")
        ->call('save');

    $course = Course::where('title', 'หลักสูตรทดสอบ L2')->firstOrFail();

    expect($course->target_audience)->toBe("นักวิชาการศึกษา\nบุคลากรในสถานศึกษา")
        ->and($course->learning_format)->toBe('ศึกษาด้วยตนเอง ผ่าน e-Learning')
        ->and($course->completion_criteria)->toBe('มีผลการเรียนมากกว่า 80%')
        ->and($course->instructor_team)->toBe('บันทึกคลิปการสอน และตรวจใบงานพร้อมเฉลย')
        ->and($course->targetAudienceList())->toBe(['นักวิชาการศึกษา', 'บุคลากรในสถานศึกษา'])
        ->and($course->certificationInfoList())->toBe(['การรับเกียรติบัตร', 'ทำเนียบนักติดตามฯ ระดับพื้นฐาน']);
});

test('admin can edit an existing course\'s structured description fields', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $course = Course::factory()->create([
        'target_audience' => 'กลุ่มเดิม',
        'learning_format' => null,
    ]);

    $this->actingAs($admin);

    Livewire::test(Edit::class, ['course' => $course])
        ->assertSet('targetAudience', 'กลุ่มเดิม')
        ->set('targetAudience', 'กลุ่มใหม่')
        ->set('learningFormat', 'เรียนในห้องเรียน')
        ->call('save');

    expect($course->fresh()->target_audience)->toBe('กลุ่มใหม่')
        ->and($course->fresh()->learning_format)->toBe('เรียนในห้องเรียน');
});

test('the public course page shows the structured description sections when present', function () {
    $course = Course::factory()->create([
        'is_published' => true,
        'target_audience' => "นักวิชาการศึกษา\nบุคลากรในสถานศึกษา",
        'learning_format' => 'ศึกษาด้วยตนเอง ผ่าน e-Learning',
        'completion_criteria' => 'มีผลการเรียนมากกว่า 80%',
        'instructor_team' => 'บันทึกคลิปการสอน',
        'certification_info' => "การรับเกียรติบัตร\nทำเนียบนักติดตามฯ",
    ]);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertSee('รายละเอียดหลักสูตร')
        ->assertSee('นักวิชาการศึกษา')
        ->assertSee('ศึกษาด้วยตนเอง ผ่าน e-Learning')
        ->assertSee('มีผลการเรียนมากกว่า 80%')
        ->assertSee('บันทึกคลิปการสอน')
        ->assertSee('ทำเนียบนักติดตามฯ');
});

test('the public course page omits the structured description section when nothing is set', function () {
    $course = Course::factory()->create([
        'is_published' => true,
        'target_audience' => null,
        'learning_format' => null,
        'completion_criteria' => null,
        'instructor_team' => null,
        'certification_info' => null,
    ]);

    $this->get(route('courses.show', $course))
        ->assertOk()
        ->assertDontSee('รายละเอียดหลักสูตร');
});
