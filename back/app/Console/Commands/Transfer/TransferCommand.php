<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;

class TransferCommand extends Command
{
    protected $signature = 'app:transfer';
    protected $description = 'Transfer all';

    public function handle()
    {
        foreach ([
            'teachers',
            'phones',
            'users',
        ] as $command) {
            $this->info(str($command)->ucfirst());
            $this->call("app:transfer:$command");
            $this->line(PHP_EOL);
        }
    }
}
