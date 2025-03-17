<?php

namespace App\Utils\Stats\Metrics;

interface MetricInterface
{
    public function getQuery();

    public function getValue(): int;

    public function getQueryValue($query): int;

    public function getDateField(): string;
}
