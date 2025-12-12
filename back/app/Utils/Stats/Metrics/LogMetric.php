<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Log;

class LogMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['device', 'table', 'entity_type', 'type'],
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function getBaseQuery()
    {
        // исключаем эмуляцию
        return Log::query()->whereNull('emulation_user_id');
    }

    public function aggregate($query): int
    {
        return $query->count();
    }
}
