<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;

class TeacherServiceMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['year'],
    ];

    public static function getQuery()
    {
        return TeacherPayment::query();
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('sum');
    }
}