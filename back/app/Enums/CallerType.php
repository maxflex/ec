<?php

namespace App\Enums;

enum CallerType: string
{
    case newClient = 'newClient';
    case oldClient = 'oldClient';
    case teacher = 'teacher';
    case other = 'other';

    public function text(): string
    {
        return match ($this) {
            self::newClient => 'клиент новый',
            self::oldClient => 'клиент старый',
            self::teacher => 'преподаватель',
            self::other => 'другое',
        };
    }
}
