<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Program;
use App\Http\Resources\JournalResource;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends \App\Http\Controllers\Admin\ReportController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!($request->has('client_id') && auth()->user()->is_head_teacher)) {
            $request->merge([
                'teacher_id' => auth()->id()
            ]);
        }

        return parent::index($request);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'program' => [Rule::enum(Program::class)],
            'year' => ['required', 'numeric', 'gt:0']
        ]);
        $report = auth()->user()->reports()->create($request->all());
        return new ReportListResource($report);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report, Request $request)
    {
        abort_unless(
            auth()->user()->is_head_teacher || $report->teacher_id === auth()->id(),
            403
        );
        return new ReportResource($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        abort_if($report->teacher_id !== auth()->id(), 403);
        $report->update($request->all());
        return new ReportListResource($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        abort_if($report->teacher_id !== auth()->id(), 403);
        $report->delete();
    }

    /**
     * Получить занятия для отображения в диалоге "Новый отчёт"
     */
    public function lessons(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric'],
            'client_id' => ['required', 'exists:clients,id'],
            'program' => ['required', Rule::enum(Program::class)]
        ]);

        $report = new Report($request->all());
        $report->teacher_id = auth()->id();
        $report->setCreatedAt(now());

        return JournalResource::collection($report->clientLessons);
    }
}
