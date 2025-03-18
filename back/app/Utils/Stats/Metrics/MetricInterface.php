<?php

namespace App\Utils\Stats\Metrics;

interface MetricInterface
{
    public function getBaseQuery();

    public function getValueForDate(string $date): int;

    public function getTotalValue(): int;

    public function aggregate($query): int;

    public function getDateField(): string;
}
