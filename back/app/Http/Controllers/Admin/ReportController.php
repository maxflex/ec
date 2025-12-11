<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use App\Models\Teacher;
use App\Utils\ChatGPT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'status', 'client_id', 'teacher_id', 'is_required',
        ],
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = DB::table('r')->withExpression('r',
            Report::selectForUnion()->union(Report::requirements())
        );

        $query->orderByRaw('
            `is_required` DESC,
            CASE `status`
                WHEN ? THEN 4
                WHEN ? THEN 3
                WHEN ? THEN 2
                WHEN ? THEN 1
                ELSE 0
            END DESC,
            `status` DESC,
            `created_at` DESC
        ', [
            ReportStatus::draft,
            ReportStatus::toCheck,
            ReportStatus::refused,
            ReportStatus::published,
        ]);

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

        return new ReportResource($report);
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

        // у преподов вкладка "новый отчет"
        if (auth()->user() instanceof Teacher) {
            // появляется, только если отчет реально требуется
            $hasRequirement = Report::requirements()
                ->where('teacher_id', auth()->id())
                ->where($params)
                ->exists();
            if ($hasRequirement) {
                $newReport = new Report([
                    ...$params,
                    'status' => ReportStatus::draft,
                ]);
                $newReport->id = -1;
                $newReport->setCreatedAt(now());
                $items->push($newReport);
            }
        }

        return ReportResource::collection($items);
    }

    public function improve(Request $request)
    {
        $validated = $request->validate([
            'cognitive_ability_comment' => ['required', 'string'],
            'homework_comment' => ['required', 'string'],
            'knowledge_level_comment' => ['required', 'string'],
            'recommendation_comment' => ['required', 'string'],
        ]);

        return ChatGPT::improveReport(new Report($validated));
    }
}
