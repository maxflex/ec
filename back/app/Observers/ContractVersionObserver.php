<?php

namespace App\Observers;

use App\Models\ContractVersion;

class ContractVersionObserver
{
    public function created(ContractVersion $contractVersion): void
    {
        // установить активной последнюю
        $contractVersion->setActiveVersion();
    }

    public function deleting(ContractVersion $contractVersion): void
    {
        $contractVersion->payments->each->delete();
    }

    public function deleted(ContractVersion $contractVersion)
    {
        // удалили последнюю существующую версию
        if ($contractVersion->chain()->count() === 0) {
            $contractVersion->contract->payments->each->delete();
            $contractVersion->contract->delete();
        } elseif ($contractVersion->is_active) {
            // удалили активную версию, переустанавливаем is_active
            $contractVersion->setActiveVersion();
        }
    }
}
