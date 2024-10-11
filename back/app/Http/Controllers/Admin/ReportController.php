<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'is_published', 'is_moderated', 'client_id', 'teacher_id'
        ],
        'type' => ['type']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => ['required']
        ]);

        $query = Report::query()
            ->selectForUnion()
            ->with(['teacher', 'client']);

        $fakeQuery = Report::fakeQuery();

        $this->filter($request, $query);
        $this->filter($request, $fakeQuery);

        $query->union($fakeQuery);

        return $this->handleIndexRequest($request, $query, ReportListResource::class);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report, Request $request)
    {
        abort_if(!$report->is_published && $request->has('is_published'), 404);
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
