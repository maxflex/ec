<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeResource;
use App\Http\Resources\QuartersGradesResource;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program', 'student_id'],
    ];

    public function index(Request $request)
    {
        $query = Grade::fakeQuery();
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, QuartersGradesResource::class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'quarter' => [Rule::enum(Quarter::class)],
            'grade' => ['required', 'numeric', 'gt:0'],
        ]);
        [$studentId, $year, $program] = explode('-', $request->id);
        $request->merge([
            'student_id' => $studentId,
            'year' => $year,
            'program' => $program,
        ]);
        $grade = Grade::create($request->all());

        return new GradeResource($grade);
    }

    public function update(Grade $grade, Request $request)
    {
        $grade->update($request->all());

        return new GradeResource($grade);
    }

    public function show($id)
    {
        $grade = Grade::fakeQuery()->whereId($id)->first();
        abort_if($grade === null, 404);

        return new QuartersGradesResource($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
    }
}
