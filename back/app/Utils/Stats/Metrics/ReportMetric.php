<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Report;

class ReportMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['year', 'program', 'status'],
    ];

    public static function getQuery()
    {
        return Report::query();
    }

    public static function getDateField(): string
    {
        return 'created_at';
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}