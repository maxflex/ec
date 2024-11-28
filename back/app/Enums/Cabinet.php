<?php

namespace App\Enums;

enum Cabinet: string
{
    case cab428 = 'cab428';
    case cab430 = 'cab430';
    case cab432 = 'cab432';
    case cab433 = 'cab433';
    case cab434 = 'cab434';
    case cab439 = 'cab439';

    case cab407 = 'cab407';
    case cab409 = 'cab409';
    case cab412 = 'cab412';
    case cab413 = 'cab413';
    case cab414 = 'cab414';
    case cab417 = 'cab417';
    case cab418 = 'cab418';
    case cab420 = 'cab420';
    case cab422 = 'cab422';
    case cab423 = 'cab423';
    case cab424 = 'cab424';

    case cab310 = 'cab310';
    case cab312 = 'cab312';
    case cab314 = 'cab314';
    case cab316 = 'cab316';

    // old
    case tur10 = 'tur10';
    case tur35 = 'tur35';
    case tur205 = 'tur205';
    case tur214 = 'tur214';
    case tur221 = 'tur221';
    case tur301 = 'tur301';
    case tur302 = 'tur302';
    case tur303 = 'tur303';
    case tur304 = 'tur304';
    case tur305 = 'tur305';
    case tur310 = 'tur310';
    case tur311 = 'tur311';
    case tur314 = 'tur314';
    case tur319 = 'tur319';
    case tur320 = 'tur320';
    case tur321 = 'tur321';
    case tur322 = 'tur322';
    case tur507 = 'tur507';
    case tur809 = 'tur809';

    public static function fromOld(int $cabinetId): self
    {
        return match ($cabinetId) {
            2 => self::tur35,
            5 => self::tur809,
            8 => self::tur507,
            10 => self::tur10,
            13 => self::tur301,
            14 => self::tur302,
            15 => self::tur303,
            16 => self::tur321,
            17 => self::tur322,
            18 => self::tur304,
            19 => self::tur320,
            20 => self::tur319,
            21 => self::tur310,
            22 => self::tur311,
            23 => self::tur314,
            25 => self::tur305,
            26 => self::tur205,
            27 => self::tur221,
            28 => self::tur214,
            29 => self::cab439,
            30 => self::cab434,
            31 => self::cab432,
            32 => self::cab430,
            33 => self::cab428,
            34 => self::cab433,
            35 => self::cab412,
            36 => self::cab407,
            37 => self::cab409,
            38 => self::cab413,
            39 => self::cab417,
            40 => self::cab423,
            41 => self::cab424,
            42 => self::cab422,
            43 => self::cab414,
            44 => self::cab418,
            45 => self::cab420,
            46 => self::cab310,
            47 => self::cab312,
            48 => self::cab314,
            49 => self::cab316,
        };
    }

    /**
     * У нас в ZOOM каждому кабинету соответствует свой
     * ZOOM UserID
     */
    public function getZoomUserId(): ?string
    {
        return match ($this) {
            self::cab310 => '_H_TAPOZR5CcAHkv4msBzw',
            self::cab312 => 'cKkPLkj-TeeDhJPkJQSVYw',
            self::cab314 => '84iZzyvKQj25UEEWwJZu_w',
            self::cab316 => 'suFs1QTdQZOfAqQZoJub2Q',
            self::cab407 => '7arTEpSxR2-WOM9sattmog',
            self::cab409 => 'tNuph7FgQC6rytbMwsKGMg',
            self::cab412 => 'I7Pm1RtuSWamxW1mt-993Q',
            self::cab413 => 'vzVCfTJKS1araTSXOKKeFQ',
            self::cab414 => 'e1MKDR9XT0uqF9CNP5ja5Q',
            self::cab417 => 'K_M7DHS-TwSLPEoriKWQxw',
            self::cab418 => 'IidauDRzQ9qlkHdq2SnVog',
            self::cab420 => 'qBy4XhvwSS-BrR0HU6NcIA',
            self::cab422 => '7VkJ7lXmTsOsMPAi1i1g8w',
            self::cab423 => 'XNrXrBHCSPOQ9KyErRzn2A',
            self::cab424 => 'oyEBX0k9SZWuWyYNx7Jj0Q',
            self::cab428 => 'V4kLgxdeT6-w9OVd4771WA',
            self::cab430 => 'k5Ae5dzUQCOwa9_IicfxBA',
            self::cab432 => 'IJwaRNz3Tre9ss7JwIJr3w',
            self::cab433 => 'LpouUBOHTOyBHrRRYkRTig',
            self::cab434 => 'k1onTy6zRxSojiK0uoGs2Q',
            self::cab439 => 'aC0Sn9wsTYOOAbcGpK8boQ',
            default => null,
        };
    }
}
