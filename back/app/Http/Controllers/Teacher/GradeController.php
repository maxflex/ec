<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Quarter;
use App\Http\Controllers\Controller;
use App\Http\Resources\GradeListResource;
use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GradeController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'program', 'client_id'],
    ];

    public function index(Request $request)
    {
        $query = Grade::fakeQuery(auth()->id());
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, GradeListResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'id' => ['required'],
            'quarter' => [Rule::enum(Quarter::class)],
            'grade' => ['required', 'numeric', 'gt:0']
        ]);
        [$clientId, $year, $program] = explode('-', $request->id);
        $grade = Grade::create([
            'teacher_id' => auth()->id(),
            'client_id' => $clientId,
            'year' => $year,
            'program' => $program,
            'quarter' => $request->quarter,
            'grade' => $request->grade,
        ]);
        return new GradeListResource($grade);
    }

    public function update(Grade $grade, Request $request)
    {
        $grade->update($request->all());
        return new GradeListResource($grade);
    }

    public function show($id)
    {
        $grade = Grade::fakeQuery(auth()->id())->whereId($id)->first();
        abort_if($grade === null, 404);
        return new GradeResource($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
    }
}
