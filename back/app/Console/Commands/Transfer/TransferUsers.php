<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class TransferUsers extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:users';
    protected $description = 'Transfer users';

    public function handle()
    {
        DB::table('users')->delete();
        $admins = DB::connection('egecrm')
            ->table('admins')
            ->get();
        $bar = $this->output->createProgressBar($admins->count());
        foreach ($admins as $admin) {
            DB::table('users')->insert([
                'id' => $admin->id,
                'first_name' => $this->nullify($admin->first_name),
                'last_name' => $this->nullify($admin->last_name),
                'middle_name' => $this->nullify($admin->middle_name),
                'created_at' => $admin->created_at,
                'updated_at' => now(),
            ]);
            $bar->advance();
        }
        $bar->finish();
    }
}
