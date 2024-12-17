<?php

namespace App\Utils\Stats\Metrics;

use App\Utils\AllPayments;
use Illuminate\Support\Facades\DB;

class AllPaymentsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'year', 'company', 'is_confirmed', 'method'
        ],
        'type' => ['type']
//        'findInSet' => ['method'],
    ];

    public static function getQuery(string $date, string $sqlFormat)
    {
        return AllPayments::query()
            ->whereRaw("DATE_FORMAT(`date`, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->sum(DB::raw("
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        "));
    }

    protected function filterType(&$query, $value)
    {
        $value
            ? $query->whereNotNull('contract_id')
            : $query->whereNull('contract_id');
    }
}