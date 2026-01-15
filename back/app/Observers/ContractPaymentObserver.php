<?php

namespace App\Observers;

use App\Enums\ContractPaymentMethod;
use App\Models\ContractPayment;
use App\Utils\OneC;
use App\Utils\Receipt\Receipt;

class ContractPaymentObserver
{
    public function saved(ContractPayment $contractPayment): void
    {
        // Синхронизация с 1С (при создании или при изменении + установка)
        if (($contractPayment->wasChanged('is_1c_synced') || $contractPayment->wasRecentlyCreated)
            && $contractPayment->is_1c_synced) {
            new OneC($contractPayment)->sync();
        }
    }

    /**
     * Отправляет чек при сохранении при установленном receipt_number
     */
    public function created(ContractPayment $payment): void
    {
        // отправить чек
        if ($payment->receipt_number) {
            $receiptData = $payment->toReceipt();

            // СБП Онлайн: чеки уходят автоматически через ЮКасса
            if ($payment->method !== ContractPaymentMethod::sbpOnline) {
                new Receipt($receiptData)->send();
            }

            // сохраняем IP, c которого будет уходить чек
            $payment->receipt_ip = config('receipt.'.$receiptData->company->value.'.ip');
            $payment->saveQuietly();
        }
    }
}
