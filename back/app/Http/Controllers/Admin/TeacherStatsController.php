<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Utils\TeacherStatsNew;
use Illuminate\Http\Request;

class TeacherStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'year' => ['required', 'numeric'],
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $year = intval($request->year);

        $teacherStats = new TeacherStatsNew($teacher, $year);

        return $teacherStats->get();
    }
}
