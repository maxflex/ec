<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ClientReview extends Model
{
    protected $fillable = [
        'program', 'text', 'rating',
    ];

    protected $casts = [
        'program' => Program::class,
    ];

    public static function fakeQuery(): Builder
    {
        $fakeReviewsCte = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoin(
                'client_reviews as cr',
                fn ($join) => $join->on('cr.teacher_id', '=', 'l.teacher_id')
                    ->on('cr.client_id', '=', 'c.client_id')
                    ->on('cr.program', '=', 'g.program')
            )
            ->selectRaw('
                NULL AS id,
                l.teacher_id,
                c.client_id,
                g.program,
                NULL AS `rating`,
                NULL AS `created_at`,
                NULL AS `text`,
                GROUP_CONCAT(DISTINCT g.year) as `years`,
                COUNT(DISTINCT cl.id) as `lessons_count`
            ')
            ->where('l.status', LessonStatus::conducted->value)
            ->whereNull('cr.id')
            ->groupBy('l.teacher_id', 'c.client_id', 'g.program');

        return DB::table('fake_reviews')
            ->withExpression('fake_reviews', $fakeReviewsCte)
            ->select('*');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSelectForUnion($query)
    {
        $cte = DB::table('client_lessons', 'cl')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->selectRaw('
                c.client_id as cte_client_id,
                l.teacher_id as cte_teacher_id,
                cvp.program as cte_program,
                COUNT(DISTINCT cl.id) as lessons_count,
                GROUP_CONCAT(DISTINCT g.year) as years
            ')
            ->groupByRaw('c.client_id, l.teacher_id, cvp.program');

        return $query
            ->leftJoinSub($cte, 'cte', fn ($join) => $join
                ->on('cte_client_id', '=', 'client_reviews.client_id')
                ->on('cte_teacher_id', '=', 'client_reviews.teacher_id')
                ->on('cte_program', '=', 'client_reviews.program')
            )
            ->selectRaw('
                client_reviews.id,
                client_reviews.teacher_id,
                client_reviews.client_id,
                client_reviews.program,
                client_reviews.rating,
                client_reviews.created_at,
                client_reviews.text,
                cte.years,
                cte.lessons_count
        ');
    }
}
