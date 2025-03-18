<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Call;

class CallMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['user_id'],
        'equals' => ['type'],
        'null' => ['answered_at'],
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function getBaseQuery()
    {
        return Call::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }
}
