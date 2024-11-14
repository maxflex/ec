<?php

namespace App\Console\Commands\Transfer;

use App\Enums\Branch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferClients extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:clients';
    protected $description = 'Transfer clients';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
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
                'user_id' => $this->getUserId($c->created_email_id),
                'head_teacher_id' => $this->nullify($c->head_teacher_id),
                'passport' => $c->series ? json_encode([
                    'series' => $c->series,
                    'number' => $c->number,
                    'birthdate' => $this->nullify($c->birthdate),
                ]) : null,
                'is_remote' => $c->is_remote,
                'created_at' => $c->created_at,
                'updated_at' => $c->updated_at,
            ]);
            DB::table('client_parents')->insert([
                'id' => $p->id,
                'client_id' => $c->id,
                'first_name' => $this->nullify($p->first_name),
                'last_name' => $this->nullify($p->last_name),
                'middle_name' => $this->nullify($p->middle_name),
                'passport' => $p->series ? json_encode([
                    'series' => $this->nullify($p->series),
                    'number' => $this->nullify($p->number),
                    'address' => $this->nullify($p->address),
                    'code' => $this->nullify($p->code),
                    'issued_date' => $this->nullify($p->issued_date),
                    'issued_by' => $this->nullify($p->issued_by),
                    'fact_address' => $this->nullify($p->fact_address),
                ]) : null,
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
    }
}
