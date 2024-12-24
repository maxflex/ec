<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;

class TeacherServiceMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['year'],
//        'findInSet' => ['method'],
    ];

    public static function getQuery(string $date, string $sqlFormat)
    {
        return TeacherPayment::query()
            ->whereRaw("DATE_FORMAT(`date`, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('sum');
    }
}