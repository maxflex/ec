<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ClientLessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status'],
    ];

    public static function getQuery(): Builder
    {
        return Lesson::query()
            ->where('status', '<>', LessonStatus::cancelled)
            ->where('is_free', false);
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        /** @var Collection<int, Lesson> $lessons */
        $lessons = $query->get();

        // conducted sum
        $sum = $lessons->where('status', LessonStatus::conducted)->reduce(
            fn($carry, Lesson $l) => $carry + $l->clientLessons()->sum('price')
            , 0
        );

        // planned sum
        $plannedLessons = $lessons->where('status', LessonStatus::planned);
        foreach ($plannedLessons as $index => $plannedLesson) {
            foreach ($plannedLesson->group->clientGroups as $clientGroup) {
                $program = $clientGroup->contractVersionProgram;
                $lessonsPassed = $program->clientLessons()->where('price', '>', 0)->count();
                $nextPrice = $program->getNextPrice($lessonsPassed + $index + 1);
                $sum += $nextPrice;
            }
        }

        return $sum;
    }
}