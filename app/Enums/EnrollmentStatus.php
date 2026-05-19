<?php

namespace App\Enums;

enum EnrollmentStatus: string
{
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Certified = 'certified';
}
