<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeacherPayment;
use Illuminate\Http\Request;

class TeacherPaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = TeacherPayment::query()
            ->with('teacher')
            ->latest();
        return $this->handleIndexRequest($request, $query);
    }

    public function show($id)
    {
        $teacherPayment = TeacherPayment::find($id);
        return $teacherPayment;
    }

    public function store(Request $request)
    {
        $teacherPayment = TeacherPayment::create($request->all());
        return $teacherPayment;
    }
}
