<?php

namespace App\Http\Controllers\Teacher;

use Illuminate\Http\Request;

class TeacherPaymentController extends \App\Http\Controllers\Admin\TeacherPaymentController
{

    public function index(Request $request)
    {
        $request->merge([
            'teacher_id' => auth()->id()
        ]);

        return parent::index($request);
    }
}