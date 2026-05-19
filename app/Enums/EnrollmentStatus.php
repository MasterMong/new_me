<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Certified = 'certified';

    public function label(): string
    {
        return match ($this) {
            self::InProgress => 'กำลังเรียน',
            self::Completed => 'เรียนจบแล้ว',
            self::Certified => 'ได้รับเกียรติบัตร',
        };
    }
}
