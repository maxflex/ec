<?php

namespace App\Models;

use App\Enums\ClientPaymentMethod;
use App\Enums\Company;
use Illuminate\Database\Eloquent\Model;

class ClientPayment extends Model
{
    protected $fillable = [
        'sum', 'date', 'purpose', 'company', 'method', 'year',
        'client_id', 'card_number', 'pko_number', 'is_confirmed', 'is_return'
    ];

    protected $casts = [
        'company' => Company::class,
        'method' => ClientPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public static function booted()
    {
        static::creating(function ($payment) {
            if (
                $payment->method === ClientPaymentMethod::cash
                && !$payment->is_return
            ) {
                $payment->pko_number = get_max_pko_number(
                    $payment->company,
                    $payment->date,
                );
            }
        });
    }
}
