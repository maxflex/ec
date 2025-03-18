<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ClientPayment;
use Illuminate\Support\Facades\DB;

class ClientPaymentMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'company', 'is_confirmed', 'is_return',
        ],
        'findInSet' => ['method', 'year'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery()
    {
        return ClientPayment::query();
    }

    public function aggregate($query): int
    {
        return $query->sum(DB::raw('
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        '));
    }
}
