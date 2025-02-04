<?php

namespace App\Console\Commands\Once;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateReportsCommand extends Command
{
    protected $signature = 'once:update-reports';

    protected $description = 'Command description';

    public function handle(): void
    {
        $data = [
            29669 => 'mathBaseExternal',
            30411 => 'mathBaseExternal',
            31086 => 'mathBaseExternal',
            32007 => 'mathBaseExternal',
            32647 => 'mathBaseExternal',
            33404 => 'mathBaseExternal',
            34833 => 'mathBaseSchool10',
            39549 => 'mathBaseExternal',
            41703 => 'mathBaseExternal',
            42166 => 'mathBaseExternal',
            43463 => 'mathProfSchool10',
            43886 => 'mathBaseExternal',
        ];

        foreach ($data as $id => $program) {
            DB::table('reports')->whereId($id)->update([
                'program' => $program
            ]);
        }
    }
}
