<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;

class TeacherPaymentMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['is_confirmed'],
        'findInSet' => ['method', 'year'],
    ];

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
        return TeacherPayment::query();
    }
}
