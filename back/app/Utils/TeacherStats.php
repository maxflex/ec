<?php

namespace App\Utils;

use App\Enums\ClientLessonStatus;
use App\Enums\LessonStatus;
use App\Enums\ReportStatus;
use App\Models\Lesson;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Support\Collection;

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

    public function get()
    {
        return [
            'lessons' => $this->getLessons(),
            'cancelled_percentage' => $this->getCancelledPercentage(),
            'report_fill_avg' => $this->getReportFillAvg(),
            'report_similarity' => $this->getReportSimilarity(),
            'conducted_next_day_count' => $this->getConductedNextDayCount(),
            'client_lesson_counts' => $this->getClientLessonCounts(),
            'review_rating_avg' => $this->getReviewRatingAvg(),
        ];
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

    /**
     * Доля отмен
     */
    private function getCancelledPercentage(): int
    {
        $total = $this->lessons->where('status', LessonStatus::conducted)->count();
        $cancelled = $this->lessons->where('status', LessonStatus::cancelled)->count();

        return (int) round(($cancelled / $total) * 100);
    }

    /**
     * Средний уровень заполненности отчетов со статусом "опубликовано"
     */
    private function getReportFillAvg(): int
    {
        return (int) round($this->reports->avg('fill'));
    }

    /**
     * Степень "одинаковости" отчетов
     */
    private function getReportSimilarity(): int
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

        return (int) round($similarity);
    }

    /**
     * Доля занятий, которая была проведена не в день занятия
     */
    private function getConductedNextDayCount(): int
    {
        return $this->lessons
            ->where('status', LessonStatus::conducted)
            ->where(fn ($l) => $l->date !== Carbon::parse($l->conducted_at)->format('Y-m-d'))
            ->count();
    }

    /**
     * Доля отсутствующих учеников в проведенных занятиях
     * Доля опаздывающих
     * Доля дистанционщиков
     * Доля занятий за счет ушедших учеников
     */
    private function getClientLessonCounts()
    {
        $total = 0;
        $absent = 0;
        $late = 0;
        $online = 0;
        $priceClientLessons = 0;
        $priceTeacher = 0;

        $left = [];

        foreach ($this->lessons->where('status', LessonStatus::conducted) as $lesson) {
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
                        $late++;
                        break;

                    case ClientLessonStatus::lateOnline:
                        $late++;
                        $online++;
                        break;
                }
                $total++;
            }

            // Доля занятий за счет ушедших учеников
            if (! array_key_exists($lesson->group_id, $left)) {
                $left[$lesson->group_id] = (object) [
                    'studentIds' => $studentIds,
                    'total' => $lesson->clientLessons->count(),
                    'left' => 0,
                ];
            } else {
                $diff = $left[$lesson->group_id]->studentIds->diff($studentIds)->count();
                $left[$lesson->group_id]->total += $lesson->clientLessons->count() + $diff;
                $left[$lesson->group_id]->left += $diff;
                $left[$lesson->group_id]->studentIds = $left[$lesson->group_id]->studentIds->merge($studentIds)->unique()->values();
            }
        }

        $leftAvg = 0;
        foreach ($left as $obj) {
            $leftAvg += ($obj->total - $obj->left) / $obj->total * 100;
        }
        $leftAvg = (int) round($leftAvg / count($left));

        return [
            'absent' => (int) round(($absent / $total) * 100),
            'late' => (int) round(($late / $total) * 100),
            'online' => (int) round(($online / $total) * 100),
            'payback' => round($priceClientLessons / $priceTeacher, 2),
            'left' => $leftAvg,
        ];
    }

    private function getReviewRatingAvg(): float
    {
        $avg = $this->teacher->clientReviews()->avg('rating');

        return round($avg, 2);
    }
}
