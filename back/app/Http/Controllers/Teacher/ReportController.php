<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReportListResource;
use App\Http\Resources\ReportResource;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $filters = [
        'equals' => ['year']
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Report::where('teacher_id', auth()->id());
        $this->filter($request, $query);
        return $this->handleIndexRequest($request, $query, ReportListResource::class);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
