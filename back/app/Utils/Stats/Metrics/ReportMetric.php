<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class ReportMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['year', 'program', 'is_published', 'is_moderated']
    ];

    public static function getQuery(): Builder
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