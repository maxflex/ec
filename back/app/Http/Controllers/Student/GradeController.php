<?php

namespace App\Http\Controllers\Student;

use App\Http\Resources\GradeResource;
use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends \App\Http\Controllers\Admin\GradeController
{
    public function index(Request $request)
    {
        $request->merge([
            'student_id' => auth()->id(),
        ]);

        return parent::index($request);
    }

    public function journal(Request $request)
    {
        return GradeResource::collection(
            Grade::where('student_id', auth()->id())
                ->where('year', $request->year)
                ->where('quarter', $request->quarter)
                ->get()
        );
    }
}
