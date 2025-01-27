<?php

namespace App\Console\Commands\Once;

use App\Models\ClientLesson;
use App\Models\Contract;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Task899Command extends Command
{
    protected $signature = 'once:task899';

    protected $description = 'Command description';

    public function handle(): void
    {
        $this->step1();
    }

    private function step1()
    {
        $contracts = Contract::where('year', 2024)->get();

        $bar = $this->output->createProgressBar(count($contracts));

        $csv = collect([collect([
            'client_id',
            'contract_id',
            'version_id',
            'program_id',
            'price_id',
            'занятий из prices',
            'проведено занятий',
            'цена занятия из prices',
            'реальные цены проведённых занятий'
        ])]);

        foreach ($contracts as $contract) {
            foreach ($contract->active_version->programs as $program) {
                $lessons = ClientLesson::query()
                    ->where('contract_version_program_id', $program->id)
                    ->join('lessons as l', 'l.id', 'lesson_id')
                    ->orderByRaw('l.date asc, l.time asc')
                    ->selectRaw("
                        client_lessons.*,
                        l.date,
                        l.time
                    ")
                    ->get();
//                dd($lessons->map(fn($item) => $item->toArray()), $program->id);
                $skip = 0;
                foreach ($program->prices as $price) {
                    $priceLessons = $lessons->skip($skip)->take($price->lessons);
                    $item = collect([
                        $contract->client_id,
                        $contract->id,
                        $contract->active_version->id,
                        $program->id,
                        $price->id,
                        $price->lessons,
                        $priceLessons->count(),
                        $price->price,
                        $priceLessons->pluck('price')->unique()->join(', ')
                    ]);
                    $csv->push($item);
                    $skip += $price->lessons;
                }
            }
            $bar->advance();
        }

        $bar->finish();

        $file = $csv
            ->map(fn($arr) => $arr->join("\t"))
            ->join("\n");

        $filename = uniqid() . '.csv';
        Storage::put("crm/other/$filename", $file);
        $this->line("\n" . cdn('other', $filename));
    }
}
