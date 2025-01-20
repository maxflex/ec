<?php

namespace App\Enums;

enum ReportStatus: string
{
    case draft = 'draft';
    case toCheck = 'toCheck';
    case refused = 'refused';
    case published = 'published';
    case empty = 'empty';
}
