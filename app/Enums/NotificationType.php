<?php

namespace App\Enums;

enum NotificationType: string
{
    case AssignmentSubmitted = 'assignment_submitted';
    case ReviewCompleted = 'review_completed';
    case RevisionNeeded = 'revision_needed';
    case CertificateIssued = 'certificate_issued';
}
