<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Models\Lesson;
use Illuminate\Support\Collection;

class ClientLessonMetric extends BaseMetric
{
    protected $filters = [
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery()
    {
        return Lesson::query()
            ->where('status', '<>', LessonStatus::cancelled)
            ->where('is_free', false);
    }

    public function aggregate($query): int
    {
        /** @var Collection<int, Lesson> $lessons */
        $lessons = $query->get();

        // conducted sum
        $sum = $lessons->where('status', LessonStatus::conducted)->reduce(
            fn ($carry, Lesson $l) => $carry + $l->clientLessons()->sum('price'), 0
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

    protected function filterDirection(&$query, array $values)
    {
        if (count($values) === 0) {
            return;
        }

        $programs = collect();
        foreach ($values as $directionString) {
            $programs = $programs->concat(
                Direction::from($directionString)->toPrograms()
            );
        }
        $programs = $programs->unique();

        $query->whereHas('group', fn ($q) => $q->whereIn('program', $programs));
    }
}
