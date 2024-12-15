<?php

namespace App\Utils;

use App\Models\Phone;
use Illuminate\Support\Facades\Http;

class Sms
{
    public static function send(Phone $phone, string $message)
    {
        $url = config('sms.host') . 'send.php';

        $response = Http::asForm()->post($url, [
            'login' => config('sms.login'),
            'psw' => config('sms.psw'),
            'sender' => config('sms.sender'),
            'charset' => 'utf-8',
            "fmt" => 3, // JSON
            "phones" => $phone->number,
            "mes" => $message,
            "tinyurl" => 1,
        ]);

        return $response->json();
    }
}