<?php

namespace App\Console\Commands\Transfer;

use App\Enums\ClientPaymentMethod;
use App\Enums\ContractPaymentMethod;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Client payments & contract payments
 */
class TransferClientPayments extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:client-payments';
    protected $description = 'Transfer client-payments';

    public function handle()
    {
        DB::table('client_payments')->delete();
        DB::table('contract_payments')->delete();
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
            if ($isContractPayment) {
                DB::table('contract_payments')->insert([
                    'sum' => $p->sum,
                    'date' => $p->date,
                    'method' => ContractPaymentMethod::fromOld($p->method)->name,
                    'is_confirmed' => $p->is_confirmed,
                    'is_return' => $p->type === 'return',
                    'contract_id' => $p->entity_id,
                    'card_number' => $p->card_number,
                    'pko_number' => $p->bill_number,
                    'user_id' => $this->getUserId($p->created_email_id),
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]);
            } else {
                DB::table('client_payments')->insert([
                    'sum' => $p->sum,
                    'date' => $p->date,
                    'year' => $p->year,
                    'method' => ClientPaymentMethod::fromOld($p->method)->name,
                    'client_id' => $p->entity_id,
                    'purpose' => $p->category === 'ege_trial' ? 'Пробник' : 'Профориентация',
                    'is_confirmed' => $p->is_confirmed,
                    'is_return' => $p->type === 'return',
                    'card_number' => $p->card_number,
                    'pko_number' => $p->bill_number,
                    'company' => $p->contract_type,
                    'user_id' => $this->getUserId($p->created_email_id),
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]);
            }
            $bar->advance();
        }
        $bar->finish();
    }
}
