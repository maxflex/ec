<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\Lesson;

class TeacherLessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status'],
        'direction' => ['direction'],
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
        return $query->sum('price');
    }


    protected function filterDirection(&$query, array $values)
    {
        if (count($values) === 0) {
            return;
        }

        $programs = collect();
        foreach ($values as $directionString) {
            $direction = Direction::from($directionString);
            $programs = $programs->concat(
                Direction::toPrograms($direction)
            );
        }
        $programs = $programs->unique();

        $query->whereHas('group', fn($q) => $q->whereIn('program', $programs));
    }
}