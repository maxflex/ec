<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FakeReport
{
    const DISABLE_LOGS = true;
    //  отчёт требуется начиная с N занятия
    const LESSONS_UNTIL_REPORT = 6;

    public static function query(): Builder
    {
        // CTE with last report created_at for each combination
        $lastReportsCte = DB::table('reports')
            ->selectRaw(<<<SQL
                teacher_id,
                client_id,
                year,
                program,
                MAX(created_at) as last_report_created_at
            SQL)
            ->groupBy('teacher_id', 'client_id', 'year', 'program');

        $fakeReportsCte = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contracts as c', 'c.id', '=', 'cl.contract_id')
            ->leftJoinSub(
                $lastReportsCte,
                'lr',
                fn ($join) =>
                $join->on('lr.teacher_id', '=', 'l.teacher_id')
                    ->on('lr.client_id', '=', 'c.client_id')
                    ->on('lr.year', '=', 'g.year')
                    ->on('lr.program', '=', 'g.program')
            )
            ->selectRaw(<<<SQL
                NULL as id,
                l.teacher_id,
                c.client_id,
                g.year,
                g.program,
                NULL as is_moderated,
                NULL as is_published,
                NULL as created_at,
                NULL as price,
                COUNT(*) as lessons_count
            SQL)
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw(<<<SQL
                (lr.last_report_created_at IS NULL OR l.date > lr.last_report_created_at)
            SQL)
            ->groupBy('l.teacher_id', 'c.client_id', 'g.year', 'g.program')
            ->havingRaw(<<<SQL
                COUNT(*) >= ?
            SQL, [
                self::LESSONS_UNTIL_REPORT
            ]);

        return DB::table('fake_reports')
            ->withExpression('last_reports', $lastReportsCte)
            ->withExpression('fake_reports', $fakeReportsCte)
            ->select('*');
    }
}
