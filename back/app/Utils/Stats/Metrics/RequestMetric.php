<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Request as ClientRequest;

class RequestMetric extends BaseMetric
{
    protected $filters = [
        'findInSet' => ['direction', 'responsible_user_id', 'status'],
        'equals' => ['is_verified'],
        'null' => ['is_from_internet'],
        'passes' => ['passes'],
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

    protected function filterPasses(&$query, $value)
    {
        $query->where(function ($q) use ($value) {
            foreach ($value as $v) {
                switch ($v) {
                    case 'hasUsedPasses':
                        $q->orWhereHas('passes', fn ($q) => $q->whereHas('passLog'));
                        break;

                    case 'hasPasses':
                        $q->orWhereHas('passes');
                        break;

                    case 'noPasses':
                        $q->orWhereDoesntHave('passes');
                        break;
                }
            }
        });
    }
}
