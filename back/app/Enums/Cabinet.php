<?php

namespace App\Enums;

enum Cabinet: string
{
    case n428 = '428';
    case n430 = '430';
    case n432 = '432';
    case n433 = '433';
    case n434 = '434';
    case n439 = '439';

    case n407 = '407';
    case n409 = '409';
    case n412 = '412';
    case n413 = '413';
    case n417 = '417';
    case n422 = '422';
    case n423 = '423';
    case n424 = '424';

        // old
    case n10 = '10';
    case n35 = '35';
    case n205 = '205';
    case n214 = '214';
    case n221 = '221';
    case n301 = '301';
    case n302 = '302';
    case n303 = '303';
    case n304 = '304';
    case n305 = '305';
    case n310 = '310';
    case n311 = '311';
    case n314 = '314';
    case n319 = '319';
    case n320 = '320';
    case n321 = '321';
    case n322 = '322';
    case n507 = '507';
    case n809 = '809';

    public static function getOld(int $cabinetId): self
    {
        return match ($cabinetId) {
            2 => self::n35,
            5 => self::n809,
            8 => self::n507,
            10 => self::n10,
            13 => self::n301,
            14 => self::n302,
            15 => self::n303,
            16 => self::n321,
            17 => self::n322,
            18 => self::n304,
            19 => self::n320,
            20 => self::n319,
            21 => self::n310,
            22 => self::n311,
            23 => self::n314,
            25 => self::n305,
            26 => self::n205,
            27 => self::n221,
            28 => self::n214,
            29 => self::n439,
            30 => self::n434,
            31 => self::n432,
            32 => self::n430,
            33 => self::n428,
            34 => self::n433,
            35 => self::n412,
            36 => self::n407,
            37 => self::n409,
            38 => self::n413,
            39 => self::n417,
            40 => self::n423,
            41 => self::n424,
            42 => self::n422
        };
    }
}
