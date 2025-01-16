<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lesson;
use Illuminate\Http\Request;

class AllLessonsController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'year' => ['required', 'numeric', 'min:2015']
        ]);

        $result = Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('year', $request->input('year'))
            ->selectRaw("
                `date`,
                 CAST(SUM(IF(`status` = 'planned', 1, 0)) AS UNSIGNED) as planned_count,
                 CAST(SUM(IF(`status` = 'conducted', 1, 0)) AS UNSIGNED) as conducted_count,
                 CAST(SUM(IF(`status` = 'cancelled', 1, 0)) AS UNSIGNED) as cancelled_count,
                 CAST(SUM(IF(
                    `status` = 'planned' AND TIMESTAMP(`date`, `time`) < NOW() - INTERVAL 1 HOUR
                 , 1, 0)) AS UNSIGNED) as need_conduct_count
            ")
            ->groupBy('date')
            ->get();

        return key_by($result, 'date');
    }
}
