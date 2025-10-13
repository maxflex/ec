<?php

namespace App\Utils\TeacherStats;

use App\Enums\ClientLessonStatus;
use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Enums\Program;
use App\Enums\ReportStatus;
use App\Models\ClientLesson;
use App\Models\Lesson;
use App\Models\Report;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class TeacherStatsNew
{
    public function __construct(
        private Teacher $teacher,
    ) {}

    /**
     * Статистика по дням
     *
     * @return array<string, TeacherStatsItem>
     */
    public function getDaily(int $year, array $directions = []): array
    {
        $programs = [];
        foreach ($directions as $direction) {
            $programs = array_merge($programs, Direction::tryFrom($direction)->toPrograms());
        }

        $lessonsByDate = Lesson::query()
            ->with('clientLessons.contractVersionProgram.contractVersion.contract')
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('g.year', $year)
            ->where('teacher_id', $this->teacher->id)
            ->where('is_substitute', false)
            ->when(count($programs), fn ($q) => $q->whereIn('g.program', $programs))
            ->orderByRaw('`date` asc, `time` asc')
            ->select('lessons.*')
            ->get()
            ->groupBy('date');

        $reportsByDate = $this->teacher->reports()
            ->where('year', $year)
            ->where('status', ReportStatus::published)
            ->when(count($programs), fn ($q) => $q->whereIn('program', $programs))
            ->get()
            ->groupBy(fn (Report $r) => Carbon::parse($r->to_check_at)->format('Y-m-d'));

        $dateStart = sprintf('%d-09-01', $year);
        $dateEnd = sprintf('%d-05-31', $year + 1);

        /**
         * Предрасчёт firstSeen/lastSeen на уровне ключа (client_id*program) внутри этого преподавателя и учебного года.
         */
        $firstSeen = []; // key => 'Y-m-d'
        $lastSeen = []; // key => 'Y-m-d'

        foreach ($lessonsByDate as $d => $lessons) {
            /** @var Collection<int, Lesson> $lessons */
            foreach ($lessons as $lesson) {
                foreach ($lesson->clientLessons as $cl) {
                    /** @var ClientLesson $cl */
                    $clientId = $cl->contractVersionProgram->contractVersion->contract->client_id;
                    $program = $cl->contractVersionProgram->program->value;
                    $key = $clientId.'-'.$program;

                    if (! isset($firstSeen[$key]) || $d < $firstSeen[$key]) {
                        $firstSeen[$key] = $d;
                    }
                    if (! isset($lastSeen[$key]) || $d > $lastSeen[$key]) {
                        $lastSeen[$key] = $d;
                    }
                }
            }
        }

        // Счётчики по датам: сколько впервые пришли / окончательно ушли
        $firstCountByDate = []; // date => count(firstSeen == date)
        foreach ($firstSeen as $d) {
            $firstCountByDate[$d] = ($firstCountByDate[$d] ?? 0) + 1;
        }

        $lastCountByDate = []; // date => count(lastSeen == date)
        foreach ($lastSeen as $d) {
            $lastCountByDate[$d] = ($lastCountByDate[$d] ?? 0) + 1;
        }

        // Накопительные агрегаты для % удержания
        $totalSeenSoFar = 0; // сколько всего уникальных ключей уже пришли к этой дате (кумулятив по firstSeen)
        $stoppedSoFar = 0; // сколько уже ушли к этой дате (кумулятив по lastSeen)

        // количество проведенных занятий
        $result = [];
        foreach (date_range($dateStart, $dateEnd) as $dateObj) {
            $date = $dateObj->format('Y-m-d');
            $item = new TeacherStatsItem;

            /**
             * Удержание аудитории — считаем КАЖДЫЙ день (даже если уроков в этот день нет).
             * - новые за день = firstSeen == date
             * - ушедшие за день = lastSeen == date
             * - % удержания = (активные / всего_seen) * 100,
             *   где активные = totalSeenSoFar - stoppedSoFar.
             */
            $newToday = $firstCountByDate[$date] ?? 0;
            $stoppedToday = $lastCountByDate[$date] ?? 0;

            $totalSeenSoFar += $newToday;
            $stoppedSoFar += $stoppedToday;

            $activeToDate = $totalSeenSoFar - $stoppedSoFar;

            $item->retention_new = $newToday;
            $item->retention_left = $stoppedToday;
            $item->retention_share = share($activeToDate, $totalSeenSoFar, true);
            // $item->retention_left_share = 100.0 - share($activeToDate, $totalSeenSoFar, true);

            if ($lessonsByDate->has($date)) {
                $lessons = $lessonsByDate[$date];
                // $item->retention_share = share($activeToDate, $totalSeenSoFar, true);

                /**
                 * Опоздания проводки:
                 */

                // количество проведенных занятий
                $lessonsConducted = $lessons->where('status', LessonStatus::conducted)->count();
                $item->lessons_conducted = $lessonsConducted;

                // количество занятий, проведенных с опозданием (не в дату занятия)
                $item->lessons_conducted_next_day = $lessons
                    ->where('status', LessonStatus::conducted)
                    ->whereNotNull('conducted_at')
                    ->where(fn (Lesson $l) => Carbon::parse($l->conducted_at)->format('Y-m-d') !== $date)
                    ->count();

                /**
                 * Посещаемость
                 */

                // суммарное количество детей, посетивших занятия в текущую дату (во всех статусах)
                $clientLessons = $lessons->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons->count(), 0);
                $item->client_lessons = $clientLessons;

                // среднее количество учеников в проведенных занятиях
                $item->client_lessons_avg = share($clientLessons, $lessonsConducted);

                // количество пропусков, то есть "не был"
                $clientLessonsAbsent = $lessons->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->where('status', ClientLessonStatus::absent)->count(), 0);
                $item->client_lessons_absent = $clientLessonsAbsent;

                // количество опозданий, то есть "опоздал"+"опоздал дист."
                $clientLessonsLate = $lessons->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->whereIn('status', ClientLessonStatus::getLateStatuses()
                    )->count(), 0);
                $item->client_lessons_late = $clientLessonsLate;

                // количество удаленки, то есть "дист"+"опоздал дист."
                $clientLessonsOnline = $lessons->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->whereIn('status', ClientLessonStatus::getOnlineStatuses()
                    )->count(), 0);
                $item->client_lessons_online = $clientLessonsOnline;

                // доля пропусков, то есть количество "не был" поделить на суммарное количество посещений во всех статусах
                $item->client_lessons_absent_share = share($clientLessonsAbsent, $clientLessons, true);

                // доля опозданий, то есть количество "опоздал"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
                $item->client_lessons_late_share = share($clientLessonsLate, $clientLessons, true);

                // доля удаленки, то есть количество "дист"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
                $item->client_lessons_online_share = share($clientLessonsOnline, $clientLessons, true);

                /**
                 * Ведомость
                 */

                // количество проведенных занятий
                // уже есть

                // количество занятий, в которых дом.задание НЕ = NULL
                $lessonsWithHomework = $lessons->whereNotNull('homework')->count();
                $item->lessons_with_homework = $lessonsWithHomework;
                $item->lessons_with_homework_avg = share($lessonsWithHomework, $lessonsConducted, true);

                // количество занятий, в которых есть хотя бы 1 прикрепленный файл
                $lessonsWithFiles = $lessons->where(fn (Lesson $l) => count($l->files) > 0)->count();
                $item->lessons_with_files = $lessonsWithFiles;
                $item->lessons_with_files_avg = share($lessonsWithFiles, $lessonsConducted, true);

                // количество выставленных оценок
                // средняя оценка по занятиям
                // количество оставленных комментариев к оценкам
                // количество оставленных общих комментариев
                $scoresCount = 0;
                $scoresSum = 0;
                $scoreCommentsCount = 0;
                $commentsCount = 0;
                foreach ($lessons as $lesson) {
                    foreach ($lesson->clientLessons as $clientLesson) {
                        /** @var ClientLesson $clientLesson */
                        if ($clientLesson->comment) {
                            $commentsCount++;
                        }
                        $scores = $clientLesson->scores ?? [];
                        foreach ($scores as $score) {
                            $scoresCount++;
                            $scoresSum += intval($score->score);
                            if ($score->comment) {
                                $scoreCommentsCount++;
                            }
                        }
                    }
                }
                $item->scores = $scoresCount;
                $item->scores_sum = $scoresSum;
                $item->scores_avg = share($scoresSum, $scoresCount);
                $item->scores_comments = $scoreCommentsCount;
                $item->comments = $commentsCount;
                // (clientLessons - clientLessonsAbsent) * 3 - это 100%
                $item->scores_share = share(
                    $scoresCount,
                    ($clientLessons - $clientLessonsAbsent) * 3,
                    true
                );
            }

            /**
             * Отчеты:
             */
            if ($reportsByDate->has($date)) {
                $reports = $reportsByDate[$date];

                // количество отчетов в статусе опубликовано
                $item->reports_published = $reports->count();

                // количество отчетов в статусе опубликовано без начисления
                $item->reports_published_no_price = $reports->where(fn (Report $r) => ! $r->price)->count();

                // средняя заполняемость отчетов
                $item->reports_fill_avg = round($reports->avg('fill'), 1);

                // средняя оценка по отчетам
                $item->reports_grade_avg = round($reports->avg('grade'), 1);
            }

            $result[$date] = $item;
        }

        return $result;
    }

    /**
     * Группирует дневные элементы в недели/месяцы и для каждого бакета
     * просто вызывает ваш getTotals (минимум кода).
     *
     * @param  array<string, TeacherStatsItem>  $dailyStats
     * @param  'day'|'week'|'month'  $mode
     * @return array<string, TeacherStatsItem>
     */
    public function groupBy(array $dailyStats, string $mode): array
    {
        if ($mode === 'day') {
            return $dailyStats;
        }

        // соберём по бакетам
        $buckets = [];              // bucketKey => TeacherStatsItem[]
        foreach ($dailyStats as $date => $item) {
            $b = $this->bucketKey($date, $mode);
            $buckets[$b][] = $item;
        }

        // агрегируем каждым бакетом через getTotals
        $out = [];
        foreach ($buckets as $b => $items) {
            $totals = $this->getTotals($items);          // уже умеет суммировать числа и усреднять float'ы «как есть»
            $out[$b] = $totals;
        }

        return $out;
    }

    private function bucketKey(string $date, string $mode): string
    {
        $c = Carbon::parse($date);

        return match ($mode) {
            'week' => $c->startOfWeek(Carbon::MONDAY)->format('Y-m-d'), // ключ = понедельник недели
            'month' => $c->startOfMonth()->format('Y-m'),                 // ключ = YYYY-MM
            default => $c->format('Y-m-d'),
        };
    }

    /**
     * @param  array<string, TeacherStatsItem>  $dailyStats
     */
    public function getTotals(array $dailyStats): TeacherStatsItem
    {
        $totals = new TeacherStatsItem;
        $cnt = new TeacherStatsItem;

        foreach ($dailyStats as $item) {
            foreach (get_object_vars($item) as $key => $value) {
                if (! $value) {
                    continue;
                }

                $totals->{$key} += $value;
                $cnt->{$key} += 1;
            }
        }

        // floats
        foreach (get_object_vars($totals) as $key => $value) {
            if (! is_float($value)) {
                continue;
            }

            switch ($key) {
                case 'scores_avg':
                    $totals->scores_avg = share(
                        $totals->scores_sum,
                        $totals->scores,
                    );
                    break;

                case 'lessons_with_homework_avg':
                    $totals->lessons_with_homework_avg = share(
                        $totals->lessons_with_homework,
                        $totals->client_lessons,
                        100
                    );
                    break;

                case 'lessons_with_files_avg':
                    $totals->lessons_with_files_avg = share(
                        $totals->lessons_with_files,
                        $totals->client_lessons,
                        100
                    );
                    break;

                case 'client_lessons_avg':
                    $totals->client_lessons_avg = share(
                        $totals->client_lessons,
                        $totals->lessons_conducted
                    );
                    break;

                case 'client_lessons_late_share':
                case 'client_lessons_online_share':
                case 'client_lessons_absent_share':
                    // доля от client_lessons
                    // client_lessons_late_share => client_lessons_late
                    $key2 = str($key)->beforeLast('_')->value();
                    $totals->{$key} = share($totals->{$key2}, $totals->client_lessons, true);
                    break;

                default:
                    // среднее средних
                    $totals->{$key} = share($totals->{$key}, $cnt->{$key});
                    break;
            }
        }

        return $totals;
    }

    /**
     * @return array<int, int>
     */
    public function getAvailableYears(): array
    {
        return $this->teacher->lessons()
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->pluck('g.year')
            ->unique()
            ->map(fn ($year) => intval($year))
            ->sortDesc()
            ->values()
            ->all();

        // if ($this->teacher->stats_new === null) {
        //     return [];
        // }
        //
        // return array_map('intval', array_keys($this->teacher->stats_new));
    }
}
