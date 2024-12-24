<?php

namespace App\Utils\Stats\Metrics;

use Illuminate\Database\Eloquent\Builder;

interface MetricInterface
{
    public static function getQuery(): Builder;

    public static function getQueryValue($query): int;

    public static function getDateField(): string;
}