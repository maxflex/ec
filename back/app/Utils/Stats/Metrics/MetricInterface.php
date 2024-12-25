<?php

namespace App\Utils\Stats\Metrics;


interface MetricInterface
{
    public static function getQuery();

    public static function getQueryValue($query): int;

    public static function getDateField(): string;
}