<?php

namespace App\Observers;

use App\Models\ClientGroup;

class ClientGroupObserver
{
    public function created(ClientGroup $clientGroup): void
    {
        $this->updateComputed($clientGroup);
    }

    private function updateComputed(ClientGroup $clientGroup): void
    {
        $clientGroup->contractVersionProgram->contractVersion->contract->client->updateSchedule(
            $clientGroup->group->year
        );
    }

    public function deleted(ClientGroup $clientGroup): void
    {
        $this->updateComputed($clientGroup);
    }
}
