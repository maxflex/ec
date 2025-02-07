<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\Teacher;
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

//        $query->orderByRaw("");
        $query->orderByRaw("
            IF(
                requirement = 'required', 2,
                IF(requirement = 'created', 1, 0)
            ) DESC,
            `status` ASC,
            `created_at` DESC
        ");

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

    public function tabs(Request $request)
    {
        if ($request->has('id')) {
            $report = Report::find($request->input('id'));
            $params = [
                'teacher_id' => $report->teacher_id,
                'client_id' => $report->client_id,
                'program' => $report->program,
                'year' => $report->year,
            ];
        } else {
            $params = [
                'teacher_id' => $request->input('teacher_id'),
                'client_id' => $request->input('client_id'),
                'program' => Program::from($request->input('program')),
                'year' => intval($request->input('year')),
            ];
        }

        $items = Report::where($params)->get();

        if (get_class(auth()->user()) === Teacher::class) {
            $newReport = new Report([
                ...$params,
                'status' => ReportStatus::draft,
            ]);
            $newReport->id = -1;
            $newReport->setCreatedAt(now());
            $items->push($newReport);
        }

        return ReportResource::collection($items);
    }

    protected function filterExcludeNotRequired(&$query)
    {
        $query->where('requirement', '<>', 'notRequired');
    }
}
