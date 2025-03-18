<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\ClientLesson;

class VisitsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['is_free'],
        'status' => ['status'],
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return 'lessons.date';
    }

    public function getBaseQuery()
    {
        return ClientLesson::query()
            ->join('lessons', 'lessons.id', '=', 'client_lessons.lesson_id');
    }

    public function aggregate($query): int
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

        $query->whereHas('contractVersionProgram', fn ($q) => $q->whereIn('program', $programs));
    }

    protected function filterStatus(&$query, $values)
    {
        $query->where(function ($query) use ($values) {
            $query->where(function ($query) use ($values) {
                foreach ($values as $value) {
                    $query->orWhereRaw("FIND_IN_SET('{$value}', client_lessons.status)");
                }
            });
        });
    }
}
