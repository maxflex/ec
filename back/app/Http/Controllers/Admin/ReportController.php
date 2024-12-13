<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'status', 'client_id', 'teacher_id'
        ],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => ['required']
        ]);

        if ($request->has('type')) {
            $query = $request->type
                ? Report::selectForUnion()
                : Report::required();
        } else {
            $query = DB::table('r')->withExpression('r',
                Report::selectForUnion()->union(Report::required())
            );
        }

        $query->with(['teacher', 'client']);

        $this->filter($request, $query);

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
//
//    protected function filterType(&$query, $type)
//    {
//        $type ? $query->whereNotNull('id') : $query->whereNull('id');
//    }
}
