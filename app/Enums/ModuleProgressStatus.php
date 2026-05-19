<?php

namespace App\Enums;

enum ModuleProgressStatus: string
{
    case Locked = 'locked';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
