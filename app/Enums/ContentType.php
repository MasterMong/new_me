<?php

namespace App\Enums;

enum ContentType: string
{
    case Video = 'video';
    case Document = 'document';
    case Link = 'link';
}
