<?php

namespace App\Utils;

use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Enums\ReportStatus;
use App\Models\Lesson;
use App\Models\Teacher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

readonly class TeacherStatsNew
{
    private Collection $lessons;

    private Collection $reports;

    public function __construct(
        private Teacher $teacher,
        private int $year
    ) {
        $this->lessons = Lesson::query()
            ->with('clientLessons')
            ->join('groups', 'groups.id', '=', 'lessons.group_id')
            ->where('groups.year', $this->year)
            ->where('teacher_id', $this->teacher->id)
            ->orderByRaw('`date` asc, `time` asc')
            ->select('lessons.*')
            ->get()
            ->groupBy('date');

        $this->reports = $this->teacher->reports()
            ->where('year', $this->year)
            ->where('status', ReportStatus::published)
            ->get();
    }

    public function get(): array
    {
        $dateStart = sprintf('%d-09-01', $this->year);
        $dateEnd = sprintf('%d-05-31', $this->year + 1);

        $clientIds = [];

        // количество проведенных занятий
        $result = [];
        foreach (date_range($dateStart, $dateEnd) as $dateObj) {
            $date = $dateObj->format('Y-m-d');
            $item = (object) [];
            $noLessons = ! $this->lessons->has($date);

            /**
             * Опоздания проводки:
             */

            // количество проведенных занятий
            $conductedLessonsCount = $noLessons ? 0 : $this->lessons[$date]
                ->where('status', LessonStatus::conducted)->count();
            $item->conducted_lessons_count = $conductedLessonsCount;

            // количество занятий, проведенных с опозданием (не в дату занятия)
            $item->conducted_next_day_count = $noLessons ? 0 : $this->lessons[$date]
                ->where('status', LessonStatus::conducted)
                ->whereNotNull('conducted_at')
                ->where(fn (Lesson $l) => Carbon::parse($l->conducted_at)->format('Y-m-d') !== $date)
                ->count();

            /**
             * Посещаемость
             */

            // суммарное количество детей, посетивших занятия в текущую дату (во всех статусах)
            $clientLessonsCount = $noLessons ? 0 : $this->lessons[$date]
                ->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons->count(), 0);

            $item->client_lessons_count = $clientLessonsCount;

            // среднее количество учеников в проведенных занятиях
            $item->client_lessons_avg = share($clientLessonsCount, $conductedLessonsCount);

            // количество пропусков то есть "не был"
            $clientLessonsAbsentCount = $noLessons ? 0 : $this->lessons[$date]
                ->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->where('status', ClientLessonStatus::absent)->count(), 0);
            $item->client_lessons_absent_count = $clientLessonsAbsentCount;

            // количество опозданий то есть "опоздал"+"опоздал дист."
            $clientLessonsLateCount = $noLessons ? 0 : $this->lessons[$date]
                ->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->whereIn('status', ClientLessonStatus::getOnlineStatuses()
                    )->count(), 0);
            $item->client_lessons_late_count = $clientLessonsLateCount;

            // количество удаленки то есть "дист"+"опоздал дист."
            $clientLessonsOnlineCount = $noLessons ? 0 : $this->lessons[$date]
                ->reduce(fn (int $c, Lesson $l) => $c + $l->clientLessons
                    ->whereIn('status', ClientLessonStatus::getLateStatuses()
                    )->count(), 0);
            $item->client_lessons_online_count = $clientLessonsOnlineCount;

            // доля пропусков, то есть количество "не был" поделить на суммарное количество посещений во всех статусах
            $item->client_lessons_absent_share = share($clientLessonsAbsentCount, $clientLessonsCount);

            // доля опозданий, то есть количество "опоздал"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
            $item->client_lessons_late_share = share($clientLessonsLateCount, $clientLessonsCount);

            // доля удаленки, то есть количество "дист"+"опоздал дист." поделить на суммарное количество посещений во всех статусах
            $item->client_lessons_online_share = share($clientLessonsOnlineCount, $clientLessonsCount);

            /**
             * Удержание аудитории
             */

            // количество детей, начавших заниматься у преподавателя в конфигурации client_ID*teacher_ID*program*year
            $newStudentsCount = 0;
            if (! $noLessons) {
                foreach ($this->lessons[$date] as $lesson) {
                    foreach ($lesson->clientLessons as $clientLesson) {
                        $clientId = $clientLesson->contractVersionProgram->contractVersion->contract->client_id;
                        $program = $clientLesson->contractVersionProgram->program->value;
                        $key = implode('-', [$clientId, $program]);
                        if (! isset($clientIds[$key])) {
                            $newStudentsCount++;
                            $clientIds[$key] = true;
                        }
                    }
                }
            }
            $item->new_students_count = $newStudentsCount;

            // количество детей, прекративших заниматься (определяется в дату последнего занятия в client_lessons) в конфигурации client_ID*teacher_ID*program*year
            // TODO

            // доля накоплением
            // TODO

            /**
             * Ведомость
             */

            // количество проведенных занятий
            // уже есть

            // количество занятий, в которых дом.задание НЕ = NULL
            $item->lessons_with_homework = $noLessons ? 0 : $this->lessons[$date]
                ->whereNotNull('homework')
                ->count();

            // количество занятий, в которых есть хотя бы 1 прикрепленный файл
            $item->lessons_with_files = $noLessons ? 0 : $this->lessons[$date]
                ->whereNotNull('files')
                ->count();

            // количество выставленных оценок
            $scoresCount = 0;
            $scores = 0;
            if (! $noLessons) {
                foreach ($this->lessons[$date] as $lesson) {
                    foreach ($lesson->clientLessons->whereNotNull('scores') as $clientLesson) {
                        foreach ($clientLesson->scores as $score) {
                            $scoresCount++;
                            $scores += intval($score['score']);
                        }
                    }
                }
            }
            $item->scores_count = $scoresCount;
            $item->scores_avg = share($scores, $scoresCount);

            /**
             * Отчеты:
             */

            // количество отчетов в статусе опубликовано

            // количество отчетов в статусе опубликовано без начисления

            // средняя заполняемость отчетов

            $result[$date] = $item;
        }

        return $result;
    }
}
