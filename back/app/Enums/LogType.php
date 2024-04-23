<?php

namespace App\Enums;

enum LogType: string
{
    case create = 'create';
    case update = 'update';
    case delete = 'delete';
    case view = 'view';
    case auth = 'auth';

    public function text(): string
    {
        return match ($this) {
            static::create => 'создание',
            static::update => 'обновление',
            static::delete => 'удаление',
            static::view => 'просмотр',
            static::auth => 'авторизация',
        };
    }
}
