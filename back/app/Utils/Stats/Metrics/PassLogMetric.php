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

    public static function getQuery(): Builder
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