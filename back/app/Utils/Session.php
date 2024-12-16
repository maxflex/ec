<?php

namespace App\Utils;

use App\Models\{Client, ClientParent, Phone, Teacher, User};
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\{Hash, Redis};

class Session
{
    /**
     * Create new session for Phone (log in)
     *
     * @param Phone $phone
     * @return string Session token
     */
    public static function logIn(Phone $phone): string
    {
        // Вход родителя приравниваем ко входу ученика
        if ($phone->entity_type === ClientParent::class) {
            $phone = $phone->entity->client->phones()->first();
        }

        $hash = Hash::make($phone->number);
        // App\Models\User => admin
        // App\Models\Client = client
        // App\Models\Teacher = teacher
        $entityString = match ($phone->entity_type) {
            User::class => 'admin',
            default => strtolower(class_basename($phone->entity_type))
        };
        $token = join("|", [$entityString, $hash]);
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
            ClientParent::class => $hour * 24 * 365,
            Teacher::class => $hour * 3,
            User::class => $hour * 3
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
        if (!$phoneId) {
            return null;
        }
        $phone = Phone::find($phoneId);
        if ($phone === null) {
            return null;
        }
        Redis::expire(self::cacheKey($token), self::getDuration($phone));
        return $phone->entity;
    }

    public static function cacheKey(string $token)
    {
        return join(':', ['session', $token]);
    }
}
