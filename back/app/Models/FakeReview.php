<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FakeReview
{
    const DISABLE_LOGS = true;

    public static function query(): Builder
    {
        $fakeReviewsCte = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('contract_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contracts as c', 'c.id', '=', 'cl.contract_id')
            ->leftJoin(
                'client_reviews as cr',
                fn ($join) =>
                $join->on('cr.teacher_id', '=', 'l.teacher_id')
                    ->on('cr.client_id', '=', 'c.client_id')
                    ->on('cr.program', '=', 'g.program')
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
            ->whereNull('cr.id')
            ->groupBy('l.teacher_id', 'c.client_id', 'g.program');

        return DB::table('fake_reviews')
            ->withExpression('fake_reviews', $fakeReviewsCte)
            ->select('*');
    }
}
