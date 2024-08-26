<?php

namespace App\Enums;

enum CallType: string
{
    case incoming = 'incoming';
    case outgoing = 'outgoing';

    public function text(): string
    {
        return match ($this) {
            static::incoming => 'входящие',
            static::outgoing => 'исходящие',
        };
    }
}
