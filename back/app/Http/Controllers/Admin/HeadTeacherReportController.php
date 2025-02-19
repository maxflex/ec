<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\HeadTeacherReportResource;
use App\Models\HeadTeacherReport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HeadTeacherReportController extends Controller
{
    protected $filters = [
        'equals' => ['year', 'teacher_id'],
    ];

    public function index(Request $request)
    {
        $query = HeadTeacherReport::with('teacher');
        $this->filter($request, $query);

        return $this->handleIndexRequest($request, $query, HeadTeacherReportResource::class);
    }

    public function update(Request $request, HeadTeacherReport $headTeacherReport)
    {
        $headTeacherReport->update($request->all());

        return new HeadTeacherReportResource($headTeacherReport);
    }

    public function destroy(HeadTeacherReport $headTeacherReport)
    {
        $headTeacherReport->delete();
    }

    protected function getAvailableYears($query): Collection
    {
        return $query->withoutGlobalScopes()->pluck('year');
    }
}
