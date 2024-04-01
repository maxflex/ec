<?php

namespace App\Enums;

enum LogType
{
    case create;
    case update;
    case delete;
    case view;
    case auth;

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
