<?php

namespace App\Enums;

enum ClientLessonStatus: string
{
    case present = 'present';
    case late = 'late';
    case absent = 'absent';
}
