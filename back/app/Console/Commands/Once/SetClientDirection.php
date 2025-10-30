<?php

namespace App\Console\Commands\Once;

use App\Models\ContractVersion;
use App\Models\ContractVersionProgram;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetClientDirection extends Command
{
    protected $signature = 'once:set-client-direction';

    protected $description = 'Create TSV with campus load per timeslot based on client lessons';

    public function handle(): void
    {
        $items = DB::table('contracts')
            ->selectRaw(' year, client_id ')
            ->groupByRaw('year, client_id')
            ->get();

        $result = [
            ['client_id', 'year', 'directions', 'url'],
        ];

        $bar = $this->output->createProgressBar(count($items));
        foreach ($items as $item) {
            // first contract version
            $firstContractVersion = ContractVersion::query()
                ->whereHas('contract',
                    fn ($q) => $q->where([
                        'year' => $item->year,
                        'client_id' => $item->client_id,
                    ]))
                ->orderBy('created_at', 'asc')
                ->first();

            $result[] = [
                $item->client_id,
                $item->year,
                $firstContractVersion->programs->map(fn (ContractVersionProgram $p) => $p->program->getDirection()->value)->sort()->join(','),
                "https://lk.ege-centr.ru/clients/{$item->client_id}?contract_id={$firstContractVersion->contract_id}",
            ];

            $bar->advance();
        }

        $bar->finish();
        $url = save_csv($result);

        $this->line(PHP_EOL);
        $this->line($url);
    }
}
