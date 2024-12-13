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
            'year', 'program', 'status', 'client_id', 'teacher_id'
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

        $requiredQuery = Report::required();

        $this->filter($request, $query);
        $this->filter($request, $requiredQuery);

        $query->union($requiredQuery);

        return $this->handleIndexRequest($request, $query, ReportListResource::class);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report, Request $request)
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
