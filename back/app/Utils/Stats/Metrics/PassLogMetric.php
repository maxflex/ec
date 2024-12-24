<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Pass;
use App\Models\PassLog;
use Illuminate\Database\Eloquent\Builder;

class PassLogMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['entity_type'],
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return PassLog::query()
            ->where('entity_type', '<>', Pass::class)
            ->whereRaw(<<<SQL
            DATE_FORMAT(used_at, ?) = ?
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