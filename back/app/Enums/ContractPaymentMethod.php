<?php

namespace App\Enums;

enum ContractPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case bill = 'bill';
    case matcap = 'matcap';
    case sbp = 'sbp';

    public static function fromOld(string $method): self
    {
        return match ($method) {
            'card' => self::card,
            'cash' => self::cash,
            'bill' => self::bill,
            'card_online' => self::matcap
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::cash => 'наличные',
            self::bill => 'счёт',
            self::matcap => 'маткапитал',
            self::sbp => 'СБП',
        };
    }
}
