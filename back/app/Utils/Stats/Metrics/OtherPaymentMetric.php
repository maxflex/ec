<?php

namespace App\Utils\Stats\Metrics;

use App\Models\OtherPayment;
use Illuminate\Support\Facades\DB;

class OtherPaymentMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'is_confirmed', 'is_return',
        ],
        'findInSet' => ['method'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery()
    {
        return OtherPayment::query();
    }

    public function aggregate($query): int
    {
        return $query->sum(DB::raw('
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        '));
    }
}
