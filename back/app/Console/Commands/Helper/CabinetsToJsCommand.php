<?php

namespace App\Console\Commands\Helper;

use App\Enums\Cabinet;
use Illuminate\Console\Command;

class CabinetsToJsCommand extends Command
{
    protected $signature = 'app:cabinets-to-js';

    protected $description = 'Файл с кабинетами для фронта Cabinet/index.js';

    public function handle(): void
    {
        $this->info(view('other.cabinets-to-js', [
            'cabinets' => Cabinet::cases(),
        ]));
    }
}
