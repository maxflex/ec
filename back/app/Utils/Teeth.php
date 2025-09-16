<?php

namespace App\Utils;

use App\Enums\LessonStatus;
use App\Enums\Program;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Teeth
{
    const MIN_SECONDS = 32400; // TIME_TO_SEC("09:00")

    const MAX_SECONDS = 75600; // TIME_TO_SEC("21:00")

    public static function get(Builder $lessonsQuery): array
    {
        $lessons = $lessonsQuery
            ->join('groups as g', 'g.id', '=', 'lessons.group_id')
            ->where('is_unplanned', false)
            ->where('status', '<>', LessonStatus::cancelled)
            ->select([
                'lessons.date',
                'lessons.time',
                'lessons.status',
                'g.program',
            ])
            ->get();

        // Расчёт длительности
        foreach ($lessons as $l) {
            $l->duration = Program::from($l->program)->getDuration();
            $l->start = self::timeToSeconds($l->time);
            $l->end = $l->start + $l->duration * 60;
            $l->weekday = Carbon::parse($l->date)->dayOfWeekIso - 1; // 0 = Пн
        }

        // Удалим одиночки по (weekday, time)
        $lessons = $lessons
            ->groupBy(fn ($l) => $l->weekday.'|'.$l->time)
            ->filter(fn ($g) => $g->count() > 1)
            ->flatten();

        // Разделим на планируемые и проведённые
        $planned = $lessons->where('status', LessonStatus::planned)->sortBy(['date', 'time']);
        $conducted = $lessons->where('status', LessonStatus::conducted)->sortByDesc(['date', 'time']);

        // Итерация вперёд — X
        $resultX = collect();
        foreach ($planned as $lesson) {
            if (! self::conflicts($lesson, $resultX)) {
                $resultX->push($lesson);
            }
        }

        // Итерация назад — Y
        $resultY = collect();
        foreach ($conducted as $lesson) {
            if (! self::conflicts($lesson, $resultX) && ! self::conflicts($lesson, $resultY)) {
                $resultY->push($lesson);
            }
        }

        // Финальная фильтрация Y: если есть конфликт с X, то пропускаем
        $resultY = $resultY->reject(fn ($l) => self::conflicts($l, $resultX));

        // Объединяем X + Y
        return $resultX
            ->map(fn ($l) => self::formatTooth($l, false))
            ->concat($resultY->map(fn ($l) => self::formatTooth($l, true)))
            ->values()
            ->all();
    }

    private static function timeToSeconds(string $time): int
    {
        return (int) self::time($time)->secondsSinceMidnight();
    }

    private static function time(string $time)
    {
        return Carbon::createFromFormat('H:i:s', $time);
    }

    private static function conflicts($lesson, Collection $set): bool
    {
        return $set->contains(
            fn ($e) => $lesson->weekday === $e->weekday
                && max($lesson->start, $e->start) < min($lesson->end, $e->end)
        );
    }

    private static function formatTooth($lesson, bool $isPast): array
    {
        [$start, $end] = [self::secondsToPercent($lesson->start), self::secondsToPercent($lesson->end)];

        return [
            'weekday' => $lesson->weekday,
            'time' => $lesson->time,
            'time_end' => self::time($lesson->time)->addMinutes($lesson->duration)->format('H:i:s'),
            'left' => $start,
            'width' => $end - $start,
            'is_past' => $isPast,
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
}
