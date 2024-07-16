<?php

namespace App\Models;

use App\Enums\ClientPaymentMethod;
use Illuminate\Database\Eloquent\Model;

class ContractPayment extends Model
{
    protected $fillable = [
        'contract_id', 'sum', 'date', 'is_confirmed', 'is_return',
        'card_number', 'pko_number', 'method'
    ];

    protected $casts = [
        'method' => ClientPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public static function booted()
    {
        static::creating(function ($payment) {
            if ($payment->method === ClientPaymentMethod::cash) {
                $payment->pko_number = get_max_pko_number($payment->contract->company);
            }
        });
    }
}
