<?php

namespace App\Utils;

use App\Models\Phone;
use Illuminate\Support\Facades\Http;

class Sms
{
    public static function send(Phone $phone, string $message)
    {
        if (is_localhost()) {
            logger('SMS', [
                'number' => $phone->number,
                'message' => $message,
            ]);

            return true;
        }

        // чтобы различать, что сообщение из EC
        // https://smsc.ru/api/http/send/smsinfo/#menu
        $message .= "\n~~~\nEC";

        $params = [
            'login' => config('sms.login'),
            'psw' => config('sms.psw'),
            'sender' => config('sms.sender'),
            'tinyurl' => 1,
            'fmt' => 3,
            'charset' => 'utf-8',
            'phones' => $phone->number,
            'mes' => $message,
        ];

        return Http::get(config('sms.host').'/send.php', $params)->json();
    }

    public static function history(array $params = [])
    {
        $url = config('sms.host').'/get.php';

        $params = [
            ...$params,
            'login' => config('sms.login'),
            'psw' => config('sms.psw'),
            'format' => 0,
            'fmt' => 3,
            'cnt' => 1000,
            'charset' => 'utf-8',
            'get_messages' => 1,
            'end' => now()->format('d.m.Y'),
        ];

        return Http::get($url, $params)->json();
    }
}
