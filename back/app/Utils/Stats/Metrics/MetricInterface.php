<?php

namespace App\Utils\Stats\Metrics;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface MetricInterface
{
    public static function getQuery(string $date, string $sqlFormat): EloquentBuilder|QueryBuilder;

    public static function getQueryValue(EloquentBuilder|QueryBuilder $query): int;
}