<?php

namespace App\Enums;

enum LessonStatus: string
{
    case planned = 'planned';
    case conducted = 'conducted';
    case cancelled = 'cancelled';
}
