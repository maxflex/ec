<?php

namespace App\Models;

use App\Enums\ClientPaymentMethod;
use App\Enums\CompanyType;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $casts = [
        'company' => CompanyType::class,
        'method' => ClientPaymentMethod::class,
        'year' => 'integer',
    ];
}
