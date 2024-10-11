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
        'program', 'text', 'rating'
    ];

    protected $casts = [
        'program' => Program::class
    ];

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
                fn($join) => $join->on('cr.teacher_id', '=', 'l.teacher_id')
                    ->on('cr.client_id', '=', 'c.client_id')
                    ->on('cr.program', '=', 'g.program')
            )
            ->selectRaw(<<<SQL
                NULL AS id,
                l.teacher_id,
                c.client_id,
                g.program,
                NULL AS `rating`,
                NULL AS `created_at`,
                NULL AS `text`,
                count(distinct cl.id) as `lessons_count`
            SQL
            )
            ->where('l.status', LessonStatus::conducted->value)
            ->whereNull('cr.id')
            ->groupBy('l.teacher_id', 'c.client_id', 'g.program');

        return DB::table('fake_reviews')
            ->withExpression('fake_reviews', $fakeReviewsCte)
            ->select('*');
    }


    public function scopeSelectForUnion($query)
    {
        return $query->selectRaw(<<<SQL
            id,
            teacher_id,
            client_id,
            program,
            rating,
            created_at,
            `text`,
            (
                select count(*) from client_lessons as cl
                join contract_version_programs as cvp 
                    on cvp.id = cl.contract_version_program_id and cvp.program = client_reviews.program
                join contract_versions as cv
                    on cv.id = cvp.contract_version_id
                join contracts as c
                    on c.id = cv.contract_id and c.client_id = client_reviews.client_id
                join lessons as l
                    on l.id = cl.lesson_id and l.teacher_id = client_reviews.teacher_id
            ) as `lessons_count`
        SQL
        );
    }

}
