<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Program;
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
        'equals' => [
            'year', 'program', 'client_id', 'quarter'
        ],
        'type' => ['type']
    ];

    public function index(Request $request)
    {
        $query = Grade::query()
            ->latest()
            ->prepareForUnion()
            ->with(['client']);

        $fakeQuery = Grade::fakeQuery(auth()->user()->entity);

        $this->filter($request, $query);
        $this->filter($request, $fakeQuery);

        $query->union($fakeQuery);

        return $this->handleIndexRequest($request, $query, GradeListResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'program' => [Rule::enum(Program::class)],
            'quarter' => [Rule::enum(Quarter::class)],
            'year' => ['required', 'numeric', 'gt:0'],
            'grade' => ['required', 'numeric', 'gt:0']
        ]);
        $grade = Grade::create($request->all());
        return new GradeListResource($grade);
    }

    public function update(Grade $grade, Request $request)
    {
        $grade->update($request->all());
        return new GradeListResource($grade);
    }

    public function show(Grade $grade)
    {
        return new GradeResource($grade);
    }

    public function destroy(Grade $grade)
    {
        $grade->delete();
    }

    protected function filterType(&$query, $type)
    {
        $type ? $query->whereNotNull('id') : $query->whereNull('id');
    }
}
