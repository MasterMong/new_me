<?php

namespace Database\Seeders;

use App\Models\ContentGroupAccess;
use App\Models\Course;
use App\Models\LearnerGroup;
use App\Models\Module;
use App\Models\ModuleContent;
use Illuminate\Database\Seeder;

class ContentGroupAccessSeeder extends Seeder
{
    public function run(): void
    {
        $course2 = Course::where('duration_hours', 8)->first();
        $supervisorGroup = LearnerGroup::where('name', 'ศึกษานิเทศก์')->first();

        $moduleIds = Module::where('course_id', $course2->id)->pluck('id');
        $contents = ModuleContent::whereIn('module_id', $moduleIds)->get();

        foreach ($contents as $content) {
            ContentGroupAccess::firstOrCreate([
                'content_id' => $content->id,
                'group_id' => $supervisorGroup->id,
            ]);
        }
    }
}
