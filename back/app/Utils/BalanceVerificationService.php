<?php

namespace App\Utils;

use App\Facades\Telegram;
use App\Models\Teacher;
use Illuminate\Support\Facades\Redis;

class BalanceVerificationService
{
    public static function sendCode(Teacher $teacher)
    {
        $code = self::generateCode();
        self::storeCode($teacher, $code);
        if (is_localhost()) {
            Telegram::sendMessage(980106803, "*$code* – код для просмотра страницы", 'MarkdownV2');
            return;
        }
        $teacher->phones()->withTelegram()->get()->each(
            fn($phone) => Telegram::sendMessage($phone->telegram_id, "*$code* – код для просмотра страницы", 'MarkdownV2')
        );
    }

    public static function verifyCode(Teacher $teacher, $code)
    {
        $verified = (string)$code === Redis::get(self::cacheKeyCode($teacher));
        if ($verified) {
            Redis::del(self::cacheKeyCode($teacher));
            Redis::set(self::cacheKey($teacher), 1, 'EX', 60 * 5);
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

    public static function storeCode(Teacher $teacher, $code)
    {
        Redis::set(self::cacheKeyCode($teacher), $code, 'EX', 60 * 5);
    }

    public static function check(Teacher $teacher)
    {
        return Redis::ttl(self::cacheKey($teacher));
    }

    public static function cacheKeyCode(Teacher $teacher)
    {
        return join(':', ['balance-verification-code', $teacher->id]);
    }

    public static function cacheKey(Teacher $teacher)
    {
        return join(':', ['balance-verification', $teacher->id]);
    }
}
