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

test('a question with more than one bold choice is kept with a best-effort answer and a warning', function () {
    $warnings = [];
    $questions = $this->parser->parseQuestions(realCourseFixturePath('4. Pre-Post Test/M8 แบบทดสอบ.docx'), $warnings);

    expect($questions)->toHaveCount(20)
        ->and($warnings)->toHaveCount(1)
        ->and($warnings[0])->toContain('Hattie');
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
