<?php

namespace App\Enums;

enum TeacherComplaintStatus: string
{
    case new = 'new';
    case inProgress = 'inProgress';
    case closed = 'closed';
}
