<?php

namespace App\Enums;

enum ContractPaymentMethod: string
{
    case card = 'card';
    case cash = 'cash';
    case bill = 'bill';
    case matcap = 'matcap';

    /**
     * СПБ касса
     */
    case sbp = 'sbp';

    /**
     * СПБ онлайн
     */
    case sbpOnline = 'sbpOnline';

    public function getTitle(): string
    {
        return match ($this) {
            self::card => 'карта',
            self::cash => 'наличные',
            self::bill => 'счёт',
            self::matcap => 'маткапитал',
            self::sbp => 'СБП касса',
            self::sbpOnline => 'СБП онлайн',
        };
    }
}
