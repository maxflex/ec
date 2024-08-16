<?php

namespace App\Models;

use App\Enums\LessonStatus;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FakeClientReview
{
    const DISABLE_LOGS = true;

    public static function query(): Builder
    {
        $fakeReviewsCte = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin(
                'client_reviews as cr',
                fn ($join) =>
                $join->on('cr.teacher_id', '=', 'l.teacher_id')
                    ->on('cr.client_id', '=', 'c.client_id')
                    ->on('cr.program', '=', 'g.program')
            )
            ->selectRaw(<<<SQL
                NULL AS id,
                l.teacher_id,
                c.client_id,
                g.program,
                NULL AS `rating`,
                NULL AS `created_at`
            SQL)
            ->where('l.status', LessonStatus::conducted->value)
            ->whereNull('cr.id')
            ->groupBy('l.teacher_id', 'c.client_id', 'g.program');

        return DB::table('fake_reviews')
            ->withExpression('fake_reviews', $fakeReviewsCte)
            ->select('*');
    }
}
