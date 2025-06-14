<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TelegramMessage;

class TelegramMessageMetric extends BaseMetric
{
    protected $filters = [
        'null' => ['status'],
        'findInSet' => ['template', 'entity_type'],
    ];

    protected $mapFilters = [
        'status' => 'telegram_id',
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function getBaseQuery()
    {
        return TelegramMessage::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }
}
