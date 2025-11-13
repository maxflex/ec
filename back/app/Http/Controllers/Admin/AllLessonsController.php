<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use App\Models\Violation;
use Illuminate\Http\Request;

class AllLessonsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015'],
        ]);

        $violationsSub = Violation::query()
            ->selectRaw('
                lesson_id,
                COUNT(*) AS client_lesson_violations_count,
                SUM(CASE WHEN is_resolved = 1 THEN 1 ELSE 0 END) AS client_lesson_violations_resolved_count
            ')
            ->groupBy('lesson_id');

        $result = Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->leftJoinSub($violationsSub, 'v', 'v.lesson_id', '=', 'lessons.id')
            ->where('year', $request->input('year'))
            ->selectRaw("
                `date`,
                CAST(SUM(IF(`status` = 'planned', 1, 0)) AS UNSIGNED) as planned_count,
                CAST(SUM(IF(`status` = 'conducted', 1, 0)) AS UNSIGNED) as conducted_count,
                CAST(SUM(IF(`status` = 'cancelled', 1, 0)) AS UNSIGNED) as cancelled_count,
                CAST(SUM(IF(is_unplanned, 1, 0)) AS UNSIGNED) as unplanned_count,
                CAST(SUM(IF(is_free, 1, 0)) AS UNSIGNED) as free_count,

                CAST(SUM(IF(is_violation, 1, 0)) AS UNSIGNED) as violations_violated_count,
                CAST(SUM(IF(is_violation = 0, 1, 0)) AS UNSIGNED) as violations_ok_count,

                CAST(SUM(IFNULL(v.client_lesson_violations_count, 0)) AS UNSIGNED) as client_lesson_violations_count,
                CAST(SUM(IFNULL(v.client_lesson_violations_resolved_count, 0)) AS UNSIGNED) as client_lesson_violations_resolved_count,

                 CAST(SUM(IF(
                    `status` = 'planned' AND TIMESTAMP(`date`, `time`) < NOW() - INTERVAL 1 HOUR
                 , 1, 0)) AS UNSIGNED) as need_conduct_count
            ")
            ->groupBy('date')
            ->get();

        return key_by($result, 'date');
    }
}
