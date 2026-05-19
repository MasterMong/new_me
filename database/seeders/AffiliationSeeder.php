<?php

namespace Database\Seeders;

use App\Models\Affiliation;
use Illuminate\Database\Seeder;

class AffiliationSeeder extends Seeder
{
    public function run(): void
    {
        $affiliations = [
            ['name' => 'สพท.เชียงใหม่ เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.เชียงใหม่ เขต 2', 'type' => 'สพท'],
            ['name' => 'สพท.เชียงราย เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.กรุงเทพมหานคร', 'type' => 'สพท'],
            ['name' => 'สพท.นนทบุรี เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.ขอนแก่น เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.นครราชสีมา เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.สงขลา เขต 1', 'type' => 'สพท'],
            ['name' => 'สพท.ภูเก็ต', 'type' => 'สพท'],
            ['name' => 'สพท.อุบลราชธานี เขต 1', 'type' => 'สพท'],
            ['name' => 'สำนักติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน (สตผ.)', 'type' => 'สำนักส่วนกลาง'],
            ['name' => 'สำนักพัฒนาครูและบุคลากรการศึกษาขั้นพื้นฐาน', 'type' => 'สำนักส่วนกลาง'],
            ['name' => 'สำนักนโยบายและแผนการศึกษาขั้นพื้นฐาน', 'type' => 'สำนักส่วนกลาง'],
            ['name' => 'สำนักงานคณะกรรมการการศึกษาขั้นพื้นฐาน (สพฐ.)', 'type' => 'สำนักส่วนกลาง'],
        ];

        Affiliation::insert($affiliations);
    }
}
