<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\Lesson;

class LessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status', 'is_free', 'is_unplanned'],
        'direction' => ['direction']
    ];

    public static function getQuery()
    {
        return Lesson::query();
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
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

        $query->whereHas('group', fn($q) => $q->whereIn('program', $programs));
    }
}