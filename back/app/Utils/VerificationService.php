<?php

namespace App\Utils;

use Illuminate\Support\Facades\Redis;
use App\Facades\Telegram;
use App\Models\Phone;

class VerificationService
{
    const CACHE_KEY = 'v3-code';

    public static function sendCode(Phone $phone)
    {
        // если код уже отправлен – ничо не делаем
        // if (Redis::get(self::CACHE_KEY) !== null) {
        //     return;
        // }
        $code = self::generateCode();
        self::storeCode($code);
        Telegram::sendMessage($phone->telegram_id, "*{$code}* – код для авторизации в ЛК", 'MarkdownV2');
    }

    public static function verifyCode($code)
    {
        $verified = (string) $code === Redis::get(self::CACHE_KEY);
        if ($verified) {
            Redis::del(self::CACHE_KEY);
            return true;
        }
        return false;
    }

    public static function generateCode()
    {
        return mt_rand(10000, 99999);
    }

    public static function storeCode($code)
    {
        Redis::set(self::CACHE_KEY, $code, 'EX', 60 * 5);
    }
}
