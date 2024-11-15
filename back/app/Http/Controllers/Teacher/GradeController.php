<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\QuartersGradesResource;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Admin\GradeController
{
    public function index(Request $request)
    {
        $query = ($request->has('client_id') && auth()->user()->is_head_teacher)
            ? Grade::fakeQuery()
            : Grade::fakeQuery(auth()->id());
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, QuartersGradesResource::class);
    }

    public function store(Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id()
        ]);

        return parent::store($request);
    }

    public function update(Grade $grade, Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id()
        ]);

        return parent::update($grade, $request);
    }
}
