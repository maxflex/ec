<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ScholarshipScore extends Model
{
    protected $fillable = [
        'client_id',
        'year',
        'month',
        'score',
        'program'
    ];

    protected $casts = [
        'program' => Program::class
    ];

    public static function getQuery()
    {
        $year = current_academic_year();
        $startFrom = "$year-10-01";

        return DB::table('lessons', 'l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join(
                'contract_version_programs as cvp',
                'cvp.id',
                '=',
                'cl.contract_version_program_id'
            )
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('g.year', $year)
            ->where('l.status', LessonStatus::conducted)
            ->whereRaw("cvp.program LIKE '%External'")
            ->where('l.date', '>', $startFrom)
            ->selectRaw("
                g.year, l.teacher_id, c.client_id, g.program,
                MONTH(l.date) AS `month`, count(*) as lessons_count
            ")
            ->groupByRaw("
                g.year, `month`, l.teacher_id, c.client_id, g.program
            ");
    }
}
