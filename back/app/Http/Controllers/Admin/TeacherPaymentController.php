<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherPaymentResource;
use App\Models\TeacherPayment;
use Illuminate\Http\Request;

class TeacherPaymentController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id']
    ];

    public function index(Request $request)
    {
        $query = TeacherPayment::query()
            ->with('teacher')
            ->latest();
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, TeacherPaymentResource::class);
    }

    public function show(TeacherPayment $teacherPayment)
    {
        return new TeacherPaymentResource($teacherPayment);
    }

    public function update(TeacherPayment $teacherPayment, Request $request)
    {
        $teacherPayment->update($request->all());
        return new TeacherPaymentResource($teacherPayment);
    }

    public function store(Request $request)
    {
        $teacherPayment = auth()->user()->entity->teacherPayments()->create($request->all());
        return new TeacherPaymentResource($teacherPayment);
    }

    public function destroy(TeacherPayment $teacherPayment)
    {
        $teacherPayment->delete();
        return new TeacherPaymentResource($teacherPayment);
    }
}
