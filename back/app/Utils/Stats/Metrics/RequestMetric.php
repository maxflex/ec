<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Request as ClientRequest;

class RequestMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['direction', 'responsible_user_id', 'status'],
        'equals' => ['is_verified'],
        'null' => ['is_from_internet'],
        'pass' => ['pass'],
    ];

    protected $mapFilters = [
        'is_from_internet' => 'user_id',
    ];

    public function getDateField(): string
    {
        return 'created_at';
    }

    public function getBaseQuery()
    {
        return ClientRequest::query();
    }

    public function aggregate($query): int
    {
        return $query->count();
    }

    protected function filterPass($query, $value)
    {
        switch ($value) {
            case 'hasUsedPasses':
                $query->whereHas('passes', fn ($q) => $q->whereHas('passLog'));
                break;

            case 'hasPasses':
                $query->whereHas('passes');
                break;

            case 'noPasses':
                $query->whereDoesntHave('passes');
                break;
        }
    }
}
