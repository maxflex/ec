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

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return ClientRequest::query()
            ->whereRaw("DATE_FORMAT(created_at, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}