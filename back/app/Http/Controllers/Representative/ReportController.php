<?php

namespace App\Http\Controllers\Representative;

use App\Enums\ReportStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => ['year'],
    ];

    public function index(Request $request)
    {
        $query = Report::query()
            ->where('client_id', auth()->user()->client_id)
            ->where('status', ReportStatus::published)
            ->orderBy('to_check_at');

        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, ReportListResource::class);
    }

    /**
     * Display the specified resource.
     */
    public function show(Report $report)
    {
        abort_if($report->status !== ReportStatus::published, 404);
        abort_if($report->client_id !== auth()->user()->client_id, 404);
        $report->read();

        return new ReportResource($report);
    }
}
