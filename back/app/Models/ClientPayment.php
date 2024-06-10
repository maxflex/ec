<?php

namespace App\Models;

use App\Enums\ClientPaymentMethod;
use App\Enums\CompanyType;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $fillable = [
        'entity_id', 'entity_type', 'sum', 'date', 'is_confirmed', 'is_return',
        'purpose', 'extra', 'company', 'method', 'year'
    ];

    protected $casts = [
        'company' => CompanyType::class,
        'method' => ClientPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
