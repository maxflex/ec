<?php

use App\Enums\Company;

return [
    Company::ip->value => [
        'base_url' => env('RECEIPT_IP_BASE_URL'),
        'login' => env('RECEIPT_IP_LOGIN'),
        'pass' => env('RECEIPT_IP_PASS'),
        'group_code' => env('RECEIPT_IP_GROUP_CODE'),
        'sno' => env('RECEIPT_IP_SNO'),
        'vat' => env('RECEIPT_IP_VAT'),
        'inn' => env('RECEIPT_IP_INN'),
        'ip' => env('RECEIPT_IP_IP'),
    ],
    Company::ano->value => [
        'base_url' => env('RECEIPT_ANO_BASE_URL'),
        'login' => env('RECEIPT_ANO_LOGIN'),
        'pass' => env('RECEIPT_ANO_PASS'),
        'group_code' => env('RECEIPT_ANO_GROUP_CODE'),
        'sno' => env('RECEIPT_ANO_SNO'),
        'vat' => env('RECEIPT_ANO_VAT'),
        'inn' => env('RECEIPT_ANO_INN'),
        'ip' => env('RECEIPT_ANO_IP'),
    ],
];
