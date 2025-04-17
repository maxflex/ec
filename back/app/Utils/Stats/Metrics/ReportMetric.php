<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\Direction;
use App\Models\Report;

class ReportMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['delivery'],
        'findInSet' => ['year', 'program', 'status'],
        'direction' => ['direction'],
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function aggregate($query): int
    {
        if (@$this->filterValues['aggregate'] === 'sum') {
            return $query->sum('price');
        }

        return $query->count();
    }

    public function getBaseQuery()
    {
        return Report::query();
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

        $query->whereIn('program', $programs);
    }
}
