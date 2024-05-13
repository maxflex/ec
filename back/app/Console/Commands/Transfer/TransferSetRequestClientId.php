<?php

namespace App\Console\Commands\Transfer;

use App\Models\Client;
use App\Models\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferSetRequestClientId extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:set-request-client-id';
    protected $description = 'Set client_id in "requests" table';

    public function handle()
    {
        DB::table('requests')->update(['client_id' => null]);
        $requestIds = DB::table('requests')->pluck('id');
        $bar = $this->output->createProgressBar(count($requestIds));
        foreach ($requestIds as $id) {
            $requestPhones = DB::table('phones')
                ->where('entity_type', Request::class)
                ->where('entity_id', $id)
                ->pluck('phone');
            $clientIds = DB::table('phones')
                ->where('entity_type', Client::class)
                ->whereIn('phone', $requestPhones)
                ->pluck('entity_id');
            if ($clientIds->count()) {
                DB::table('requests')->whereId($id)->update([
                    'client_id' => $clientIds->first()
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
