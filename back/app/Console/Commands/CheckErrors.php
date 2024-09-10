<?php

namespace App\Console\Commands;

use App\Models\Error;
use Illuminate\Console\Command;

class CheckErrors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-errors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Пересчитать все ошибки';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Error::check();
    }
}
