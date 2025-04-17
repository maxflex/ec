<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\Lesson;

class LessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['is_free', 'is_unplanned'],
        'direction' => ['direction'],
        'findInSet' => ['status'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery()
    {
        return Lesson::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    protected function filterDirection($query, array $values)
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
