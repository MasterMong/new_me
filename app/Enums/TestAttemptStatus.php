<?php

namespace App\Enums;

enum TestAttemptStatus: string
{
    case InProgress = 'in_progress';
    case Submitted = 'submitted';
    case PendingReview = 'pending_review';
    case Passed = 'passed';
    case Failed = 'failed';
    case RevisionNeeded = 'revision_needed';
}
