<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\Request;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::query()
            ->orderByRaw(<<<SQL
                if(`status` = 'active', 1, 0) desc,
                concat(last_name, first_name, middle_name) asc
            SQL);
        return $this->handleIndexRequest($request, $query);
    }

    public function show(Teacher $teacher)
    {
        return new TeacherResource($teacher);
    }
}
