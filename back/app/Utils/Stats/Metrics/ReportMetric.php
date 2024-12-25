<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Report;

class ReportMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['year', 'program', 'is_published', 'is_moderated']
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