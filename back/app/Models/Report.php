<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Enums\TelegramTemplate;
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
    ];

    /**
     * Запрос получает все "требуется создать"
     * https://doc.ege-centr.ru/tasks/834
     */
    public static function requirements()
    {
        // сколько занятий должно пройти до требования отчета,
        // где ученик в каком-то варианте присутствовал (был/дист/опоздал)
        $lessonsNeeded = 4;
        $year = current_academic_year();
        $nextYear = $year + 1;

        // все даты последнего отчета
        $groupBy = 'teacher_id, client_id, `year`, program';
        $maxDates = DB::table('reports', 'r')
            ->where('year', $year)
            ->groupByRaw($groupBy)
            ->selectRaw("$groupBy, MAX(DATE(IFNULL(to_check_at, created_at))) as max_date");

        $programsCourses = [];
        $programsSchoolAndExternal = [];
        foreach (Program::cases() as $program) {
            // ОГЭ исключить
            if (str($program->value)->contains('Oge')) {
                continue;
            }
            switch ($program->getDirection()) {
                case Direction::courses9:
                case Direction::courses10:
                case Direction::courses11:
                    $programsCourses[] = $program;
                    break;

                case Direction::school8:
                case Direction::school9:
                case Direction::school10:
                case Direction::school11:
                case Direction::external:
                    $programsSchoolAndExternal[] = $program;
                    break;

                default:
            }
        }

        $courses = DB::table('lessons', 'l')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->leftJoin('md', fn ($join) => $join
                ->on('md.teacher_id', '=', 'l.teacher_id')
                ->on('md.client_id', '=', 'c.client_id')
                ->on('md.year', '=', 'g.year')
                ->on('md.program', '=', 'g.program')
            )
            // требование отчёта может возникать только в текущем учебном году
            ->where('g.year', $year)
            // на всякий случай, но в любом случае client_lessons должны соответствовать только проведённым
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw('IF(ISNULL(md.max_date), TRUE, l.date > md.max_date)')
            ->whereIn('g.program', $programsCourses)
            ->groupByRaw('l.teacher_id, c.client_id, g.year, g.program')
            ->selectRaw("
                NULL as `id`,
                NULL as `status`,
                l.teacher_id,
                c.client_id,
                g.year,
                g.program,
                COUNT(*) as lessons_count,
                IF((
                    COUNT(*) >= 6
                    AND SUM(IF(cl.status <> 'late', 1, 0)) >= $lessonsNeeded
                ), 'required', 'notRequired') as `requirement`,
                NULL as `created_at`
            ");

        $periods = [
            ["$year-10-15", "$year-10-25"],
            ["$year-12-15", "$year-12-25"],
            ["$nextYear-02-15", "$nextYear-02-25"],
            ["$nextYear-04-15", "$nextYear-04-25"],
        ];
        $schoolAndExternal = DB::table('lessons', 'l')
            ->join('client_lessons as cl', 'cl.lesson_id', '=', 'l.id')
            ->join('contract_version_programs as cvp', 'cvp.id', '=', 'cl.contract_version_program_id')
            ->join('contract_versions as cv', 'cv.id', '=', 'cvp.contract_version_id')
            ->join('contracts as c', 'c.id', '=', 'cv.contract_id')
            ->join('groups as g', 'g.id', '=', 'l.group_id')
            ->leftJoin('md', fn ($join) => $join
                ->on('md.teacher_id', '=', 'l.teacher_id')
                ->on('md.client_id', '=', 'c.client_id')
                ->on('md.year', '=', 'g.year')
                ->on('md.program', '=', 'g.program')
            )
            // требование отчёта может возникать только в текущем учебном году
            ->where('g.year', $year)
            // на всякий случай, но в любом случае client_lessons должны соответствовать только проведённым
            ->where('l.status', LessonStatus::conducted->value)
            ->whereRaw('IF(ISNULL(md.max_date), TRUE, l.date > md.max_date)')
            ->whereIn('g.program', $programsSchoolAndExternal)
            ->selectRaw("
                NULL as `id`,
                NULL as `status`,
                l.teacher_id,
                c.client_id,
                g.year,
                g.program,
                COUNT(*) as lessons_count,
                IF((
                    (
                        IF(ISNULL(md.max_date), TRUE, md.max_date < ?) AND
                        SUM(IF(cl.status <> 'absent' AND CURRENT_DATE() >= ? AND l.date <= ?, 1, 0)) >= $lessonsNeeded
                    )
                    OR
                    (
                        IF(ISNULL(md.max_date), TRUE, md.max_date < ?) AND
                        SUM(IF(cl.status <> 'absent' AND CURRENT_DATE() >= ? AND l.date <= ?, 1, 0)) >= $lessonsNeeded
                    )
                    OR
                    (
                        IF(ISNULL(md.max_date), TRUE, md.max_date < ?) AND
                        SUM(IF(cl.status <> 'absent' AND CURRENT_DATE() >= ? AND l.date <= ?, 1, 0)) >= $lessonsNeeded
                    )
                    OR
                    (
                        IF(ISNULL(md.max_date), TRUE, md.max_date < ?) AND
                        SUM(IF(cl.status <> 'absent' AND CURRENT_DATE() >= ? AND l.date <= ?, 1, 0)) >= $lessonsNeeded
                    )
                ), 'required', 'notRequired') as `requirement`,
                -- для сортировки
                MAX(CONCAT(l.date, ' ', l.time)) as `created_at`
            ", [
                $periods[0][0], $periods[0][0], $periods[0][1],
                $periods[1][0], $periods[1][0], $periods[1][1],
                $periods[2][0], $periods[2][0], $periods[2][1],
                $periods[3][0], $periods[3][0], $periods[3][1],
            ])
            ->groupByRaw('l.teacher_id, c.client_id, g.year, g.program');

        $requirements = DB::table('courses')
            ->withExpression('md', $maxDates)
            ->withExpression('courses', $courses)
            ->union($schoolAndExternal);

        return DB::table('requirements')->withExpression('requirements', $requirements);
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
    public function read(): void
    {
        TelegramMessage::sendTemplate(
            TelegramTemplate::reportRead,
            $this->client->parent,
            ['report' => $this]
        );
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
