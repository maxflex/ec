<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Request as ClientRequest;
use Illuminate\Database\Eloquent\Builder;

class RequestMetric extends BaseMetric
{
    protected $filters = [
        'equals' => [
            'direction', 'responsible_user_id'
        ],
        'null' => ['is_from_internet'],
    ];

    protected $mapFilters = [
        'is_from_internet' => 'user_id'
    ];

    public static function getQuery(): Builder
    {
        return ClientRequest::query();
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