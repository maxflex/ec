<?php

namespace App\Observers;

use App\Enums\Company;
use App\Models\ContractPayment;
use App\Utils\OneC;
use App\Utils\Receipt;

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

    public function creating(ContractPayment $contractPayment): void
    {
        // отправить чек
        if ($contractPayment->receipt_number) {
            new Receipt($contractPayment)->send();

            // сохраняем IP, c которого будет уходить чек
            $contractPayment->receipt_ip = $contractPayment->contract->company === Company::ano
                ? config('receipt.ano.proxy')
                : $_SERVER['REMOTE_ADDR'];
        }
    }
}
