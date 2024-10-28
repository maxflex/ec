<?php

namespace App\Console\Commands\Transfer;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TransferUsers extends Command
{
    use TransferTrait;

    protected $signature = 'app:transfer:users';
    protected $description = 'Transfer users';

    public function handle()
    {
        Schema::disableForeignKeyConstraints();
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
                'created_at' => $admin->created_at,
                'updated_at' => now(),
            ]);
            $bar->advance();
        }
        $bar->finish();
        Schema::enableForeignKeyConstraints();
        // Hardcode: active certain users
        DB::table('users')->whereIn('id', [1, 5, 12])->update([
            'is_active' => 1,
            'is_call_notifications' => 1,
        ]);
    }
}
