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
        'program', 'text', 'rating', 'client_id', 'teacher_id',
        'is_marked',
    ];

    protected $casts = [
        'program' => Program::class,
        'is_marked' => 'boolean',
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
                NULL AS `text`,
                0 AS `is_marked`
            ')
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw('NOT EXISTS (
                select 1 from client_reviews as cr
                where cr.teacher_id = l.teacher_id
                and cr.client_id = c.client_id
                and cr.program = cvp.program
            )')
            ->groupBy('l.teacher_id', 'c.client_id', 'cvp.program');

        return DB::table('requirements')->withExpression('requirements', $requirements);
    }

    /**
     * @return array{lessons_count: int, years: array<int>}
     */
    public static function getLessonsCountAndYears(int $clientId, int $teacherId, Program $program): array
    {

        $data = DB::table('client_lessons', 'cl')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->selectRaw('
                COUNT(DISTINCT cl.id) as lessons_count,
                GROUP_CONCAT(DISTINCT c.year) as years
            ')
            ->where('c.client_id', $clientId)
            ->where('l.teacher_id', $teacherId)
            ->where('cvp.program', $program)
            ->groupByRaw('c.client_id, l.teacher_id, cvp.program')
            ->first();

        return [
            'lessons_count' => $data->lessons_count,
            'years' => array_map('intval', explode(',', $data->years)),
        ];
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
            text,
            is_marked
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
