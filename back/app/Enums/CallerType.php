<?php

namespace App\Enums;

enum CallerType: string
{
    case newClient = 'newClient';
    case newClientRecruit = 'newClientRecruit';
    case oldClient = 'oldClient';
    case oldClientRecruit = 'oldClientRecruit';
    case activeClient = 'activeClient';
    case teacher = 'teacher';
    case other = 'other';

    public function text(): string
    {
        return match ($this) {
            self::newClient => 'клиент новый',
            self::newClientRecruit => 'клиент новый привлечение',
            self::oldClient => 'клиент старый',
            self::oldClientRecruit => 'клиент старый привлечение',
            self::activeClient => 'клиент активный',
            self::teacher => 'преподаватель',
            self::other => 'другое',
        };
    }
}
