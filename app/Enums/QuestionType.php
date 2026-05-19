<?php

namespace App\Enums;

enum QuestionType: string
{
    case MultipleChoice = 'multiple_choice';
    case Essay = 'essay';
    case FileUpload = 'file_upload';
}
