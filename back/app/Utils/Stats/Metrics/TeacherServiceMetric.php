<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;

class TeacherServiceMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['year'],
    ];

    public function getDateField(): string
    {
        return 'date';
    }

    public function getQueryValue($query): int
    {
        return $query->sum('sum');
    }

    public function getQuery()
    {
        return TeacherPayment::query();
    }
}
