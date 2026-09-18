<?php

namespace App\Services;

use RuntimeException;
use ZipArchive;

class DocxTextExtractor
{
    private const WORD_NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * Every paragraph in the document as its own list of runs — each run's
     * text kept separate from its neighbors, with whether that specific run
     * is bold. Word's own "bold the correct answer" convention (used
     * throughout the source pre/post-test docs) only survives at run
     * granularity: some docs put each answer choice in its own paragraph,
     * others fuse all four choices into a single paragraph's runs, so a
     * plain per-paragraph text+bold flag can't tell choices apart in the
     * fused case. Callers needing simple text should use paragraphs().
     *
     * @return list<list<array{text: string, bold: bool}>>
     */
    public function paragraphRuns(string $path): array
    {
        $xml = $this->readDocumentXml($path);

        $dom = new \DOMDocument;
        $dom->loadXML($xml);
        $xpath = new \DOMXPath($dom);
        $xpath->registerNamespace('w', self::WORD_NS);

        $paragraphs = [];

        foreach ($xpath->query('//w:p') as $p) {
            $runs = [];

            foreach ($xpath->query('.//w:r', $p) as $run) {
                $text = '';

                foreach ($xpath->query('.//w:t', $run) as $t) {
                    $text .= $t->textContent;
                }

                if ($text === '') {
                    continue;
                }

                $bold = $xpath->query('.//w:rPr/w:b', $run)->length > 0;

                $runs[] = ['text' => $text, 'bold' => $bold];
            }

            if ($runs !== []) {
                $paragraphs[] = $runs;
            }
        }

        return $paragraphs;
    }

    /**
     * Every paragraph as plain text plus whether any run in it is bold —
     * a flattened, simpler view of paragraphRuns() for callers that don't
     * need to distinguish multiple runs within one paragraph.
     *
     * @return list<array{text: string, bold: bool}>
     */
    public function paragraphs(string $path): array
    {
        return array_map(
            fn (array $runs) => [
                'text' => trim(implode('', array_column($runs, 'text'))),
                'bold' => in_array(true, array_column($runs, 'bold'), true),
            ],
            $this->paragraphRuns($path)
        );
    }

    public function plainText(string $path): string
    {
        return implode("\n", array_column($this->paragraphs($path), 'text'));
    }

    /**
     * Every image embedded in the document (word/media/*), in the order
     * they're stored in the zip. Docx images are commonly anchored/floating
     * drawings rather than simple inline runs, so there's no reliable way
     * to tie one back to a specific paragraph — callers that need images
     * alongside text get them as a flat list instead.
     *
     * @return list<array{filename: string, contents: string}>
     */
    public function images(string $path): array
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open docx file: {$path}");
        }

        $images = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if ($name !== false && str_starts_with($name, 'word/media/')) {
                $contents = $zip->getFromIndex($i);

                if ($contents !== false) {
                    $images[] = ['filename' => basename($name), 'contents' => $contents];
                }
            }
        }

        $zip->close();

        return $images;
    }

    private function readDocumentXml(string $path): string
    {
        $zip = new ZipArchive;

        if ($zip->open($path) !== true) {
            throw new RuntimeException("Unable to open docx file: {$path}");
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            throw new RuntimeException("word/document.xml not found in: {$path}");
        }

        return $xml;
    }
}
