<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;

class TeacherPaymentMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['is_confirmed'],
        'findInSet' => ['method', 'year'],
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