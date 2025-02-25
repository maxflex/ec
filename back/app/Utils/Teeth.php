<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class Teeth
{
    const MIN_SECONDS = 33000; // TIME_TO_SEC("09:10")

    const MAX_SECONDS = 74400; // TIME_TO_SEC("20:40")

    public static function get(Builder $lessonsQuery, int $year, bool $isGroup = false): object
    {
        $query = $lessonsQuery
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('is_unplanned', 0)
            ->where('g.year', $year);

        // Смысл в чём: если группа приближается к концу, расписание в ней не должно меняться
        // (исчезать и т.д), поэтому мы анализируем (максимальная дата занятия – 1 месяц)
        // и занятия во всех статусах. Иначе только занятия в статусе planned
        $flag = false;
        if ($isGroup) {
            $maxLessonDateSubMonth = Carbon::parse($query->max('lessons.date'))->subMonth();
            if (now()->gt($maxLessonDateSubMonth)) {
                $flag = true;
                $query
                    ->where('status', '<>', LessonStatus::cancelled) // conducted | planned
                    ->where('lessons.date', '>=', $maxLessonDateSubMonth->format('Y-m-d'));
            }
        }

        if (! $flag) {
            $query->where('status', LessonStatus::planned);
        }

        $query
            ->selectRaw("
                DAYOFWEEK(`date`) as `weekday`,
                `time`,
                IF(g.program LIKE '%School8' OR g.program LIKE '%School9', 55, 125) as `duration`,
                count(*) as cnt
            ")
            ->groupBy('weekday', 'time', 'duration')
            ->having('cnt', '>', 1); // должно быть более 1 совпадения

        $lessons = $query->get();

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
            self::secondsToPercent($endSeconds),
        ];
    }

    private static function timeToSeconds(string $time): int
    {
        return (int) self::time($time)->secondsSinceMidnight();
    }

    private static function time(string $time)
    {
        return Carbon::createFromFormat('H:i:s', $time);
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
}
