<?php

return [
    'guards' => [
        // custom session driver
        'crm' => [
            'driver' => 'redis-session',
            'provider' => 'phone-user-provider',
        ],
    ],

    'providers' => [
        'phone-user-provider' => [
            'driver' => 'phone-user-provider',  // Reference to the custom driver
            'model' => App\Models\Phone::class, // This remains as Phone
        ],
    ],
];
