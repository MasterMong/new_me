<?php

namespace App\Services;

class RealCourseContentParser
{
    private const CHOICE_MARKERS = ['ก.', 'ข.', 'ค.', 'ง.'];

    public function __construct(private DocxTextExtractor $extractor) {}

    /**
     * Parse a "M{n} แบบทดสอบ.docx" pre/post-test file into multiple-choice
     * questions with the correct answer resolved from Word's bold-text
     * convention (see DocxTextExtractor). Source docs mix two authoring
     * styles — one answer choice per paragraph, or all four choices fused
     * into a single paragraph's runs (M6) — so parsing works off a single
     * flattened stream of "option" and "text" segments rather than raw
     * paragraph boundaries, and looks for four consecutive ก/ข/ค/ง options
     * to close out a question instead of relying on either style alone.
     *
     * A question whose bold-answer marking is ambiguous (zero or more than
     * one bold choice — a source-doc authoring slip, not a parsing bug) is
     * still returned with a best-effort correct index (the first bold
     * choice, or 0), but flagged in $warnings so the caller can surface it
     * for a human to verify rather than silently trusting a guess.
     *
     * @param  list<string>  $warnings  Populated with one message per ambiguous question.
     * @return list<array{text: string, choices: array{0:string,1:string,2:string,3:string}, correct: int}>
     */
    public function parseQuestions(string $path, array &$warnings = []): array
    {
        $segments = $this->flattenIntoSegments($path);

        $questions = [];
        $buffer = [];
        $started = false;
        $i = 0;
        $count = count($segments);

        while ($i < $count) {
            $segment = $segments[$i];

            // Skip the instructions/preamble — every source file's instructions
            // block ends with a "คะแนนเต็ม N คะแนน" line right before question 1.
            if (! $started) {
                if ($segment['type'] === 'text' && str_contains($segment['text'], 'คะแนนเต็ม')) {
                    $started = true;
                }
                $i++;

                continue;
            }

            $isMarkedChoiceBlock = $this->isChoiceBlock($segments, $i);
            $isImplicitChoiceBlock = ! $isMarkedChoiceBlock && $buffer !== [] && $this->isImplicitChoiceBlock($segments, $i);

            if ($isMarkedChoiceBlock || $isImplicitChoiceBlock) {
                $questionText = $this->stripLeadingNumber(
                    trim(implode(' ', array_column($buffer, 'text')))
                );

                $choices = [
                    $segments[$i]['text'], $segments[$i + 1]['text'],
                    $segments[$i + 2]['text'], $segments[$i + 3]['text'],
                ];
                $boldFlags = [
                    $segments[$i]['bold'], $segments[$i + 1]['bold'],
                    $segments[$i + 2]['bold'], $segments[$i + 3]['bold'],
                ];

                $correct = $this->resolveCorrectChoice($questionText, $boldFlags, $warnings);

                $questions[] = ['text' => $questionText, 'choices' => $choices, 'correct' => $correct];
                $buffer = [];
                $i += 4;

                continue;
            }

            $buffer[] = $segment;
            $i++;
        }

        return $questions;
    }

    /**
     * Parse a "M{n}.docx" outline file into module metadata: title,
     * objectives, content topics (one per video content item), the
     * passing-score percentage and, for the three modules whose outline
     * also names a graded-worksheet threshold, that minimum point value.
     *
     * @return array{title: string, objectives: list<string>, topics: list<string>, passing_score_pct: int, assignment_min_points: ?int, hours: int}
     */
    public function parseModuleOutline(string $path): array
    {
        $lines = array_column($this->extractor->paragraphs($path), 'text');

        [$title, $offset] = $this->extractTitle($lines);

        $sections = ['objectives' => [], 'content' => [], 'assessment' => [], 'duration' => []];
        $current = null;

        for ($i = $offset; $i < count($lines); $i++) {
            $line = $lines[$i];

            if ($line === 'วัตถุประสงค์') {
                $current = 'objectives';

                continue;
            }
            if ($line === 'เนื้อหา') {
                $current = 'content';

                continue;
            }
            if (str_starts_with($line, 'แนวทางการจัด') || str_starts_with($line, 'แนวทางการกิจกรรม') || $line === 'สื่อการเรียนรู้') {
                $current = null;

                continue;
            }
            if (str_starts_with($line, 'การวัดและประเมิน') || str_starts_with($line, 'การวัดผลประเมินผล')) {
                $current = 'assessment';

                continue;
            }
            if (str_starts_with($line, 'ระยะเวลา')) {
                $current = 'duration';

                continue;
            }

            if ($current !== null) {
                $sections[$current][] = preg_replace('/^\d+\.\s*/u', '', $line);
            }
        }

        $passingScorePct = 80;
        $assignmentMinPoints = null;

        foreach ($sections['assessment'] as $line) {
            if (preg_match('/ร้อยละ\s*(\d+)/u', $line, $m)) {
                $passingScorePct = (int) $m[1];
            }
            if (preg_match('/ตั้งแต่\s*(\d+)\s*คะแนน/u', $line, $m)) {
                $assignmentMinPoints = (int) $m[1];
            }
        }

        $hours = 3;

        foreach ($sections['duration'] as $line) {
            if (preg_match('/(\d+)\s*ชั่วโมง/u', $line, $m)) {
                $hours = (int) $m[1];
            }
        }

        return [
            'title' => $title,
            'objectives' => $sections['objectives'],
            'topics' => $sections['content'],
            'passing_score_pct' => $passingScorePct,
            'assignment_min_points' => $assignmentMinPoints,
            'hours' => $hours,
        ];
    }

