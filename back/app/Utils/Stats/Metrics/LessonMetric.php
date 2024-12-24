<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;

class LessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status'],
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return Lesson::query()
            ->whereRaw("DATE_FORMAT(`date`, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->count();
    }
}