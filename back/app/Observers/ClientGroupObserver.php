<?php

namespace App\Observers;

use App\Jobs\UpdateScheduleJob;
use App\Models\ClientGroup;

class ClientGroupObserver
{
    public function created(ClientGroup $clientGroup): void
    {
        $this->updateComputed($clientGroup);
    }

    private function updateComputed(ClientGroup $clientGroup): void
    {
        $client = $clientGroup->contractVersionProgram->contractVersion->contract->client;
        UpdateScheduleJob::dispatch($client, $clientGroup->group->year);
    }

    public function deleted(ClientGroup $clientGroup): void
    {
        $this->updateComputed($clientGroup);
    }

    public function updated(ClientGroup $clientGroup): void
    {
        $this->updateComputed($clientGroup);
    }
}
