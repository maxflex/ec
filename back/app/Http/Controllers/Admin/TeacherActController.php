<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherActListResource;
use App\Http\Resources\TeacherActResource;
use App\Models\Teacher;
use App\Models\TeacherAct;
use App\Models\TeacherContract;
use Illuminate\Http\Request;

class TeacherActController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'year'],
    ];

    public function index(Request $request)
    {
        $query = TeacherAct::query();

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

    /**
     * Для фильтра "есть несоответствия"
     */
    private function hasProblems(Request $request)
    {
        $hasProblems = (bool) $request->input('has_problems');

        $data = TeacherAct::query()
            ->where('year', $request->year)
            ->where('is_active', true)
            ->get()
            ->filter(fn (TeacherAct $e) => (bool) $e->problems_count === $hasProblems);

        return paginate(TeacherActListResource::collection($data));
    }
}
