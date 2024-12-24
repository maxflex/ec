<?php

namespace App\Utils\Stats\Metrics;

use App\Enums\LessonStatus;
use App\Models\Lesson;
use Illuminate\Database\Eloquent\Builder;

class TeacherLessonMetric extends BaseMetric
{
    protected $filters = [
        'equals' => ['status'],
    ];

    public static function getQuery(string $date, string $sqlFormat): Builder
    {
        return Lesson::query()
            ->where('status', '<>', LessonStatus::cancelled)
            ->whereRaw("DATE_FORMAT(`date`, ?) = ?", [
                $sqlFormat,
                $date
            ]);
    }

    public static function getQueryValue($query): int
    {
        return $query->sum('price');
    }
}