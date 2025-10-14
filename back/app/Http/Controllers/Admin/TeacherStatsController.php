<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Utils\TeacherStats\TeacherStats;
use Illuminate\Http\Request;

class TeacherStatsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'teacher_id' => ['required', 'exists:teachers,id'],
            'year' => ['required_without:available_years', 'numeric'],
            'mode' => ['required_without:available_years', 'in:day,week,month'],
            'direction' => ['sometimes', 'array'],
        ]);

        $teacher = Teacher::find($request->teacher_id);
        $stats = new TeacherStats($teacher);

        if ($request->has('available_years')) {
            return $stats->getAvailableYears();
        }

        $dailyStats = $stats->getDaily(
            intval($request->year),
            $request->direction ?? [],
        );

        return [
            'items' => $stats->groupBy($dailyStats, $request->mode),
            'totals' => $stats->getTotals($dailyStats),
        ];
    }
}
