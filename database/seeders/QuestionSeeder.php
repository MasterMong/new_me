<?php

namespace Database\Seeders;

use App\Enums\AssessmentType;
use App\Enums\GradingMode;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\Question;
use App\Models\QuestionChoice;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    /**
     * Reusable question pools for the per-module pre/post-tests and
     * assignments CourseSeeder generates. Shared and randomly sampled per
     * assessment rather than hand-written once per module.
     */
    private const MC_POOL = [
        ['text' => 'ขั้นตอนแรกของการวางแผนติดตามและประเมินผลโครงการคือข้อใด?', 'choices' => ['กำหนดวัตถุประสงค์และตัวชี้วัดที่ชัดเจน', 'จัดทำรายงานสรุปผล', 'คัดเลือกผู้เข้าร่วมโครงการ', 'ประกาศผลการดำเนินงาน'], 'correct' => 0],
        ['text' => 'ข้อใดคือความแตกต่างหลักระหว่างข้อมูลเชิงปริมาณและเชิงคุณภาพ?', 'choices' => ['ข้อมูลเชิงปริมาณวัดเป็นตัวเลข ส่วนเชิงคุณภาพอธิบายลักษณะและความหมาย', 'ข้อมูลทั้งสองแบบเก็บด้วยวิธีเดียวกันเสมอ', 'ข้อมูลเชิงคุณภาพใช้ได้เฉพาะแบบสอบถามเท่านั้น', 'ข้อมูลเชิงปริมาณไม่สามารถนำมาวิเคราะห์ทางสถิติได้'], 'correct' => 0],
        ['text' => 'เครื่องมือใดเหมาะสมที่สุดสำหรับเก็บข้อมูลเชิงลึกจากผู้ให้ข้อมูลหลัก?', 'choices' => ['การสัมภาษณ์เชิงลึก', 'แบบสอบถามปรนัยล้วน', 'การนับสถิติเบื้องต้น', 'การประกาศทางเว็บไซต์'], 'correct' => 0],
        ['text' => 'หลักฐานเชิงประจักษ์ (Evidence-based) มีความสำคัญต่อการประเมินผลอย่างไร?', 'choices' => ['ช่วยให้ข้อสรุปและข้อเสนอแนะมีความน่าเชื่อถือ', 'ทำให้ไม่ต้องเก็บข้อมูลเพิ่มเติมอีก', 'ใช้แทนการวิเคราะห์ทางสถิติได้ทั้งหมด', 'ลดความจำเป็นในการเขียนรายงาน'], 'correct' => 0],
        ['text' => 'ข้อใดเป็นตัวอย่างของตัวชี้วัดผลลัพธ์ (Outcome Indicator)?', 'choices' => ['อัตราการนำความรู้ไปใช้จริงหลังการอบรม', 'จำนวนผู้เข้าร่วมการอบรม', 'จำนวนเอกสารที่แจกจ่าย', 'จำนวนชั่วโมงการอบรม'], 'correct' => 0],
        ['text' => 'การให้ข้อมูลป้อนกลับ (Feedback) ที่มีประสิทธิภาพควรมีลักษณะอย่างไร?', 'choices' => ['ชัดเจน เจาะจง และนำไปปฏิบัติได้จริง', 'กว้างและเป็นนามธรรม', 'เน้นตำหนิมากกว่าชี้แนะ', 'ให้เฉพาะเมื่อพบข้อผิดพลาดร้ายแรงเท่านั้น'], 'correct' => 0],
        ['text' => 'ในวงจร PDCA ขั้นตอนใดหมายถึงการลงมือปฏิบัติตามแผน?', 'choices' => ['Do', 'Plan', 'Check', 'Act'], 'correct' => 0],
        ['text' => 'เหตุใดข้อมูลพื้นฐาน (Baseline Data) จึงมีความสำคัญก่อนเริ่มโครงการ?', 'choices' => ['ใช้เป็นจุดอ้างอิงเปรียบเทียบการเปลี่ยนแปลงหลังดำเนินโครงการ', 'ใช้กำหนดงบประมาณโครงการเพียงอย่างเดียว', 'ใช้แทนรายงานฉบับสมบูรณ์', 'ไม่มีผลต่อการวิเคราะห์ผล'], 'correct' => 0],
        ['text' => 'ผู้มีส่วนได้ส่วนเสีย (Stakeholder) ในโครงการทางการศึกษาหมายถึงใคร?', 'choices' => ['บุคคลหรือกลุ่มที่ได้รับผลกระทบหรือเกี่ยวข้องกับโครงการ', 'เฉพาะผู้บริหารระดับสูงเท่านั้น', 'เฉพาะผู้ให้ทุนสนับสนุน', 'บุคคลภายนอกที่ไม่เกี่ยวข้องกับโครงการ'], 'correct' => 0],
        ['text' => 'ข้อใดคือประโยชน์หลักของการนิเทศแบบกัลยาณมิตร (Peer Coaching)?', 'choices' => ['ครูให้ข้อมูลป้อนกลับและพัฒนาการสอนร่วมกัน', 'ลดภาระงานของผู้บริหาร', 'ใช้แทนการประเมินผลการปฏิบัติงานได้ทั้งหมด', 'เหมาะสำหรับใช้ในห้องเรียนขนาดใหญ่เท่านั้น'], 'correct' => 0],
    ];

    private const SHORT_ANSWER_POOL = [
        ['text' => 'PDCA ย่อมาจากอะไร (ภาษาอังกฤษ)?', 'correct_answer' => 'Plan Do Check Act'],
        ['text' => 'ตัวย่อ SMART ในการตั้งเป้าหมาย ตัว M หมายถึงอะไร?', 'correct_answer' => 'Measurable'],
        ['text' => 'PLC ย่อมาจากคำว่าอะไร?', 'correct_answer' => 'Professional Learning Community'],
        ['text' => 'ข้อมูลที่เก็บก่อนเริ่มโครงการเพื่อใช้เปรียบเทียบเรียกว่าอะไร?', 'correct_answer' => 'ข้อมูลพื้นฐาน'],
        ['text' => 'การประเมินที่ทำระหว่างการดำเนินโครงการเพื่อปรับปรุงเรียกว่าอะไร?', 'correct_answer' => 'การประเมินระหว่างดำเนินการ'],
        ['text' => 'การประเมินที่ทำเมื่อสิ้นสุดโครงการเพื่อสรุปผลเรียกว่าอะไร?', 'correct_answer' => 'การประเมินสรุปผล'],
    ];

    private const ESSAY_POOL = [
        ['text' => 'อธิบายความสำคัญของการกำหนดตัวชี้วัดที่ชัดเจนก่อนเริ่มโครงการ'],
        ['text' => 'ยกตัวอย่างการนำผลการติดตามและประเมินผลไปใช้ปรับปรุงการดำเนินงานจริง'],
        ['text' => 'วิเคราะห์ปัจจัยที่ส่งผลต่อความสำเร็จของการนิเทศการศึกษาในบริบทของหน่วยงานท่าน'],
        ['text' => 'อธิบายบทบาทของการให้ข้อมูลป้อนกลับเชิงสร้างสรรค์ในการพัฒนาวิชาชีพครู'],
    ];

    private const FILE_UPLOAD_POOL = [
        ['text' => 'แนบไฟล์แผนการติดตามและประเมินผลที่ท่านจัดทำ พร้อมคำอธิบายสังเขป'],
        ['text' => 'แนบไฟล์รายงานผลการนิเทศการสอนที่ท่านปฏิบัติจริง พร้อมภาพประกอบ (ถ้ามี)'],
    ];

    public function run(): void
    {
        $this->seedFlagshipBanks();
        $this->seedModuleAssessments();
    }

    /**
     * The two flagship courses' course-wide pre/post-tests keep their
     * original, hand-curated 4 MC + 1 essay question banks.
     */
    private function seedFlagshipBanks(): void
    {
        $course1 = Course::where('duration_hours', 6)->first();
        $course2 = Course::where('duration_hours', 8)->first();

        // module_id must be constrained to null: every module now also has
        // its own pre_test/post_test, so an unscoped ->first() would be
        // ambiguous between the course-wide assessment and a module one.
        $assessments = [
            'c1_pre' => Assessment::where('course_id', $course1->id)->whereNull('module_id')->where('type', 'pre_test')->first(),
            'c1_post' => Assessment::where('course_id', $course1->id)->whereNull('module_id')->where('type', 'post_test')->first(),
            'c2_pre' => Assessment::where('course_id', $course2->id)->whereNull('module_id')->where('type', 'pre_test')->first(),
            'c2_post' => Assessment::where('course_id', $course2->id)->whereNull('module_id')->where('type', 'post_test')->first(),
        ];

        $bank = [
            'c1_pre' => [
                ['text' => 'การติดตามและประเมินผล (M&E) มีวัตถุประสงค์หลักเพื่ออะไร?', 'choices' => ['ตรวจสอบความก้าวหน้าและผลลัพธ์ของโครงการ', 'รายงานผลงานต่อผู้บังคับบัญชาเท่านั้น', 'ลดงบประมาณการดำเนินงาน', 'เพิ่มจำนวนบุคลากรในองค์กร'], 'correct' => 0],
                ['text' => 'ข้อใดคือตัวชี้วัดเชิงปริมาณ (Quantitative Indicator)?', 'choices' => ['อัตราการสำเร็จการศึกษา', 'ความพึงพอใจของครู', 'คุณภาพการสอน', 'บรรยากาศในห้องเรียน'], 'correct' => 0],
                ['text' => 'ในวงจร PDCA ตัวอักษร "C" หมายถึงอะไร?', 'choices' => ['Check — การตรวจสอบ', 'Control — การควบคุม', 'Create — การสร้าง', 'Change — การเปลี่ยนแปลง'], 'correct' => 0],
                ['text' => 'การเก็บข้อมูลเชิงคุณภาพ (Qualitative) วิธีใดเหมาะสมที่สุด?', 'choices' => ['การสัมภาษณ์เชิงลึก', 'แบบสำรวจออนไลน์แบบมาตราส่วน', 'การนับจำนวนนักเรียน', 'การทดสอบมาตรฐาน'], 'correct' => 0],
                ['text' => 'สรุปความหมายของการติดตามและประเมินผลใน 1 ประโยค', 'type' => 'short_answer', 'correct_answer' => 'การตรวจสอบความก้าวหน้าและประเมินผลสำเร็จของโครงการอย่างเป็นระบบ'],
                ['text' => 'อธิบายความสำคัญของการกำหนดตัวชี้วัดในการติดตามโครงการ', 'type' => 'essay'],
            ],
            'c1_post' => [
                ['text' => 'ผลลัพธ์ (Outcome) ของ โครงการแตกต่างจากผลผลิต (Output) อย่างไร?', 'choices' => ['Outcome คือการเปลี่ยนแปลงระยะยาว Output คือสิ่งที่ผลิตได้โดยตรง', 'Output คือเป้าหมายสูงสุด Outcome คือกิจกรรม', 'ทั้งสองคำมีความหมายเดียวกัน', 'Outcome วัดได้ง่ายกว่า Output เสมอ'], 'correct' => 0],
                ['text' => 'ข้อใดเป็นเครื่องมือวิเคราะห์สาเหตุปัญหาที่นิยมใช้ในการประเมินผล?', 'choices' => ['แผนภูมิก้างปลา (Fishbone Diagram)', 'Gantt Chart', 'Flow Chart', 'Pie Chart'], 'correct' => 0],
                ['text' => 'Stakeholder Analysis ช่วยในการติดตามโครงการอย่างไร?', 'choices' => ['ระบุผู้มีส่วนได้ส่วนเสียและระดับอิทธิพล', 'คำนวณงบประมาณโครงการ', 'วางแผนกิจกรรมประจำวัน', 'ออกแบบแบบสอบถาม'], 'correct' => 0],
                ['text' => 'Baseline Data มีความสำคัญต่อการประเมินผลอย่างไร?', 'choices' => ['เป็นจุดอ้างอิงเปรียบเทียบก่อน-หลังดำเนินโครงการ', 'ใช้สำหรับจ่ายเงินเดือนบุคลากร', 'ระบุชื่อผู้รับผิดชอบโครงการ', 'กำหนดวันสิ้นสุดโครงการ'], 'correct' => 0],
                ['text' => 'ตัวย่อ SMART ในการตั้งเป้าหมาย ตัว S หมายถึงอะไร?', 'type' => 'short_answer', 'correct_answer' => 'Specific'],
                ['text' => 'ให้ยกตัวอย่างการนำผลการติดตามและประเมินผลไปใช้ปรับปรุงการดำเนินโครงการ', 'type' => 'essay'],
            ],
            'c2_pre' => [
                ['text' => 'การนิเทศการศึกษา (Educational Supervision) มีเป้าหมายหลักคืออะไร?', 'choices' => ['พัฒนาคุณภาพการจัดการเรียนรู้ของครู', 'ตรวจสอบการมาปฏิบัติงานของครู', 'ลดจำนวนชั่วโมงสอนของครู', 'เพิ่มจำนวนห้องเรียน'], 'correct' => 0],
                ['text' => 'กระบวนการนิเทศแบบ Clinical Supervision มีกี่ขั้นตอน?', 'choices' => ['3 ขั้นตอน (ก่อน ระหว่าง หลังสังเกตการสอน)', '2 ขั้นตอน (วางแผนและสะท้อนผล)', '5 ขั้นตอน', '1 ขั้นตอน (สังเกตการสอนเท่านั้น)'], 'correct' => 0],
                ['text' => 'ศึกษานิเทศก์ควรมีทักษะหลักด้านใดในการนิเทศ?', 'choices' => ['การสื่อสาร การสังเกต และการให้ข้อมูลป้อนกลับ', 'การบริหารงบประมาณเท่านั้น', 'การออกแบบหลักสูตรเท่านั้น', 'การทดสอบนักเรียน'], 'correct' => 0],
                ['text' => 'Peer Coaching ในบริบทการนิเทศหมายถึงอะไร?', 'choices' => ['ครูนิเทศและให้ข้อมูลป้อนกลับซึ่งกันและกัน', 'ผู้อำนวยการนิเทศครูเท่านั้น', 'นักเรียนสอนนักเรียน', 'ผู้ปกครองตรวจการบ้าน'], 'correct' => 0],
                ['text' => 'PLC ย่อมาจากคำว่าอะไรในภาษาอังกฤษ?', 'type' => 'short_answer', 'correct_answer' => 'Professional Learning Community'],
                ['text' => 'อธิบายบทบาทของศึกษานิเทศก์ในการพัฒนาคุณภาพการศึกษาในเขตพื้นที่', 'type' => 'essay'],
            ],
            'c2_post' => [
                ['text' => 'การนิเทศแบบ Formative ต่างจาก Summative อย่างไร?', 'choices' => ['Formative ช่วยพัฒนาระหว่างกระบวนการ Summative ตัดสินผลปลายทาง', 'Summative ทำก่อน Formative ทำหลัง', 'ทั้งสองแบบมีวัตถุประสงค์เดียวกัน', 'Formative ใช้เฉพาะการสอบปลายภาค'], 'correct' => 0],
                ['text' => 'ข้อใดเป็นตัวอย่างของ Evidence-Based Supervision?', 'choices' => ['ใช้ข้อมูลผลสัมฤทธิ์นักเรียนเป็นฐานในการให้ข้อเสนอแนะ', 'ให้คำแนะนำตามประสบการณ์ส่วนตัวของนิเทศก์', 'สังเกตการสอนโดยไม่บันทึกข้อมูล', 'ให้ครูประเมินตนเองเท่านั้น'], 'correct' => 0],
                ['text' => 'PLC (Professional Learning Community) สนับสนุนการนิเทศอย่างไร?', 'choices' => ['สร้างชุมชนแห่งการเรียนรู้ให้ครูพัฒนาร่วมกันอย่างต่อเนื่อง', 'แทนที่การนิเทศแบบดั้งเดิมทั้งหมด', 'ลดบทบาทของศึกษานิเทศก์', 'ใช้เฉพาะในโรงเรียนขนาดใหญ่'], 'correct' => 0],
                ['text' => 'การสะท้อนคิด (Reflective Practice) มีประโยชน์ต่อการนิเทศอย่างไร?', 'choices' => ['ช่วยให้ครูวิเคราะห์การสอนและพัฒนาตนเองอย่างมีระบบ', 'ใช้แทนการประเมินครูโดยผู้บริหาร', 'เหมาะสำหรับนักเรียนเท่านั้น', 'ลดเวลาในการนิเทศ'], 'correct' => 0],
                ['text' => 'เครื่องมือที่ใช้สังเกตการสอนเพื่อเก็บข้อมูลเชิงประจักษ์เรียกว่าอะไร?', 'type' => 'short_answer', 'correct_answer' => 'แบบสังเกตการสอน'],
                ['text' => 'วิเคราะห์ปัจจัยที่ส่งผลต่อความสำเร็จของการนิเทศการศึกษาในบริบทของ สพท. และเสนอแนวทางพัฒนา', 'type' => 'essay', 'points' => 2],
            ],
        ];

        foreach ($assessments as $key => $assessment) {
            foreach ($bank[$key] as $sortOrder => $q) {
                $this->createQuestion($assessment, $sortOrder + 1, $q);
            }
        }
    }

    /**
     * Every module-level pre-test/post-test/assignment (see CourseSeeder)
     * draws a small, randomly-sampled set of questions from the shared pools
     * above, shaped by the assessment's own grading mode.
     */
    private function seedModuleAssessments(): void
    {
        $assessments = Assessment::whereNotNull('module_id')->get();

        foreach ($assessments as $assessment) {
            $questions = $this->questionsFor($assessment);

            foreach ($questions as $sortOrder => $q) {
                $this->createQuestion($assessment, $sortOrder + 1, $q);
            }
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function questionsFor(Assessment $assessment): array
    {
        if ($assessment->type === AssessmentType::Assignment) {
            return [
                ['type' => 'essay', 'points' => 2] + fake()->randomElement(self::ESSAY_POOL),
                ['type' => 'file_upload', 'points' => 2] + fake()->randomElement(self::FILE_UPLOAD_POOL),
            ];
        }

        return match ($assessment->grading_mode) {
            // Fully auto-graded: 2 multiple choice + 1 short answer.
            GradingMode::Auto => [
                ...array_map(fn ($q) => ['type' => 'multiple_choice', 'points' => 1] + $q, fake()->randomElements(self::MC_POOL, 2)),
                ['type' => 'short_answer', 'points' => 1] + fake()->randomElement(self::SHORT_ANSWER_POOL),
            ],
            // Fully human-graded: 2 essays, no auto-gradable questions.
            GradingMode::Manual => array_map(
                fn ($q) => ['type' => 'essay', 'points' => 2] + $q,
                fake()->randomElements(self::ESSAY_POOL, 2)
            ),
            // A mix of both: 1 auto-graded MC + 1 human-graded essay.
            GradingMode::Mixed => [
                ['type' => 'multiple_choice', 'points' => 1] + fake()->randomElement(self::MC_POOL),
                ['type' => 'essay', 'points' => 2] + fake()->randomElement(self::ESSAY_POOL),
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $q
     */
    private function createQuestion(Assessment $assessment, int $sortOrder, array $q): void
    {
        $type = $q['type'] ?? 'multiple_choice';
        $isEssay = $type === 'essay';
        $isShortAnswer = $type === 'short_answer';

        $question = Question::firstOrCreate(
            ['assessment_id' => $assessment->id, 'sort_order' => $sortOrder],
            [
                'question_type' => $type,
                'question_text' => $q['text'],
                'points' => $q['points'] ?? 1,
                'grading_mode' => $isEssay || $type === 'file_upload' ? GradingMode::Manual->value : GradingMode::Auto->value,
                'correct_answer' => $isShortAnswer ? $q['correct_answer'] : null,
            ]
        );

        if ($type === 'multiple_choice') {
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
