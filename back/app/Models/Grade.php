<?php

namespace App\Models;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Grade extends Model
{
    protected $fillable = [
        'grade', 'client_id', 'program', 'year', 'quarter',
        'teacher_id'
    ];

    protected $casts = [
        'grade' => 'int',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Получаем все комбинации ученик-программа-год,
     * в группах, где установлена хотя бы одна четверть
     *
     */
    public static function fakeQuery(int $teacherId = null): Builder
    {
        $cte = DB::table('lessons', 'l')
            ->whereNotNull('quarter')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->selectRaw("
                CONCAT(c.client_id, '-',  g.`year`, '-', cvp.program) AS `id`,
                c.client_id, cvp.program, cl.contract_version_program_id, g.year,
                CAST(sum(if(`quarter` = 'q1', 1, 0)) AS UNSIGNED) AS `q1_cnt`,
                CAST(sum(if(`quarter` = 'q2', 1, 0)) AS UNSIGNED) AS `q2_cnt`,
                CAST(sum(if(`quarter` = 'q3', 1, 0)) AS UNSIGNED) AS `q3_cnt`,
                CAST(sum(if(`quarter` = 'q4', 1, 0)) AS UNSIGNED) AS `q4_cnt`
            ")
            ->groupByRaw('c.client_id, cvp.program, cl.contract_version_program_id, g.year');

        if ($teacherId) {
            $cte->where('l.teacher_id', $teacherId);
        }

        return DB::table('fake_grades')
            ->withExpression('fake_grades', $cte)
            ->selectRaw('*');
    }
}
