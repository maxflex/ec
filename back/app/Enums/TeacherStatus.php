<?php

namespace App\Enums;

enum TeacherStatus
{
    case inactive;
    case active;
    case earlyReserve;
    case lateReserve;
    case usedToWork;
    case interview;
    case closed;

    public static function getById(int $id): TeacherStatus
    {
        return match ($id) {
            0 => self::inactive,
            1 => self::earlyReserve,
            6 => self::lateReserve,
            2 => self::active,
            3 => self::usedToWork,
            4 => self::interview,
            5 => self::closed
        };
    }
}
