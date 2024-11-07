<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\PersonResource;
use App\Models\Teacher;

class TeacherController extends Controller
{
    public function show(Teacher $teacher)
    {
        return new PersonResource($teacher);
    }
}