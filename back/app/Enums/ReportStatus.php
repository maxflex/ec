<?php

namespace App\Enums;

enum ReportStatus: string
{
    case new = 'new';
    case refused = 'refused';
    case moderated = 'moderated';
    case published = 'published';
}
