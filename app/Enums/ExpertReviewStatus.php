<?php

namespace App\Enums;

enum ExpertReviewStatus: string
{
    case Pending = 'pending';
    case Passed = 'passed';
    case RevisionNeeded = 'revision_needed';
}
