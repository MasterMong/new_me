<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseGroupAccess;
use App\Models\LearnerGroup;
use Illuminate\Database\Seeder;

class CourseGroupAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Course 2 is already restricted at the content level to ศึกษานิเทศก์
        // (see ContentGroupAccessSeeder) — restrict the course itself too, so
        // the catalog/visibility gate is exercised by demo data as well.
        $course2 = Course::where('duration_hours', 8)->first();
        $supervisorGroup = LearnerGroup::where('name', 'ศึกษานิเทศก์')->first();

        CourseGroupAccess::firstOrCreate([
            'course_id' => $course2->id,
            'group_id' => $supervisorGroup->id,
        ]);
    }
}
