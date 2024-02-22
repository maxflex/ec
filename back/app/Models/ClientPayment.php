<?php

namespace App\Models;

use App\Enums\ClientPaymentMethod;
use App\Enums\CompanyType;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $casts = [
        'company' => CompanyType::class,
        'type' => PaymentType::class,
        'method' => ClientPaymentMethod::class,
        'year' => 'integer',
    ];
}