    /**
     * Parse the course-overview doc ("รายละเอียดหลักสูตร.docx") into the
     * structured fields the course detail page shows — target audience,
     * learning format, completion criteria, instructor team, and สพฐ.
     * certification info. The doc has no table or other structure to key
     * off: it's a flat list of bold section headings each followed by
     * plain-text body paragraphs, so a heading's own bold text is the only
     * thing that groups the paragraphs under it. Headings the doc has that
     * aren't in the map below (e.g. "คุณลักษณะเด่น") are intentionally
     * dropped — the course page only surfaces the five it lists.
     *
     * @return array<string, string>
     */
    public function parseCourseOverview(string $path): array
    {
        $headingToField = [
            'กลุ่มเป้าหมาย' => 'target_audience',
            'รูปแบบการเรียนรู้' => 'learning_format',
            'เกณฑ์การจบหลักสูตร' => 'completion_criteria',
            'ทีมวิทยากร / พี่เลี้ยง' => 'instructor_team',
            'การรับรองจาก สพฐ.' => 'certification_info',
        ];

        $fields = [];
        $currentField = null;

        foreach ($this->extractor->paragraphs($path) as $paragraph) {
            if ($paragraph['text'] === '') {
                continue;
            }

            if ($paragraph['bold']) {
                $currentField = $headingToField[$paragraph['text']] ?? null;

                continue;
            }

            if ($currentField === null) {
                continue;
            }

            $fields[$currentField] = isset($fields[$currentField])
                ? $fields[$currentField]."\n".$paragraph['text']
                : $paragraph['text'];
        }

        return $fields;
    }

    /**
     * Render a knowledge-sheet ("ใบความรู้") docx as simple rich-text HTML
     * for the learner-facing content viewer: a bold paragraph becomes a
     * heading, consecutive "1. …" / "2. …" lines become an ordered list,
     * and everything else is a paragraph. The docx's own embedded images
     * are appended at the end as a gallery — they're anchored/floating
     * drawings in the source file with no reliable link back to a specific
     * paragraph, so reproducing their original in-text position isn't
     * attempted; $imageUrlResolver is handed each image's raw bytes and
     * filename and returns the public URL to embed.
     *
     * @param  callable(string $contents, string $filename): string  $imageUrlResolver
     */
    public function renderKnowledgeSheetHtml(string $path, callable $imageUrlResolver): string
    {
        $html = '';
        $listItems = [];

        $flushList = function () use (&$html, &$listItems) {
            if ($listItems === []) {
                return;
            }

            $html .= '<ol>'.implode('', array_map(fn ($item) => '<li>'.e($item).'</li>', $listItems)).'</ol>';
            $listItems = [];
        };

        foreach ($this->extractor->paragraphs($path) as $paragraph) {
            if ($paragraph['bold']) {
                $flushList();
                $html .= '<h3>'.e($paragraph['text']).'</h3>';

                continue;
            }

            if (preg_match('/^\d+[.)]\s*(.+)/u', $paragraph['text'], $matches)) {
                $listItems[] = $matches[1];

                continue;
            }

            $flushList();
            $html .= '<p>'.e($paragraph['text']).'</p>';
        }

        $flushList();

        $images = $this->extractor->images($path);

        if ($images !== []) {
            $html .= '<h3>รูปประกอบเนื้อหา</h3>';

            foreach ($images as $image) {
                $url = $imageUrlResolver($image['contents'], $image['filename']);
                $html .= '<img src="'.e($url).'" alt="">';
            }
        }

        return $html;
    }

    /**
     * @param  list<string>  $lines
     * @return array{0: string, 1: int} [title, index of first line after the title]
     */
    private function extractTitle(array $lines): array
    {
        $first = $lines[0] ?? '';

        if (preg_match('/^Module\s*\d+\s*(.*)$/u', $first, $m)) {
            $rest = trim($m[1]);

            if ($rest !== '') {
                return [$rest, 1];
            }

            return [trim($lines[1] ?? ''), 2];
        }

        return [$first, 1];
    }

