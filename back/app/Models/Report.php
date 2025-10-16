<?php

namespace App\Models;

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
    protected $fillable = [
        'year', 'program', 'price', 'client_id', 'status', 'grade',
        'recommendation_comment', 'knowledge_level_comment', 'teacher_id',
        'cognitive_ability_comment', 'homework_comment',
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

        $periods = [
            ["$year-10-15",      "$year-11-10"],
            ["$year-12-15",      "$nextYear-01-10"],
            ["$nextYear-02-15",  "$nextYear-03-10"],
            ["$nextYear-04-15",  "$nextYear-05-10"],
        ];

        [$periodStart, $periodEnd] = collect($periods)
            ->first(fn ($p) => $today >= $p[0] && $today <= $p[1]) ?? [null, null];

        $courses = Program::getAllCourses()->map(fn (Program $p) => $p->value)->all();
        $schoolAndExternal = Program::getAllSchool()
            ->merge(Program::getAllExternal())
            ->merge(Program::getAllPracticum())
            ->map(fn (Program $p) => $p->value)
            ->all();

        // Последний отчёт по каждой плоскости в текущем учебном году
        $md = DB::table('reports as r')
            ->where('r.year', $year)
            ->groupBy('r.teacher_id', 'r.client_id', 'r.year', 'r.program')
            ->selectRaw('
            r.teacher_id,
            r.client_id,
            r.year,
            r.program,
            MAX(r.created_at) as last_report_at
        ');

        // ==== КУРСЫ: требование, если прошло >= 6 занятий с момента последнего отчёта ====
        $lessonsNeeded = 6;
        $coursesRequired = DB::table('lessons as l')
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
            ->havingRaw("
            SUM(
                CASE
                    WHEN md.last_report_at IS NULL THEN 1
                    WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1
                    ELSE 0
                END
            ) >= $lessonsNeeded
        ")
            ->selectRaw("
            NULL as id,
            NULL as status,
            l.teacher_id,
            c.client_id,
            g.year,
            g.program,
            SUM(
                CASE
                    WHEN md.last_report_at IS NULL THEN 1
                    WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1
                    ELSE 0
                END
            ) as lessons_count,
            1 as `is_required`,
            NULL as created_at
        ");

        // ==== SCHOOL/EXTERNAL/PRACTICUM: требование только внутри периода и если в нём ещё нет отчёта ====
        // Если вне периода — просто не добавляем этот блок в UNION
        $schoolRequired = null;
        if ($periodStart !== null) {
            $lessonsNeeded = 3;
            $schoolRequired = DB::table('lessons as l')
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
                ->whereIn('g.program', $schoolAndExternal)
                // Нет отчёта в текущем периоде
                ->whereNotExists(function ($q) use ($periodStart, $periodEnd) {
                    $q->from('reports as r')
                        ->whereColumn('r.teacher_id', 'l.teacher_id')
                        ->whereColumn('r.client_id', 'c.client_id')
                        ->whereColumn('r.year', 'g.year')
                        ->whereColumn('r.program', 'g.program')
                        ->whereBetween('r.created_at', [$periodStart, $periodEnd]);
                })
                ->groupBy('l.teacher_id', 'c.client_id', 'g.year', 'g.program')
                ->selectRaw("
                    NULL as id,
                    NULL as status,
                    l.teacher_id,
                    c.client_id,
                    g.year,
                    g.program,
                    SUM(
                        CASE
                            WHEN md.last_report_at IS NULL THEN 1
                            WHEN CONCAT(l.date, ' ', l.time) > md.last_report_at THEN 1
                            ELSE 0
                        END
                    ) as lessons_count,
                    1 as `is_required`,
                    NULL as created_at
                ")
                ->having('lessons_count', '>=', $lessonsNeeded);
        }

        // Собираем итоговую «requirements»
        if ($schoolRequired) {
            $requirements = $coursesRequired->unionAll($schoolRequired);
        } else {
            $requirements = $coursesRequired;
        }

        // Вернём CTE 'requirements'
        return DB::table('requirements')
            ->withExpression('md', $md)
            ->withExpression('requirements', $requirements);
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
