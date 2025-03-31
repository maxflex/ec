<?php

namespace App\Enums;

enum SendTo: string
{
    case students = 'students';
    case parents = 'parents';
    case teachers = 'teachers';
}
