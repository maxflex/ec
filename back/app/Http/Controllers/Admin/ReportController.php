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
            'year', 'program', 'status', 'client_id', 'teacher_id',
            'requirement'
        ],
        'excludeNotRequired' => ['exclude_not_required']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'year' => ['required']
        ]);

        // для быстродействия не делаем union, если выбран фильтр созданные/требуется
        // хотя улучшение сомнительно
        $query = DB::table('r')->withExpression('r',
            Report::selectForUnion()->union(Report::requirements())
        );

        $query->orderByRaw("IF(requirement = 'created', 0, 1) desc");

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

    protected function filterExcludeNotRequired(&$query)
    {
        $query->where('requirement', '<>', 'notRequired');
    }
}
