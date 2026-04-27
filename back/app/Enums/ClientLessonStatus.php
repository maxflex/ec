<?php

namespace App\Enums;

use Illuminate\Support\Collection;

enum ClientLessonStatus: string
{
    case present = 'present';
    case presentOnline = 'presentOnline';
    case late = 'late';
    case lateOnline = 'lateOnline';
    case absent = 'absent';

    public static function getOnlineStatuses(): Collection
    {
        return collect([
            self::presentOnline,
            self::lateOnline,
        ]);
    }

    public static function getLateStatuses(): Collection
    {
        return collect([
            self::late,
            self::lateOnline,
        ]);
    }

    public function text(?int $minutesLate = null): string
    {
        // Для статусов опоздания добавляем длительность, если она указана.
        $lateSuffix = $minutesLate ? " на {$minutesLate} мин" : '';

        return match ($this) {
            self::present => 'был',
            self::presentOnline => 'был онлайн',
            self::late => "опоздал{$lateSuffix}",
            self::lateOnline => "опоздал онлайн{$lateSuffix}",
            self::absent => 'не был',
        };
    }
}
