<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherService;

class TeacherServiceMetric extends BaseMetric
{
    public function getDateField(): string
    {
        return '`date`';
    }

    public function aggregate($query): int
    {
        return $query->sum('sum');
    }

    public function getBaseQuery()
    {
        return TeacherService::query();
    }
}
