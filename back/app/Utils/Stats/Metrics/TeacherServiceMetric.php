<?php

namespace App\Utils\Stats\Metrics;

use App\Models\TeacherPayment;
use Illuminate\Database\Eloquent\Builder;

class TeacherServiceMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['year'],
    ];

    public static function getQuery(): Builder
    {
        return TeacherPayment::query();
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('sum');
    }
}