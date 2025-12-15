<?php

use App\Enums\Company;

return [
    Company::ip->value => [
        'base_url' => env('ATOL_IP_BASE_URL'),
        'login' => env('ATOL_IP_LOGIN'),
        'pass' => env('ATOL_IP_PASS'),
        'group_code' => env('ATOL_IP_GROUP_CODE'),
        'sno' => env('ATOL_IP_SNO'),
        'vat' => env('ATOL_IP_VAT'),
        'inn' => env('ATOL_IP_INN'),
    ],
];
