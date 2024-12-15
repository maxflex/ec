<?php

namespace App\Utils;

use App\Facades\Telegram;
use App\Models\Phone;
use Illuminate\Support\Facades\Redis;

class VerificationService
{
    public static function sendCode(Phone $phone, bool $sendBySms = false)
    {
        // если код уже отправлен – ничего не делаем
        // if (Redis::get(self::cacheKey($phone)) !== null) {
        //     return;
        // }
        $code = self::generateCode();
        self::storeCode($phone, $code);
        if (is_localhost()) {
            return;
        }
        $sendBySms
            ? Sms::send($phone, "Код подтверждения: $code")
            : Telegram::sendMessage($phone->telegram_id, "*$code* – код для авторизации", 'MarkdownV2');
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
