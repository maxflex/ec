<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ClientReview extends Model
{
    protected $fillable = [
        'program', 'text', 'rating',
    ];

    protected $casts = [
        'program' => Program::class,
    ];

    /**
     * Требования создать отзыв
     */
    public static function requirements()
    {
        // GROUP_CONCAT(DISTINCT g.year) as `years`,
        $requirements = DB::table('lessons', 'l')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->selectRaw('
                NULL AS id,
                l.teacher_id,
                c.client_id,
                cvp.program,
                NULL AS `rating`,
                NULL AS `created_at`,
                NULL AS `text`
            ')
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw('NOT EXISTS (
                select 1 from client_reviews as cr
                where cr.teacher_id = l.id
                and cr.client_id = c.id
                and cr.program = cvp.program
            )')
            ->groupBy('l.teacher_id', 'c.client_id', 'cvp.program');

        return DB::table('requirements')->withExpression('requirements', $requirements);
    }

    public function scopeSelectForUnion($query)
    {
        $query->selectRaw('
            id,
            teacher_id,
            client_id,
            program,
            rating,
            created_at,
            text
        ');
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
}
