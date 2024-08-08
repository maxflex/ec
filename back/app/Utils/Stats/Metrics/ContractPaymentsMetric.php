<?php

namespace App\Utils\Stats\Metrics;

use App\Models\ContractPayment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

class ContractPaymentsMetric extends BaseMetric
{
    protected $filters = [
        'contract' => ['year'],
        'equals' => ['is_return'],
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return ContractPayment::query()
            ->whereRaw(<<<SQL
            DATE_FORMAT(created_at, ?) = ?
            SQL, [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue(Builder|QueryBuilder $query): int
    {
        return $query->sum(DB::raw(<<<SQL
            CAST(IF(is_return = 1, -`sum`, `sum`) AS SIGNED)
        SQL
        ));
    }

    protected function filterContract(&$query, $value, $field)
    {
        $query->whereHas('contract', fn($q) => $q->where($field, $value));
    }
}