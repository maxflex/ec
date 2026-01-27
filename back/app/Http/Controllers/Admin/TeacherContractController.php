<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherContractListResource;
use App\Http\Resources\TeacherContractResource;
use App\Models\Teacher;
use App\Models\TeacherContract;
use Illuminate\Http\Request;

class TeacherContractController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'is_active', 'year'],
    ];

    public function index(Request $request)
    {
        // если выбран фильтр "есть несоответствия"
        if ($request->has('has_problems')) {
            return $this->hasProblems($request);
        }

        $query = TeacherContract::query();

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, TeacherContractListResource::class);
    }

    /**
     * Для фильтра "есть несоответствия"
     */
    private function hasProblems(Request $request)
    {
        $hasProblems = (bool) $request->input('has_problems');

        $data = TeacherContract::query()
            ->where('year', $request->year)
            ->where('is_active', true)
            ->get()
            ->filter(fn (TeacherContract $e) => (bool) $e->problems_count === $hasProblems);

        return paginate(TeacherContractListResource::collection($data));
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
        return new TeacherContractResource($teacherContract);
    }

    public function update(Request $request, TeacherContract $teacherContract)
    {
        $teacherContract->update($request->all());

        return new TeacherContractListResource($teacherContract);
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

        return TeacherContract::loadData($teacher, $year, $request->input('date_from'), $request->input('date_to'));
    }
}
