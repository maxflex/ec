<?php

namespace App\Enums;

enum ReportStatus: string
{
    case new = 'new';
    case toCheck = 'toCheck';
    case refused = 'refused';
    case moderated = 'moderated';
    case published = 'published';
    case empty = 'empty';
}
