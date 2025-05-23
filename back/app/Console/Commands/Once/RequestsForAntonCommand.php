<?php

namespace App\Console\Commands\Once;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Storage;

class RequestsForAntonCommand extends Command
{
    protected $signature = 'once:requests-for-anton';

    protected $description = 'Command description';

    public function handle(): void
    {
        $periods = [
            ['2024-01-01', '2024-05-23'],
            ['2025-01-01', '2025-05-23'],
        ];

        foreach ($periods as $i => $period) {
            $result = [[
                'договор', 'клиент', 'откуда узнали', 'заявка', 'источник', 'направление',
            ]];
            $contracts = DB::table('contracts', 'c')
                ->join('contract_versions as cv', 'cv.contract_id', '=', 'c.id')
                ->whereRaw('
                    c.id = (
                        select min(c2.id) from contracts as c2
                        where c2.client_id = c.client_id
                    )
                    and cv.id = (
                        select min(cv2.id) from contract_versions as cv2
                        where cv2.contract_id = cv.contract_id
                    )
                ')
                ->whereBetween('cv.date', $period)
                ->selectRaw('c.*')
                ->get();

            foreach ($contracts as $contract) {
                $client = Client::find($contract->client_id);
                $clientUrl = "https://lk.ege-centr.ru/clients/{$client->id}";
                $row = [$contract->id, $clientUrl, $client->heard_about_us?->value];
                $requests = $client->requests;
                if (count($requests) === 0) {
                    $row = array_merge($row, ['', '', '']);
                }
                foreach ($requests as $index => $request) {
                    if ($index > 0) {
                        $result[] = $row;
                        $row = [$contract->id, $clientUrl, $client->heard_about_us?->value];
                    }
                    $requestUrl = "https://lk.ege-centr.ru/requests/{$request->id}";
                    $row[] = $requestUrl;
                    $row[] = $request->source;
                    $row[] = $request->direction?->value;
                }
                $result[] = $row;
            }

            $tsv = '';
            foreach ($result as $row) {
                $tsv .= implode("\t", $row)."\n";
            }

            $filename = uniqid().'.tsv';
            Storage::put("crm/other/$filename", $tsv);
            $this->line("\n".cdn('other', $filename));
        }

    }
}
