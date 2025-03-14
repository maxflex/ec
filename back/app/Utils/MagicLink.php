<?php

namespace App\Utils;

use App\Models\Phone;

class MagicLink
{
    public static function create(Phone $phone): string
    {
        $key = uniqid();
        cache([$key => $phone->id], now()->addHours(24));

        return $key;
    }

    public static function get(string $key): ?Phone
    {
        $phoneId = cache()->pull($key);
        // $phoneId = cache($key);

        if (! $phoneId) {
            return null;
        }

        return Phone::find($phoneId);
    }
}
