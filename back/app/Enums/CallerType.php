<?php

namespace App\Enums;

enum CallerType: string
{
    case newClient = 'newClient';
    case newClientRecruit = 'newClientRecruit';
    case oldClient = 'oldClient';
    case teacher = 'teacher';
    case other = 'other';

    public function text(): string
    {
        return match ($this) {
            self::newClient => 'новые клиент',
            self::newClientRecruit => 'новый клиент (вербовка)',
            self::oldClient => 'старый клиент',
            self::teacher => 'преподаватель',
            self::other => 'другое',
        };
    }
}
