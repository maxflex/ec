<?php

namespace App\Enums;

enum OtherPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case sbp = 'sbp';

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::sbp => 'СБП',
            self::cash => 'наличные',
        };
    }
}
