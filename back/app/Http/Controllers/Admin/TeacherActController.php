<?php

namespace App\Http\Controllers\Admin;

use App\Exports\TeacherActExport;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherActListResource;
use App\Http\Resources\TeacherActResource;
use App\Models\Teacher;
use App\Models\TeacherAct;
use App\Models\TeacherContract;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TeacherActController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'year'],
    ];

    public function index(Request $request)
    {
        $query = TeacherAct::query()
            ->with('teacher')
            ->join('teachers as t', 't.id', '=', 'teacher_acts.teacher_id')
            ->orderByRaw('t.last_name, t.first_name, t.middle_name, `date`');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TeacherActListResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $teacherAct = $teacher->contracts()->create($request->all());

        return new TeacherActListResource($teacherAct);
    }

    public function show(TeacherAct $teacherAct)
    {
        return new TeacherActResource($teacherAct);
    }

    public function update(Request $request, TeacherAct $teacherAct)
    {
        $teacherAct->update($request->all());

        return new TeacherActListResource($teacherAct);
    }

    public function destroy(TeacherAct $teacherAct)
    {
        $teacherAct->delete();

        return response()->json();
    }

    public function loadMassData(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer'],
        ]);

        $year = intval($request->year);

        return TeacherAct::loadMassData($year, $request->input('date_from'), $request->input('date_to'));
    }

    public function massStore(Request $request)
    {
        $request->validate([
            'year' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'date_from' => ['required', 'date_format:Y-m-d'],
            'date_to' => ['required', 'date_format:Y-m-d'],
            'teacher_ids' => ['required', 'array'],
            'teacher_ids.*' => ['required', 'exists:teachers,id'],
        ]);

        $year = intval($request->year);

        $teachers = Teacher::whereIn('id', $request->teacher_ids)->get();
        foreach ($teachers as $teacher) {
            $teacher->acts()->create([
                ...$request->all(),
                'data' => TeacherContract::loadData($teacher, $year, $request->input('date_from'), $request->input('date_to')),
            ]);
        }
    }

    public function export()
    {
        $teacherActs = TeacherAct::query()
            ->with('teacher')
            ->get()
            ->sortBy(['teacher.last_name', 'teacher.first_name', 'teacher.middle_name', 'date']);

        $export = new TeacherActExport($teacherActs);

        return Excel::download($export, 'acts.xlsx');
    }
}
