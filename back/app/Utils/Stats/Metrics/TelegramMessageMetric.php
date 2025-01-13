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
        'status' => 'telegram_id'
    ];

    public static function getQuery()
    {
        return TelegramMessage::query();
    }

    public static function getDateField(): string
    {
        return 'created_at';
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}