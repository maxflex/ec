<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\ClientParent;
use App\Models\Request;
use App\Models\Teacher;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class ScoutReimportAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:reimport-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reimport all searchable models';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = [
            Request::class,
            Teacher::class,
            Client::class,
            ClientParent::class,
        ];
        // Это снесёт весь persons индекс, т.к. у всех он одинаковый
        Artisan::call('scout:flush', [
            'model' => Client::class
        ]);
        $this->info('Index has been flushed');
        foreach ($models as $model) {
            $this->line(PHP_EOL);
            $this->info('Importing ' . $model);
            Artisan::call('scout:import', ['model' => $model]);
            $this->line(Artisan::output());
        }
    }
}
