<?php

namespace App\Enums;

enum InstructionStatus: string
{
    case draft = 'draft';
    case readyForConst = 'readyForConst';
    case toCheckTeacher = 'toCheckTeacher';
    case finalCheckBeforePublished = 'finalCheckBeforePublished';
    case published = 'published';
}
