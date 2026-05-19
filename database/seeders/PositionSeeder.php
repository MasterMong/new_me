<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            ['name' => 'ศึกษานิเทศก์', 'sort_order' => 1],
            ['name' => 'ผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา', 'sort_order' => 2],
            ['name' => 'รองผู้อำนวยการสำนักงานเขตพื้นที่การศึกษา', 'sort_order' => 3],
            ['name' => 'นักวิชาการศึกษา', 'sort_order' => 4],
            ['name' => 'นักวิเคราะห์นโยบายและแผน', 'sort_order' => 5],
            ['name' => 'นักจัดการงานทั่วไป', 'sort_order' => 6],
            ['name' => 'เจ้าพนักงานธุรการ', 'sort_order' => 7],
            ['name' => 'นักทรัพยากรบุคคล', 'sort_order' => 8],
            ['name' => 'อื่น ๆ', 'sort_order' => 99],
        ];

        Position::insert($positions);
    }
}
