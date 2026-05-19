<?php

namespace App\Enums;

enum GradingMode: string
{
    case Auto = 'auto';
    case Manual = 'manual';
    case Mixed = 'mixed';
}
