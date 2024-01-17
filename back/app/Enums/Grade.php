<?php

namespace App\Enums;

enum Grade
{
    case grade1;
    case grade2;
    case grade3;
    case grade4;
    case grade5;
    case grade6;
    case grade7;
    case grade8;
    case grade9;
    case grade10;
    case grade11;
    case students;
    case other;
    case external;
    case school8;
    case school9;
    case school10;
    case online;
    case practicum11;

    public static function getById($id): self | null
    {
        return match ($id) {
            7 => self::grade7,
            8 => self::grade8,
            9 => self::grade9,
            10 => self::grade10,
            11 => self::grade11,
            12 => self::students,
            13 => self::other,
            14 => self::external,
            15 => self::school8,
            16 => self::school9,
            17 => self::school10,
            18 => self::online,
            19 => self::practicum11,
            default => null
        };
    }
}
