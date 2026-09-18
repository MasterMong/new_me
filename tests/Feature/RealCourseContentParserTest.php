<?php

use App\Services\DocxTextExtractor;
use App\Services\RealCourseContentParser;

function realCourseFixturePath(string $relative): string
{
    return base_path('ข้อมูลทำระบบ ME-Learning/'.$relative);
}

beforeEach(function () {
    $this->parser = new RealCourseContentParser(new DocxTextExtractor);
});

test('parses one-choice-per-paragraph questions with the bold answer resolved', function () {
    $questions = $this->parser->parseQuestions(realCourseFixturePath('4. Pre-Post Test/M1 แบบทดสอบ.docx'));

    expect($questions)->toHaveCount(15);

    $first = $questions[0];
    expect($first['choices'])->toHaveCount(4)
        ->and($first['choices'][$first['correct']])->toBe('วิเคราะห์ความเชื่อมโยงระหว่างกิจกรรมกับผลลัพธ์ที่คาดหวัง');
});

test('parses questions whose four choices are fused into a single paragraph', function () {
    $questions = $this->parser->parseQuestions(realCourseFixturePath('4. Pre-Post Test/M6 แบบทดสอบ.docx'));

    expect($questions)->toHaveCount(15);

    $first = $questions[0];
    expect($first['text'])->toContain('เครื่องมือใดเหมาะสำหรับเก็บรวบรวมข้อมูล')
        ->and($first['choices'][$first['correct']])->toBe('Google Forms');
});

test('parses a question authored as a Word auto-numbered list with no literal marker text', function () {
    $questions = $this->parser->parseQuestions(realCourseFixturePath('4. Pre-Post Test/M6 แบบทดสอบ.docx'));

    $promptQuestion = collect($questions)->first(fn ($q) => str_contains($q['text'], 'โครงสร้าง Prompt'));

    expect($promptQuestion)->not->toBeNull()
        ->and($promptQuestion['choices'][$promptQuestion['correct']])->toBe('บทบาท + บริบท + งานที่ต้องการ + รูปแบบ Output');
});

test('M8 parses cleanly with no ambiguous-answer warnings', function () {
    // Was previously ambiguous: a stray bold space left over from editing made
    // the Hattie & Timperley question look like it had two correct choices
    // (see the RealCourseSeeder commit history) — fixed directly in the
    // source .docx, so this now parses with zero warnings.
    $warnings = [];
    $questions = $this->parser->parseQuestions(realCourseFixturePath('4. Pre-Post Test/M8 แบบทดสอบ.docx'), $warnings);

    $hattieQuestion = collect($questions)->first(fn ($q) => str_contains($q['text'], 'Hattie'));

    expect($questions)->toHaveCount(20)
        ->and($warnings)->toBe([])
        ->and($hattieQuestion['choices'][$hattieQuestion['correct']])->toBe('Where am I going? → How am I going? → Where to next?');
});

test('an ambiguous bold marking is kept with a best-effort answer and a warning', function () {
    $extractor = new class extends DocxTextExtractor
    {
        public function paragraphRuns(string $path): array
        {
            return [
                [['text' => 'คะแนนเต็ม 1 คะแนน', 'bold' => false]],
                [['text' => 'คำถามทดสอบ', 'bold' => false]],
                [['text' => 'ก. หนึ่ง', 'bold' => true]],
                [['text' => 'ข. สอง', 'bold' => false]],
                [['text' => 'ค. สาม', 'bold' => false]],
                [['text' => 'ง. สี่', 'bold' => true]],
            ];
        }
    };

    $warnings = [];
    $questions = (new RealCourseContentParser($extractor))->parseQuestions('unused-path-stubbed-extractor', $warnings);

    expect($questions)->toHaveCount(1)
        ->and($questions[0]['correct'])->toBe(0)
        ->and($warnings)->toHaveCount(1)
        ->and($warnings[0])->toContain('found 2');
});

test('parses a module outline with a title on its own line and no worksheet threshold', function () {
    $outline = $this->parser->parseModuleOutline(realCourseFixturePath('3. Outline M1-M9/M1.docx'));

    expect($outline['title'])->toBe('หลักการ แนวคิด และทฤษฎีการติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน')
        ->and($outline['hours'])->toBe(3)
        ->and($outline['passing_score_pct'])->toBe(80)
        ->and($outline['assignment_min_points'])->toBeNull()
        ->and($outline['topics'])->toHaveCount(2);
});

