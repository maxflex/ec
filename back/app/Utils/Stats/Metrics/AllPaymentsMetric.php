<?php

namespace App\Utils\Stats\Metrics;

use App\Utils\AllPayments;
use Illuminate\Support\Facades\DB;

class AllPaymentsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'company', 'is_confirmed', 'is_return'
        ],
        'type' => ['type'],
        'findInSet' => ['method', 'year'],
    ];

    public static function getQuery()
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