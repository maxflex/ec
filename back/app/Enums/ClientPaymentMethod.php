<?php

namespace App\Enums;

enum ClientPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case sbp = 'sbp';

    public static function fromOld(string $method): self
    {
        return match ($method) {
            'cash' => self::cash,
            default => self::card,
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::sbp => 'СБП',
            self::cash => 'наличные',
        };
    }
}
