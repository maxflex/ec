<?php

namespace App\Utils;

use App\Enums\LogType;
use Illuminate\Support\Facades\{Redis, Hash};
use App\Models\{Phone, Teacher, Client, Log, User};

class Session
{
    public static function createToken(Phone $phone): string
    {
        $token = Hash::make($phone->number);
        Redis::set(
            self::cacheKey($token),
            $phone->id,
            'EX',
            self::getDuration($phone)
        );
        return $token;
    }

    public static function getDuration(Phone $phone): int
    {
        $hour = 60 * 60;
        return match ($phone->entity_type) {
            Client::class => $hour * 24 * 365,
            Teacher::class => $hour * 3,
            User::class => $hour * 3
        };
    }

    public static function logout(string $token)
    {
        Redis::del(self::cacheKey($token));
    }

    public static function get(?string $token): ?Phone
    {
        if ($token === null) {
            return null;
        }
        $phoneId = Redis::get(self::cacheKey($token));
        if (!$phoneId) {
            return null;
        }
        $phone = Phone::find($phoneId);
        if ($phone === null) {
            return null;
        }
        Redis::expire(self::cacheKey($token), self::getDuration($phone));
        return $phone;
    }

    public static function cacheKey(string $token)
    {
        return join(':', ['session', $token]);
    }
}
