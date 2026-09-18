<?php

namespace App\Enums;

enum ContentType: string
{
    case Video = 'video';
    case Document = 'document';
    case Link = 'link';
    case Test = 'test';

    public function label(): string
    {
        return match ($this) {
            self::Video => 'วิดีโอ',
            self::Document => 'เอกสาร',
            self::Link => 'ลิงก์',
            self::Test => 'แบบทดสอบ',
        };
    }
}
