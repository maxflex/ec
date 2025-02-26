<?php

namespace App\Http\Controllers\Admin;

use App\Enums\LessonStatus;
use App\Enums\TeacherPaymentMethod;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherPaymentResource;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use Illuminate\Http\Request;

class TeacherPaymentController extends Controller
{
    protected $filters = [
        'equals' => ['teacher_id', 'year', 'method'],
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
        $teacherPayment = auth()->user()->teacherPayments()->create($request->all());

        return new TeacherPaymentResource($teacherPayment);
    }

    public function destroy(TeacherPayment $teacherPayment)
    {
        $teacherPayment->delete();

        return new TeacherPaymentResource($teacherPayment);
    }

    public function getSuggestions(Teacher $teacher, Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015'],
        ]);

        $lessonsConducted = $teacher->lessons()
            ->where('status', LessonStatus::conducted)
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('g.year', $request->year)
            ->sum('price');

        $paidLessons = $teacher->payments()
            ->where('method', TeacherPaymentMethod::bill)
            ->where('year', $request->year)
            ->sum('sum');

        $paidOther = $teacher->payments()
            ->where('method', '<>', TeacherPaymentMethod::bill)
            ->where('year', $request->year)
            ->sum('sum');

        $reports = $teacher->reports()
            ->where('year', $request->year)
            ->sum('price');

        $services = $teacher->services()
            ->where('year', $request->year)
            ->sum('sum');

        return [
            'to_pay_lessons' => $lessonsConducted - $paidLessons,
            'to_pay_other' => $reports + $services - $paidOther,
        ];
    }
}
