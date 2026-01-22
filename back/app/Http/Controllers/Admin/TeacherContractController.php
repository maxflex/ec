<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherContractListResource;
use App\Models\Teacher;
use App\Models\TeacherContract;
use Illuminate\Http\Request;

class TeacherContractController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id'],
    ];

    public function index(Request $request)
    {
        $query = TeacherContract::query();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TeacherContractListResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $teacherContract = $teacher->contracts()->create($request->all());

        return new TeacherContractListResource($teacherContract);
    }

    public function show(TeacherContract $teacherContract)
    {
        return $teacherContract;
    }

    public function update(Request $request, TeacherContract $teacherContract)
    {
        $data = $request->validate([
            'teacher_id' => ['required', 'exists:teachers'],
            'year' => ['required', 'integer'],
            'date' => ['required', 'date'],
            'data' => ['required'],
            'user_id' => ['required', 'exists:users'],
            'is_active' => ['boolean'],
            'file' => ['nullable'],
        ]);

        $teacherContract->update($data);

        return $teacherContract;
    }

    public function destroy(TeacherContract $teacherContract)
    {
        $teacherContract->delete();

        return response()->json();
    }

    public function loadData(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'year' => ['required', 'integer'],
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $year = intval($request->year);

        return TeacherContract::loadData($teacher, $year);
    }
}
