<?php

namespace App\Models;

use App\Enums\{LessonStatus, Program, TelegramTemplate};
use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\{Attributes\ObservedBy, Builder, Model, Relations\BelongsTo};
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    protected $fillable = [
        'year', 'program', 'price', 'homework_comment',
        'is_moderated', 'is_published', 'client_id',
        'recommendation_comment', 'knowledge_level_comment',
        'cognitive_ability_comment', 'grade'
    ];

    protected $casts = [
        'is_moderated' => 'boolean',
        'is_published' => 'boolean',
        'program' => Program::class,
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function getPreviousAttribute(): ?Report
    {
        return Report::where('teacher_id', $this->teacher_id)
            ->where('client_id', $this->client_id)
            ->where('year', $this->year)
            ->where('program', $this->program)
            ->where('created_at', '<', $this->created_at)
            ->latest()
            ->first();
    }

    public function getLessonsAttribute(): Builder
    {
        $query = Lesson::query()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'lessons.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->where('lessons.status', LessonStatus::conducted->value)
            ->where('conducted_at', '<', $this->created_at)
            ->where('teacher_id', $this->teacher_id)
            ->where('c.client_id', $this->client_id)
            ->where('g.year', $this->year)
            ->where('g.program', $this->program);

        $previousReport = $this->previous;
        if ($previousReport !== null) {
            $query->where('lessons.conducted_at', '>', $previousReport->created_at);
        }

        return $query;
    }

    public function getClientLessonsAttribute(): Collection
    {
        return ClientLesson::whereIn('id', $this->lessons->pluck('cl.id'))->with('lesson')->get();
    }

    /**
     * Прочитать отчёт
     */
    public function read(): void
    {
        TelegramMessage::sendTemplate(
            TelegramTemplate::reportRead,
            $this->client->parent->phones()->withTelegram()->get()->all(),
            ['report' => $this]
        );
    }

    public static function fakeQuery(): \Illuminate\Database\Query\Builder
    {
        //  отчёт требуется начиная с N занятия
        $lessonsUntilReportIsNeeded = 6;

        // CTE with last report created_at for each combination
        $lastReportsCte = DB::table('reports')
            ->selectRaw(<<<SQL
                teacher_id,
                client_id,
                year,
                program,
                MAX(created_at) as last_report_created_at
            SQL
            )
            ->groupBy('teacher_id', 'client_id', 'year', 'program');

        $fakeReportsCte = DB::table('lessons as l')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoinSub(
                $lastReportsCte,
                'lr',
                fn($join) => $join->on('lr.teacher_id', '=', 'l.teacher_id')
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
                COUNT(*) as lessons_count,
                NULL as grade
            SQL
            )
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw(<<<SQL
                (lr.last_report_created_at IS NULL OR l.date > lr.last_report_created_at)
            SQL
            )
            ->groupBy('l.teacher_id', 'c.client_id', 'g.year', 'g.program')
            ->havingRaw(" COUNT(*) >= ?", [
                $lessonsUntilReportIsNeeded
            ]);

        return DB::table('fake_reports')
            ->withExpression('last_reports', $lastReportsCte)
            ->withExpression('fake_reports', $fakeReportsCte)
            ->select('*');
    }

    public function scopeSelectForUnion($query)
    {
        return $query->selectRaw(<<<SQL
            id,
            teacher_id,
            client_id,
            year,
            program,
            is_moderated,
            is_published,
            created_at,
            price,
            NULL as lessons_count,
            `grade`
        SQL);
    }
}
