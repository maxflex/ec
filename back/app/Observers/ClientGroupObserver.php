<?php

namespace App\Observers;

use App\Models\ClientGroup;

class ClientGroupObserver
{
    public function created(ClientGroup $clientGroup): void
    {
        $clientGroup->contractVersionProgram->updateStatus();
    }

    public function deleted(ClientGroup $clientGroup): void
    {
        $clientGroup->contractVersionProgram->updateStatus();
    }
}
