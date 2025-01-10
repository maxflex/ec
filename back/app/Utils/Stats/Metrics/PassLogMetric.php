<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Pass;
use App\Models\PassLog;

class PassLogMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['entity_type'],
    ];

    public static function getQuery()
    {
        return PassLog::query()
            ->where('entity_type', '<>', Pass::class);
    }

    public static function getDateField(): string
    {
        return 'used_at';
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}