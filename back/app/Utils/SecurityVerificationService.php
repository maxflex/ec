<?php

namespace App\Utils;

use App\Facades\Telegram;
use Illuminate\Support\Facades\Redis;

/**
 * Отправка кодов подтверждения для Security
 */
class SecurityVerificationService
{
    const CACHE_KEY = 'security-code';

    public static function sendCode()
    {
        // если код уже отправлен – ничего не делаем
        if (Redis::get(self::CACHE_KEY) !== null) {
            return;
        }
        $code = self::generateCode();
        self::storeCode($code);
        // 5808892115 акк охраны
        $chatIds = is_localhost() ? [84626120] : [5808892115, 84626120, 1254789772];
        foreach ($chatIds as $chatId) {
            Telegram::sendMessage($chatId, "*{$code}* – охрана", 'MarkdownV2');
        }
    }

    public static function verifyCode($code)
    {
        $verified = (string)$code === Redis::get(self::CACHE_KEY);
        if ($verified) {
            Redis::del(self::CACHE_KEY);
            return true;
        }
        return false;
    }

    public static function generateCode()
    {
        return mt_rand(1000000, 9999999);
    }

    public static function storeCode($code)
    {
        Redis::set(self::CACHE_KEY, $code, 'EX', 60 * 5);
    }
}
