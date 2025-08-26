<?php

namespace App\Models;

use App\Enums\Company;
use App\Enums\OtherPaymentMethod;
use App\Traits\HasName;
use App\Utils\AllPayments;
use Illuminate\Database\Eloquent\Model;

/**
 * Фактически эти платежи сейчас только для пробных ЕГЭ
 */
class OtherPayment extends Model
{
    use HasName;

    protected $fillable = [
        'sum', 'date', 'method', 'card_number', 'pko_number', 'is_confirmed',
        'is_return', 'first_name', 'last_name', 'middle_name', 'purpose',
    ];

    protected $casts = [
        'method' => OtherPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
    ];

    public static function booted()
    {
        static::creating(function ($payment) {
            if (
                $payment->method === OtherPaymentMethod::cash
                && ! $payment->is_return
            ) {
                $payment->pko_number = get_max_pko_number(
                    Company::ooo, // пробники всегда ООО
                    $payment->date,
                );
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * После создания платежа, он должен падать в список к all-payments
     */
    public function formatForAllPayments()
    {
        return AllPayments::query()
            ->where('id', $this->id)
            ->whereNull('contract_id')
            ->first();
    }

    public function setCardNumberAttribute($value)
    {
        $this->attributes['card_number'] = $value ? preg_replace('/\D/', '', $value) : null;
    }
}
