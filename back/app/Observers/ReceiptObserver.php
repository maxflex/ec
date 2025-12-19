<?php

namespace App\Observers;

use App\Models\ContractPayment;
use App\Utils\Receipt\Receipt;

/**
 * Отправляет чек при сохранении при установленном receipt_number
 */
class ReceiptObserver
{
    public function created(ContractPayment $payment): void
    {
        // отправить чек
        if ($payment->receipt_number) {
            $receiptData = $payment->toReceipt();

            new Receipt($receiptData)->send();

            // сохраняем IP, c которого будет уходить чек
            $payment->receipt_ip = config('receipt.'.$receiptData->company->value.'.ip');
            $payment->saveQuietly();
        }
    }
}
