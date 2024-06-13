<?php

namespace App\Enums;

enum TeacherStatus: string
{
    case inactive = 'inactive';
    case active = 'active';
    case earlyReserve = 'earlyReserve';
    case lateReserve = 'lateReserve';
    case usedToWork = 'usedToWork';
    case interview = 'interview';
    case closed = 'closed';

    public static function getById(int $id): self
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
