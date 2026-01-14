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
            // ->where('is_substitute', false)
            ->where('status', '<>', LessonStatus::cancelled)
            ->select([
                'lessons.date',
                'lessons.time',
                'lessons.status',
                'g.program',
            ])
            ->get();

        // Расчёт длительности и координат
        foreach ($lessons as $l) {
            $l->duration = Program::from($l->program)->getDuration();
            $l->start = self::timeToSeconds($l->time);
            $l->end = $l->start + $l->duration * 60;
            $l->weekday = Carbon::parse($l->date)->dayOfWeekIso - 1; // 0 = Пн
        }

        /**
         * Условие 1 и 3: Убрана фильтрация одиночек.
         * Условие 2: Либо синие (planned), либо блеклые (conducted).
         */

        // Проверяем, есть ли хотя бы одно планируемое занятие
        $hasPlanned = $lessons->contains('status', LessonStatus::planned);

        if ($hasPlanned) {
            // РЕЖИМ 1: Есть планируемые занятия. Показываем только будущее (синие зубы).
            // Сортируем по возрастанию, чтобы взять ближайшие актуальные слоты.
            $candidates = $lessons
                ->where('status', LessonStatus::planned)
                ->sortBy(['date', 'time']);
        } else {
            // РЕЖИМ 2: Планируемых занятий нет. Показываем историю (блеклые зубы).
            // Сортируем по убыванию, чтобы взять последние проведенные слоты (актуальные на момент завершения).
            $candidates = $lessons
                ->where('status', LessonStatus::conducted)
                ->sortByDesc(['date', 'time']);
        }

        // Формируем итоговый набор, исключая визуальные конфликты (наложения)
        $result = collect();

        foreach ($candidates as $lesson) {
            // Если слот ещё не занят, добавляем зуб
            if (! self::conflicts($lesson, $result)) {
                $result->push($lesson);
            }
        }

        return $result
            ->map(fn ($l) => self::formatTooth($l, ! $hasPlanned))
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
