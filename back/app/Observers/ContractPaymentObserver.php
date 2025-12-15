<?php

namespace App\Observers;

use App\Models\ContractPayment;
use App\Utils\OneC;
use App\Utils\Receipt;

class ContractPaymentObserver
{
    public function saved(ContractPayment $contractPayment): void
    {
        if ($contractPayment->wasChanged('is_1c_synced') && $contractPayment->is_1c_synced) {
            new OneC($contractPayment)->sync();
        }
        if ($contractPayment->wasChanged('receipt_sent_to') && $contractPayment->receipt_sent_to) {
            new Receipt($contractPayment)->send();
        }
    }
}
