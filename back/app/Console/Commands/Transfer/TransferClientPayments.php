<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ClientPaymentMethod;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferClientPayments extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-payments';
    protected $description = 'Transfer client-payments';

    public function handle()
    {
        DB::table('client_payments')->delete();
        $items = DB::connection('egecrm')
            ->table('payments')
            ->whereIn('entity_type', [
                ET_CLIENT,
                ET_CONTRACT,
            ])
            ->where('category', '<>', 'lunch')
            ->get();
        $bar = $this->output->createProgressBar($items->count());
        foreach ($items as $p) {
            $isContractPayment = $p->entity_type === ET_CONTRACT;
            DB::table('client_payments')->insert([
                'sum' => $p->sum,
                'date' => $p->date,
                'year' => $p->year,
                'method' => ClientPaymentMethod::getFromOld($p->method)->name,
                'type' => $p->type,
                'is_confirmed' => $p->is_confirmed,
                'entity_type' => $isContractPayment ? Contract::class : Client::class,
                'entity_id' => $p->entity_id,
                'purpose' => $isContractPayment ? null : ($p->category === 'ege_trial' ? 'Пробник' : 'Профориентация'),
                'extra' => $p->bill_number ?? $p->card_number,
                'company' => $p->contract_type,
                'user_id' => $this->getUserId($p->created_email_id),
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
