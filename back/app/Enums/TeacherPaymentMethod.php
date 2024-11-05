<?php

namespace App\Enums;

enum TeacherPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case bill = 'bill';
    case mutual = 'mutual';

    public static function fromOld(string $method): self
    {
        return match ($method) {
            'card' => self::card,
            'cash' => self::cash,
            'bill' => self::bill,
            'mutual' => self::mutual,
        };
    }

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::cash => 'наличные',
            self::bill => 'счёт',
            self::mutual => 'взаимозачёт',
        };
    }
}
