<?php

namespace App\Utils;

use Illuminate\Support\Facades\{Redis, Hash};
use App\Models\Phone;

class Session
{
    public static function createToken(Phone $phone): string
    {
        $token = Hash::make($phone->number);
        $ttl = 60 * 60 * 3; // 3 hours
        Redis::set(self::cacheKey($token), $phone->id, 'EX', $ttl);
        return $token;
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
        return Phone::find($phoneId);
    }

    public static function cacheKey(string $token)
    {
        return join(':', ['session', $token]);
    }
}
