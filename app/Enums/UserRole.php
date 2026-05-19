<?php

namespace App\Enums;

enum UserRole: string
{
    case Learner = 'learner';
    case Expert = 'expert';
    case Admin = 'admin';
}
