<?php

namespace App\Enums;

enum ContractLessonStatus: string
{
    case present = 'present';
    case late = 'late';
    case absent = 'absent';
}
