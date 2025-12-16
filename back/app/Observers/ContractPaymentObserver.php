<?php

namespace App\Observers;

use App\Models\ContractPayment;
use App\Utils\OneC;
use App\Utils\Receipt;

class ContractPaymentObserver
{
    public function saved(ContractPayment $contractPayment): void
    {
        // при создании или при изменении + установка
        if (($contractPayment->wasChanged('is_1c_synced') || $contractPayment->wasRecentlyCreated)
            && $contractPayment->is_1c_synced) {
            new OneC($contractPayment)->sync();
        }
        // при создании или при изменении + установка
        if (($contractPayment->wasChanged('receipt_sent_to') || $contractPayment->wasRecentlyCreated)
            && $contractPayment->receipt_sent_to) {
            new Receipt($contractPayment)->send();
        }
    }
}
