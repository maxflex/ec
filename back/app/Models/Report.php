<?php

namespace App\Models;

use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\ReportDelivery;
use App\Enums\ReportStatus;
use App\Enums\TelegramTemplate;
use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    protected $fillable = [
        'year', 'program', 'price', 'client_id', 'status', 'grade',
        'recommendation_comment', 'knowledge_level_comment', 'teacher_id',
        'cognitive_ability_comment', 'homework_comment', 'delivery',
    ];

    protected $casts = [
        'program' => Program::class,
        'status' => ReportStatus::class,
        'delivery' => ReportDelivery::class,
    ];

    /**
     * Запрос получает все "требуется создать"
     * https://doc.ege-centr.ru/tasks/834
     */
    public static function requirements()
    {
        $year = current_academic_year();
        $nextYear = $year + 1;

        $periods = [
            ["$year-10-15", "$year-11-10"],
            ["$year-12-15", "$nextYear-01-10"],
            ["$nextYear-02-15", "$nextYear-03-10"],
            ["$nextYear-04-15", "$nextYear-05-10"],
        ];
        $periods = collect($periods)->map(fn ($period) => [
            Carbon::parse($period[0])->startOfDay(),
            Carbon::parse($period[1])->endOfDay(),
        ]);

        $today = Carbon::today();
        $currentPeriod = $periods->first(fn ($period) => $today->between($period[0], $period[1]));

        $isPeriodActive = $currentPeriod !== null;
        [$periodStart, $periodEnd] = $currentPeriod ?? [null, null];

        return DB::table('client_lessons as cl')
            ->join('lessons as l', 'l.id', '=', 'cl.lesson_id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->leftJoin('reports as r', function ($join) use ($periodStart, $periodEnd) {
                $join->on('r.teacher_id', '=', 'l.teacher_id')
                    ->on('r.client_id', '=', 'c.client_id')
                    ->on('r.year', '=', 'g.year')
                    ->on('r.program', '=', 'g.program');

                if ($periodStart !== null && $periodEnd !== null) {
                    $join->whereBetween('r.created_at', [$periodStart, $periodEnd]);
                }
            })
            ->where('g.year', $year)
            ->where('l.status', LessonStatus::conducted->value)
            ->groupByRaw('l.teacher_id, c.client_id, g.year, g.program')
            ->selectRaw(
                "NULL as `id`,
                NULL as `status`,
                l.teacher_id,
                c.client_id,
                g.year,
                g.program,
                COUNT(DISTINCT cl.id) as lessons_count,
                CASE WHEN ? AND COUNT(DISTINCT r.id) = 0 THEN 'required' ELSE 'notRequired' END as `requirement`,
                MAX(l.conducted_at) as `created_at`",
                [$isPeriodActive ? 1 : 0]
            );
    }

    public static function getMenuCounts(int $teacherId): bool
    {
        return Report::query()
            ->where('teacher_id', $teacherId)
            ->where('status', ReportStatus::refused)
            ->exists();
    }

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

    /**
     * @return Collection<int, ClientLesson>
     */
    public function getClientLessonsAttribute(): Collection
    {
        return ClientLesson::whereIn('id', $this->lessons->pluck('cl.id'))->with('lesson')->get();
    }

    /**
     * Наполняемость отчета
     */
    public function getFillAttribute(): int
    {
        $max = 1000; // сколько символов = 100% заполняемость

        $totalLength = collect([
            $this->homework_comment,
            $this->recommendation_comment,
            $this->cognitive_ability_comment,
            $this->knowledge_level_comment,
        ])->reduce(fn ($carry, $comment) => $carry + mb_strlen($comment), 0);

        return min(round($totalLength * 100 / $max), 100);
    }

    /**
     * Прочитать отчёт
     */
    public function read(?int $telegramId = null): void
    {
        if ($telegramId !== null) {
            $phone = Phone::where('telegram_id', $telegramId)->first();

            TelegramMessage::sendTemplate(
                TelegramTemplate::reportRead,
                $phone,
                ['report' => $this]
            );

        }

        $this->update([
            'delivery' => ReportDelivery::read,
        ]);
    }

    public function scopeSelectForUnion($query)
    {
        return $query->selectRaw("
            id,
            status,
            teacher_id,
            client_id,
            year,
            program,
            NULL as lessons_count,
            'created' as `requirement`,
            created_at
        ");
    }
}
