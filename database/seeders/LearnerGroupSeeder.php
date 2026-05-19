<?php

namespace Database\Seeders;

use App\Models\LearnerGroup;
use Illuminate\Database\Seeder;

class LearnerGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            [
                'name' => 'ผู้เรียนทั่วไป',
                'description' => 'กลุ่มผู้เรียนทั่วไปที่ลงทะเบียนในระบบ',
                'is_active' => true,
            ],
            [
                'name' => 'ผู้เรียน สตผ.',
                'description' => 'บุคลากรจากสำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน',
                'is_active' => true,
            ],
            [
                'name' => 'ผู้บริหาร สพท.',
                'description' => 'ผู้อำนวยการและรองผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา',
                'is_active' => true,
            ],
            [
                'name' => 'ศึกษานิเทศก์',
                'description' => 'ศึกษานิเทศก์จากสำนักงานเขตพื้นที่การศึกษาทั่วประเทศ',
                'is_active' => true,
            ],
        ];

        foreach ($groups as $group) {
            LearnerGroup::create($group);
        }
    }
}
