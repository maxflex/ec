<?php

namespace App\Enums;

enum SendTo: string
{
    case students = 'students';
    case representatives = 'representatives';
    case teachers = 'teachers';
}
