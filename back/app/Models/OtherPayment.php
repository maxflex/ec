<?php

namespace App\Models;

use App\Enums\Company;
use App\Enums\OtherPaymentMethod;
use App\Observers\ReceiptObserver;
use App\Observers\UserIdObserver;
use App\Traits\HasName;
use App\Utils\AllPayments;
use App\Utils\Receipt\ReceiptData;
use App\Utils\Receipt\ReceiptInterface;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;

/**
 * Фактически эти платежи сейчас только для пробных ЕГЭ
 */
#[ObservedBy([UserIdObserver::class, ReceiptObserver::class])]
class OtherPayment extends Model implements ReceiptInterface
{
    use HasName;

    protected $fillable = [
        'sum', 'date', 'method', 'card_number', 'pko_number', 'is_confirmed',
        'is_return', 'first_name', 'last_name', 'middle_name', 'purpose',
        'receipt_number',
    ];

    protected $casts = [
        'method' => OtherPaymentMethod::class,
        'is_confirmed' => 'boolean',
        'is_return' => 'boolean',
    ];

    public static function booted()
    {
        static::creating(function (OtherPayment $payment) {
            if (
                $payment->method === OtherPaymentMethod::cash
                && ! $payment->is_return
            ) {
                $payment->pko_number = get_max_pko_number(
                    $payment->company,
                    $payment->date,
                );
            }
        });
    }

    /**
     * Пробники всегда АНО
     */
    public function getCompanyAttribute(): Company
    {
        return Company::ano;
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

    public function toReceipt(): ReceiptData
    {
        return new ReceiptData(
            $this,
            $this->purpose,
            $this->formatName('full'),
            $this->company,
        );
    }
}
