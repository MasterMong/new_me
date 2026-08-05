<?php

use App\Enums\UserRole;
use App\Livewire\Admin\Courses\Modules;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\User;
use Livewire\Livewire;

test('admin can assign experts to a module', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openExpertAssignment', $module->id)
        ->set('selectedExpertIds', [(string) $expert->id])
        ->call('saveExpertAssignments')
        ->assertSet('showExpertModal', false);

    $this->assertDatabaseHas('module_expert_assignments', [
        'module_id' => $module->id,
        'expert_id' => $expert->id,
        'assigned_by' => $admin->id,
    ]);
});

test('saving with no experts selected clears existing assignments', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $expert->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openExpertAssignment', $module->id)
        ->set('selectedExpertIds', [])
        ->call('saveExpertAssignments');

    $this->assertDatabaseMissing('module_expert_assignments', [
        'module_id' => $module->id,
        'expert_id' => $expert->id,
    ]);
});

test('opening expert assignment pre-fills currently assigned experts', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin->value]);
    $expert = User::factory()->create(['role' => UserRole::Expert->value]);
    $course = Course::factory()->create();
    $module = Module::factory()->create(['course_id' => $course->id, 'requires_expert_review' => true]);

    ModuleExpertAssignment::create([
        'module_id' => $module->id,
        'expert_id' => $expert->id,
        'assigned_at' => now(),
    ]);

    $this->actingAs($admin);

    Livewire::test(Modules::class, ['course' => $course])
        ->call('openExpertAssignment', $module->id)
        ->assertSet('selectedExpertIds', [(string) $expert->id]);
});
