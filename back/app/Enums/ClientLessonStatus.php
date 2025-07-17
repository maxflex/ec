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
}
