<?php

namespace App\Console\Commands\Once;

use App\Models\Contract;
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
            ['client_id', 'year', 'directions_separate', 'directions_merge', 'contract_ids'],
        ];

        $bar = $this->output->createProgressBar(count($items));
        foreach ($items as $item) {
            $contracts = Contract::where([
                'year' => $item->year,
                'client_id' => $item->client_id,
            ])->get();

            $directions = collect();

            foreach ($contracts as $contract) {
                foreach ($contract->first_version->programs as $program) {
                    $directions->push($program->program->getDirection()->value);
                }
            }

            $result[] = [
                $item->client_id,
                $item->year,
                $directions->sort()->join(','),
                $directions->unique()->sort()->join(','),
                $contracts->map(fn (Contract $c) => $c->id)->join(','),
            ];

            $bar->advance();
        }

        $bar->finish();
        $url = save_csv($result);

        $this->line(PHP_EOL);
        $this->line($url);
    }
}
