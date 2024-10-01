<?php

namespace App\Enums;

enum SendTo: string
{
    case studentsAndParents = 'studentsAndParents';
    case students = 'students';
    case parents = 'parents';
}
