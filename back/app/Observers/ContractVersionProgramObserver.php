<?php

namespace App\Observers;

use App\Enums\CvpStatus;
use App\Models\ContractVersionProgram;

class ContractVersionProgramObserver
{
    public function creating(ContractVersionProgram $cvp): void
    {
        $cvp->status = CvpStatus::active;
    }

    public function deleting(ContractVersionProgram $cvp): void
    {
        $cvp->prices->each->delete();
    }
}
