<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Enums\LessonStatus;
use App\Models\ClientLesson;

class ClientLessonMetric extends BaseMetric
{
    protected $filters = [
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return 'lessons.`date`';
    }

    public function getBaseQuery()
    {
        return ClientLesson::query()
            ->join('lessons', 'client_lessons.lesson_id', '=', 'lessons.id')
            ->where('lessons.status', LessonStatus::conducted);
    }

    public function aggregate($query): int
    {
        return $query->sum('client_lessons.price');
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

        $query
            ->join('groups', 'lessons.group_id', '=', 'groups.id')
            ->whereIn('groups.program', $programs);
    }
}
