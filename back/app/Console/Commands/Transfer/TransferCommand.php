<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;

class TransferCommand extends Command
{
    protected $signature = 'app:transfer';
    protected $description = 'Transfer all';

    public function handle()
    {
//        Schema::disableForeignKeyConstraints();
//        MigrationError::table()->truncate();
//        foreach ([
//                     'users',
//                     'clients',
//                     'requests',
//                     'teachers',
//                     'phones',
//                     'contracts',
//                     'groups',
//                     'client-groups',
//                     'teacher-payments',
//                     'teacher-services',
//                     'client-payments',
//                     'lessons',
//                     'client-lessons',  // долго
//                     'reports',
//                     'head-teacher-reports',
//                     'reviews',
//                     'payment-additionals',
//                     'comments',
//                     'passes',
//                     'vacations',
//                     'grades',
//                     'group-acts',
//        ] as $command) {
//            $this->info(str($command)->ucfirst());
//            $this->call("app:transfer:$command");
//            $this->line(PHP_EOL);
//        }
//        Schema::enableForeignKeyConstraints();
    }
}
