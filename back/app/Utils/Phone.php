<?php

namespace App\Utils;

use ValueError;

class Phone
{
    /**
     * @param $clean – очищать номер перед валидацией
     * @param $mobile – валидация только мобильных
     */
    public static function validate(string $number, bool $clean = true, bool $mobile = false): bool
    {
        $pattern = $mobile ? "/^79[0-9]{9}$/" : "/^7[0,4,9][0-9]{9}$/";

        return preg_match($pattern, $clean ? self::clean($number) : $number);
    }

    /**
     * +7 (900) 111-22-33 => 79001112233
     */
    public static function clean($phone): string
    {
        return preg_replace("/[^0-9]/", "", (string) $phone);
    }

    /**
     * Если номер начинается с 8 – значит, меняем на 7
     * Если номер НЕ начинается с 7 - значит, подставляем 7
     */
    public static function autoCorrectFirstDigit($phone): string
    {
        $phone = self::clean($phone);

        if ($phone[0] === '8') {
            $phone[0] = '7';
        }

        if ($phone[0] !== '7') {
            $phone = '7' . $phone;
        }

        return $phone;
    }

    /**
     * Взять последние 10 цифр номера
     */
    public static function lastDigits($phone): string
    {
        $phone = self::clean($phone);
        return '7' . substr($phone, -10);
    }

    /**
     * 79001112233 => +7 (900) 111-22-33
     */
    public static function format($number)
    {
        if ($number[0] == '+') {
            return $number;
        }

        $cuts = [1, 3, 3, 2, 2];

        $i = 0;
        $parts = [];
        foreach ($cuts as $cut) {
            try {
                $parts[] = mb_strimwidth($number, $i, $cut);
            } catch (ValueError $e) {
                return $number;
            }
            $i += $cut;
        }
        return "+$parts[0] ({$parts[1]}) {$parts[2]}-{$parts[3]}-{$parts[4]}";
    }
}
