<?php

namespace App\Enums;

enum Cabinet: string
{
    case cab407 = 'cab407';
    case cab408 = 'cab408';
    case cab409 = 'cab409';
    case cab412 = 'cab412';
    case cab413 = 'cab413';
    case cab414 = 'cab414';
    case cab416 = 'cab416';
    case cab417 = 'cab417';
    case cab418 = 'cab418';
    case cab420 = 'cab420';
    case cab422 = 'cab422';
    case cab423 = 'cab423';
    case cab424 = 'cab424';
    case cab427 = 'cab427';
    case cab428 = 'cab428';
    case cab430 = 'cab430';
    case cab432 = 'cab432';
    case cab433 = 'cab433';
    case cab434 = 'cab434';

    case cab308 = 'cab308';
    case cab309 = 'cab309';
    case cab310 = 'cab310';
    case cab312 = 'cab312';
    case cab314 = 'cab314';
    case cab316 = 'cab316';

    /**
     * Inactive
     */
    case cab439 = 'cab439';
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

    /**
     * Кабинет неактивен
     */
    public function isArchived(): bool
    {
        return $this->capacity() === 0;
    }

    /**
     * Номинальная вместимость
     */
    public function capacity(): int
    {
        return match ($this) {
            self::cab310, self::cab427, self::cab409, self::cab412, self::cab309 => 8,
            self::cab407, self::cab408, self::cab308, self::cab312, self::cab424, self::cab417, self::cab423 => 10,
            self::cab414, self::cab413, self::cab420, self::cab418, self::cab416, self::cab316 => 12,
            self::cab422, self::cab314, self::cab434, self::cab433, self::cab432, self::cab430, self::cab428 => 13,

            default => 0
        };
    }

    /**
     * cab422 => 422
     */
    public function getName()
    {
        return filter_var($this->value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * У нас в ZOOM каждому кабинету соответствует свой
     * ZOOM UserID
     */
    /**
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
     */
}
