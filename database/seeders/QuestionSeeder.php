<?php

namespace Database\Seeders;

use App\Enums\GradingMode;
use App\Enums\QuestionType;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionChoice;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        $assessments = [
            'c1_pre' => Assessment::where('course_id', $course1->id)->where('type', 'pre_test')->first(),
            'c1_post' => Assessment::where('course_id', $course1->id)->where('type', 'post_test')->first(),
            'c2_pre' => Assessment::where('course_id', $course2->id)->where('type', 'pre_test')->first(),
            'c2_post' => Assessment::where('course_id', $course2->id)->where('type', 'post_test')->first(),
        ];

        // Question bank per assessment key — 4 MC + 1 essay each
        $bank = [
            'c1_pre' => [
                ['text' => 'การติดตามและประเมินผล (M&E) มีวัตถุประสงค์หลักเพื่ออะไร?', 'choices' => ['ตรวจสอบความก้าวหน้าและผลลัพธ์ของโครงการ', 'รายงานผลงานต่อผู้บังคับบัญชาเท่านั้น', 'ลดงบประมาณการดำเนินงาน', 'เพิ่มจำนวนบุคลากรในองค์กร'], 'correct' => 0],
                ['text' => 'ข้อใดคือตัวชี้วัดเชิงปริมาณ (Quantitative Indicator)?', 'choices' => ['อัตราการสำเร็จการศึกษา', 'ความพึงพอใจของครู', 'คุณภาพการสอน', 'บรรยากาศในห้องเรียน'], 'correct' => 0],
                ['text' => 'ในวงจร PDCA ตัวอักษร "C" หมายถึงอะไร?', 'choices' => ['Check — การตรวจสอบ', 'Control — การควบคุม', 'Create — การสร้าง', 'Change — การเปลี่ยนแปลง'], 'correct' => 0],
                ['text' => 'การเก็บข้อมูลเชิงคุณภาพ (Qualitative) วิธีใดเหมาะสมที่สุด?', 'choices' => ['การสัมภาษณ์เชิงลึก', 'แบบสำรวจออนไลน์แบบมาตราส่วน', 'การนับจำนวนนักเรียน', 'การทดสอบมาตรฐาน'], 'correct' => 0],
                ['text' => 'อธิบายความสำคัญของการกำหนดตัวชี้วัดในการติดตามโครงการ', 'type' => 'essay'],
            ],
            'c1_post' => [
                ['text' => 'ผลลัพธ์ (Outcome) ของโครงการแตกต่างจากผลผลิต (Output) อย่างไร?', 'choices' => ['Outcome คือการเปลี่ยนแปลงระยะยาว Output คือสิ่งที่ผลิตได้โดยตรง', 'Output คือเป้าหมายสูงสุด Outcome คือกิจกรรม', 'ทั้งสองคำมีความหมายเดียวกัน', 'Outcome วัดได้ง่ายกว่า Output เสมอ'], 'correct' => 0],
                ['text' => 'ข้อใดเป็นเครื่องมือวิเคราะห์สาเหตุปัญหาที่นิยมใช้ในการประเมินผล?', 'choices' => ['แผนภูมิก้างปลา (Fishbone Diagram)', 'Gantt Chart', 'Flow Chart', 'Pie Chart'], 'correct' => 0],
                ['text' => 'Stakeholder Analysis ช่วยในการติดตามโครงการอย่างไร?', 'choices' => ['ระบุผู้มีส่วนได้ส่วนเสียและระดับอิทธิพล', 'คำนวณงบประมาณโครงการ', 'วางแผนกิจกรรมประจำวัน', 'ออกแบบแบบสอบถาม'], 'correct' => 0],
                ['text' => 'Baseline Data มีความสำคัญต่อการประเมินผลอย่างไร?', 'choices' => ['เป็นจุดอ้างอิงเปรียบเทียบก่อน-หลังดำเนินโครงการ', 'ใช้สำหรับจ่ายเงินเดือนบุคลากร', 'ระบุชื่อผู้รับผิดชอบโครงการ', 'กำหนดวันสิ้นสุดโครงการ'], 'correct' => 0],
                ['text' => 'ให้ยกตัวอย่างการนำผลการติดตามและประเมินผลไปใช้ปรับปรุงการดำเนินโครงการ', 'type' => 'essay'],
            ],
            'c2_pre' => [
                ['text' => 'การนิเทศการศึกษา (Educational Supervision) มีเป้าหมายหลักคืออะไร?', 'choices' => ['พัฒนาคุณภาพการจัดการเรียนรู้ของครู', 'ตรวจสอบการมาปฏิบัติงานของครู', 'ลดจำนวนชั่วโมงสอนของครู', 'เพิ่มจำนวนห้องเรียน'], 'correct' => 0],
                ['text' => 'กระบวนการนิเทศแบบ Clinical Supervision มีกี่ขั้นตอน?', 'choices' => ['3 ขั้นตอน (ก่อน ระหว่าง หลังสังเกตการสอน)', '2 ขั้นตอน (วางแผนและสะท้อนผล)', '5 ขั้นตอน', '1 ขั้นตอน (สังเกตการสอนเท่านั้น)'], 'correct' => 0],
                ['text' => 'ศึกษานิเทศก์ควรมีทักษะหลักด้านใดในการนิเทศ?', 'choices' => ['การสื่อสาร การสังเกต และการให้ข้อมูลป้อนกลับ', 'การบริหารงบประมาณเท่านั้น', 'การออกแบบหลักสูตรเท่านั้น', 'การทดสอบนักเรียน'], 'correct' => 0],
                ['text' => 'Peer Coaching ในบริบทการนิเทศหมายถึงอะไร?', 'choices' => ['ครูนิเทศและให้ข้อมูลป้อนกลับซึ่งกันและกัน', 'ผู้อำนวยการนิเทศครูเท่านั้น', 'นักเรียนสอนนักเรียน', 'ผู้ปกครองตรวจการบ้าน'], 'correct' => 0],
                ['text' => 'อธิบายบทบาทของศึกษานิเทศก์ในการพัฒนาคุณภาพการศึกษาในเขตพื้นที่', 'type' => 'essay'],
            ],
            'c2_post' => [
                ['text' => 'การนิเทศแบบ Formative ต่างจาก Summative อย่างไร?', 'choices' => ['Formative ช่วยพัฒนาระหว่างกระบวนการ Summative ตัดสินผลปลายทาง', 'Summative ทำก่อน Formative ทำหลัง', 'ทั้งสองแบบมีวัตถุประสงค์เดียวกัน', 'Formative ใช้เฉพาะการสอบปลายภาค'], 'correct' => 0],
                ['text' => 'ข้อใดเป็นตัวอย่างของ Evidence-Based Supervision?', 'choices' => ['ใช้ข้อมูลผลสัมฤทธิ์นักเรียนเป็นฐานในการให้ข้อเสนอแนะ', 'ให้คำแนะนำตามประสบการณ์ส่วนตัวของนิเทศก์', 'สังเกตการสอนโดยไม่บันทึกข้อมูล', 'ให้ครูประเมินตนเองเท่านั้น'], 'correct' => 0],
                ['text' => 'PLC (Professional Learning Community) สนับสนุนการนิเทศอย่างไร?', 'choices' => ['สร้างชุมชนแห่งการเรียนรู้ให้ครูพัฒนาร่วมกันอย่างต่อเนื่อง', 'แทนที่การนิเทศแบบดั้งเดิมทั้งหมด', 'ลดบทบาทของศึกษานิเทศก์', 'ใช้เฉพาะในโรงเรียนขนาดใหญ่'], 'correct' => 0],
                ['text' => 'การสะท้อนคิด (Reflective Practice) มีประโยชน์ต่อการนิเทศอย่างไร?', 'choices' => ['ช่วยให้ครูวิเคราะห์การสอนและพัฒนาตนเองอย่างมีระบบ', 'ใช้แทนการประเมินครูโดยผู้บริหาร', 'เหมาะสำหรับนักเรียนเท่านั้น', 'ลดเวลาในการนิเทศ'], 'correct' => 0],
                ['text' => 'วิเคราะห์ปัจจัยที่ส่งผลต่อความสำเร็จของการนิเทศการศึกษาในบริบทของ สพท. และเสนอแนวทางพัฒนา', 'type' => 'essay', 'points' => 2],
            ],
        ];

        foreach ($assessments as $key => $assessment) {
            $questions = $bank[$key];
            foreach ($questions as $sortOrder => $q) {
                $isEssay = ($q['type'] ?? 'multiple_choice') === 'essay';

                $question = Question::firstOrCreate(
                    ['assessment_id' => $assessment->id, 'sort_order' => $sortOrder + 1],
                    [
                        'question_type' => $isEssay ? QuestionType::Essay->value : QuestionType::MultipleChoice->value,
                        'question_text' => $q['text'],
                        'points' => $q['points'] ?? 1,
                        'grading_mode' => $isEssay ? GradingMode::Manual->value : GradingMode::Auto->value,
                    ]
                );

                if (! $isEssay) {
                    foreach ($q['choices'] as $choiceOrder => $choiceText) {
                        QuestionChoice::firstOrCreate(
                            ['question_id' => $question->id, 'sort_order' => $choiceOrder + 1],
                            [
                                'choice_text' => $choiceText,
                                'is_correct' => $choiceOrder === $q['correct'],
                            ]
                        );
                    }
                }
            }
        }
    }
}