    private function isChoiceBlock(array $segments, int $i): bool
    {
        $expected = ['ก', 'ข', 'ค', 'ง'];

        for ($offset = 0; $offset < 4; $offset++) {
            $segment = $segments[$i + $offset] ?? null;

            if ($segment === null || $segment['type'] !== 'option' || $segment['letter'] !== $expected[$offset]) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fallback for the rare option that was authored as a Word auto-numbered
     * list (w:numPr) instead of typed "ก./ข./ค./ง." text — the marker glyph
     * is rendered by Word from list formatting, so it never appears as run
     * text at all, and flattenIntoSegments() has nothing to key off of.
     * Recognized as four consecutive plain-text segments, none of which
     * looks like the start of the next question, with at least one bold.
     */
    private function isImplicitChoiceBlock(array $segments, int $i): bool
    {
        $boldSeen = false;

        for ($offset = 0; $offset < 4; $offset++) {
            $segment = $segments[$i + $offset] ?? null;

            if ($segment === null || $segment['type'] !== 'text') {
                return false;
            }

            if (preg_match('/^(?:ข้อ\s*)?\d+[.\s]/u', $segment['text']) === 1) {
                return false;
            }

            $boldSeen = $boldSeen || $segment['bold'];
        }

        return $boldSeen;
    }

    /**
     * @param  array{0: bool, 1: bool, 2: bool, 3: bool}  $boldFlags
     */
    /**
     * @param  list<string>  $warnings
     */
    private function resolveCorrectChoice(string $questionText, array $boldFlags, array &$warnings): int
    {
        $boldIndexes = array_keys(array_filter($boldFlags));

        if (count($boldIndexes) === 1) {
            return $boldIndexes[0];
        }

        $warnings[] = sprintf(
            'Expected exactly one bold (correct) answer choice, found %d — defaulted to choice %s. Question: %s',
            count($boldIndexes),
            $boldIndexes === [] ? '"A" (no bold choice found)' : ('"'.chr(65 + $boldIndexes[0]).'"'),
            $questionText
        );

        return $boldIndexes[0] ?? 0;
    }

    private function stripLeadingNumber(string $text): string
    {
        return trim(preg_replace('/^(?:ข้อ\s*)?\d+[.\s]*/u', '', $text, 1));
    }

    /**
     * Flatten a docx's paragraphs into a single ordered stream of segments,
     * each either a marker-led answer choice (type "option", with its
     * resolved ก/ข/ค/ง letter, choice text with the marker stripped, and
     * whether it was bold) or plain non-option text (type "text"). This
     * normalizes the two authoring styles in the source files — one choice
     * per paragraph, or all four choices fused into one paragraph's runs —
     * into the same shape so parseQuestions() doesn't need to special-case
     * either one.
     *
     * @return list<array{type: 'option', letter: string, text: string, bold: bool}|array{type: 'text', text: string, bold: bool}>
     */
    private function flattenIntoSegments(string $path): array
    {
        $out = [];

        foreach ($this->extractor->paragraphRuns($path) as $runs) {
            $text = implode('', array_column($runs, 'text'));
            $boldBytes = '';

            foreach ($runs as $run) {
                $boldBytes .= str_repeat($run['bold'] ? '1' : '0', strlen($run['text']));
            }

            $markerOffsets = [];

            foreach (self::CHOICE_MARKERS as $marker) {
                $pos = 0;

                while (($found = strpos($text, $marker, $pos)) !== false) {
                    $markerOffsets[$found] = mb_substr($marker, 0, 1);
                    $pos = $found + strlen($marker);
                }
            }

            if ($markerOffsets === []) {
                $trimmed = trim($text);

                if ($trimmed !== '') {
                    $out[] = ['type' => 'text', 'text' => $trimmed, 'bold' => str_contains($boldBytes, '1')];
                }

                continue;
            }

            ksort($markerOffsets);
            $offsets = array_keys($markerOffsets);
            $letters = array_values($markerOffsets);

            $lead = trim(substr($text, 0, $offsets[0]));

            if ($lead !== '') {
                $out[] = [
                    'type' => 'text',
                    'text' => $lead,
                    'bold' => str_contains(substr($boldBytes, 0, $offsets[0]), '1'),
                ];
            }

            foreach ($offsets as $idx => $start) {
                $end = $offsets[$idx + 1] ?? strlen($text);
                $segmentText = trim(preg_replace('/^[ก-ง]\.\s*/u', '', trim(substr($text, $start, $end - $start))));

                $out[] = [
                    'type' => 'option',
                    'letter' => $letters[$idx],
                    'text' => $segmentText,
                    'bold' => str_contains(substr($boldBytes, $start, $end - $start), '1'),
                ];
            }
        }

        return $out;
    }
}
