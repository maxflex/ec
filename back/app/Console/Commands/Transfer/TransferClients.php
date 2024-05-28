<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Branch;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferClients extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:clients';
    protected $description = 'Transfer clients';

    public function handle()
    {
        DB::table('client_parents')->delete();
        DB::table('clients')->delete();
        $clients = DB::connection('egecrm')
            ->table('clients')
            ->get();
        $parents = DB::connection('egecrm')
            ->table('representatives')
            ->get()
            ->keyBy('client_id');
        $bar = $this->output->createProgressBar($clients->count());
        foreach ($clients as $c) {
            $p = $parents[$c->id];
            DB::table('clients')->insert([
                'id' => $c->id,
                'first_name' => $c->first_name,
                'last_name' => $c->last_name,
                'middle_name' => $c->middle_name,
                'branches' => $this->mapEnum($c->branches, Branch::class),
                'birthdate' => $this->nullify($c->birthdate),
                'user_id' => $this->getUserId($c->created_email_id),
                'head_teacher_id' => $this->nullify($c->head_teacher_id),
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ]);
            DB::table('client_parents')->insert([
                'id' => $p->id,
                'client_id' => $c->id,
                'first_name' => $this->nullify($p->first_name),
                'last_name' => $this->nullify($p->last_name),
                'middle_name' => $this->nullify($p->middle_name),
                'passport_series' => $this->nullify($p->series),
                'passport_number' => $this->nullify($p->number),
                'passport_address' => $this->nullify($p->address),
                'passport_code' => $this->nullify($p->code),
                'passport_issued_date' => $this->nullify($p->issued_date),
                'passport_issued_by' => $this->nullify($p->issued_by),
                'fact_address' => $this->nullify($p->fact_address),
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