test('parses a module outline with the title fused onto the "Module N" line', function () {
    $outline = $this->parser->parseModuleOutline(realCourseFixturePath('3. Outline M1-M9/M4.docx'));

    expect($outline['title'])->toBe('เครื่องมือ การใช้เครื่องมือ และเกณฑ์การให้คะแนนสำหรับการติดตาม และประเมินผลการจัดการศึกษาขั้นพื้นฐาน')
        ->and($outline['topics'])->toHaveCount(2);
});

test('parses a module outline with an expert-graded worksheet threshold', function () {
    $outline = $this->parser->parseModuleOutline(realCourseFixturePath('3. Outline M1-M9/M8.docx'));

    expect($outline['assignment_min_points'])->toBe(64)
        ->and($outline['topics'])->toHaveCount(5);
});

test('parses the course overview doc into its five bold-headed sections', function () {
    $overview = $this->parser->parseCourseOverview(
        realCourseFixturePath('1. คำอธิบายหลหักสูตร/1. รายละเอียดหลักสูตร.docx')
    );

    expect($overview['target_audience'])->toBe(
        "นักวิชาการศึกษา / นักวิเคราะห์นโยบาย (บุคลากรส่วนกลาง)\nบุคลากรใน สพท. / สถานศึกษาสังกัด สพฐ.\nผู้ที่สนใจ"
    )
        ->and($overview['learning_format'])->toBe('ศึกษาด้วยตนเอง ผ่าน e-Learning')
        ->and($overview['completion_criteria'])->toBe('มีผลการเรียนมากกว่า 80% และการประเมินทักษะจากผลงาน ผ่านครบ 3 ชิ้นงาน')
        ->and($overview['instructor_team'])->toBe('บันทึกคลิปการสอน และตรวจใบงานพร้อมเฉลย')
        ->and($overview['certification_info'])->toBe("การรับเกียรติบัตร\nทำเนียบนักติดตามฯ ระดับพื้นฐาน")
        // "คุณลักษณะเด่น" and "คำอธิบายระดับ สมรรถนะ" aren't in the field
        // map, so their bodies shouldn't be attributed to any field.
        ->and($overview)->toHaveCount(5);
});

test('renders a knowledge sheet docx as headed, listed rich-text html with its images appended', function () {
    $resolvedImages = [];

    $html = $this->parser->renderKnowledgeSheetHtml(
        realCourseFixturePath('5. เอกสารประกอบ/M1/M1. ปรับ 18 ส.ค. 69 10.54 น..docx'),
        function (string $contents, string $filename) use (&$resolvedImages) {
            $resolvedImages[] = $filename;

            return 'https://example.com/'.$filename;
        }
    );

    expect($html)->toContain('<h3>วัตถุประสงค์</h3>')
        ->and($html)->toContain('<p>')
        // "1. เพื่อ..." / "2. เพื่อ..." lines fold into a single ordered list.
        ->and($html)->toContain(
            '<ol><li>เพื่อให้ผู้เข้ารับการพัฒนามีความรู้ความเข้าใจเกี่ยวกับหลักการ แนวคิด และทฤษฎีการติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน</li>'
            .'<li>เพื่อให้ผู้เข้ารับการพัฒนามีความรู้ความเข้าใจเกี่ยวกับทฤษฎีที่เกี่ยวข้องกับการติดตามและประเมินผลการจัดการศึกษาขั้นพื้นฐาน</li></ol>'
        )
        ->and($html)->toContain('<h3>รูปประกอบเนื้อหา</h3>')
        ->and($html)->toContain('<img src="https://example.com/')
        ->and($resolvedImages)->not->toBeEmpty();
});

test('the knowledge sheet renderer skips the image gallery when the source docx has no images', function () {
    $resolverCalled = false;

    $html = $this->parser->renderKnowledgeSheetHtml(
        realCourseFixturePath('1. คำอธิบายหลหักสูตร/1. รายละเอียดหลักสูตร.docx'),
        function () use (&$resolverCalled) {
            $resolverCalled = true;

            return 'https://example.com/pic.png';
        }
    );

    expect($html)->toContain('<h3>กลุ่มเป้าหมาย</h3>')
        ->and($html)->not->toContain('รูปประกอบเนื้อหา')
        ->and($html)->not->toContain('<img')
        ->and($resolverCalled)->toBeFalse();
});
