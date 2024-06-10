<?php

namespace App\Enums;

enum TeacherPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case invoice = 'invoice';
    case mutual = 'mutual';

    public static function getFromOld(string $method): self
    {
        return match ($method) {
            'card' => self::card,
            'cash' => self::cash,
            'bill' => self::invoice,
            'mutual' => self::mutual,
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::cash => 'наличные',
            self::invoice => 'счёт',
            self::mutual => 'взаимозачёт',
        };
    }
}
