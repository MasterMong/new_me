<?php

namespace Database\Seeders;

use App\Enums\PrerequisiteType;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Module;
use App\Models\ModulePrerequisite;
use Illuminate\Database\Seeder;

class ModulePrerequisiteSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        // Course 1 module 5 requires passing the module-2 mini-quiz with at
        // least 80% — an assessment-based prerequisite on a non-adjacent
        // module, on top of the plain sequential (is_sequential) gating.
        $module5 = Module::where('course_id', $course1->id)->where('module_number', 5)->first();
        $module2Quiz = Assessment::where('course_id', $course1->id)
            ->where('module_id', Module::where('course_id', $course1->id)->where('module_number', 2)->value('id'))
            ->first();

        if ($module5 && $module2Quiz) {
            ModulePrerequisite::firstOrCreate([
                'module_id' => $module5->id,
                'prerequisite_type' => PrerequisiteType::Assessment->value,
                'prerequisite_assessment_id' => $module2Quiz->id,
            ], [
                'min_score_pct' => 80,
            ]);
        }

        // Course 2 modules are not sequential (is_sequential = false), but
        // module 3 still explicitly requires module 1 to be completed first —
        // demonstrates prerequisite-based gating independent of strict order.
        $course2Module1 = Module::where('course_id', $course2->id)->where('module_number', 1)->first();
        $course2Module3 = Module::where('course_id', $course2->id)->where('module_number', 3)->first();

        if ($course2Module1 && $course2Module3) {
            ModulePrerequisite::firstOrCreate([
                'module_id' => $course2Module3->id,
                'prerequisite_type' => PrerequisiteType::Module->value,
                'prerequisite_module_id' => $course2Module1->id,
            ]);
        }
    }
}
