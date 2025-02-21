<?php

namespace App\Console\Commands;

use App\Events\AppUpdatedEvent;
use Illuminate\Console\Command;

class AppUpdatedCommand extends Command
{
    protected $signature = 'app:app-updated';

    protected $description = 'App updated signal';

    public function handle(): void
    {
        AppUpdatedEvent::dispatch([
            // TODO: clear filters1
            'filters-strings',
        ]);
    }
}
