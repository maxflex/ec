<?php

namespace App\Enums;

enum PaymentType: string
{
    case payment = 'payment';
    case return = 'return';
}
