<?php

namespace App\Utils;

use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Enums\ReportStatus;
use App\Models\Lesson;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

readonly class TeacherStats
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
            ->get();

        $this->reports = $this->teacher->reports()
            ->where('year', $this->year)
            ->where('status', ReportStatus::published)
            ->get();
    }

    public function get(): array
    {
        $clientLessons = $this->getClientLessons();
        $conductedLessonsCount = $this->lessons->where('status', LessonStatus::conducted)->count();
        $cancelledLessonsCount = $this->lessons->where('status', LessonStatus::cancelled)->count();
        $conductedNextDayCount = $this->getConductedNextDayCount();

        return [
            'lessons' => $this->getLessons(),
            'reports_count' => $this->reports->count(),
            'client_lessons_count' => $clientLessons->count, // не используется?
            'client_lessons_avg' => $clientLessons->avg,
            'conducted_lessons_count' => $conductedLessonsCount,
            'cancelled_lessons_count' => $cancelledLessonsCount,
            'cancelled_lessons_percent' => $this->percent($cancelledLessonsCount, $conductedLessonsCount),
            'report_fill_avg' => $this->getReportFillAvg(),
            'report_similarity_percent' => $this->getReportSimilarityPercent(),
            'conducted_next_day_count' => $conductedNextDayCount,
            'conducted_next_day_percent' => $this->percent($conductedNextDayCount, $conductedLessonsCount),
            'client_lessons_late_count' => $clientLessons->late,
            'client_lessons_late_percent' => $this->percent($clientLessons->late, $clientLessons->count),
            'client_lessons_online_count' => $clientLessons->online,
            'client_lessons_online_percent' => $this->percent($clientLessons->online, $clientLessons->count),
            'client_lessons_absent_count' => $clientLessons->absent,
            'client_lessons_absent_percent' => $this->percent($clientLessons->absent, $clientLessons->count),
            'students_left_percent' => $clientLessons->studentsLeftPercent,
            'payback' => $clientLessons->payback,
        ];
    }

    /**
     * Доля отсутствующих учеников в проведенных занятиях
     * Доля опаздывающих
     * Доля дистанционщиков
     * Доля занятий за счет ушедших учеников
     */
    private function getClientLessons(): object
    {
        $count = 0;
        $absent = 0;
        $late = 0;
        $online = 0;
        $priceClientLessons = 0;
        $priceTeacher = 0;
        $left = [];
        $lessons = $this->lessons->where('status', LessonStatus::conducted);

        foreach ($lessons as $lesson) {
            $studentIds = collect();
            $priceTeacher += $lesson->price;
            foreach ($lesson->clientLessons as $clientLesson) {
                $studentIds->push($clientLesson->contract_version_program_id);
                $priceClientLessons += $clientLesson->price;
                switch ($clientLesson->status) {
                    case ClientLessonStatus::absent:
                        $absent++;
                        break;

                    case ClientLessonStatus::late:
                    case ClientLessonStatus::lateOnline:
                        $late++;
                        break;

                    case ClientLessonStatus::presentOnline:
                    case ClientLessonStatus::lateOnline:
                        $online++;
                        break;
                }
                $count++;
            }

            // Доля занятий за счет ушедших учеников
            if (! array_key_exists($lesson->group_id, $left)) {
                $left[$lesson->group_id] = (object) [
                    'studentIds' => $studentIds,
                    'count' => $lesson->clientLessons->count(),
                    'left' => 0,
                ];
            } else {
                $diff = $left[$lesson->group_id]->studentIds->diff($studentIds)->count();
                $left[$lesson->group_id]->count += $lesson->clientLessons->count() + $diff;
                $left[$lesson->group_id]->left += $diff;
                $left[$lesson->group_id]->studentIds = $left[$lesson->group_id]->studentIds->merge($studentIds)->unique()->values();
            }
        }

        $studentsLeftPercent = 0;
        foreach ($left as $obj) {
            $studentsLeftPercent += ($obj->count - $obj->left) / $obj->count * 100;
        }
        $studentsLeftPercent = round($studentsLeftPercent / count($left), 1);

        return (object) [
            'absent' => $absent,
            'late' => $late,
            'online' => $online,
            'payback' => round($priceClientLessons / $priceTeacher, 1),
            'studentsLeftPercent' => $studentsLeftPercent,
            'count' => $count,
            'avg' => $lessons->count() ? round($count / $lessons->count(), 1) : 0,
        ];
    }

    /**
     * Кол-во занятий, которая была проведена не в день занятия
     */
    private function getConductedNextDayCount(): int
    {
        return $this->lessons
            ->where('status', LessonStatus::conducted)
            ->where(fn ($l) => $l->date !== Carbon::parse($l->conducted_at)->format('Y-m-d'))
            ->count();
    }

    /**
     * Количество занятий по направлениям проведенные и планируемые
     */
    private function getLessons(): array
    {
        $result = [];

        foreach ($this->lessons as $lesson) {
            $direction = $lesson->group->program->getDirection()->value;
            if (! array_key_exists($direction, $result)) {
                $result[$direction] = (object) [
                    'conducted' => 0,
                    'planned' => 0,
                ];
            }
            if ($lesson->status === LessonStatus::conducted) {
                $result[$direction]->conducted++;
            }
            if ($lesson->status === LessonStatus::planned) {
                $result[$direction]->planned++;
            }
        }

        $collect = collect();
        foreach ($result as $direction => $counts) {
            $collect->push((object) [
                'direction' => $direction,
                'conducted' => $counts->conducted,
                'planned' => $counts->planned,
            ]);
        }

        return $collect->sortByDesc(fn ($e) => $e->conducted + $e->planned)->values()->all();
    }

    private function percent(int $value, int $total): float
    {
        return round(($value / $total) * 100, 1);
    }

    /**
     * Средний уровень заполненности отчетов со статусом "опубликовано"
     */
    private function getReportFillAvg(): float
    {
        return round($this->reports->avg('fill'), 1);
    }

    /**
     * Степень "одинаковости" отчетов
     */
    private function getReportSimilarityPercent(): float
    {
        $textFields = collect([
            'homework_comment', 'cognitive_ability_comment', 'knowledge_level_comment', 'recommendation_comment',
        ]);

        $half = (int) ceil($this->reports->count() / 2);
        $getText = fn ($reports) => $reports
            ->map(fn ($report) => $textFields->map(fn ($field) => $report->$field)->join(' '))
            ->join(' ');

        $cleanText = fn ($text) => collect(explode(' ', strtolower(preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text))))
            ->filter(fn ($word) => mb_strlen($word) > 3)
            ->join(' ');

        $textA = $cleanText($getText($this->reports->slice(0, $half)));
        $textB = $cleanText($getText($this->reports->slice($half)));

        $shingles = fn ($text, $size = 5) => collect(range(0, mb_strlen($text) - $size))
            ->map(fn ($i) => mb_substr($text, $i, $size))
            ->unique();

        $shinglesA = $shingles($textA);
        $shinglesB = $shingles($textB);

        $intersection = $shinglesA->intersect($shinglesB)->count();
        $union = $shinglesA->merge($shinglesB)->unique()->count();

        $similarity = ($intersection / $union) * 100;

        return round($similarity, 1);
    }

    /**
     * Сохранить средние значения статистики по всем преподавателям
     */
    public static function saveAvg(array $stats): bool
    {
        return Storage::disk('local')->put(
            'teacher-stats-avg.json',
            json_encode($stats)
        );
    }

    /**
     * Загрузить средние значения по всем преподавателям
     */
    public static function loadAvg(): array
    {
        $stats = Storage::disk('local')->get('teacher-stats-avg.json');

        return json_decode($stats, true);
    }
}
