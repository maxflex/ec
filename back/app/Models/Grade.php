<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Quarter;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Grade extends Model
{
    protected $fillable = [
        'grade', 'client_id', 'program', 'year', 'quarter'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function fakeQuery(?Teacher $teacher = null): Builder
    {
        /**
         * Группа-четверть, в которой прошли все занятия (нет запланированных)
         * Только по этим группа-четвертям смотреть нужна ли оценка
         */
        $groupQuarterSub = DB::table('lessons')
            ->selectRaw(<<<SQL
              `group_id`, `quarter`, sum(`status` = ?) as planned
            SQL, [
                LessonStatus::planned->value
            ])
            ->whereNotNull('quarter')
            ->when(
                $teacher,
                fn ($q) =>
                $q->whereIn('group_id', $teacher->lessons()->pluck('group_id')->unique())
            )
            ->groupBy('group_id', 'quarter')
            ->having('planned', '=', 0);

        $fakeGrades = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contracts as c', 'c.id', '=', 'cl.contract_id')
            ->joinSub(
                $groupQuarterSub,
                'gq',
                fn ($join) =>
                $join->on('gq.group_id', '=', 'l.group_id')
                    ->on('gq.quarter', '=', 'l.quarter')
            )
            ->leftJoin(
                'grades as gr',
                fn ($join) =>
                $join->on('gr.client_id', '=', 'c.client_id')
                    ->on('gr.program', '=', 'g.program')
                    ->on('gr.year', '=', 'g.year')
                    ->on('gr.quarter', '=', 'l.quarter')
            )
            ->selectRaw(<<<SQL
                NULL AS id,
                c.client_id,
                g.program,
                g.year,
                l.quarter,
                NULL AS `grade`,
                NULL AS `created_at`
            SQL)
            ->where('l.status', LessonStatus::conducted->value)
            ->whereNotNull('l.quarter')
            ->whereNull('gr.id')
            ->groupBy('c.client_id', 'g.program', 'g.year', 'l.quarter');

        return DB::table('fake_grades')
            ->withExpression('fake_grades', $fakeGrades)
            ->select('*');
    }

    public static function fakeQueryFinal(): Builder
    {
        /**
         * Итоговая оценка нужна только в тех группах, где прошли все занятия
         */
        $finalSub = DB::table('lessons')
            ->selectRaw(<<<SQL
              `group_id`, sum(`status` = ?) as planned
            SQL, [
                LessonStatus::planned->value
            ])
            ->whereNotNull('quarter')
            ->groupBy('group_id')
            ->having('planned', '=', 0);

        $finalGrades = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contracts as c', 'c.id', '=', 'cl.contract_id')
            ->joinSub($finalSub, 'f', 'f.group_id', '=', 'g.id')
            ->leftJoin(
                'grades as gr',
                fn ($join) =>
                $join->on('gr.client_id', '=', 'c.client_id')
                    ->on('gr.program', '=', 'g.program')
                    ->on('gr.year', '=', 'g.year')
                    ->where('gr.quarter', Quarter::final->value)
            )
            ->selectRaw(<<<SQL
                NULL AS id,
                c.client_id,
                g.program,
                g.year,
                ? AS `quarter`,
                NULL AS `grade`,
                NULL AS `created_at`
            SQL, [
                Quarter::final->value
            ])
            ->where('l.status', LessonStatus::conducted->value)
            ->whereNotNull('l.quarter')
            ->whereNull('gr.id')
            ->groupBy('c.client_id', 'g.program', 'g.year');

        return DB::table('final_grades')
            ->withExpression('final_grades', $finalGrades)
            ->select('*');
    }

    public function scopePrepareForUnion($query)
    {
        return $query->selectRaw(<<<SQL
            id,
            client_id,
            program,
            `year`,
            `quarter`,
            `grade`,
            created_at
        SQL);
    }
}
