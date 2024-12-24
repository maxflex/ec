<?php

namespace App\Utils\Stats\Metrics;

use App\Utils\AllPayments;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AllPaymentsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'year', 'company', 'is_confirmed',
        ],
        'type' => ['type'],
        'findInSet' => ['method'],
    ];

    public static function getQuery(): Builder
    {
        return AllPayments::query();
    }

    public static function getQueryValue($query): int
    {
        return $query->sum(DB::raw("
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        "));
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    protected function filterType(&$query, $value)
    {
        $value
            ? $query->whereNotNull('contract_id')
            : $query->whereNull('contract_id');
    }
}