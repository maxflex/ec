<?php

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;

class SetReportDeliveryCommand extends Command
{
    protected $signature = 'once:set-report-delivery';

    protected $description = 'Проставить Report delivery';

    public function handle(): void {}
}
