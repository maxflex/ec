<?php

namespace App\Enums;

enum ContractPaymentMethod: string
{
    case card = 'card';
    case online = 'online';
    case cash = 'cash';
    case invoice = 'invoice';

    public static function fromOld(string $method): self
    {
        return match ($method) {
            'card' => self::card,
            'cash' => self::cash,
            'bill' => self::invoice,
            'card_online' => self::online
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::online => 'карта онлайн',
            self::cash => 'наличные',
            self::invoice => 'счёт',
        };
    }
}
