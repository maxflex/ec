<?php

namespace App\Models;

use App\Enums\ContractPaymentMethod;
use App\Observers\ContractPaymentObserver;
use App\Observers\UserIdObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([UserIdObserver::class, ContractPaymentObserver::class])]
class ContractPayment extends Model
{
    protected $fillable = [
        'contract_id', 'sum', 'date', 'is_confirmed', 'is_return',
        'card_number', 'pko_number', 'method', 'external_id',
        'is_1c_synced', 'receipt_sent_to',
    ];

    protected $casts = [
        'method' => ContractPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
        'is_1c_synced' => 'boolean',
    ];

    public static function booted()
    {
        static::creating(function ($payment) {
            if (
                $payment->method === ContractPaymentMethod::cash
                && ! $payment->is_return
            ) {
                $payment->pko_number = get_max_pko_number(
                    $payment->contract->company,
                    $payment->date,
                );
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Contract>
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function getRealSumAttribute(): int
    {
        return $this->is_return ? $this->sum * -1 : $this->sum;
    }

    public function setCardNumberAttribute($value)
    {
        $this->attributes['card_number'] = $value ? preg_replace('/\D/', '', $value) : null;
    }
}
