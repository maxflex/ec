<?php

/**
 * т.н. Зубы
 */

namespace App\Utils;

use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Builder;

class Teeth
{
    const MIN_SECONDS = 36000; // SEC_TO_TIME = 10:00
    const MAX_SECONDS = 75600; // SEC_TO_TIME = 21:00

    /**
     * @param Lesson[] $lessons
     */
    public static function get(Builder $lessonsQuery)
    {
        $lessons = $lessonsQuery
            // ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->selectRaw(<<<SQL
                DAYOFWEEK(`date`) as `weekday`,
                `time`,
                `time` + interval g.duration minute as `t_end`,
                TIME_TO_SEC(`time`) as `start`,
                TIME_TO_SEC(`time` + INTERVAL g.duration MINUTE) as `end`
            SQL)
            ->groupBy('weekday', 'time', 'duration')
            ->get();

        foreach ($lessons as $l) {
            $weekday = $l->weekday === 1 ? 6 : $l->weekday - 2;
            $startPercent = self::getPercent($l->start);
            $endPercent = self::getPercent($l->end);
            // if (!isset($teeth[$weekday])) {
            //     $teeth[$weekday] = [];
            // }
            $teeth[$weekday][] = [
                'time' => $l->time,
                'time_end' => $l->t_end,
                'left' => $startPercent,
                'width' => $endPercent - $startPercent,
            ];
        }
        return (object) $teeth;
    }

    private static function getPercent($seconds)
    {
        return intval(round(
            ($seconds - self::MIN_SECONDS) / (self::MAX_SECONDS - self::MIN_SECONDS) * 100
        ));
    }
}
