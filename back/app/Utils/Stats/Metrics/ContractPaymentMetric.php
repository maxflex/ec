<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ContractPayment;
use Illuminate\Support\Facades\DB;

class ContractPaymentMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['is_confirmed', 'is_return'],
        'contract' => ['company'],
        'findInSet' => ['method', 'year'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function getBaseQuery()
    {
        return ContractPayment::query();
    }

    public function aggregate($query): int
    {
        return $query->sum(DB::raw('
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        '));
    }

    protected function filterContract($query, $value, $field)
    {
        $query->whereHas('contract', fn ($q) => $q->where($field, $value));
    }
}
