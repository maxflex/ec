<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TransferCommand extends Command
{
    protected $signature = 'app:transfer';
    protected $description = 'Transfer all';

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
                     'client-groups',
                     'teacher-payments',
                     'teacher-services',
                     'client-payments',
                     'lessons',
                     'client-lessons', // самое долгое
                     'reviews',
                     'comments',
                     'passes'
        ] as $command) {
            $this->info(str($command)->ucfirst());
            $this->call("app:transfer:$command");
            $this->line(PHP_EOL);
        }
        Schema::enableForeignKeyConstraints();
    }
}
