<?php

namespace App\Utils\Stats\Metrics;

use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;

class TeacherLessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status'],
    ];

    public static function getQuery(): Builder
    {
        return Lesson::query();
    }

    public static function getDateField(): string
    {
        return 'date';
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('price');
    }
}