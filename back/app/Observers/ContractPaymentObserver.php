<?php

namespace App\Observers;

use App\Models\ContractPayment;
use App\Utils\OneC;

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
}
