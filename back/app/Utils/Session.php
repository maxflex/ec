<?php

namespace App\Utils;

use App\Models\Client;
use App\Models\Phone;
use App\Models\Representative;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class Session
{
    /**
     * Create new session for Phone (log in)
     *
     * @return string Session token
     */
    public static function logIn(Phone $phone): string
    {
        $hash = Hash::make($phone->number);

        // App\Models\User => admin
        // App\Models\Client => client
        // App\Models\Representative => representative
        // App\Models\Teacher => teacher
        $entityString = match ($phone->entity_type) {
            User::class => 'admin',
            default => strtolower(class_basename($phone->entity_type))
        };

        $token = implode('|', [$entityString, $hash]);
        Redis::set(
            self::cacheKey($token),
            $phone->id,
            'EX',
            self::getDuration($phone)
        );

        return $token;
    }

    public static function cacheKey(string $token)
    {
        return implode(':', ['session', $token]);
    }

    public static function getDuration(Phone $phone): int
    {
        $hour = 60 * 60;

        return match ($phone->entity_type) {
            Client::class, Representative::class => $hour * 24 * 30,
            Teacher::class, User::class => $hour * 3
        };
    }

    public static function logout(string $token)
    {
        Redis::del(self::cacheKey($token));
    }

    public static function get(?string $token): ?Authenticatable
    {
        if ($token === null) {
            return null;
        }
        $phoneId = Redis::get(self::cacheKey($token));
        if (! $phoneId) {
            return null;
        }
        $phone = Phone::find($phoneId);
        if ($phone === null) {
            return null;
        }
        Redis::expire(self::cacheKey($token), self::getDuration($phone));

        $user = $phone->entity;

        // хак, чтобы можно было получить реальный номер телефона, под которым авторизован пользователь
        $user->phone = $phone;

        return $user;
    }
}
