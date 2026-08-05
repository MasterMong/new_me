<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Module;
use App\Models\ModuleExpertAssignment;
use App\Models\User;
use Illuminate\Database\Seeder;

class ModuleExpertAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first();
        $expert1 = User::where('email', 'expert1@me-learning.go.th')->first();
        $expert2 = User::where('email', 'expert2@me-learning.go.th')->first();

        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        // Course 1 module 4 (requires_expert_review) — assigned to expert1 only.
        $course1ReviewModule = Module::where('course_id', $course1->id)->where('requires_expert_review', true)->first();

        // Course 2 module 3 (requires_expert_review) — assigned to expert2 only.
        $course2ReviewModule = Module::where('course_id', $course2->id)->where('requires_expert_review', true)->first();

        $assignments = [
            [$course1ReviewModule, $expert1],
            [$course2ReviewModule, $expert2],
        ];

        foreach ($assignments as [$module, $expert]) {
            if (! $module) {
                continue;
            }

            ModuleExpertAssignment::firstOrCreate(
                ['module_id' => $module->id, 'expert_id' => $expert->id],
                ['assigned_at' => now()->subDays(30), 'assigned_by' => $admin->id]
            );
        }
    }
}
