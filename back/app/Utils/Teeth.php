<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Teeth
{
    const MIN_SECONDS = 37200; // SEC_TO_TIME = 10:20
    const MAX_SECONDS = 74400; // SEC_TO_TIME = 20:40

    public static function get(Builder $lessonsQuery, int $year): object
    {
        $lessons = $lessonsQuery
            ->where('status', LessonStatus::planned)
            ->where('is_unplanned', 0)
            ->where('g.year', $year)
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->selectRaw("
                DAYOFWEEK(`date`) as `weekday`,
                `time`,
                IF(g.program LIKE '%School8' OR g.program LIKE '%School9', 55, 125) as `duration`
            ")
            ->groupBy('weekday', 'time', 'duration')
            ->get();

        $teeth = [];
        foreach ($lessons as $l) {
            $weekday = $l->weekday === 1 ? 6 : $l->weekday - 2;
            [$start, $end] = self::getPercents($l);
            $teeth[$weekday][] = [
                'time' => $l->time,
                'time_end' => self::time($l->time)->addMinutes($l->duration)->format('H:i:s'),
                'left' => $start,
                'width' => $end - $start,
            ];
        }
        return (object) $teeth;
    }


    /**
     * @return array{int, int}
     */
    private static function getPercents($l): array
    {
        $startSeconds = self::timeToSeconds($l->time);
        $endSeconds = $startSeconds + $l->duration * 60;

        return [
            self::secondsToPercent($startSeconds),
            self::secondsToPercent($endSeconds)
        ];
    }

    /**
     * 10:20 – 0%
     * 20:40 – 100%
     */
    private static function secondsToPercent($seconds)
    {
        return intval(round(
            ($seconds - self::MIN_SECONDS) / (self::MAX_SECONDS - self::MIN_SECONDS) * 100
        ));
    }

    private static function timeToSeconds(string $time): int
    {
        return (int)self::time($time)->secondsSinceMidnight();
    }

    private static function time(string $time)
    {
        return Carbon::createFromFormat('H:i:s', $time);
    }
}
