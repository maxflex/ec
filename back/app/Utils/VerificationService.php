<?php

namespace App\Utils;

use Illuminate\Support\Facades\Redis;
use App\Facades\Telegram;
use App\Models\Phone;

class VerificationService
{
    public static function sendCode(Phone $phone)
    {
        // если код уже отправлен – ничо не делаем
        // if (Redis::get(self::cacheKey($phone)) !== null) {
        //     return;
        // }
        $code = self::generateCode();
        self::storeCode($phone, $code);
        if (!is_localhost()) {
            Telegram::sendMessage($phone->telegram_id, "*{$code}* – код для авторизации в ЛК", 'MarkdownV2');
        }
    }

    public static function verifyCode(Phone $phone, $code)
    {
        $verified = (string) $code === Redis::get(self::cacheKey($phone));
        if ($verified) {
            Redis::del(self::cacheKey($phone));
            return true;
        }
        return false;
    }

    public static function generateCode()
    {
        if (is_localhost()) {
            return 1111;
        }
        return mt_rand(1000, 9999);
    }

    public static function storeCode(Phone $phone, $code)
    {
        Redis::set(self::cacheKey($phone), $code, 'EX', 60 * 5);
    }

    public static function cacheKey(Phone $phone)
    {
        return join(':', ['code', $phone->number]);
    }
}
