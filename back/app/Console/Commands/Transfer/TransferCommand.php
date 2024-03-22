<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TransferCommand extends Command
{
    protected $signature = 'app:transfer';
    protected $description = 'Transfer all';

    // https://laravel.com/docs/10.x/eloquent-relationships#many-to-many
    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        foreach ([
            'users',
            'clients',
            'requests',
            'teachers',
            'phones',
            'contracts',
            'groups',
            'contract-group',
            'teacher-payments',
            'teacher-services',
        ] as $command) {
            $this->info(str($command)->ucfirst());
            $this->call("app:transfer:$command");
            $this->line(PHP_EOL);
        }
        Schema::enableForeignKeyConstraints();
    }
}
