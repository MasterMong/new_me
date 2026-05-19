<?php

namespace App\Enums;

enum AssessmentType: string
{
    case PreTest = 'pre_test';
    case PostTest = 'post_test';
    case ModuleTest = 'module_test';
    case Assignment = 'assignment';
}
