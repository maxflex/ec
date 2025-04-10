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

        return Http::get(config('sms.host'), $params)->json();
    }
}
