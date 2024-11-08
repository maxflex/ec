<?php

namespace App\Enums;

enum ClientLessonStatus: string
{
    case present = 'present';
    case presentOnline = 'presentOnline';
    case late = 'late';
    case lateOnline = 'lateOnline';
    case absent = 'absent';
}
