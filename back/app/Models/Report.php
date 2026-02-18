<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Observers\ReportObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[ObservedBy(ReportObserver::class)]
class Report extends Model
{
    // сколько символов = 100% заполняемость
    const int PERFECT_LENGTH = 1000;

    protected $fillable = [
        'year', 'program', 'price', 'client_id', 'status', 'grade',
        'teacher_id', 'comment', 'ai_comment',
    ];

    protected $casts = [
        'program' => Program::class,
        'status' => ReportStatus::class,
        'is_read' => 'boolean',
    ];

    /**
     * Либо отчет требуется, либо есть отчет "возвращено"
     */
    public static function getMenuCounts(int $teacherId): bool
    {
        $hasRefused = Report::query()
            ->where('year', current_academic_year())
            ->where('teacher_id', $teacherId)
            ->where('status', ReportStatus::refused)
            ->exists();

        $hasRequirements = Report::requirements()
            ->where('year', current_academic_year())
            ->where('teacher_id', $teacherId)
            ->exists();

        return $hasRefused || $hasRequirements;
    }

    /**
     * Запрос получает все "требуется создать"
     * https://doc.ege-centr.ru/tasks/834
     */
    public static function requirements()
    {
        $year = current_academic_year();
        $nextYear = $year + 1;
        $today = now()->toDateString();

        // 1. ОПРЕДЕЛЯЕМ ПЕРИОДЫ (Даты)

        // 8 класс: окно 10-25
        $school8Periods = [
            ["$year-10-10",      "$year-10-25"],
            ["$year-12-10",      "$year-12-25"],
            ["$nextYear-02-10",  "$nextYear-02-25"],
            ["$nextYear-04-10",  "$nextYear-04-25"],
        ];
        [$s8Start, $s8End] = collect($school8Periods)
            ->first(fn ($p) => $today >= $p[0] && $today <= $p[1]) ?? [null, null];

        // Остальные: окно 20-03
        $otherPeriods = [
            ["$year-10-20",      "$year-11-03"],
            ["$year-12-20",      "$nextYear-01-03"],
            ["$nextYear-02-20",  "$nextYear-03-03"],
            ["$nextYear-04-20",  "$nextYear-05-03"],
        ];
        [$otherStart, $otherEnd] = collect($otherPeriods)
            ->first(fn ($p) => $today >= $p[0] && $today <= $p[1]) ?? [null, null];

        // 2. ОПРЕДЕЛЯЕМ ПРОГРАММЫ (ID-шники)

        $courses = Program::getAllCourses()->map(fn (Program $p) => $p->value)->all();

        $school8 = Program::getAllSchool()
            ->filter(fn (Program $p) => $p->getDirection() === Direction::school8)
            ->map(fn (Program $p) => $p->value)
            ->all();

        $otherSchoolAndExt = Program::getAllSchool()
            ->reject(fn (Program $p) => $p->getDirection() === Direction::school8)
            ->merge(Program::getAllExternal())
            ->merge(Program::getAllPracticum())
            ->map(fn (Program $p) => $p->value)
            ->all();

        // 3. БАЗОВЫЕ CTE (Last Report Date)
        $md = DB::table('reports as r')
            ->where('r.year', $year)
            ->groupBy('r.teacher_id', 'r.client_id', 'r.year', 'r.program')
            ->selectRaw('r.teacher_id, r.client_id, r.year, r.program, MAX(r.created_at) as last_report_at');

        // 4. СБОРКА ЧАСТЕЙ ЗАПРОСА (Parts)
        $parts = [];

        // А) ЧАСТЬ: КУРСЫ (Всегда проверяем, если прошло 6 занятий)
        $parts[] = DB::table('lessons as l')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->leftJoinSub($md, 'md', function ($join) {
                $join->on('md.teacher_id', '=', 'l.teacher_id')
                    ->on('md.client_id', '=', 'c.client_id')
                    ->on('md.year', '=', 'g.year')
                    ->on('md.program', '=', 'g.program');
            })
            ->where('g.year', $year)
            ->whereIn('g.program', $courses)
            ->groupBy('l.teacher_id', 'c.client_id', 'g.year', 'g.program')
            ->havingRaw("SUM(CASE WHEN md.last_report_at IS NULL THEN 1 WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1 ELSE 0 END) >= 6")
            ->selectRaw("NULL as id, NULL as status, l.teacher_id, c.client_id, g.year, g.program, SUM(CASE WHEN md.last_report_at IS NULL THEN 1 WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1 ELSE 0 END) as lessons_count, 1 as `is_required`, NULL as created_at");

        // ФУНКЦИЯ-СТРОИТЕЛЬ ДЛЯ ШКОЛ (Чтобы не дублировать код join-ов)
        $buildSchoolQuery = function (array $programs, string $start, string $end) use ($year, $md) {
            return DB::table('lessons as l')
                ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
                ->join('groups as g', 'g.id', '=', 'l.group_id')
                ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
                ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
                ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
                ->leftJoinSub($md, 'md', function ($join) {
                    $join->on('md.teacher_id', '=', 'l.teacher_id')
                        ->on('md.client_id', '=', 'c.client_id')
                        ->on('md.year', '=', 'g.year')
                        ->on('md.program', '=', 'g.program');
                })
                ->where('g.year', $year)
                ->whereIn('g.program', $programs)
                // Условие: нет отчета в заданном периоде
                ->whereNotExists(function ($q) use ($start, $end) {
                    $q->from('reports as r')
                        ->whereColumn('r.teacher_id', 'l.teacher_id')
                        ->whereColumn('r.client_id', 'c.client_id')
                        ->whereColumn('r.year', 'g.year')
                        ->whereColumn('r.program', 'g.program')
                        ->whereBetween('r.created_at', [$start, $end]);
                })
                ->groupBy('l.teacher_id', 'c.client_id', 'g.year', 'g.program')
                ->havingRaw("SUM(CASE WHEN md.last_report_at IS NULL THEN 1 WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1 ELSE 0 END) >= 3")
                ->selectRaw("NULL as id, NULL as status, l.teacher_id, c.client_id, g.year, g.program, SUM(CASE WHEN md.last_report_at IS NULL THEN 1 WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1 ELSE 0 END) as lessons_count, 1 as `is_required`, NULL as created_at");
        };

        // Б) ЧАСТЬ: 8 Класс (Если сейчас идет период)
        if ($s8Start && $s8End) {
            $parts[] = $buildSchoolQuery($school8, $s8Start, $s8End);
        }

        // В) ЧАСТЬ: Остальные (Если сейчас идет период)
        if ($otherStart && $otherEnd) {
            $parts[] = $buildSchoolQuery($otherSchoolAndExt, $otherStart, $otherEnd);
        }

        // 5. ИТОГОВЫЙ UNION ALL
        $finalQuery = array_shift($parts); // Берем первый элемент (курсы)
        foreach ($parts as $part) {
            $finalQuery->unionAll($part);
        }

        return DB::table('requirements')
            ->withExpression('md', $md)
            ->withExpression('requirements', $finalQuery);
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
        $totalLength = mb_strlen((string) $this->comment);

        return min(round($totalLength * 100 / self::PERFECT_LENGTH), 100);
    }

    /**
     * Прочитать отчет
     */
    public function read(): void
    {
        $this->is_read = true;
        $this->save();
    }

    public function scopeSelectForUnion($query)
    {
        return $query->selectRaw('
            id,
            status,
            teacher_id,
            client_id,
            year,
            program,
            NULL as lessons_count,
            0 as `is_required`,
            created_at
        ');
    }
}
