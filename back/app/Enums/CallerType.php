<?php

namespace App\Enums;

enum CallerType: string
{
    case newClient = 'newClient';
    case newClientRecruit = 'newClientRecruit';
    case oldClient = 'oldClient';
    case activeClient = 'activeClient';
    case teacher = 'teacher';
    case other = 'other';

    public function text(): string
    {
        return match ($this) {
            self::newClient => 'клиент новый',
            self::newClientRecruit => 'клиент привлечение',
            self::oldClient => 'клиент старый',
            self::activeClient => 'клиент активный',
            self::teacher => 'преподаватель',
            self::other => 'другое',
        };
    }
}
