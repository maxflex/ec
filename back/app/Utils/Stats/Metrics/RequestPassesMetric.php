<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Pass;

class RequestPassesMetric extends BaseMetric
{
    protected $filters = [
        'direction' => ['direction'],
        'hasUsed' => ['has_used'],
    ];

    public function getDateField(): string
    {
        return '`date`';
    }

    public function aggregate($query): int
    {
        return $query
            ->groupByRaw('`date`, request_id')
            ->selectRaw('`date`, request_id')
            ->get()
            ->count();
    }

    public function getBaseQuery()
    {
        return Pass::query()->whereNotNull('request_id');
    }

    protected function filterDirection(&$query, array $directions)
    {
        if (count($directions) === 0) {
            return;
        }
        $query->whereHas('request',
            fn ($q) => $q->whereIn('direction', $directions)
        );
    }

    protected function filterHasUsed(&$query, $value)
    {
        $value
            ? $query->whereHas('passLog')
            : $query->whereDoesntHave('passLog');
    }
}
