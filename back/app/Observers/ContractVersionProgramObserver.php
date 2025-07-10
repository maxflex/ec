<?php

namespace App\Observers;

use App\Enums\SwampStatus;
use App\Models\ContractVersionProgram;

class ContractVersionProgramObserver
{
    public function creating(ContractVersionProgram $cvp): void
    {
        $cvp->status = SwampStatus::toFulfil;
    }

    public function deleting(ContractVersionProgram $cvp): void
    {
        $cvp->prices->each->delete();
    }
}
