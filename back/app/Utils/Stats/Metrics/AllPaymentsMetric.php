<?php

namespace App\Utils\Stats\Metrics;

use App\Utils\AllPayments;
use Illuminate\Support\Facades\DB;

class AllPaymentsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'company', 'is_confirmed', 'is_return',
        ],
        'type' => ['type'],
        'findInSet' => ['method', 'year'],
    ];

    public function getDateField(): string
    {
        return 'date';
    }

    public function getQuery()
    {
        return AllPayments::query();
    }

    public function getQueryValue($query): int
    {
        return $query->sum(DB::raw('
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        '));
    }

    protected function filterType(&$query, $value)
    {
        $value
            ? $query->whereNotNull('contract_id')
            : $query->whereNull('contract_id');
    }
}
