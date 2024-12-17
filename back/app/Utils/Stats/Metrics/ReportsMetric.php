<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Report;
use Illuminate\Database\Eloquent\Builder;

class ReportsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['year', 'program', 'is_published', 'is_moderated']
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return Report::query()
            ->whereRaw(<<<SQL
            DATE_FORMAT(created_at, ?) = ?
            SQL, [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}