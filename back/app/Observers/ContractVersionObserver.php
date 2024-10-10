<?php

namespace App\Observers;

use App\Models\ContractVersion;
use App\Models\ContractVersionProgram;

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
        $contractVersion->programs->each(function (ContractVersionProgram $cvp) {
            $cvp->prices->each->delete();
            $cvp->delete();
        });
    }

    public function deleted(ContractVersion $contractVersion)
    {
        // удалили последнюю существующую версию
        if ($contractVersion->chain()->count() === 0) {
            $contractVersion->contract->delete();
        } else if ($contractVersion->is_active) {
            // удалили активную версию, переустанавливаем is_active
            $contractVersion->setActiveVersion();
        }
    }
}
