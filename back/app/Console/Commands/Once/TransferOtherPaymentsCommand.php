<?php

namespace App\Console\Commands\Once;

use App\Models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TransferOtherPaymentsCommand extends Command
{
    protected $signature = 'once:transfer-other-payments';

    protected $description = 'Command description';

    public function handle(): void
    {
        DB::table('other_payments')->truncate();
        $clientPayments = DB::table('client_payments')->get();
        $clients = Client::with('representative')
            ->whereIn('id', $clientPayments->pluck('client_id')->unique())
            ->get()
            ->keyBy('id');

        $bar = $this->output->createProgressBar(count($clientPayments));
        $noContract = collect();

        foreach ($clientPayments as $clientPayment) {
            $client = $clients[$clientPayment->client_id];
            $hasContract = $client->contracts()->exists();

            DB::table('other_payments')->insert([
                'method' => $clientPayment->method,
                'sum' => $clientPayment->sum,
                'date' => $clientPayment->date,
                'purpose' => $clientPayment->purpose,
                'card_number' => $clientPayment->card_number,
                'pko_number' => $clientPayment->pko_number,
                'is_confirmed' => $clientPayment->is_confirmed,
                'is_return' => $clientPayment->is_return,
                'user_id' => $clientPayment->user_id,
                'created_at' => $clientPayment->created_at,
                'updated_at' => $clientPayment->updated_at,
                'first_name' => $client->first_name ?: null,
                'last_name' => $client->last_name ?: null,
                'middle_name' => $client->middle_name ?: null,
            ]);

            // если нет договора – сносим клиента
            if (! $hasContract) {
                $noContract->push($client->id);
            }

            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL.'Cleaning up...'.PHP_EOL);

        DB::table('client_payments')->truncate();

        $noContract = $noContract->unique();
        $bar = $this->output->createProgressBar($noContract->count());

        foreach ($noContract as $clientId) {
            DB::table('client_parents')->where('client_id', $clientId)->delete();
            DB::table('phones')
                ->where('entity_type', Client::class)
                ->where('entity_id', $clientId)
                ->delete();
            DB::table('comments')
                ->where('entity_type', Client::class)
                ->where('entity_id', $clientId)
                ->delete();
            DB::table('clients')->where('id', $clientId)->delete();
            $bar->advance();
        }
        $bar->finish();
    }
}
