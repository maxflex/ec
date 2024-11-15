<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Resources\HeadTeacherReportResource;
use Illuminate\Http\Request;

class HeadTeacherReportController extends \App\Http\Controllers\Admin\HeadTeacherReportController
{
    public function index(Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id()
        ]);
        return parent::index($request);
    }


    public function store(Request $request)
    {
        $headTeacherReport = auth()->user()->headTeacherReports()->create(
            $request->all()
        );
        return new HeadTeacherReportResource($headTeacherReport);
    }
}
