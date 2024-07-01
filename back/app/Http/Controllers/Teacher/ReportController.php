<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\Program;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\FakeReport;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'teacher_id'],
        'type' => ['type']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate(['year' => ['required']]);
        $request->merge([
            'teacher_id' => auth()->id()
        ]);

        $query = Report::query()
            ->prepareForUnion()
            ->latest()
            ->with(['teacher', 'client']);

        $fakeQuery = FakeReport::query();

        $this->filter($request, $query);
        $this->filter($request, $fakeQuery);

        $query->union($fakeQuery);

        return $this->handleIndexRequest($request, $query, ReportListResource::class);
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
        $report = auth()->user()->entity->reports()->create($request->all());
        return new ReportListResource($report);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        return new ReportResource($report);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Report $report)
    {
        $report->update($request->all());
        return new ReportListResource($report);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Report $report)
    {
        $report->delete();
    }

    protected function filterType(&$query, $type)
    {
        $type ? $query->whereNotNull('id') : $query->whereNull('id');
    }
}
