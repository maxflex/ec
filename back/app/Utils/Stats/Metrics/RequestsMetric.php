<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Request as ClientRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class RequestsMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['program', 'responsible_user_id']
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return ClientRequest::query()
            ->whereRaw(<<<SQL
            DATE_FORMAT(created_at, ?) = ?
            SQL, [
                $sqlFormat,
                $date
            ]);
    }

    public static function getMetric(Builder|QueryBuilder $query): int
    {
        return $query->count();
    }
}