<?php

namespace App\Enums;

enum TeacherComplaintRecipient: string
{
    case director = 'director';
    case academicDirector = 'academicDirector';
    case office = 'office';
    case maintenance = 'maintenance';
    case teacherSupport = 'teacherSupport';
    case accounting = 'accounting';
}
