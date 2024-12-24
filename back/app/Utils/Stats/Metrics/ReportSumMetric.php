<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class ReportSumMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'year', 'program', 'is_published', 'is_moderated'
        ]
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return Report::query()
            ->whereRaw("DATE_FORMAT(created_at, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('price');
    }
}