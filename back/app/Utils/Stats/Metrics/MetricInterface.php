<?php

namespace App\Utils\Stats\Metrics;

interface MetricInterface
{
    public static function getQuery(string $date, string $sqlFormat);

    public static function getQueryValue($query): int;
}