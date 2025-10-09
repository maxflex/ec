<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use App\Utils\TeacherStats\TeacherStatsNew;
use Illuminate\Http\Request;

class TeacherStatsNewController extends Controller
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
        $stats = new TeacherStatsNew($teacher);

        if ($request->has('available_years')) {
            return $stats->getAvailableYears();
        }

        $items = $stats->get(
            intval($request->year),
            $request->mode,
            $request->direction ?? [],
        );

        return [
            'items' => $items,
            'totals' => $stats->getTotals($items),
        ];
    }
}
