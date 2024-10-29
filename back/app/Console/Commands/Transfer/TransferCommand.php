<?php

namespace App\Console\Commands\Transfer;

use App\Utils\MigrationError;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class TransferCommand extends Command
{
    protected $signature = 'app:transfer';
    protected $description = 'Transfer all';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
        MigrationError::table()->truncate();
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
                     'client-lessons',  // долго
                     'reports',
                     'head-teacher-reports',
                     'reviews',
                     'comments',
                     'passes',
                     'payment-additionals',
                     'vacations',
        ] as $command) {
            $this->info(str($command)->ucfirst());
            $this->call("app:transfer:$command");
            $this->line(PHP_EOL);
        }
        Schema::enableForeignKeyConstraints();
    }
}
